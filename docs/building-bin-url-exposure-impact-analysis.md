# Building BIN Exposure and URL-Binding Impact Analysis

## 1. Purpose and scope

This document is a focused static-code study of where the `bin` value from `building_info.buildings` is exposed outside the server, especially in URLs. It does not change application code.

The review covers:

- URL path parameters and query strings;
- JSON, DataTables, map, and API responses;
- rendered pages and forms;
- spreadsheet/CSV exports;
- public file paths derived from BIN;
- the likely impact of replacing BIN-based route binding with a separate public identifier.

This is a source review, not a runtime penetration test. Actual HTTP status codes, role assignments, web-server rules, GeoServer security, and production storage permissions were not tested.

## 2. Executive finding

`BIN` is not only displayed as building data. It is the Eloquent primary key and the cross-module join key, and it is used directly as a route identifier.

The source of this behavior is:

- `app/Models/BuildingInfo/Building.php:23` maps the model to `building_info.buildings`;
- `app/Models/BuildingInfo/Building.php:24` declares `bin` as the primary key;
- `app/Models/BuildingInfo/Building.php:34-47` and `61-62` use BIN in containment, toilet, owner, and application relationships.

As a result, the same predictable value identifies a building in the database, appears in UI/API/export output, and is passed back through URLs to read or mutate records.

Exposing an identifier is not automatically an authorization vulnerability. The material risk is the combination of:

1. a predictable/enumerable identifier such as `B000001`;
2. broad response surfaces that reveal valid BINs;
3. endpoints that accept BIN in a URL or request;
4. missing or overly broad object-level permission checks; and
5. BIN joining to owner, tax, sanitation, application, containment, location, and image data.

## 3. Confirmed URL exposure inventory

### 3.1 Building resource URLs — direct BIN in the path

The building resource and custom routes are declared in `routes/web.php:88-99`. Because the `Building` model primary key is BIN and the controllers call `Building::find($id)`, `{id}` is effectively `{bin}`.

| Effective URL | Operation | Evidence | Exposure |
|---|---|---|---|
| `/building-info/buildings/{BIN}` | show | `routes/web.php:99`; `app/Http/Controllers/BuildingInfo/BuildingController.php:195` | BIN in browser URL and logs |
| `/building-info/buildings/{BIN}/edit` | edit | resource route; link generated at `app/Services/BuildingInfo/BuildingStructureService.php:1055` | BIN in URL |
| `/building-info/buildings/{BIN}` | update/delete | form actions at `resources/views/building-info/buildings/edit.blade.php:9-11` and `app/Services/BuildingInfo/BuildingStructureService.php:1048` | BIN in request path |
| `/building-info/buildings/{BIN}/history` | history | `routes/web.php:96`; generated at `app/Services/BuildingInfo/BuildingStructureService.php:1063` | BIN in URL |
| `/building-info/buildings/{BIN}/listContainments` | connected containments | `routes/web.php:98`; AJAX composition at `resources/views/building-info/buildings/index.blade.php:601` | BIN in URL and frontend state |

Authentication is applied by `BuildingController` at `app/Http/Controllers/BuildingInfo/BuildingController.php:50-58`. Resource actions receive named permissions, but helper endpoints such as `getData`, `getHouseNumbers`, `getSanitationSystem`, `history`, and `listContainments` are not included in those `only` lists. They therefore appear to require authentication without the same explicit feature permission. Runtime role/middleware testing is required to confirm the practical reach.

### 3.2 BIN passed through FSM/containment URLs

| Effective URL | Purpose | Evidence |
|---|---|---|
| `/fsm/containments/{BIN}/create` | create a containment for a building | `routes/web.php:368`; link at `resources/views/building-info/buildings/partial-form.blade.php:640` |
| `/fsm/containments/{BIN}/containmentData` | load containment data while editing a building | `routes/web.php:364`; AJAX URL at `resources/views/building-info/buildings/edit.blade.php:47` |
| `/fsm/containments/{containmentId}/buildings/{BIN}` | remove building/containment connection | `routes/web.php:366`; forms at `resources/views/fsm/containments/listBuilding.blade.php:32` and `resources/views/fsm/ct-pt/listBuilding.blade.php:35` |

The delete route has an explicit permission mapping in `app/Http/Controllers/Fsm/ContainmentController.php:49-51`. The create route is more ambiguous: `createContainment` is a custom method and is not the standard `create` method named in the permission middleware at lines 44-45.

### 3.3 Sewer-connection URLs — direct BIN in the path

| Effective URL | Purpose | Evidence |
|---|---|---|
| `/sewerconnection/sewerconnection/data/{BIN}` | approve/update sewer connection for the building | `routes/web.php:191`; frontend URL at `resources/views/sewer-connection/approve.blade.php:45`; lookup at `app/Services/SewerConnection/SewerConnectionService.php:82-108` |
| `/sewerconnection/sewerconnection/datageom/{BIN}` | return building geometry | `routes/web.php:192`; lookup at `app/Services/SewerConnection/SewerConnectionService.php:123-142` |

