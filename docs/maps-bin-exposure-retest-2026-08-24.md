checkout 

# Map Tools BIN Exposure Re-test

Date: 2026-08-24
Scope: `resources/views/maps/index.blade.php`, `MapsController`, `MapsService`, map routes, GeoServer property requests, and map-triggered exports.

## Result

BIN exposure is confirmed in the current Map module.

The browser receives or sends BIN through:

1. Laravel path and query parameters;
2. Laravel JSON responses;
3. direct GeoServer GetFeatureInfo/WFS responses;
4. map popups and generated Building edit links;
5. CSV, KML, SHP, and Excel outputs.

This is a static-code verification. Runtime HTTP testing could not be completed because `http://localhost:80` refused the connection. `php artisan route:list` was also blocked by an unrelated missing `App\Http\Controllers\FileController`. The confirmed findings below are based on active request/response and rendering code, not comments alone.

## URL-binding-only conclusion

If the scope is strictly “does an actual BIN value appear in a URL?”, only the following map flows require Building URL-binding changes:

| Flow                                          | Effective URL containing a BIN value                                     |
| --------------------------------------------- | ------------------------------------------------------------------------ |
| Building-list Map button                      | `/maps?layer=buildings_layer&field=bin&val={BIN}`                      |
| Building-list Nearest Road button             | `/maps?layer=buildings_layer&field=bin&val={BIN}&action=building-road` |
| Map BIN autocomplete                          | `/maps/search-auto-complete/bin/{BIN-or-prefix}`                       |
| Map Building search by BIN                    | `/maps/search-building/bin/{BIN}`                                      |
| Zoom to searched/opened Building              | `/maps/extent/buildings_layer/bin/{BIN}`                               |
| Nearest road request after opening a Building | `/maps/building-road/bin/{BIN}`                                        |
| Find Containments Connected to Building       | `/maps/building-containment?bin={BIN}`                                 |
| Find Associated Buildings                     | `/maps/getassociated-mainbuilding?bin={BIN}`                           |
| Buildings Using Community Toilets             | `/maps/buildings-toilet-network?bin={BIN}`                             |
| Building popup Edit link                      | `/building-info/buildings/{BIN}/edit`                                  |
| Building popup house-image checks             | `/storage/emptyings/houses/{BIN}.jpg` and `.jpeg`                    |

These should use `building.public_id` at the browser boundary and translate to BIN inside Laravel before existing database joins.

The remaining tools listed below expose BIN in responses, popups, or downloads, but do **not** place a specific BIN value in their request/navigation URL. If the approved scope is URL binding only, they do not require route changes merely because their JSON or export contains BIN.

Also, a GeoServer URL containing `PROPERTYNAME=bin` contains only the column name, not a Building's BIN value. It is data-output exposure, but it is not a record identifier embedded in the URL.

## Confirmed map tools that expose BIN

