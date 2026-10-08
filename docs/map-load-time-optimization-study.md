# IMIS Map Load Time Optimization Study

## Scope

This study reviews why the current IMIS map page loads slowly on `master` and how to reduce the load time safely. The review is based on the current Laravel map route surface, `MapsController`, `MapsService::mapsIndex()`, `resources/views/layouts/maps.blade.php`, and `resources/views/maps/index.blade.php`.

The main target page is the authenticated map page rendered by `MapsController@index`, which delegates the first page response to `MapsService::mapsIndex()`.

## Executive Summary

The map is slow because the first page load does too much work before the user can interact with the map.

The main problems are:

1. The map Blade response is very large and contains most map JavaScript inline.
2. The map layout loads heavy global assets up front, including a 10.57 MB `app.js`, 2.12 MB `app.css`, OpenLayers, ol-ext, Chart.js, and Google Maps.
3. Google Maps is loaded on every map page visit even though the default map path is OpenLayers and Google layers are only needed if the user selects a Google base layer.
4. The startup script creates WMS layer objects for all permitted overlays, appends many checkbox/select options, and then turns on default layers immediately.
5. `MapsService::mapsIndex()` runs many lookup queries on every request even though most values are relatively static.
6. Several map tools run expensive PostGIS operations after startup, which makes interactions slow even if the initial page becomes faster.

The safest first phase is:

1. Use production frontend assets and Laravel caches in deployment.
2. Lazy-load Google Maps only when a Google base layer is selected.
3. Cache `MapsService::mapsIndex()` lookup data.
4. Reduce the default visible layers so heavy layers do not request WMS images before the user needs them.

The highest-value structural phase is to extract the inline map JavaScript from Blade into cacheable map-specific JS bundles.

## Current Evidence From Code

## Key Code Files

These are the main files involved in the slow map startup:

| File                                        | Why it matters                                                                                                                                                                                                                                                           |
| ------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `resources/views/maps/index.blade.php`    | Main unmanaged/large map page. It contains the map markup, overlay setup, inline JavaScript, event handlers, WMS layer setup, filters, export, print, search, drawing, and report tool logic in one file. This is the biggest maintainability and browser payload issue. |
| `resources/views/layouts/maps.blade.php`  | Loads the map page assets, including Google Maps, OpenLayers, ol-ext, global app assets, and Chart.js. This controls much of the initial browser cost.                                                                                                                   |
| `app/Http/Controllers/MapsController.php` | Entry controller for`/maps` and many AJAX/map tool endpoints.                                                                                                                                                                                                          |
| `app/Services/Maps/MapsService.php`       | Provides`mapsIndex()` lookup data and many heavier spatial-query-backed tool responses.                                                                                                                                                                                |
| `webpack.mix.js`                          | Current frontend build entry. It builds the global app bundle but does not yet define a map-specific bundle.                                                                                                                                                             |

### Large Initial Payload

Current file sizes in the workspace:

| File                                       |             Size |
| ------------------------------------------ | ---------------: |
| `resources/views/maps/index.blade.php`   |    635,971 bytes |
| `resources/views/layouts/maps.blade.php` |      5,420 bytes |
| `public/js/app.js`                       | 10,570,901 bytes |
| `public/css/app.css`                     |  2,116,890 bytes |
| `public/js/ol.js`                        |    543,365 bytes |
| `public/js/ol-ext.min.js`                |    692,135 bytes |

This means the browser receives a large HTML document plus large CSS/JS bundles before the map is fully usable.

### Heavy Assets Loaded Up Front

`resources/views/layouts/maps.blade.php` loads:

- OpenLayers CSS and JS.
- ol-ext CSS and JS.
- global `app.css`.
- global `app.js`.
- `map_layout.js`.
- Google Maps API.
- Chart.js from CDN.

The important issue is that some of these are not always needed for the first useful map render. Google Maps and Chart.js are clear examples.

### Google Maps Always Loads

The map layout includes the Google Maps script directly:

```html
<script async defer src="https://maps.google.com/maps/api/js?key={{ Config::get('constants.API_KEY_GOOGLE') }}"></script>
```

But the Google base layers are only one option in the base layer selector. The map code defines Google Streets, Hybrid, Satellite, and Terrain as selectable base layers, while non-Google layers can be used without Google Maps.

Because `initMap()` directly calls `new google.maps.Map(...)`, the current code assumes Google Maps is already available. This blocks lazy loading until we add guards around Google-only functions.

### All Overlay WMS Layers Are Constructed During Startup