`SewerConnectionController` applies authentication, but its permission middleware names only `index` and `destroy` (`app/Http/Controllers/SewerConnection/SewerConnectionController.php:13-18`). `approvesewer`, `geombin`, and `getData` therefore appear to be authentication-only endpoints. This is a high-priority authorization-review area because one route changes state via GET and another reveals building location.

### 3.4 Map URLs and query strings

Building actions generate URLs containing both the field name `bin` and its value:

- `/maps?layer=buildings_layer&field=bin&val={BIN}` from `app/Services/BuildingInfo/BuildingStructureService.php:1071`;
- the same URL plus `action=building-road` at line 1074;
- `/maps/search-building/bin/{BIN}` through `routes/web.php:515` and `app/Http/Controllers/MapsController.php:793-829`;
- `/maps/building-road/bin/{BIN}` through `routes/web.php:495` and `app/Services/Maps/MapsService.php:1498`;
- `/maps/building-containment?bin={BIN}` using `routes/web.php:479` and `app/Services/Maps/MapsService.php:123-149`;
- `/maps/getassociated-mainbuilding?bin={BIN}` using `routes/web.php:481` and `app/Services/Maps/MapsService.php:194-215`.

Map routes are authenticated (`routes/web.php:477-535`), but the map permission check is commented out in `app/Http/Controllers/MapsController.php:50-55`. BIN may also be returned together with geometry, for example:

- containment-to-buildings response: `app/Services/Maps/MapsService.php:159-185`;
- map search response: `app/Http/Controllers/MapsController.php:823-829`;
- autocomplete BIN list: `app/Http/Controllers/MapsController.php:870`;
- numerous spatial report responses in `app/Services/Maps/MapsService.php`, including lines 428-435, 983-1004, 1045-1052, and 1666-1674.

Several map helpers accept a client-supplied field/attribute name as well as a value and interpolate them into SQL, for example `app/Services/Maps/MapsService.php:258-273`. That is broader than BIN exposure and should receive a separate SQL-injection/allow-list review.

### 3.5 API path and response exposure

Protected API routes expose BIN in these ways:

| API | Behavior | Evidence |
|---|---|---|
| `GET /api/buildingcode` | returns every non-deleted building BIN | `routes/api.php:116-123`; `app/Http/Controllers/Api/BuildingSurveyController.php:46-63` |
| `GET /api/revamp/get-Building-bin/{BIN-prefix}` | BIN/prefix appears in URL; returns matching full `Building` models | `routes/api.php:138-146`; `app/Http/Controllers/BuildingSearchController.php:30-38` |
| road-code, house-number, sewer-code, preconnected, and sanitation searches | returns `Building` models, which include BIN even when search input is another field | `app/Http/Controllers/BuildingSearchController.php:42-94` |

The revamp group is inside `auth:sanctum` (`routes/api.php:43-46` and 138-148). The controller also applies `auth`, but its named permissions cover standard CRUD methods, not these search methods (`app/Http/Controllers/BuildingSearchController.php:14-22`).

There is also a duplicate sewer-code search route outside the Sanctum group at `routes/api.php:33`. The controller's own `auth` middleware should still apply, but this should be verified because API and web authentication guards can behave differently.

## 4. BIN exposed outside URL paths

These surfaces matter because they disclose valid identifiers that can then be replayed against BIN-based URLs.

### 4.1 JSON/DataTables and dropdowns

- Building list data selects and returns `building_info.buildings.bin`: `app/Services/BuildingInfo/BuildingStructureService.php:969-989`; rendered as a column at `resources/views/building-info/buildings/index.blade.php:292-293`.
- Building selector endpoints return BIN as Select2 `id`, even when the visible text is a house number: `app/Services/BuildingInfo/BuildingStructureService.php:1115-1125`, 1164-1168, and 1175-1217.
- CT/PT building selection returns and renders BIN: `app/Http/Controllers/Fsm/CtptController.php:282-284`; `resources/views/fsm/ct-pt/addBuildings.blade.php:75` and 118-136.
- CT/PT and application tables render BIN: `resources/views/fsm/ct-pt/index.blade.php:178-179`; `resources/views/fsm/applications/index.blade.php:270-271`.
- Connected-building lists render BIN: `resources/views/fsm/containments/listBuilding.blade.php:26`; `resources/views/fsm/ct-pt/listBuilding.blade.php:29`.

### 4.2 Rendered pages and reports