| Map tool/surface                                        | Exposure channel                                | Confirming evidence                                                                                                                                                                                                                                                                                                                                                                  |
| ------------------------------------------------------- | ----------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Building Search — BIN                                  | autocomplete URL/response and search path/JSON  | BIN is a selectable search field at`resources/views/maps/index.blade.php:81-83`; autocomplete constructs `/maps/search-auto-complete/bin/{keywords}` at lines 1831-1835 and returns BIN at `app/Http/Controllers/MapsController.php:847-875`; search constructs `/maps/search-building/bin/{BIN}` at Blade lines 10632-10635 and returns `bin` at controller lines 793-838 |
| Map link from Building list                             | map query string                                | Building actions generate`/maps?layer=buildings_layer&field=bin&val={BIN}` and nearest-road variant at `app/Services/BuildingInfo/BuildingStructureService.php:1071-1074`                                                                                                                                                                                                        |
| Zoom to Building extent                                 | URL path                                        | `handleZoomToExtent()` creates `/maps/extent/buildings_layer/bin/{BIN}` at `resources/views/maps/index.blade.php:12209-12213`; the map search calls it with returned BIN at lines 10667-10669                                                                                                                                                                                  |
| Find Nearest Road for Building                          | URL path                                        | map entry supplies BIN as`val`; `displayBuildingRoad()` creates `/maps/building-road/bin/{BIN}` at Blade lines 10216-10220; route is `routes/web.php:495`                                                                                                                                                                                                                    |
| Feature Information / Identify — Buildings layer       | direct GeoServer response, popup, link          | GetFeatureInfo explicitly requests`bin` plus owner/contact and other fields at Blade lines 9257-9288; popup labels BIN and creates a BIN-based Building edit link at lines 9893-9901                                                                                                                                                                                               |
| Feature Information — associated Building              | direct GeoServer response/popup                 | Building GetFeatureInfo requests`building_associated_to`; popup labels it “BIN of Main Building” at Blade lines 9486-9487                                                                                                                                                                                                                                                        |
| Feature Information — Containments                     | direct GeoServer response/popup                 | containment property list requests`responsible_bin` at Blade lines 9299 and 11122; popup labels it at lines 9854-9855                                                                                                                                                                                                                                                              |
| Feature Information — PT/CT Toilets                    | direct GeoServer response/popup                 | toilet property list requests`bin` at Blade lines 9355 and 11128                                                                                                                                                                                                                                                                                                                   |
| Find Buildings Connected to Containment                 | Laravel JSON                                    | UI control is at Blade lines 42-43;`MapsService::getContainmentToBuildings()` selects and returns `bin` with geometry at `app/Services/Maps/MapsService.php:159-185`                                                                                                                                                                                                           |
| Find Containments Connected to Building                 | GeoServer response plus Laravel query parameter | UI control is at Blade lines 44-45; Building GetFeatureInfo supplies`properties.bin`, then AJAX sends `bin` to `/maps/building-containment` at lines 7727-7754; service consumes it at `MapsService.php:123-149`                                                                                                                                                             |
| Find Associated Buildings                               | GeoServer response plus Laravel query parameter | UI control is at Blade lines 46-47; GetFeatureInfo supplies BIN and AJAX sends it to`/maps/getassociated-mainbuilding` at lines 7850-7878; service consumes it at `MapsService.php:194-215`                                                                                                                                                                                      |
| Buildings Using Community Toilets                       | GeoServer response plus Laravel query parameter | tool is at Blade line 1157; toilets-layer GetFeatureInfo supplies BIN, then AJAX sends it to`/maps/buildings-toilet-network` at lines 8292-8319; controller consumes BIN at `MapsController.php:1263-1268`                                                                                                                                                                       |
| Applications on map — all/current filters              | Laravel JSON                                    | `getApplicationContainments()` returns `bin` at `MapsService.php:496-533`; the map endpoint is `routes/web.php:501`                                                                                                                                                                                                                                                          |
| Applications by year/month                              | Laravel JSON                                    | `getApplicationContainmentsYearMonth()` returns `bin` at `MapsService.php:547-595`; frontend reads it at Blade lines 11807-11822                                                                                                                                                                                                                                               |
| Applications by date                                    | Laravel JSON                                    | `getApplicationOnDate()` returns `bin` at `MapsService.php:668-707`; frontend reads it at Blade lines 11984-12002                                                                                                                                                                                                                                                              |
| Applications not taken to TP — all/date/year-month     | Laravel JSON                                    | service returns`bin` in `getApplicationNotTP()`, `getApplicationNotTPOnDate()`, and `getApplicationNotTPContainmentsYearMonth()` at `MapsService.php:606-658`, 715-772, and 783 onward; frontend reads BIN at Blade lines 11705-11730 and 11913-11940                                                                                                                      |
| Buildings to Sewer                                      | Laravel JSON                                    | tool is at Blade line 1129;`/maps/drain-buildings` returns `{bin, geom}` at `MapsController.php:551-573`                                                                                                                                                                                                                                                                       |
| Buildings to Road                                       | Laravel JSON and Excel                          | tool is at Blade line 1136; controller returns the service`buildings` collection at `MapsController.php:582-599`; service builds Building results with BIN at `MapsService.php:980-1004`; export is linked at Blade lines 93-103                                                                                                                                               |
| Sewers Potential Buildings                              | Laravel JSON and export                         | tool is at Blade line 1122; controller returns`buildings` from `buildingsPopContentPolygon()` at `MapsController.php:609-627`; that service returns BIN-derived identifiers at `MapsService.php:1205-1226`                                                                                                                                                                   |
| Water Bodies Buffer Summary Information                 | Laravel JSON and Excel                          | tool is at Blade lines 1178-1182; controller returns`buildings` at `MapsController.php:636-653`; service returns BIN-derived `gid` at `MapsService.php:1213-1226`                                                                                                                                                                                                            |
| Wards Summary Information                               | Laravel JSON and Excel                          | tool is at Blade lines 1186-1190; controller returns`buildings` at `MapsController.php:930-945`; the shared service returns BIN-derived `gid` at `MapsService.php:1213-1226`                                                                                                                                                                                                 |
| Road Buffer Summary Information                         | Laravel JSON and Excel                          | tool is at Blade lines 1194-1198; controller returns`buildings` at `MapsController.php:721-738`; the shared service returns BIN-derived `gid` at `MapsService.php:1213-1226`                                                                                                                                                                                                 |
| Point Buffer Summary Information, including multi-point | Laravel JSON and Excel                          | tool is at Blade lines 1202-1207; endpoints return`buildings` at `MapsController.php:663-710`; service returns `{bin, geom}` at `MapsService.php:1035-1105`                                                                                                                                                                                                                  |
| Summary Information Buffer Filter / custom polygon      | Laravel JSON and Excel                          | tool is at Blade lines 1169-1174;`/maps/buffer-polygon-buildings` returns `buildings` at `MapsController.php:907-921`; service returns BIN-derived identifiers at `MapsService.php:1205-1226`                                                                                                                                                                                |
| KML Summary Information                                 | Laravel JSON                                    | `getKmlSummaryInfo()` calls KML Building lookup at `MapsController.php:1372-1398`; the response uses `gid = bin` at `MapsService.php:1112-1191`                                                                                                                                                                                                                              |
| Hard to Reach Buildings                                 | Laravel JSON and Excel                          | tool is at Blade lines 1140-1143; controller returns Building arrays at`MapsController.php:1040-1063` and 1141-1201; service returns `{bin, geom}` at `MapsService.php:1643-1688`                                                                                                                                                                                              |
| Buildings Close to Water Bodies                         | Laravel JSON and Excel                          | tool is at Blade lines 1147-1150; controller returns Building arrays at`MapsController.php:1106-1132`; service returns `{bin, geom}` at `MapsService.php:1700-1723`                                                                                                                                                                                                            |
| Generic layer download/export — Buildings              | direct GeoServer WFS output                     | generic Building property list begins with`bin` at Blade lines 11120-11122                                                                                                                                                                                                                                                                                                         |
| Tax ISS export                                          | direct GeoServer CSV/KML/SHP                    | WFS URL explicitly includes`PROPERTYNAME=tax_code,bin,...` at Blade line 11456                                                                                                                                                                                                                                                                                                     |
| Water Supply ISS export                                 | direct GeoServer CSV/KML/SHP                    | WFS URL explicitly includes`PROPERTYNAME=water_customer_id,bin,...` at Blade line 11527                                                                                                                                                                                                                                                                                            |
| Solid Waste ISS export                                  | direct GeoServer CSV/KML/SHP                    | WFS URL explicitly includes`PROPERTYNAME=swm_customer_id,bin,...` at Blade line 11593                                                                                                                                                                                                                                                                                              |
| Spatial Excel reports                                   | downloaded files                                | Building/owner, Building/containment, point, road, and related export classes select BIN, including`BuildingsListExport.php:55`, `PointBuildingsListExport.php:48`, `BuildingsRoadListExport.php:46`, and `BuildContainExport.php:55-69`                                                                                                                                     |