`resources/views/maps/index.blade.php` loops through `mLayer` and creates an `ol.layer.Image` with `ol.source.ImageWMS` for each permitted overlay. Each layer is then added to the OpenLayers map, even though it starts as `visible: false`.

This still creates client-side startup work for every available overlay and makes the map initialization heavy. The same block also builds overlay checkbox HTML, export overlay options, feature-info options, and style selectors.

### Heavy Default Layers Are Enabled Immediately

After building the overlay controls, the map script automatically turns on:

- `buildings_layer`
- `wardboundary_layer`
- `citypolys_layer`
- `roads_width_zoom_layer`

The building layer is already included in `conditionalLayers`, so it only becomes visible when zoom is greater than 14. However, it is still checked by default and participates in startup logic. If the initial zoom or user flow crosses the threshold early, it can trigger expensive GeoServer rendering for a large building dataset.

### Backend Lookup Queries Run On Every Map Page Request

`MapsService::mapsIndex()` fetches many values before rendering the Blade:

- building functional uses
- building use categories
- wards
- due years
- distinct ward list
- distinct application years
- structure types used by buildings
- road hierarchy values
- road surface type values
- all road code/name options
- city boundary bbox
- drain cover types
- operational treatment plants
- drain surface types
- sewer locations

Most of these are lookup/config lists. They do not need to be queried fresh on every page load.

### PostGIS Tool Queries Are Expensive

Several map interactions use expensive operations:

- `ST_Buffer`
- `ST_Union`
- `ST_Difference`
- `ST_Intersects`
- `ST_AsText`
- `ORDER BY RANDOM()`
- `pgr_drivingdistance`

These operations are not necessarily the first page-load bottleneck, but they explain slow responses after the map is open, especially for buffer, inaccessible building, water body, due building, and isochrone tools.

## Root Cause

The problem is not one single slow line. The map page has accumulated too many startup responsibilities:

1. Render all map UI.
2. Render all tool modals/forms.
3. Inline almost all map JavaScript.
4. Load global frontend assets.
5. Load optional third-party APIs.
6. Build all permitted WMS layer objects.
7. Build all overlay controls.
8. Enable default layers.
9. Fetch backend lookup data.

Because these happen together, the user pays the cost before reaching the first useful map interaction.

## Recommended Solution Plan

## Phase 1: Quick Wins

### 1. Build production frontend assets

Use the existing script:

```bash
npm run production
```

Then use Laravel deployment caches:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Production environment should use:

```env
APP_ENV=production
APP_DEBUG=false
```

Expected impact: smaller JS/CSS, faster parsing, less Laravel runtime overhead.

Risk: low. Needs regression testing because minification can expose existing JavaScript assumptions.

### 2. Lazy-load Google Maps

Remove the Google Maps script from initial layout load. Add a loader function that injects the script only when a selected base layer has `type: 'google'`.

Also guard these functions so they only run after Google is loaded:

- `initMap()`
- `onCenterChanged()`
- `onResolutionChanged()`
- `onWindowResize()`
- `updateMapSize()`

Expected impact: users who never choose Google base maps avoid the Google Maps network and initialization cost.

Risk: medium. Current code assumes `google.maps` is globally available.

### 3. Cache `mapsIndex()` lookup data

Use `Cache::remember()` around mostly-static lookup queries in `MapsService::mapsIndex()`.

Suggested initial TTL: 1 hour.

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
- `maps.road_codes`
- `maps.city_bbox`
- `maps.drain_cover_types`
- `maps.treatment_plants`
- `maps.drain_surface_types`
- `maps.sewer_locations`

Expected impact: lower database work on every `/maps` request.

Risk: low to medium. Data may be stale until TTL expires.

### 4. Reduce default visible layers

Review whether `buildings_layer` must be checked on first load. If not, start with only:

- `citypolys_layer`
- `wardboundary_layer`

Then let users enable buildings, roads, or other overlays as needed.

Expected impact: fewer first-load GeoServer/WMS requests and less rendering pressure.

Risk: medium. Product users may expect buildings to appear by default.

## Phase 2: Frontend Structure

### 5. Extract map JavaScript out of Blade

Create a map-specific JS structure:

```text
resources/js/maps/index.js
resources/js/maps/layers.js
resources/js/maps/google-base-layer.js
resources/js/maps/tools.js
resources/js/maps/search.js
resources/js/maps/export.js
resources/js/maps/print.js
```

Keep only server-generated config in Blade:

```html
<script>
window.IMIS_MAP_CONFIG = {
    geoserver: {
        workspace: "...",
        url: "...",
        authKey: "..."
    },
    bbox: "...",
    routes: {...},
    permissions: {...}
};
</script>
```

