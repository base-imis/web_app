- Version: V1.2.0
- Application: Base IMIS v2.0.0 — desludging scheduling scope
- Document type: Code and workflow reference
- Source reviewed: 2026-10-13
- Source branch: `v2.0.0-desludging-schedule-clean`, commit `50927a82`

# Fecal Sludge IMS — Service Delivery

This document follows the submodule and code-component layout used by `05 - Fecal Sludge IMS.md`. It covers the service chain from provider setup and scheduled desludging through application, emptying, sludge collection, and feedback. Field definitions belong in the [existing data dictionary](../data_dictionary/data_dictionary.md).

This revision describes the clean scheduling branch. The later ANF, multi-trip, sludge-collection-log, Places and expanded API changes assigned to v2.0.1 are outside this release. References to existing emptying, sludge collection and feedback describe the baseline service chain, not those later features.

## Service Delivery Chain

The intended business flow is:

```text
Provider and ward setup
        ↓
Generate priority, dates, and temporary schedule
        ↓
Customer agrees ───────────────→ Application
        │                              ↓
        └─ Disagrees → Reintegration → Application
                                       ↓
                              Emptying service
                                       ↓
                              Sludge collection
                                       ↓
                              Customer feedback
```

Manual applications also enter at **Application**. The schedule is a regenerated working queue, not an application or a permanent service record. There is no supervisory-assessment stage in the intended workflow for this application. A current code dependency that can block scheduled confirmation is documented under [Implementation considerations](#implementation-considerations).

## Shared Code Pattern

The code-document convention is **Tables → Views → Models → Controller → Service → Request → principal functions** for each submodule. Web routes use the `/fsm` prefix and `auth` middleware. Controllers typically coordinate requests and responses; services or models perform persistence and business operations. Several older modules still contain business logic in controllers.

| Component | Location or role |
| --- | --- |
| Web routes | `routes/web.php` |
| Mobile routes | `routes/api.php`, protected by `auth:sanctum` |
| Controllers | `app/Http/Controllers/Fsm` and `app/Http/Controllers/Api` |
| Services | `app/Services/Fsm` |
| Models | `app/Models/Fsm` |
| Form requests | `app/Http/Requests/Fsm` |
| Views | `resources/views/fsm` |
| Database | PostgreSQL `fsm` schema; supporting `building_info`, `layer_info`, `auth`, and `public` data |

## Service Provider IMS

### Service Providers

**Tables:** `fsm.service_providers` stores provider identity, operational status, contract path and legacy `service_area` CSV. `fsm.service_provider_wards` stores one provider/ward pair per row with a unique pair constraint. The latter's provider reference is logical; its migration does not declare a database foreign key.

**Views:** `resources/views/fsm/service-providers/{index,create,edit,show,history,partial-form}.blade.php`.

**Model:** `app/Models/Fsm/ServiceProvider.php`.

**Controller:** `app/Http/Controllers/Fsm/ServiceProviderController.php` handles the resource routes, DataTable data, history and export.

**Service:** `app/Services/Fsm/ServiceProviderService.php`; `storeOrUpdate()` normalizes and synchronizes selected wards to both coverage stores. Optional provider-user creation is initiated by `ServiceProviderController` through `UserService`.

**Request:** `app/Http/Requests/Fsm/ServiceProviderRequest.php` validates identity, contact, service-area wards and the contract PDF. The contract is required on creation, optional on update, PDF-only and limited to 5 MB.

| Function or action | Current behavior |
| --- | --- |
| Create/update provider | Saves provider and synchronized ward coverage. |
| Change to non-operational | Unassigns incomplete applications previously allocated to that provider. |
| Read provider coverage | Scheduling and reintegration accept either normalized ward rows or the compatibility CSV. |

#### Add service provider

1. `ServiceProviderController::create()` loads all municipality wards from `layer_info.wards` in ward order and the status choices from `ServiceProviderStatus`. It also calls `getUncoveredServiceAreaWards()` for the add-form information popover.
2. The uncovered list is the set of positive municipality ward numbers minus coverage belonging to non-deleted operational providers. Coverage is combined from the provider CSV and normalized ward table. The popover is informational: the form still offers all wards, and overlapping provider coverage is allowed.
3. `create.blade.php` submits a multipart `POST /fsm/service-providers`. The shared partial collects company name, email, office ward, address, contact name/gender/number, operational status, contract PDF and one or more service-area wards. The office `ward` and coverage `service_area[]` are separate fields.
4. `ServiceProviderRequest` validates the submission before `store()`. Company names must be unique among non-deleted providers. Email must be valid, at most 255 characters and unique against `auth.users`; the check is not a provider-table email uniqueness constraint. Contact number accepts digits only. Status is boolean. Service area must be an array with at least one integer ward value.
5. The contract is required, must pass PDF file validation and cannot exceed 5 MB (`max:5120`). The browser's PDF accept filter is supplemented by server validation. `store()` sanitizes the company name for a filename, appends the current timestamp and writes the file to the `public` disk under `contract_documents/`. The database receives the relative path.
6. `ServiceProviderService::storeOrUpdate(null, $data)` converts wards to integers, removes non-positive and duplicate values, sorts them, saves a CSV such as `1,3,5`, saves the provider, and replaces its normalized ward rows through `syncServiceAreaWards()`. Creation returns the provider ID.
7. If `create_user` is filled, the controller passes the original request fields to `UserService::storeOrUpdate()`, with the new provider ID, user type `Service Provider`, role `Service Provider - Admin`, company name as the account name, contact gender, and the email local part as the username. The password is required when `create_user=on`, confirmed, and subject to the configured complexity and uncompromised-password rule. The user-account step is only part of creation.
8. The controller redirects to the provider list with a provider-only or provider-and-user success message. Validation errors return the form with errors and old input.

#### Edit service provider

1. `edit($id)` uses `findOrFail()`; missing or soft-deleted providers return 404. It loads all wards and status choices, converts the existing `service_area` CSV to integer selections, and renders `edit.blade.php` using `Form::model()`.
2. The form submits multipart `PATCH /fsm/service-providers/{id}`. `old('service_area', $selectedWards ?? [])` preserves submitted coverage after validation errors. The existing PDF link is shown when a path is present; an existing provider without a contract receives an explanatory message. The uncovered-wards popover is shown only on the add form.
3. `ServiceProviderRequest` applies the required identity, contact, status and service-area rules on update. Company uniqueness ignores the current provider. The email uniqueness rule ignores users with this provider ID; the update also applies the request's email regex. A replacement PDF is optional but must still be PDF and at most 5 MB.
4. Without a replacement, `update()` retains the current contract path. With a replacement, it stores the new PDF first and then deletes the previous file if it exists on the public disk, before calling the provider service to save the new path.
5. `storeOrUpdate($id, $data)` saves the edited provider and recomputes both coverage stores. `syncServiceAreaWards()` deletes existing rows for this provider, then inserts one timestamped row for each normalized ward. Changing coverage does not itself regenerate the schedule.
6. Whenever the submitted status is non-operational (`0`), the service finds that provider's applications where `emptying_status = false` and sets `service_provider_id` to null. Completed emptyings are not unassigned by this operation. This condition applies on any update with status 0, not only a transition from operational to non-operational.
7. The controller redirects to the provider list with an update success message. This action does not create or update the optional provider-admin account.

#### Service provider details

`show($id)` loads the provider, returns 404 if absent, resolves its readable status through `ServiceProviderStatus::getDescription()`, and renders `show.blade.php`. The page displays company name, email, office ward, address, contact name/gender/number, contract link, service-area wards and status, with a Back to List button.

The contract link uses `asset('storage/' . $serviceProvider->contract_document_pdf)` and opens in another tab. A missing path displays `Not available`; the detail view does not check whether the referenced file actually exists. Service-area display comes from the CSV with comma-and-space formatting, not from a live read of the normalized coverage table. Empty coverage displays `Not available`.

#### Provider routes and access

All routes are in the authenticated `/fsm` web group. Controller permission checks for provider actions are:

| Method and route | Controller action | Permission |
| --- | --- | --- |
| `GET /fsm/service-providers` | `index()` | `List Service Providers` |
| `GET /fsm/service-providers/create` | `create()` | `Add Service Provider` |
| `POST /fsm/service-providers` | `store()` | `Add Service Provider` |
| `GET /fsm/service-providers/{service_provider}/edit` | `edit()` | `Edit Service Provider` |
| `PUT/PATCH /fsm/service-providers/{service_provider}` | `update()` | `Edit Service Provider` |
| `GET /fsm/service-providers/{service_provider}` | `show()` | `View Service Provider` |
| `DELETE /fsm/service-providers/{service_provider}` | `destroy()` | `Delete Service Provider` |
| `GET /fsm/service-providers/export` | `export()` | `Export Service Providers to CSV` |

The `/data` and `/{id}/history` endpoints also exist. Their controller methods have authentication but are not included in the controller's action-specific provider permission lists; visibility of list buttons should not be treated as proof of direct-route authorization.

#### Persistence and scheduling dependencies

- `2026_08_14_100641_add_contract_document_pdf_and_service_area_to_service_providers_table.php` adds the contract path and compatibility CSV fields.
- `2026_08_20_115109_create_service_provider_wards_table.php` adds the normalized coverage table, unique provider/ward pairs, and indexes. It does not declare a foreign key or prevent different providers from covering the same ward.
- Validation checks integer ward values but does not enforce that each submitted ward exists in `layer_info.wards`. The service discards non-positive values. The UI supplies municipality wards; this differs from a database-backed membership validation rule.
- The provider save, coverage delete/insert, contract storage/deletion, and optional user creation are not wrapped in one controller/service transaction. A failure between steps can leave partial database or file changes. Contract replacement has no automatic file rollback.
- Schedule regeneration reads operational provider coverage from both stores. Overlapping coverage resolves to the lowest operational provider ID for a queue row. Reintegration also resolves providers using the current building ward. Provider edits do not immediately recalculate existing temporary assignments.

### Employee Information

**Table/model:** `fsm.employees`; `app/Models/Fsm/EmployeeInfo.php`.

**Controller/service/request:** `EmployeeInfoController.php`, `EmployeeInfoService.php`, `EmployeeInfoRequest.php` under their respective `Fsm` directories. They manage provider staff, including drivers and emptiers used by emptying records. Provider-specific mobile queries use the authenticated user's `service_provider_id`.

### Desludging Vehicles

**Table/model:** `fsm.desludging_vehicles`; `app/Models/Fsm/VacutugType.php`.

**Controller/service:** `VacutugTypeController.php` and `VacutugTypeService.php`. Vehicle selection in emptying is linked to the provider and vehicle's operational status. The mobile vacutug endpoint filters by the authenticated provider.

## Treatment Plant IMS

### Treatment Plants

**Table/model:** `fsm.treatment_plants`; `app/Models/Fsm/TreatmentPlant.php`.

**Controller/service/request:** `TreatmentPlantController.php`, `TreatmentPlantService.php`, `TreatmentPlantRequest.php`. Treatment plants are master data for emptying destinations and sludge receipt. The mobile emptying list exposes operational FSTP and co-treatment plants (types 3 and 4). Treatment Plant Admin visibility is filtered by the user's associated plant where the controller applies that rule.

## Emptying Service IMS

The existing application, emptying, sludge-collection and feedback modules remain the permanent service chain. The two scheduled-desludging submodules feed into application creation; they do not replace downstream records.

### Scheduled Desludging

**Tables:** `fsm.containments` supplies status, priority, FSTP distance and dates; `fsm.desludging_schedule_temp` is the regenerated queue snapshot; `fsm.service_provider_wards` and `fsm.service_providers.service_area` provide ward coverage; `public.site_settings` supplies scheduling settings. Building BIN, ward, address and owner come from `building_info` tables.

**View/model:** `resources/views/fsm/desludging-schedule/index.blade.php`; `app/Models/Fsm/DesludgingSchedule.php`.

**Controller/service:** `app/Http/Controllers/Fsm/DesludgingScheduleController.php` and `app/Services/Fsm/DesludgingScheduleService.php`. The controller exposes index, DataTable, regenerate, accept and disagree actions. The service owns generation, assignment and handoff to the application form.

**Request:** These actions validate their `Request` inputs in the service; there is no separate schedule FormRequest in the repository.

| Function | Current behavior |
| --- | --- |
| `regenerate()` | Runs all generation steps inside one database transaction with a PostgreSQL advisory transaction lock; concurrent generation returns HTTP 409. |
| `setPriority()` | Recalculates priority from a valid last-emptying date or construction date; no usable date has priority 1. |
| `setEmptyingDate()` | Allocates eligible never-emptied, active containments to working dates under remaining daily trip capacity. |
| `refreshTemporarySchedule()` | Deletes and rebuilds the queue from eligible containments; its membership criteria differ from date allocation. |
| Provider assignment | Assigns uncovered wards to operational providers without coverage, then assigns queue rows by current building ward; unmatched rows remain unassigned. |
| `redirectToApplication()` | Validates selected row values, stores them in the session and opens the application form in confirm mode. |
| `disagree()` | Sets containment status to 4 and removes its queue row in a transaction. |

**Eligibility and ordering:** Priority recalculation considers non-deleted containments with null, 0 or 4 status. Date allocation considers non-deleted containments with `emptied_status = false` and null/0 status; it orders by priority, then FSTP distance, then containment ID. The temporary queue considers null/0/3 status, excludes containments with a non-deleted open application, and sequences rows by FSTP distance, then priority, then containment ID. Thus queue membership and queue sequence must not be described as identical to date allocation.

**Priority bands:** 1 = no usable reference date or older than three years; 2 = older than one year but not older than three; 3 = within one year. Future reference dates are unusable. Exact one- and three-year boundaries follow the service's strict comparisons.

**Capacity:** The service skips configured weekend and holiday dates and subtracts existing confirmed application bookings and automatic bookings from `Trip Capacity Per Day`. It advances until all eligible containments have dates or a safety horizon is exceeded. A non-positive capacity is rejected.

**Provider assignment:** The service preserves existing coverage. It distributes uncovered municipality wards among active providers with no service area; the selection is shuffled and round-robin, so an initial assignment can vary. If multiple active providers cover a queue row's ward, the lowest provider ID wins. An unmatched row remains unassigned.

| `public.site_settings` name (`desludging_schedule` category) | Seeded value |
| --- | --- |
| `Schedule Desludging Start Date` | Empty |
| `Schedule Regeneration Period` | `6` |
| `Trip Capacity Per Day` | `20` |
| `Weekend` | `Friday,Saturday` |
| `Holiday Dates` | Empty |

### Scheduled Desludging Reintegration

**Tables:** `fsm.containments` with status 4, building/owner context, and provider coverage. Reintegration is a view over these records, not a separate reintegration table.

**View:** `resources/views/fsm/desludging-reintegration/index.blade.php`.

**Controller/service:** `DesludgingReintegrationController.php` lists eligible records and calls `DesludgingScheduleService::redirectReintegrationToApplication()` on confirmation. Provider resolution checks the BIN's current building ward against both coverage stores, selects the lowest-ID operational provider and uses a separate advisory lock.

**Handoff:** Confirmation returns to application creation with server-side schedule context. It does not directly restore a deleted temporary queue row. Municipality administrative/help-desk users can view across providers; provider users are filtered to configured wards.

### Application

**Table/model:** `fsm.applications`; `app/Models/Fsm/Application.php`. Each application links BIN, containment, provider and proposed emptying date to later service records.

**Views:** `resources/views/fsm/applications/{index,create,edit,history,application_report,monthly_report}.blade.php`.

**Controller/service/request:** `ApplicationController.php`, `ApplicationService.php`, `ApplicationRequest.php` under their respective `Fsm` directories.

| Function or action | Current behavior |
| --- | --- |
| Manual create | Creates an application without a schedule-row handoff. |
| Scheduled confirm | `ApplicationRequest::prepareForValidation()` locks BIN, containment, road, ward and provider to session values when available. |
| `ApplicationService::createApplication()` | Checks for an existing non-deleted, non-emptied application for the containment before insert. Within a transaction, creates the application, updates building/owner context and, in confirm mode, sets containment status to 1 and deletes queue rows for the containment. |
| Reports | Per-application and monthly PDF routes remain part of the existing module. |

The open-application check is application logic, not a database partial unique index. Scheduled confirmation is **not currently end-to-end clean** because the request and service still reference an out-of-scope assessment-date field; see [Implementation considerations](#implementation-considerations). This document does not add an assessment stage.

### Emptying

**Table/model:** `fsm.emptyings`; `app/Models/Fsm/Emptying.php`.

**Views:** `resources/views/fsm/emptying/{index,create,edit,history,partial-form}.blade.php`.

**Controller/service/request:** `EmptyingController.php`, `EmptyingService.php`, `EmptyingRequest.php`. The web service records the vehicle, crew, destination, sludge volume, trips, time, receipt/cost and evidence. It sets the application's `emptying_status`, updates containment last/next emptying dates (next date = emptied date plus three years), emptied status and emptying count. The web request checks volume against containment size and requires end time after start time.

**Mobile API:** `app/Http/Controllers/Api/EmptyingServiceController.php` uses `EmptyingApiRequest.php` under Sanctum. The API accepts multipart emptying data and JPEG receipt/optional house evidence. Provider-specific vehicle and employee lists are filtered by the authenticated provider. API and web validation are not identical; test both paths.

### Sludge Collection

**Table/model:** `fsm.sludge_collections`; `app/Models/Fsm/SludgeCollection.php`.

**Views:** `resources/views/fsm/sludge-collection/{index,create,edit,show,history,partial-form}.blade.php`.

**Controller/request:** `SludgeCollectionController.php` and `SludgeCollectionRequest.php`. The record links an application to the receiving plant and records delivery volume, date, trips, vehicle and entry/exit times. On store, the controller sets `applications.sludge_collection_status = true`. Date/trip/time validation and plant-specific visibility are applied in this module.

### Feedback

**Table/model:** `fsm.feedbacks`; `app/Models/Fsm/Feedback.php`.

**Views:** `resources/views/fsm/feedbacks/{index,create,edit,show}.blade.php`.

**Controller/request:** `FeedbackController.php` and `FeedbackRequest.php`. Feedback records service quality, PPE use and comments against an application/provider; store sets `applications.feedback_status = true`. Provider rating display uses recent feedback. There is no separate feedback service class in this repository.

### Help Desk

**Table/model:** `fsm.help_desks`; `app/Models/Fsm/HelpDesk.php`.

**Controller/service/request:** `HelpDeskController.php`, `HelpDeskService.php`, `HelpDeskRequest.php`. This is supporting master data for the service workflow, not an additional service stage.

## Routes and Permissions

| Action | Route | Enforced schedule permission |
| --- | --- | --- |
| List schedule / data | `GET /fsm/desludging-schedule`, `/data` | `List Schedule Desludging` |
| Regenerate | `POST /fsm/desludging-schedule/regenerate` | `Regenerate Schedule Desludging` |
| Begin confirmation | `POST /fsm/desludging-schedule/accept` | `Confirm Schedule Desludging` |
| Disagree | `POST /fsm/desludging-schedule/{bin}/disagree` | `Delete Schedule Desludging` |
| List reintegration / data | `GET /fsm/desludging-reintegration`, `/data` | `List Schedule Reintegration` |
| Confirm reintegration | `POST /fsm/desludging-reintegration/confirm` | `Confirm Schedule Reintegration` |

Other resource routes remain in `routes/web.php`: `/fsm/service-providers`, `/fsm/employee-infos`, `/fsm/desludging-vehicles`, `/fsm/treatment-plants`, `/fsm/application`, `/fsm/emptying`, `/fsm/sludge-collection`, `/fsm/feedback`, and `/fsm/help-desks`. Some permissions exist in the seeder without a corresponding action in these controllers; the table above lists enforcement observed in the code, not every seeded permission. Direct-request authorization should be tested, not inferred from hidden UI buttons.

## Mobile API

The following endpoints are declared in `routes/api.php` under `/api` and `auth:sanctum`. Their current names are retained here for client compatibility; endpoint naming does not add a supervisory-assessment stage to this workflow.

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/application/{id}` | Application details and building context. |
| GET | `/api/containment/{application_id}` | Containment linked to an application. |
| GET | `/api/service-providers` | Operational provider list. |
| GET | `/api/assessed-applications` | Legacy-named list used by the emptying client for work awaiting emptying. |
| GET | `/api/treatment-plants` | Operational FSTP and co-treatment facilities. |
| GET | `/api/vacutugs`, `/api/drivers`, `/api/emptiers` | Provider-filtered vehicle and staff choices. |
| POST | `/api/save-emptying` | Validate and save emptying and image evidence. |
| POST | `/api/logout` | End the authenticated API session. |

The save-emptying request includes application ID, sludge volume, vehicle, plant, driver, at least one emptier, trips, receipt/cost, start/end times, service-receiver details, a required JPEG receipt and an optional JPEG house image. The endpoint's response bodies use both `success` and `status` conventions in current code; callers should treat HTTP status as authoritative.

## Validation and Business Rules

| Area | Implemented rule |
| --- | --- |
| Provider | Company name and relevant user email uniqueness; at least one service-area ward; PDF contract at most 5 MB. |
| Application | Proposed date is required and checked against the current date; application service rejects another open application for the containment. |
| Web emptying | At least one trip, non-negative total cost, end time after start time, and volume no greater than containment size. |
| Mobile emptying | Separate `EmptyingApiRequest` rules, including a required JPEG receipt and positive total cost. |
| Sludge collection | Date at or after today, at least one trip, and exit time after entry time. |
| Feedback | Service-quality and PPE responses are required. |

Laravel FormRequest validation failures follow normal web/API response behavior. Schedule generation reports concurrent execution with HTTP 409 and unexpected errors with HTTP 500. Errors are reported through Laravel logging; production configuration should keep `APP_DEBUG=false`.

## Storage, Transactions and Reporting

- Provider contracts use the Laravel `public` disk under `contract_documents/`; on update, the controller stores the new file and deletes the old file before the provider database update completes.
- Emptying evidence uses `public/emptyings/receipts/` and `public/emptyings/houses/`; Intervention Image handles saving and mobile-path resizing/compression.
- Schedule regeneration is one transaction with an advisory transaction lock. Disagreement and application creation also use transactions. Database rollback does not automatically remove previously written files.
- Supported models use soft deletion and revision history where their model declares those traits. DataTables and export/report endpoints are part of existing controllers; do not infer identical behavior for every submodule.

## Technology and Deployment

The repository specifies PHP `^8.1` and Laravel `^8.75` in `composer.json`. The implementation uses PostgreSQL/PostGIS, Laravel web sessions and Sanctum, Spatie permissions, Blade, Yajra DataTables and Laravel storage. Source code also uses Intervention Image for emptying evidence and PDF/CSV libraries for reports and exports.

Before deployment, back up the database and uploaded files; then deploy dependencies/assets, run the repository migrations, confirm the five scheduling settings in `public.site_settings`, rebuild application caches as appropriate, and verify the public storage link and write permissions. Do not run migrations against a target environment without its backup and rollback procedure. This document records the source-code state and does not itself execute deployment commands.

## Verification Checklist

1. Create a provider with a valid PDF and multiple wards; verify positive, unique, sorted CSV values and matching normalized rows. Check the uncovered-wards popover against operational providers in both stores, including the all-covered case and overlapping coverage.
2. Reject missing create contracts, non-PDF files, files above 5 MB, missing coverage, duplicate active company names and invalid contact/email values. Test optional admin creation with and without a valid confirmed password.
3. Edit provider details and coverage; verify old input after a validation failure. Test keeping a contract, replacing it, and viewing a provider with no contract. Verify non-operational updates unassign only applications awaiting emptying, and that provider-admin credentials are not edited by this action.
4. Verify provider detail fields, status description, PDF URL, CSV display and 404 for a missing provider. Check Add/Edit/View permissions with direct requests, not only UI buttons. Verify file handling failure paths separately from successful database saves.
5. Test priority boundaries, future dates, daily capacity, weekends/holidays, concurrent regeneration, unassigned wards and overlapping provider coverage.
6. Verify disagreement removes a queue row, status 4 appears in reintegration, and reintegration resolves a provider by current building ward.
7. Test manual application and scheduled confirmation separately; verify the known confirmation dependency below before claiming completion.
8. Record web and mobile emptying, then sludge collection and feedback; verify application flags and containment dates.
9. Test direct-route permission enforcement for municipality, provider and treatment-plant users.

## Implementation Considerations

1. **Scheduled-confirmation blocker:** Current `ApplicationRequest.php` requires `supervisory_assessment_date` when `action_type=confirm`, and `ApplicationService.php` reads that value. This is an out-of-scope code dependency, not a supported supervisory-assessment stage for this application. Remove or otherwise resolve the dependency in code and tests before describing scheduled confirmation as fully working; this document does not add a field, role, permission or workflow step for it.
2. **Different scheduling populations:** Date allocation only updates never-emptied active containments, while the queue also includes status 3 and can include previously emptied containments. Keep this distinction visible in UI/QA expectations.
3. **Temporary IDs:** Regeneration deletes and recreates queue rows. Their IDs are not stable references.
4. **Dual provider coverage:** The CSV and normalized ward table must stay synchronized while both are read by schedule and reintegration queries.
5. **Concurrency:** The open-application uniqueness check has no database-level partial unique index. Concurrent submissions warrant testing.
6. **API response shape and storage:** Mobile responses use more than one boolean-key convention; file writes are not rolled back with database transactions.

## Source Traceability

This revision was compared with `documentations/code_documents/05 - Fecal Sludge IMS.md`, the prior FSM technical draft, `routes/web.php`, `routes/api.php`, the FSM controllers/services/requests/models named above, scheduling migrations from August 2026, and `database/seeders/PermissionsSeeder.php`. It is a source-code review, not a claim that deployment migrations or end-to-end tests were run.

The expanded provider workflows were checked against `ServiceProviderController.php`, `ServiceProviderService.php`, `ServiceProviderRequest.php`, `ServiceProvider.php` and `resources/views/fsm/service-providers/{create,edit,partial-form,show}.blade.php` on the clean branch. Existing regression references include `tests/Feature/Fsm/ServiceProviderCreationTest.php`, `ServiceProviderCrudTest.php` and the scheduled-desludging feature tests; the checklist above states what to verify, not a test-execution result.