## Exposure type summary

### BIN exposed in a browser URL

- Building search and autocomplete;
- Building extent/zoom;
- Building nearest-road lookup;
- map links opened from Building list;
- BIN query parameters used by Building-containment, associated-Building, and toilet-network tools.

### BIN exposed in browser-visible JSON

- Building search;
- containment-to-Buildings;
- application marker tools;
- sewer/road/point/ward/polygon/water-body decision tools;
- hard-to-reach and water-body-inaccessible tools.

Some responses rename BIN to `gid` or assign it to a frontend property named `house_number`; this still exposes the underlying BIN value.

### BIN exposed directly by GeoServer

- Building GetFeatureInfo;
- Containment `responsible_bin`;
- Toilet BIN;
- generic Building WFS download;
- tax, water, and SWM WFS exports.

### BIN visibly rendered

- Building feature popup;
- associated main-Building field;
- responsible BIN in containment popup;
- BIN-based edit link;
- some marker popups where a feature has a `bin` property (`resources/views/maps/index.blade.php:5876-5881`).

## BIN uses that are internal-only, not exposure by themselves

These occurrences do not expose BIN unless the result includes it:

- `buildings.bin = build_contains.bin` joins;
- `applications.bin = buildings.bin` joins;
- `COUNT(bin)` aggregations;
- server-side BIN filters whose responses contain only counts or coordinates.

Examples:

- `MapsService::getContainmentBuildings()` selects BIN internally but returns only latitude/longitude at `MapsService.php:428-453`;
- due-Building queries select BIN but return only latitude/longitude at `MapsService.php:1546-1595`;
- Building/containment relationship joins throughout the map service are legitimate internal uses.

## Priority for URL-binding remediation

1. Replace BIN navigation in Building search, extent, nearest-road, popup edit, and Building-list map links with `building.public_id`.
2. Change relationship tools to send `building_public_id`; Laravel resolves it to BIN before existing `build_contains` queries.
3. Add `public_id` to the GeoServer Building layer/view and remove BIN from default GetFeatureInfo/WFS property lists.
4. Replace BIN or BIN-derived `gid` in decision-tool JSON with `public_id` unless BIN is genuinely needed for display/export.
5. Move sensitive owner/contact/payment WFS responses and exports behind authorized Laravel endpoints.
6. Keep BIN inside database joins and approved operational exports where it remains a legitimate business field.

## Conclusion

The Map module is a major BIN exposure surface. The exposure is not limited to one search field: it exists across map navigation, relationship tools, application markers, decision/summary tools, GeoServer feature information, and exports. Building URL binding must therefore include the Map/Laravel/GeoServer boundary, while preserving BIN for internal joins such as `build_contains`.