Expected impact: smaller HTML, better browser caching, easier optimization and testing.

Risk: medium to high. The current file is large and tightly coupled to Blade variables and permission directives.

Recommended approach: extract in small groups, not all at once.

### 6. Build a map-specific asset bundle

`webpack.mix.js` currently builds only the global app bundle. Add a separate map bundle so the map can load only what it needs.

Example target:

```js
mix.js('resources/js/maps/index.js', 'public/js/maps');
```

Expected impact: the map page stops depending on the full global `app.js` for everything.

Risk: medium. Some existing widgets may depend on globals from `app.js`.

## Phase 3: GeoServer And Layer Delivery

### 7. Prefer tiled/cached WMS for suitable overlays

Current startup creates `ol.source.ImageWMS` for normal overlays with `'TILED': true`. For frequently used base/context overlays, consider real `ol.source.TileWMS` plus GeoWebCache where visual behavior allows it.

Candidates:

- municipality boundary
- ward boundary
- roads
- buildings at high zoom

Expected impact: progressive map rendering and cache reuse.

Risk: medium. Labels, styling, and print/export output must be checked.

### 8. Delay WMS layer object creation

Instead of creating all overlay WMS layers at startup, create each layer lazily when the user first checks it.

Expected impact: less JavaScript work at first render.

Risk: medium. Export, feature info, and style selector code currently assumes each `mLayer[key].layer` exists.

## Phase 4: Spatial Query Optimization

### 9. Profile slow map tool endpoints

After first-load work, profile these tools with real data:

- road inaccessible summary
- water body inaccessible summary
- point buffer buildings
- polygon buffer buildings
- due building markers
- toilet isochrone area
- feature-info and extent endpoints

Use:

```sql
EXPLAIN ANALYZE
```

Check spatial indexes on large geometry tables:

- `building_info.buildings.geom`
- `fsm.containments.geom`
- `utility_info.roads.geom`
- `utility_info.drains.geom`
- `utility_info.sewers.geom`
- `layer_info.wards.geom`
- `layer_info.citypolys.geom`
- `layer_info.waterbodys.geom`

Expected impact: faster post-load interactions.

Risk: medium to high. Query changes affect analytical/report output.

### 10. Remove expensive query patterns where possible

Specific improvements:

- Avoid `ORDER BY RANDOM()` on large due-building datasets.
- Avoid repeated `ST_AsText()` unless the frontend truly needs WKT.
- Avoid repeated `ST_Buffer()` inside row filters; compute once in a CTE. 
- Avoid repeated `ST_Union()` for static layers; precompute/cache union geometries.
- Prefer GeoJSON/minimal fields for browser rendering over large WKT payloads.

## Measurement Plan

Measure before and after each phase:

- HTML response size for `/maps`
- total transferred JS/CSS
- number of startup network requests
- time to first map paint
- time until map controls respond
- slowest WMS request
- slowest Laravel map endpoint request
- database query count/time in `MapsService::mapsIndex()`
- browser console errors

Recommended tools:

- Browser DevTools Network tab
- Browser DevTools Performance tab
- Laravel query logging on staging
- PostgreSQL `EXPLAIN ANALYZE`
- GeoServer request logs

## QA Acceptance Criteria

The optimization is acceptable only if:

- `/maps` becomes visually usable faster than baseline.
- Google Maps is not requested until a Google base layer is selected.
- Map opens without JavaScript console errors.
- City/ward context still appears on initial load.
- Layer checkbox toggles still work.
- Building layer still appears correctly when enabled and zoomed in.
- Search and feature-info still work.
- Print/export tools still work.
- Buffer/report tools still return the same counts/results as before.
- Role/permission-controlled layers still show/hide correctly.

## Recommended Implementation Order

1. Capture baseline timings in browser DevTools.
2. Deploy production assets and Laravel caches.
3. Add cache around `MapsService::mapsIndex()` lookup data.
4. Lazy-load Google Maps and guard Google-only calls.
5. Reduce default layer startup, especially `buildings_layer`.
6. Extract map JavaScript into cacheable files in small batches.
7. Create a map-specific frontend bundle.
8. Convert selected WMS layers to tiled/cached delivery.
9. Profile and optimize slow PostGIS endpoints.

## Final Recommendation

Start with Phase 1. It gives the best improvement-to-risk ratio and does not require rewriting the large map page.

Do not start by refactoring all of `resources/views/maps/index.blade.php` at once. The file controls many tools, filters, permissions, exports, and map interactions. A full rewrite would be risky. The better path is to first remove avoidable startup costs, then extract and optimize in controlled batches.