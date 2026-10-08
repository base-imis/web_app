# URL Binding Start Work Plan

Date: 2026-06-26

Project: IMIS Revamp / `lang_web_app`

## Goal

The goal is to stop exposing direct database IDs in URLs, such as:

```text
/fsm/application/12/history
/fsm/emptying/create/12
/api/revamp/application/12
```

Instead, public-facing URLs should use a safe public value:

```text
/fsm/application/7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1/history
/fsm/emptying/create/7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
/api/revamp/application/7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
```

Internally, the app can still use the normal numeric `id` for joins, reports, foreign keys, and database relations.

## Recommended First Move

Start with one small pilot module first. Do not change the whole project at once.

Recommended pilot:

```text
FSM Application
```

Why this module first:

- It has important user-facing URLs.
- It is connected with emptying, feedback, sludge collection, containment, map, and API flows.
- If this module is handled well, the same pattern can be reused in other modules.

## Recommended Technical Approach

Add a new column called `public_id` to each table that needs hidden URLs.

Use `public_id` in routes and links.

Keep `id` for internal database work.

Simple rule:

```text
URL = public_id
Database joins = id
```

## Why Not Only Laravel Route Binding?

Laravel route binding helps clean controller code, but it does not hide IDs by itself.

If the route still uses numeric `id`, then the URL will still expose the database ID.

So the proper fix is:

- add `public_id`;
- fill it for old records;
- generate it for new records;
- use it in URLs;
- resolve it back to the model inside controllers or route binding.

## Suggested Implementation Order

### Step 1: Add public ID support

Create migrations to add `public_id` to the selected table.

For pilot:

```text
fsm.applications
```

Later modules can follow the same style.

Column recommendation:

```php
$table->uuid('public_id')->nullable()->unique();
```

Then backfill old rows with UUID values.

After all old rows are filled, make sure new rows always receive a `public_id`.

### Step 2: Update the model

For the pilot, update:

```text
app/Models/Fsm/Application.php
```

The model should generate `public_id` when a new record is created.

Later, we can decide whether to use Laravel route model binding with:

```php
public function getRouteKeyName()
{
    return 'public_id';
}
```

or manually resolve:

```php
Application::where('public_id', $publicId)->firstOrFail();
```

For this project, manual resolving may be safer at first because many existing services still expect numeric IDs.

### Step 3: Update routes

Change public route parameter names so the code is clear.

Example:

```php
Route::get('application/{application_public_id}/history', ...);
```

Avoid keeping the route name as `{id}` when it is no longer the numeric ID.

### Step 4: Update controllers

Controllers should receive `public_id` from the URL, find the model, and pass the internal numeric `id` to existing service methods only when needed.

Example:

```php
$application = Application::where('public_id', $applicationPublicId)->firstOrFail();
$id = $application->id;
```

This keeps the service layer stable while URLs become safer.

### Step 5: Update generated links and buttons

Most links are not written directly in Blade files only. A lot of action buttons are generated from service classes for DataTables.

So for every changed route, update:

- Blade links;
- service-generated action buttons;
- AJAX URLs;
- API response values used by mobile or frontend clients.

### Step 6: Update API routes carefully

Current API routes expose IDs:

```text
/api/revamp/application/{id}
/api/revamp/containment/{application_id}
```

These should be changed only after checking the mobile/API consumer impact.

For safer rollout, support both old and new API routes temporarily:

```text
/api/revamp/application/{public_id}
/api/revamp/application-by-id/{id}     temporary/internal only if needed
```

Then remove the old numeric route after clients are updated.

## Files Affected First

These are the files to touch for the pilot module.

### Routes

```text
routes/web.php
routes/api.php
```

Important current routes:

```text
routes/web.php:381  application/{id}/history
routes/web.php:384  application/{id}/application-report
routes/web.php:392  emptying/create/{id}
routes/web.php:404  feedback/editFeedback/{id}
routes/web.php:412  sludge-collection/create/{id}
routes/api.php:66   /application/{id}
routes/api.php:67   /containment/{application_id}
```

### Pilot model

```text
app/Models/Fsm/Application.php
```

### Pilot controllers

```text
app/Http/Controllers/Fsm/ApplicationController.php
app/Http/Controllers/Fsm/EmptyingController.php
app/Http/Controllers/Fsm/FeedbackController.php
app/Http/Controllers/Fsm/SludgeCollectionController.php
app/Http/Controllers/Api/ApiServiceController.php
```