- Building edit header displays BIN: `resources/views/building-info/buildings/edit.blade.php:6`.
- Building detail obtains its record by BIN and can expose connected owner, sanitation, containment, image, and other building data: `app/Http/Controllers/BuildingInfo/BuildingController.php:195-216`.
- CT/PT detail displays BIN: `resources/views/fsm/ct-pt/show.blade.php:40-42`.
- Application report displays the related building BIN: `resources/views/fsm/applications/application_report.blade.php:80-81`.

### 4.3 Exports

Confirmed direct export surfaces include:

- main building export: `app/Exports/BuildingsListExport.php:55-117` and `resources/views/exports/buildings-list.blade.php:4-9,54`;
- building-owner export: `app/Exports/BuildingsOwnerExport.php:39-41` and `resources/views/exports/buildings-owners.blade.php:4,23`;
- building-containment export: `app/Exports/BuildContainExport.php:55-69` and `resources/views/exports/build-contain.blade.php:4,11`;
- point/road spatial building exports: `app/Exports/PointBuildingsListExport.php:48-116`, `app/Exports/BuildingsRoadListExport.php:38-104`, and `app/Exports/BuildRoadContainExport.php:44-56`;
- CT/PT export includes the related building BIN: `app/Services/Fsm/CtptServiceClass.php:330-333`;
- building/containment exports include BIN alongside owner and identity fields: `app/Services/BuildingInfo/BuildingStructureService.php:908-912` and `app/Services/Fsm/ContainmentService.php:491-495`.

Exporting BIN can be legitimate business behavior. The control question is whether the export permission, recipient, retention policy, and joined personal data are appropriate.

### 4.4 GeoServer/WFS requests

The map page explicitly requests BIN as a GeoServer property in tax, water-payment, and solid-waste-payment exports:

- `resources/views/maps/index.blade.php:11456`;
- `resources/views/maps/index.blade.php:11527`;
- `resources/views/maps/index.blade.php:11593`.

These requests combine BIN with tax/customer IDs, ward, names, contacts, due years, and geometry. Laravel route binding will not protect direct GeoServer access; GeoServer authentication, layer permissions, and `authkey` handling must be reviewed separately.

### 4.5 Public storage filenames derived from BIN

House images are named `{BIN}.{extension}` and stored on the public disk:

- filename creation: `app/Services/BuildingInfo/BuildingStructureService.php:103-107,575-577`;
- emptying workflow: `app/Services/Fsm/EmptyingService.php:1001-1013` and 1101-1107;
- public asset URL construction: `app/Http/Controllers/BuildingInfo/BuildingController.php:199-209` and `app/Services/Fsm/EmptyingService.php:331-343`;
- direct link in the building detail page: `resources/views/building-info/buildings/show.blade.php:526-536`.

The effective path is normally `/storage/emptyings/houses/{BIN}.jpg` or `.jpeg`. If the standard public storage symlink is web-accessible, possession or guessing of a BIN may allow a request to bypass Laravel controller authorization entirely. Route-binding changes do not address this filename exposure.

## 5. Data impact of a disclosed BIN

BIN is a high-value correlation key, not a standalone random label. The model and queries use it to connect:

- owners: `app/Models/BuildingInfo/Building.php:45-47`;
- FSM applications: `app/Models/BuildingInfo/Building.php:61-62`;
- containments: `app/Models/BuildingInfo/Building.php:32-36`;
- shared/community toilets: `app/Models/BuildingInfo/Building.php:39-42` and 98-100;
- sewer connections: `app/Services/SewerConnection/SewerConnectionService.php:88-108`;
- tax, water, and solid-waste payment map layers: `resources/views/maps/index.blade.php:11456,11527,11593`;
- building geometry/location: `app/Services/Maps/MapsService.php:159-185` and `app/Http/Controllers/MapsController.php:823-829`;
- house images: the public storage paths described above.

The principal impact is correlation: one known BIN can act as a pivot among property, owner/contact, sanitation, payment, service, location, and image information, subject to the permissions on each endpoint.

## 6. Impact of introducing public URL binding

Assumed target design: keep `building_info.buildings.bin` as the internal business/join key, add a non-sequential `public_id` for external routes, and bind URLs to `public_id`.

### 6.1 Required changes

1. **Database and model**
   - Add, backfill, uniquely index, and make `public_id` non-null.
   - Add `getRouteKeyName()` returning `public_id`, or use explicit route binding.
   - Keep `bin` as the database primary/business key initially to avoid rewriting all relationships.

2. **Building controllers and services**
   - Current methods use `Building::find($id)`, which means BIN (`BuildingController.php:195,403,479` and `BuildingStructureService.php:385`).
   - Route-facing methods must resolve `public_id`, while internal relationship methods may continue to use BIN.
   - Avoid a global change that silently makes existing `find()` calls interpret a public ID as the database primary key.

