# URL Binding Research - Current Branch

Date: 2026-07-09

Branch checked earlier:

```text
v1.3.0-nsd
```

## 1. Summary

The current branch exposes numeric database IDs in multiple browser and API routes.

There is no existing app-level `public_id`, UUID route binding, ULID, Hashids, or model `getRouteKeyName()` pattern in the code.

The earlier research was valid, but the implementation starting point should be adjusted based on review feedback.

## 2. Updated Recommendation

Do not start with a broad FSM Application conversion.

Start with a small Feedback pilot.

Why:

- Feedback is smaller and easier to review.
- It still includes route, controller, model, view, link, and permission behavior.
- It can become a reference implementation for other developers.
- It avoids changing a central module before the team agrees on the exact standard.

Building IMS/BIN should be treated as a higher-risk later target because BIN may connect with tax, owner, and building records.

## 3. Existing URL Exposure Found

The app has exposed ID routes in:

```text
routes/web.php
routes/api.php
```

Examples include:

```text
application/{id}/history
application/{id}/application-report
emptying/create/{id}
feedback/editFeedback/{id}
sludge-collection/create/{id}
containments/{id}/history
ctpt/{id}/history
buildings/{id}/history
building-surveys/{id}/approve
nsd-setting/{id}/edit
waterborne/{id}/history
hotspots/{id}/history
/api/revamp/application/{id}
/api/revamp/containment/{application_id}
```

There are also many `Route::resource(...)` declarations. These can create show/edit/update/delete routes that also carry an ID-like parameter.

## 4. Feedback Pilot Research

Feedback is connected to Application, but the URL binding pilot can still be kept small.

Relevant areas:

```text
routes/web.php
app/Models/Fsm/Feedback.php
app/Http/Controllers/Fsm/FeedbackController.php
app/Services/Fsm/ApplicationService.php
resources/views/fsm/feedbacks/index.blade.php
resources/views/fsm/feedbacks/create.blade.php
resources/views/fsm/feedbacks/edit.blade.php
resources/views/fsm/feedbacks/show.blade.php
```

Why `ApplicationService` matters:

```text
Application list/action buttons can create or open Feedback-related routes.
```

So even if the pilot is Feedback, we must check where Feedback links are generated from Application screens.

## 5. Suggested public_id Pattern

Use UUID `public_id`.

Example:

```text
7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
```

Laravel generation:

```php
use Illuminate\Support\Str;

$publicId = (string) Str::uuid();
```

Rule:

```text
URLs use public_id.
Database logic uses id.
```

## 6. Code-Level Execution Impact

This is the key point to add for review.

### Before

```text
URL has numeric id
Controller receives id
Controller/service runs find(id)
```

Example:

```php
$feedback = Feedback::findOrFail($id);
```

### After

```text
URL has public_id
Controller receives public_id
Controller resolves record by public_id
Internal logic continues with id
```

Example:

```php
$feedback = Feedback::where('public_id', $feedbackPublicId)->firstOrFail();
```

Then existing code can still use:

```php
$feedback->id
$feedback->application_id
```

### What Should Stay Numeric

These should not be changed in the first pilot:

```text
primary key id
foreign keys
application_id
joins
report queries
internal filters
revision/history record IDs
```

Example:

```text
feedbacks.application_id = applications.id
```

This remains correct.

## 7. Implementation Checklist For Pilot

1. Add nullable `public_id` column to `fsm.feedbacks`.
2. Add unique index on `public_id`.
3. Backfill existing Feedback rows.
4. Auto-generate `public_id` for new Feedback rows.
5. Update Feedback URL routes to accept `feedback_public_id`.
6. Resolve Feedback by `public_id` in controller.
7. Keep internal logic using numeric `id`.
8. Update Feedback action links to use `public_id`.
9. Check Application-generated Feedback buttons.
10. Test valid public ID URL.
11. Test invalid public ID URL.
12. Test unauthorized access.
13. Confirm `application_id` save/update behavior still works.
14. Write final checklist for other developers.

## 8. Developer Policy Draft

For future IMIS modules and future projects:

```text
Do not expose database primary keys directly in user-facing URLs.
```

Recommended standard:

```text
public_id = public URL/API identifier
id = internal database identifier
```

Any module with show, edit, update, delete, history, approval, report, or public API detail routes should plan `public_id` from the beginning.

## 9. Why This Matters

URL binding reduces easy URL-level record guessing.

It also gives the team a clear development standard:

```text
Public links should not depend on database IDs.
```

This does not replace backend security. Permission and ownership checks must still block unauthorized access.

## 10. Later Priority Areas

After the Feedback pilot, review these areas:

### Building IMS / BIN

This should be prioritized because BIN can be connected with:

```text
building records
owner records
tax records
survey records
```

### Application and FSM Flows

Application, Emptying, Feedback, Sludge Collection, and Containment are connected. Once the pilot is approved, these can be converted carefully module by module.

### API Routes

API routes should be changed only after checking consumers:

```text
/api/revamp/application/{id}
/api/revamp/containment/{application_id}
```

If any mobile or external client uses numeric IDs, keep temporary compatibility.

## 11. Final Recommendation

Use Feedback as the pilot and review the result with the team.

The pilot should produce three outputs:

- detailed developer checklist;
- short policy note for future projects;
- short explanation of why URL binding is important.

After that, decide whether to continue with Building IMS/BIN or connected FSM modules.