### Pilot services

```text
app/Services/Fsm/ApplicationService.php
app/Services/Fsm/EmptyingService.php
```

These services currently build many action links and also receive numeric IDs from controllers.

### Pilot database files

Create new migrations. Do not edit old migrations unless this is still a fresh local database only.

Suggested new migration:

```text
database/migrations/YYYY_MM_DD_HHMMSS_add_public_id_to_fsm_applications_table.php
```

If rollout includes related FSM records, also add later migrations for:

```text
fsm.emptyings
fsm.feedbacks
fsm.sludge_collections
fsm.containments
```

## Other Files Likely Affected Later

These files contain public `{id}` URLs, `find($id)`, `findOrFail($id)`, or related URL actions. They should be handled phase by phase after the pilot.

### Building module

```text
routes/web.php
app/Http/Controllers/BuildingInfo/BuildingController.php
app/Http/Controllers/BuildingInfo/BuildingSurveyController.php
app/Services/BuildingInfo/BuildingStructureService.php
app/Models/BuildingInfo/Building.php
app/Models/BuildingInfo/BuildingSurvey.php
resources/views/building-info/buildings/index.blade.php
resources/views/building-info/buildings/edit.blade.php
resources/views/building-info/buildings/partial-form.blade.php
resources/views/building-info/building-surveys/index.blade.php
```

Current exposed routes include:

```text
building-info/buildings/{id}/history
building-info/buildings/{id}/listContainments
building-info/building-surveys/{id}/approve
```

### Containment and public toilet module

```text
app/Http/Controllers/Fsm/ContainmentController.php
app/Http/Controllers/Fsm/CtptController.php
app/Http/Controllers/Fsm/CtptUserController.php
app/Services/Fsm/ContainmentService.php
app/Services/Fsm/CtptServiceClass.php
app/Services/Fsm/CtptUserServiceClass.php
app/Models/Fsm/Containment.php
app/Models/Fsm/Ctpt.php
app/Models/Fsm/CtptUsers.php
```

Current exposed routes include:

```text
fsm/containments/{id}/containmentData
fsm/containments/{id}/listBuildings
fsm/containments/{id}/history
fsm/containments/{id}/type-change-history
fsm/ctpt/{id}/buildings
fsm/ctpt/{id}/buildings/add
fsm/ctpt/{id}/history
fsm/ctpt-users/{id}/history
```

### Emptying, feedback, and sludge collection

```text
app/Http/Controllers/Fsm/EmptyingController.php
app/Http/Controllers/Fsm/FeedbackController.php
app/Http/Controllers/Fsm/SludgeCollectionController.php
app/Services/Fsm/EmptyingService.php
app/Services/Fsm/ApplicationService.php
app/Models/Fsm/Emptying.php
app/Models/Fsm/Feedback.php
app/Models/Fsm/SludgeCollection.php
```

Current exposed routes include:

```text
fsm/emptying/create/{id}
fsm/emptying/{id}/history
fsm/feedback/editFeedback/{id}
fsm/sludge-collection/create/{id}
fsm/sludge-collection/{id}/history
```

### FSM setup and master data

```text
app/Http/Controllers/Fsm/ServiceProviderController.php
app/Http/Controllers/Fsm/EmployeeInfoController.php
app/Http/Controllers/Fsm/HelpDeskController.php
app/Http/Controllers/Fsm/TreatmentPlantController.php
app/Http/Controllers/Fsm/TreatmentPlantTestController.php
app/Http/Controllers/Fsm/TreatmentPlantEffectivenessController.php
app/Http/Controllers/Fsm/VacutugTypeController.php
app/Http/Controllers/Fsm/KpiTargetController.php
app/Services/Fsm/ServiceProviderService.php
app/Services/Fsm/EmployeeInfoService.php
app/Services/Fsm/HelpDeskService.php
app/Services/Fsm/TreatmentPlantService.php
app/Services/Fsm/TreatmentPlantTestService.php
app/Services/Fsm/TreatmentPlantEffectivenessService.php
app/Services/Fsm/VacutugTypeService.php
app/Services/Fsm/KpiService.php
```

Current exposed routes include history and detail routes for:

```text
kpi-targets
service-providers
employee-infos
help-desks
treatment-plants
treatment-plant-test
desludging-vehicles
```

### Utility module

