# IMIS Revamp URL Binding Impact Analysis and Estimate

Date: 2026-07-09

## 1. Goal

This document explains the impact of introducing URL binding in IMIS Revamp.

The purpose is to hide internal database IDs from public URLs while keeping existing database execution stable.

This is a review document before development starts.

## 2. Updated Review Position

The previous analysis recommended FSM Application as the pilot.

After review, the better starting point is:

```text
Feedback as a small pilot/reference module.
```

Reason:

- Application is central and touches many connected flows.
- Starting there may make the first implementation larger than needed.
- Feedback is smaller and can still prove the full pattern.
- The output can become a checklist for other developers.

Building IMS/BIN should be considered a higher-risk target later because BIN may connect with tax, building, and owner data. But for the first controlled test, Feedback is a practical starting point.

## 3. Current Situation

The app has many routes that expose numeric IDs through:

- direct `{id}` routes;
- `Route::resource(...)` routes;
- DataTable action buttons;
- Blade links;
- JavaScript/AJAX URLs;
- API routes.

Controllers and services commonly use:

```php
find($id)
findOrFail($id)
where('id', $id)
```

This means the work is not just a route change. Each full workflow must be checked from list page to controller execution.

## 4. What Will Be Affected

### High Impact

- routes for the selected pilot module;
- model for the selected pilot table;
- migration and backfill;
- controller methods receiving URL IDs;
- DataTable and Blade action buttons;
- permission checks;
- QA checklist and developer handoff notes.

### Medium Impact

- API routes if the selected module is exposed through API;
- JavaScript-created URLs;
- redirects after create/update/delete;
- reports/history pages;
- documentation and policy notes.

### Low Impact

- internal joins;
- existing primary keys;
- existing foreign keys;
- most report queries;
- internal filters that are not exposed as public URLs.

## 5. Code-Level Execution Impact

This was the main missing part in the earlier document.

### Current Execution

Typical current flow:

```text
URL contains numeric id
Controller receives id
Controller/service runs find(id)
Business logic continues
```

Example:

```php
$feedback = Feedback::findOrFail($id);
```

### New Execution

Target flow:

```text
URL contains public_id
Controller receives public_id
Controller resolves the record by public_id
Business logic continues using internal id
```

Example:

```php
$feedback = Feedback::where('public_id', $feedbackPublicId)->firstOrFail();
```

After that, existing code can still use:

```php
$feedback->id
$feedback->application_id
```

### Query Impact

Only the first lookup from the URL changes.

Internal queries like these should usually stay unchanged:

```php
Feedback::where('application_id', $applicationId)
Application::findOrFail($feedback->application_id)
```

Joins should also stay unchanged:

```text
feedbacks.application_id = applications.id
```

### Performance Impact

The new lookup uses `public_id`.

To avoid slow queries, every `public_id` column must have a unique index.

With the index, normal record lookup impact should be small.

## 6. Main Risks

### Broken Links

Some links may still send numeric IDs after routes are changed.

Control:

- scan DataTable buttons;
- scan Blade links;
- scan JS/AJAX URLs;
- test the full workflow.

### Missed Backfill

Old records may not have `public_id`.

Control:

- make column nullable first;
- backfill old records;
- verify missing/duplicate values;
- make it required later only after confirmation.

### False Security Feeling

UUIDs reduce guessing, but they do not replace authorization.

Control:

- keep role and permission checks;
- test unauthorized access;
- confirm record-level rules still apply.

### Scope Creep

Trying to convert all modules at once can break many flows.

Control:

- start with Feedback pilot;
- document checklist;
- expand only after review.

## 7. Pilot Scope: Feedback

Suggested pilot files:

```text
routes/web.php
app/Models/Fsm/Feedback.php
app/Http/Controllers/Fsm/FeedbackController.php
app/Services/Fsm/ApplicationService.php
resources/views/fsm/feedbacks/index.blade.php
resources/views/fsm/feedbacks/create.blade.php
resources/views/fsm/feedbacks/edit.blade.php
resources/views/fsm/feedbacks/show.blade.php
database/migrations/YYYY_MM_DD_HHMMSS_add_public_id_to_fsm_feedbacks_table.php
```

`ApplicationService` is included because Application list buttons create/open Feedback actions.

## 8. Suggested Work Plan

### Phase 1: Review and Agreement

Tasks:

- confirm `public_id` as the field name;
- confirm UUID as the value;
- confirm Feedback as the pilot;
- confirm internal IDs will remain for joins and foreign keys;
- confirm QA checklist.

Estimate:

```text
0.5 to 1 day
```

### Phase 2: Database and Model

Tasks:

- add nullable `public_id` to `fsm.feedbacks`;
- add unique index;
- backfill old feedback records;
- auto-generate `public_id` for new feedback records.

Estimate:

```text
1 day
```

### Phase 3: Routes and Controller

Tasks:

- update Feedback URL parameters;
- resolve Feedback by `public_id`;
- keep internal logic using numeric `id`;
- make invalid public IDs return 404.

Estimate:

```text
1 day
```

### Phase 4: Links and UI Flow

Tasks:

- update Feedback action buttons;
- update Blade links;
- update DataTable actions;
- update redirects if any;
- confirm Application-to-Feedback flow.

Estimate:

```text
1 to 2 days
```

### Phase 5: Testing and Checklist

Tasks:

- test valid Feedback links;
- test invalid public IDs;
- test unauthorized access;
- test create/edit/show/delete where applicable;
- write final developer checklist from the pilot.

Estimate:

```text
1 day
```

## 9. Estimate Summary

| Scope | Estimated Time |
| --- | --- |
| Feedback pilot only | 3 to 5 working days |
| Pilot plus checklist and policy note | 4 to 6 working days |
| Applying pattern to main FSM modules | 2 to 3 weeks |
| Building/BIN-focused rollout | Needs separate review; likely 1 to 2 weeks for core Building flows |
| Full app rollout | 4 to 6 weeks |

## 10. QA Checklist For Pilot

- existing Feedback records have `public_id`;
- new Feedback records get `public_id`;
- Feedback list loads;
- Feedback show/edit links use `public_id`;
- numeric ID URL does not expose data unless a temporary route is intentionally kept;
- invalid public ID returns 404;
- unauthorized user cannot access restricted Feedback;
- Application-to-Feedback button works;
- internal `application_id` save/update behavior still works;
- reports/exports using Feedback are not broken.

## 11. Expected Output From Pilot

The pilot should produce:

- a working Feedback URL-binding example;
- a reusable developer checklist;
- a small policy note for future modules/projects;
- a few lines explaining why URL binding matters;
- clear decision points before moving to Building IMS/BIN.

## 12. Final Assessment

The implementation is useful, but it should start small.

Feedback is a better pilot than Application because it is easier to review and safer to adjust. Application and Building/BIN can be handled after the team agrees on the pattern from the pilot.
