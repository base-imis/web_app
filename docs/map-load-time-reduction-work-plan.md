# IMIS Map Load Time Reduction Work Plan

## Purpose

The current map page takes a long time to become usable because it does too much work during the first page load. This plan explains the practical moves to reduce map load time in `C:\xampp\htdocs\lang_web_app`, based on the current `MapsController`, `MapsService`, map layout, and map Blade implementation.

The target is to make the map visible and interactive faster without removing existing map tools.

## Current Load-Time Problems

### 1. The map Blade is too large

`resources/views/maps/index.blade.php` is approximately 555 KB and 9,814 lines. It contains page markup, map configuration, tool markup, overlay configuration, event handlers, AJAX handlers, print logic, export logic, drawing logic, and search logic in one file.

Because most JavaScript is inline inside the Blade file, the browser cannot cache the map logic independently. Every map page request sends a large HTML response again.

### 2. The map layout loads heavy assets upfront

`resources/views/layouts/maps.blade.php` loads several large scripts/styles before or during map startup:

- `public/js/app.js`: approximately 10.57 MB
- `public/css/app.css`: approximately 2.12 MB
- `public/js/ol-ext.min.js`: approximately 692 KB
- `public/js/ol.js`: approximately 543 KB
- Google Maps API
- Chart.js from CDN

Some of these assets are not needed for the first visible map render.

### 3. Google Maps loads even when the user does not select a Google base map

The layout loads:

`https://maps.google.com/maps/api/js?key=...`

on every map page load. However, the default map is OpenLayers. Google Maps is only needed when the user selects a Google base layer such as Google Streets, Hybrid, Satellite, or Terrain.

This should be lazy-loaded only when needed.

### 4. Many map layers are initialized immediately

The map startup script builds `mLayer`, loops through all allowed overlays, creates OpenLayers WMS layer objects, adds them to the map, builds checkbox HTML, builds export options, and builds feature-info options.

After that, the page immediately turns on default layers:

- `buildings_layer`
- `wardboundary_layer`
- `citypolys_layer`
- `roads_width_zoom_layer`

The building layer is especially expensive because it represents a large spatial dataset.

### 5. Backend dropdown data is fetched on every map page load

`app/Services/Maps/MapsService.php::mapsIndex()` fetches several mostly-static dropdown/config values before returning the map page:

- building functional uses
- building use categories
- wards
- due years
- application years
- structure types
- road hierarchy
- road surface types
- city boundary bbox

Most of this data changes rarely and can be cached.

### 6. Some interaction tools run heavy spatial queries after user action

Several map tools use expensive spatial operations such as `ST_Buffer`, `ST_Union`, `ST_Difference`, `ST_Intersects`, `ST_AsText`, and `ORDER BY RANDOM()`.

These do not necessarily slow the first load, but they cause slow tool responses after the map opens.

## Recommended Fix Moves

## Move 1: Build and serve production assets

### What to do

Run the production frontend build and Laravel optimization commands in deployment:

```bash
npm run production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set production environment values:

```env
APP_ENV=production
APP_DEBUG=false
```

### Why this matters

The current local environment shows `APP_ENV=local` and `APP_DEBUG=true`. The current compiled `public/js/app.js` and `public/css/app.css` are very large. Production builds should reduce asset size and improve browser parsing time.

### Files involved

- `.env`
- `webpack.mix.js`
- `public/js/app.js`
- `public/css/app.css`

### Risk

Low. This is deployment/build configuration, but it should be tested because minification can reveal JavaScript errors hidden in development builds.

### QA

- Open `/maps`.
- Confirm map loads.
- Confirm left and right sidebars still open.
- Confirm base layer selector works.
- Confirm overlay checkboxes work.
- Confirm browser console has no JavaScript errors.

## Move 2: Stop loading Google Maps on initial page load

### What to do

Remove the upfront Google Maps script from `resources/views/layouts/maps.blade.php`:

```html
<script async defer src="https://maps.google.com/maps/api/js?key=..."></script>
```

Replace it with a lazy loader function in the map JavaScript:

```js
let googleMapsLoadingPromise = null;