```text
app/Http/Controllers/UtilityInfo/RoadlineController.php
app/Http/Controllers/UtilityInfo/DrainController.php
app/Http/Controllers/UtilityInfo/SewerLineController.php
app/Http/Controllers/UtilityInfo/WaterSupplysController.php
app/Services/UtilityInfo/RoadlineService.php
app/Services/UtilityInfo/DrainService.php
app/Services/UtilityInfo/SewerLineService.php
app/Services/UtilityInfo/WaterSupplysService.php
app/Models/UtilityInfo/Roadline.php
app/Models/UtilityInfo/Drain.php
app/Models/UtilityInfo/SewerLine.php
app/Models/UtilityInfo/WaterSupplys.php
```

Note: Some utility actions already use `code` instead of numeric `id` in generated links. Those should be reviewed before changing anything, because they may already be safer than raw IDs.

### Sewer connection module

```text
app/Http/Controllers/SewerConnection/SewerConnectionController.php
app/Services/SewerConnection/SewerConnectionService.php
app/Models/SewerConnection/SewerConnection.php
```

Current exposed routes include:

```text
sewerconnection/data/{id}
sewerconnection/datageom/{id}
sewerconnection/geomsewer/{id}
```

### Layer and public health modules

```text
app/Http/Controllers/LayerInfo/LowIncomeCommunityController.php
app/Services/LayerInfo/LowIncomeCommunityServiceClass.php
app/Models/LayerInfo/LowIncomeCommunity.php
app/Http/Controllers/PublicHealth/YearlyWaterborneController.php
app/Http/Controllers/PublicHealth/WaterSamplesController.php
app/Http/Controllers/PublicHealth/HotspotController.php
app/Services/PublicHealth/WaterborneService.php
app/Services/PublicHealth/WaterSamplesService.php
app/Services/PublicHealth/HotspotServiceClass.php
app/Models/PublicHealth/YearlyWaterborne.php
app/Models/PublicHealth/WaterSamples.php
app/Models/PublicHealth/Hotspots.php
```

Current exposed routes include history/detail routes for:

```text
low-income-communities
waterborne
water-samples
hotspots
```

### Auth and language modules

```text
app/Http/Controllers/Auth/UserController.php
app/Http/Controllers/Auth/RoleController.php
app/Services/Auth/UserService.php
app/Http/Controllers/Language/LanguageController.php
app/Models/User.php
app/Models/Language/Language.php
app/Models/Language/Translate.php
resources/views/roles/index.blade.php
resources/views/includes/footer.blade.php
```

Current exposed routes include:

```text
auth/users/login-activities/{user_id}
auth/searchPermission/{id}
language/generate/{id}
language/import/{id}
language/add-translation/{id}
```

## Files That Should Usually Not Change First

Do not start by changing all joins, dashboard reports, or foreign keys.

These internal references can keep using numeric IDs:

```text
app/Services/DashboardService.php
app/Services/Maps/MapsService.php
app/Http/Controllers/MapsController.php
exports
reports
foreign key relations
raw SQL joins
```

Only change these later if they return IDs into public URLs or API responses.

## Testing Checklist

For the pilot module, test these manually:

- application list loads;
- application view works with `public_id`;
- application edit works with `public_id`;
- application history works with `public_id`;
- application report works with `public_id`;
- emptying create from application works;
- feedback create from application works;
- sludge collection create from application works;
- API application detail works with `public_id`;
- old numeric ID URL does not expose data, or redirects only if we intentionally keep backward support;
- user cannot access another restricted record just by changing the URL value.

## Suggested Work Estimate

Pilot module:

```text
2 to 4 working days
```

FSM module rollout:

```text
2 to 3 weeks
```

Full app rollout:

```text
4 to 6 weeks
```

The estimate depends on how many old numeric routes must remain temporarily for mobile/API users.

## My Recommended Starting Task

Start with this task:

```text
Add public_id URL binding for FSM Application detail, history, report, and connected create actions.
```

Scope:

- add `public_id` to `fsm.applications`;
- backfill existing applications;
- generate `public_id` for new applications;
- update application routes;
- update `ApplicationController`;
- update action links from `ApplicationService`;
- update connected create links for emptying, feedback, and sludge collection;
- update `/api/revamp/application/{id}` to support public ID;
- test the affected browser and API flows.

After this is stable, copy the same pattern to emptying, containment, service provider, and other modules.