3. **Generated links, forms, and AJAX**
   - Update all building show/edit/update/delete/history/list-containment links at `BuildingStructureService.php:1048-1074`.
   - Update edit and containment AJAX URLs at `resources/views/building-info/buildings/edit.blade.php:11,47` and index AJAX at `resources/views/building-info/buildings/index.blade.php:601`.
   - Update containment creation and connection-removal URLs if their path identifiers are intended to become public IDs.

4. **Maps**
   - Stop generating `field=bin&val={BIN}` URLs.
   - Translate `public_id` to BIN server-side before legacy spatial joins, or add `public_id` to the required layer/view.
   - Direct GeoServer calls require their own design; Laravel route binding cannot translate identifiers inside WFS/CQL requests.

5. **Sewer and FSM workflows**
   - Sewer endpoints currently accept BIN and query `Building::where('bin', $bin)`.
   - Containment and CT/PT pivot tables legitimately use BIN internally. URL/public identifier translation should occur at the controller boundary, not by changing those joins immediately.

6. **APIs and compatibility**
   - Mobile or external clients may depend on `/api/buildingcode` and revamp search responses returning BIN.
   - Decide explicitly whether BIN remains a documented business field, is replaced by `public_id`, or is returned only to privileged clients.
   - Version breaking response and path changes instead of silently changing identifier semantics.

7. **Files and exports**
   - Rename/migrate public image objects or serve them through an authorized controller using opaque file IDs. Merely changing web routes leaves `/storage/.../{BIN}.jpg` intact.
   - Exports require a policy decision: public URL binding does not automatically mean BIN must disappear from authorized business reports.

### 6.2 Risk of a partial migration

A partial implementation can create inconsistent identity domains:

- some `{id}` parameters would mean BIN while others mean `public_id`;
- frontend code could send `public_id` into services that join on BIN and return no data;
- update/delete operations could target the wrong lookup column;
- maps and GeoServer layers could still reveal BIN even after building pages stop using it;
- old bookmarked links, integrations, and mobile clients could break;
- dual acceptance of BIN and `public_id` could preserve enumeration unless BIN fallback is deliberately time-limited and authorized.

## 7. Priority assessment

| Priority | Area | Reason |
|---|---|---|
| Critical review | Public BIN-named house images | May bypass Laravel authentication/authorization and is predictable |
| High | Sewer approval and geometry GET endpoints | BIN in path; sensitive action/location; methods appear authentication-only |
| High | Building helper/data endpoints | Reveal valid BINs; several are not listed under explicit feature permissions |
| High | Map and GeoServer surfaces | BIN correlated with geometry, owner/contact, and payment data; separate security boundary |
| High | Search APIs returning full Building models | More fields than a minimal lookup response; search methods lack named feature permission |
| Medium | Standard building CRUD/history URLs | Direct BIN exposure, but standard actions have at least partial authentication/permission coverage |
| Medium | FSM building-connection routes | BIN in path; delete is permission protected, custom create/read routes need verification |
| Policy-dependent | Authorized UI, reports, and exports | BIN may be a legitimate operational field; minimize joined personal data and recipients |

## 8. Recommended migration sequence

1. Inventory production consumers and confirm which roles can call every endpoint in sections 3 and 4.
2. Fix object-level authorization and public file delivery first; opaque IDs do not replace authorization.
3. Add and backfill a unique non-sequential `public_id` while retaining BIN internally.
4. Convert the core building show/edit/update/delete/history/list-containment routes and their generated links.
5. Convert sewer, containment, CT/PT, application, and map boundary endpoints by translating `public_id` to internal BIN server-side.
6. Version external APIs and reduce search responses to explicitly selected fields.
7. Redesign public storage filenames and direct GeoServer exposure.
8. Remove any temporary BIN URL fallback after logs show no remaining consumers.

## 9. Verification checklist for a future implementation

- A BIN placed in each former building URL no longer resolves a record.
- A valid `public_id` resolves only when the user has the required feature and object permission.
- Changing a `public_id` to another valid value does not allow horizontal access.
- Building list/dropdown APIs return only fields required by the caller.
- Sewer approval cannot be performed by GET and cannot be performed without its explicit permission.
- Map search, geometry, and connected-record endpoints enforce permissions independently of whether links are visible in the UI.
- Direct `/storage/emptyings/houses/{BIN}.jpg` requests do not return images.
- GeoServer layers cannot be queried directly for unauthorized BIN/contact/payment/geometry combinations.
- Exports remain available only to intended roles and contain only approved columns.
- Existing internal BIN joins for owners, applications, containments, toilets, and sewer connections still work.

## 10. Bottom line

BIN is currently both an internal relational key and an external identifier. URL binding to an opaque `public_id` will reduce enumeration and correlation through browser URLs, logs, referrers, and copied links, but it is only one part of the remediation. The most important companion controls are explicit endpoint/object authorization, minimal API responses, protected file delivery, and GeoServer/layer security.