function loadGoogleMapsApi() {
    if (window.google && window.google.maps) {
        return Promise.resolve();
    }

    if (googleMapsLoadingPromise) {
        return googleMapsLoadingPromise;
    }

    googleMapsLoadingPromise = new Promise(function(resolve, reject) {
        const script = document.createElement('script');
        script.src = googleMapsApiUrl;
        script.async = true;
        script.defer = true;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });

    return googleMapsLoadingPromise;
}
```

When the selected base layer is Google, call the loader first:

```js
loadGoogleMapsApi().then(function () {
    if (!gmap) {
        initMap();
    }
    gmap.setMapTypeId(bLayer[selected].mapType);
    $('#gmap').css('visibility', 'visible');
});
```

### Why this matters

Users who never switch to a Google base map should not pay the Google Maps network and initialization cost.

### Files involved

- `resources/views/layouts/maps.blade.php`
- `resources/views/maps/index.blade.php`
- later target: extracted map JS file

### Risk

Medium. Existing code assumes `google.maps` is available in `initMap()`, `onWindowResize()`, and `updateMapSize()`.

### QA

- Open `/maps` and confirm the map loads without Google Maps errors.
- Select OpenStreetMap or Bing base layers.
- Select Google Streets, Hybrid, Satellite, and Terrain.
- Collapse/expand sidebar while Google layer is active.
- Confirm `updateMapSize()` does not call `google.maps.event.trigger()` before Google is loaded.

## Move 3: Split map JavaScript out of Blade

### What to do

Create a dedicated map bundle such as:

- `resources/js/maps/index.js`
- `resources/js/maps/layers.js`
- `resources/js/maps/tools.js`
- `resources/js/maps/search.js`
- `resources/js/maps/print.js`

Compile it to:

- `public/js/maps/index.js`

Keep only server-generated config in Blade:

```html
<script>
window.IMIS_MAP_CONFIG = {
    routes: {
        extent: "{{ url('maps/extent') }}",
        searchAutoComplete: "{{ url('maps/search-auto-complete') }}",
        drainBuildings: "{{ url('maps/drain-buildings') }}"
    },
    geoserver: {
        workspace: "{{ config('constants.GEOSERVER_WORKSPACE') }}",
        url: "{{ config('constants.GEOSERVER_URL') }}",
        authKey: "{{ config('constants.AUTH_KEY') }}"
    },
    bbox: @json($bboxstring),
    permissions: @json($mapPermissions)
};
</script>
```

Move large static layer definitions out of Blade where possible. Use permission flags to decide which layers to expose.

### Why this matters

External JS can be cached by the browser. The HTML response becomes smaller, Blade rendering becomes simpler, and the map code becomes easier to maintain.

### Files involved

- `resources/views/maps/index.blade.php`
- `resources/js/maps/*`
- `webpack.mix.js`
- `resources/views/layouts/maps.blade.php`

### Risk

Medium to high because the current script is large and tightly coupled to Blade variables.

### Recommended sequence

1. Extract only helper functions first.
2. Extract layer definitions next.
3. Extract tool handlers in groups.
4. Keep route/config injection in Blade.
5. Test after each extraction.

### QA

- Compare current and refactored map behavior with the same user role.
- Confirm permissions still hide/show the same layers and tools.
- Confirm search, filters, export, print, drawing, and feature info still work.
- Confirm repeated page refresh uses cached JS in browser network tab.

## Move 4: Create a lightweight map startup path

### What to do

Change the default startup behavior so the first render only shows essential city context:

- city polygon or municipal boundary
- ward boundary
- optional road-width layer only after zoom threshold

Delay `buildings_layer` until:

- user checks the building layer, or
- zoom is above the configured threshold, or
- a specific search/feature action needs it.

Current startup around `showLayer()` turns on several layers automatically. Review whether `buildings_layer` must be visible by default.

### Why this matters

Large building WMS rendering is one of the most likely GeoServer costs. Users can see the map faster if the first view starts with lighter context layers.

### Files involved

- `resources/views/maps/index.blade.php`
- later target: extracted map layer startup file

### Risk

Medium. Some users may expect buildings to appear immediately. If this is a product requirement, keep buildings hidden until zoom while showing a clear checked/disabled state.

### QA

- Open `/maps` and measure time until first map paint.
- Confirm city and ward boundaries appear.
- Zoom in beyond level 14 and confirm buildings appear if the checkbox is selected.
- Search for a BIN and confirm the building still appears/zooms correctly.

## Move 5: Cache map index dropdown/config data

### What to do

Wrap mostly-static `mapsIndex()` queries in `Cache::remember()`.

Example:

```php
$pickWardResults = Cache::remember('maps.pick_wards', 3600, function () {
    return DB::select('select distinct ward from layer_info.wards order by ward asc');
});
```

Suggested cache keys:

- `maps.functional_uses`
- `maps.use_categories`
- `maps.wards`
- `maps.due_years`
- `maps.pick_wards`
- `maps.pick_application_years`
- `maps.structure_types`
- `maps.road_hierarchy`
- `maps.road_surface_types`
- `maps.city_bbox`

Add cache invalidation when related admin data changes, or use a safe TTL such as 1 hour initially.

### Why this matters

The map page should not hit many lookup tables on every request when most values rarely change.

### Files involved

- `app/Services/Maps/MapsService.php`
- optionally related create/update/delete services for invalidation

### Risk

Low to medium. Stale dropdown values are possible until TTL expires.

### QA

- Load map once, then reload and compare database query count/time.
- Update a road hierarchy/surface type in data and confirm cache refresh behavior is acceptable.
- Confirm all dropdowns still populate correctly.

## Move 6: Reduce unnecessary initial library loading

### What to do

Audit whether `Chart.js`, `ol-ext`, and all AdminLTE/global JS in `app.js` are needed for initial map use.

Possible changes:

- Load Chart.js only when a chart/report tool opens.
- Load `ol-ext.min.js` only when a tool depending on it is used.
- Build a smaller map-specific JS bundle instead of using the full global `app.js`.

### Why this matters

The browser spends time downloading, parsing, and executing JavaScript before the map becomes responsive. Reducing the first bundle size improves real and perceived load time.

### Files involved

- `resources/views/layouts/maps.blade.php`
- `webpack.mix.js`
- `resources/js/app.js`
- new `resources/js/maps/index.js`

### Risk

Medium. Existing UI widgets may depend on global imports from `app.js`.

### QA

- Confirm date pickers, multiple-select controls, autocomplete, sidebars, modals, alerts, and map tools still work.
- Use browser network tab to confirm reduced initial JS payload.

## Move 7: Tune GeoServer layer delivery

### What to do

For default or frequently used layers, prefer tiled WMS where appropriate:

- city boundary
- ward boundary
- roads
- buildings at high zoom

Confirm GeoServer caching is enabled for expensive layers through GeoWebCache if available.

Review whether `ImageWMS` layers should become `TileWMS` for better tile caching and progressive rendering.

### Why this matters

Single image WMS requests can block the visible map until the full image is rendered. Tiled layers can load progressively and cache better.

### Files involved

- map layer initialization in `resources/views/maps/index.blade.php`
- GeoServer layer configuration
- GeoWebCache configuration

### Risk

Medium. Labeling, styling, and layer ordering can look slightly different with tiled WMS.

### QA

- Compare visual output before and after for each converted layer.
- Pan and zoom repeatedly to confirm tiles cache and redraw smoothly.
- Confirm printed/exported map output still looks acceptable.

## Move 8: Optimize heavy spatial queries used by map tools

### What to do

Review expensive queries after the first-load fixes are complete. Focus on:

- `ST_Buffer`
- `ST_Union`
- `ST_Difference`
- `ST_Intersects`
- `ST_AsText`
- `ORDER BY RANDOM()`

Specific candidates:

- `getRoadInaccesibleISummaryInfo()`
- `getWaterbodyInaccesibleISummaryInfo()`
- `buildingsPopContentPolygon()`
- `getPointBufferBuildingsSummary()`
- due building queries with `ORDER BY RANDOM()`

Use spatial indexes and `EXPLAIN ANALYZE` for each slow query. Avoid converting geometry to text until the final response requires it.

### Why this matters

These tools may not block the first map paint, but they create slow user interactions after the map has loaded.

### Files involved

- `app/Services/Maps/MapsService.php`
- `app/Http/Controllers/MapsController.php`
- PostGIS indexes/functions

### Risk

Medium to high. Spatial query changes must be tested carefully because geometry results affect reports and map output.

### QA

- Record before/after query time with realistic data.
- Compare result counts before/after.
- Verify map highlights and exported reports still match.

## Suggested Implementation Phases

### Phase 1: Quick wins

Goal: reduce first-load cost without major refactor.

Tasks:

1. Ensure production asset build is used.
2. Disable debug in deployed environment.
3. Add Laravel config/route/view cache.
4. Lazy-load Google Maps.
5. Guard Google resize calls when Google is not loaded.
6. Cache `mapsIndex()` lookup data.

Expected impact: noticeable faster first load with low code risk.

### Phase 2: Frontend structure cleanup

Goal: make the map code cacheable and maintainable.

Tasks:

1. Extract inline map JS from Blade into external JS modules.
2. Keep only config/routes/permission JSON in Blade.
3. Split map tools by feature area.
4. Build a map-specific JS bundle.
5. Remove unnecessary global libraries from the initial map layout.

Expected impact: smaller HTML, better browser caching, easier future optimization.

### Phase 3: Map layer and GeoServer tuning

Goal: reduce map rendering work.

Tasks:

1. Reduce default visible layers.
2. Delay building layer until zoom/user action.
3. Convert suitable WMS layers to tiled/cached delivery.
4. Enable/verify GeoWebCache for heavy layers.
5. Review style complexity for building and road layers.

Expected impact: faster first visual map render and smoother pan/zoom.

### Phase 4: Spatial query optimization

Goal: speed up heavy map tools after the map opens.

Tasks:

1. Profile each slow map tool endpoint.
2. Add/verify spatial indexes.
3. Avoid repeated `ST_Union`/`ST_Buffer` work where cached/precomputed geometry is possible.
4. Replace `ORDER BY RANDOM()` in large result sets.
5. Return GeoJSON or minimal fields instead of full WKT where possible.

Expected impact: faster reports, filters, buffers, and selection tools.

## Acceptance Criteria

The work can be considered successful when:

- `/maps` becomes visually usable faster than the current baseline.
- Initial HTML response is significantly smaller after JS extraction.
- Initial JS payload is reduced or browser-cached effectively.
- Google Maps is not requested unless a Google base map is selected.
- Default map layers render without blocking the full page.
- Map dropdowns still populate correctly.
- Existing tools still work: search, layer toggles, filters, feature info, print, export, drawing tools, buffer tools, and report tools.
- Browser console has no startup errors.

## Measurement Plan

Before and after each phase, record:

- page HTML response size
- total JS/CSS transferred
- number of network requests before map is usable
- time to first map paint
- time until map controls respond
- slowest GeoServer WMS request
- slowest Laravel map endpoint request
- database query count/time for `MapsService::mapsIndex()`

Recommended tools:

- browser DevTools Network tab
- browser DevTools Performance tab
- Laravel query logging in local/staging
- PostgreSQL `EXPLAIN ANALYZE`
- GeoServer request logs

## Developer Notes

Do not start by rewriting the full map page at once. The current map has many tools and permission-controlled sections. Start with low-risk startup improvements, then extract/refactor in small batches.

The safest first code changes are:

1. cache `mapsIndex()` lookup data;
2. lazy-load Google Maps;
3. guard Google-specific calls;
4. reduce default visible heavy layers if product owners agree.

The highest-value structural change is moving inline map JavaScript out of `index.blade.php` into cacheable map-specific assets.

