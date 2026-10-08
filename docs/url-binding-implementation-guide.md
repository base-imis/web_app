# IMIS Revamp URL Binding Implementation Guide

Date: 2026-07-09

## 1. Purpose

This document explains how URL binding should be implemented in IMIS Revamp after review feedback.

The goal is not to remove every use of `id` from the codebase.

The goal is to stop exposing internal database IDs in URLs where users can see, copy, or manually change them.

Example today:

```text
/fsm/feedback/12/edit
```

Target pattern:

```text
/fsm/feedback/7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1/edit
```

Internally, the app can still use the normal numeric `id` for joins, relationships, reports, filters, and foreign keys.

## 2. Updated Review Direction

Based on review feedback, we should not begin with a large implementation across FSM Application.

The better first move is:

```text
Use Feedback as a small pilot/reference module.
```

Reason:

- Feedback is smaller than the full Application flow.
- It still has real URL, controller, service, view, and permission behavior to test.
- It can become a checklist/reference for other developers.
- It avoids starting with a large central module before the team agrees on the final pattern.

Building IMS and BIN-related URLs should be treated as a higher-risk future focus because BIN can connect with tax, owner, and building records. But because FSM has already been studied, Feedback is a good first pilot.

## 3. Recommended Approach

Add a new field called `public_id` to URL-facing records.

Use UUID values.

Example:

```text
7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
```

Laravel generation:

```php
use Illuminate\Support\Str;

$publicId = (string) Str::uuid();
```

Simple rule:

```text
Use public_id in URLs.
Use id inside database logic.
```

## 4. What We Should Not Do

Do not replace the primary key `id`.

Do not rewrite all joins and foreign keys.

Do not remove numeric ID dependency from internal code in the first phase.

Do not start with a full project-wide conversion.

Do not assume URL binding is a replacement for backend permission checks.

## 5. Feedback Pilot Scope

The first pilot should focus on Feedback-related URLs and execution flow.

Likely files:

```text
routes/web.php
app/Models/Fsm/Feedback.php
app/Http/Controllers/Fsm/FeedbackController.php
resources/views/fsm/feedbacks/index.blade.php
resources/views/fsm/feedbacks/create.blade.php
resources/views/fsm/feedbacks/edit.blade.php
resources/views/fsm/feedbacks/show.blade.php
database/migrations/YYYY_MM_DD_HHMMSS_add_public_id_to_fsm_feedbacks_table.php
```

Related code may still touch:

```text
app/Models/Fsm/Application.php
app/Services/Fsm/ApplicationService.php
```

because Feedback is created from Application action buttons.

## 6. Implementation Steps

### Step 1: Add public_id Column

Create a new migration for `fsm.feedbacks`.

Example:

```php
Schema::table('fsm.feedbacks', function (Blueprint $table) {
    $table->uuid('public_id')->nullable()->unique();
});
```

Keep it nullable first so old records can be backfilled safely.

### Step 2: Backfill Existing Feedback Records

Generate UUIDs for old records that do not have `public_id`.

Example logic:

```php
Feedback::whereNull('public_id')
    ->chunkById(100, function ($feedbacks) {
        foreach ($feedbacks as $feedback) {
            $feedback->public_id = (string) Str::uuid();
            $feedback->save();
        }
    });
```

### Step 3: Auto-Generate public_id For New Records

Update `app/Models/Fsm/Feedback.php`.

Example:

```php
use Illuminate\Support\Str;

protected static function booted()
{
    static::creating(function ($feedback) {
        if (empty($feedback->public_id)) {
            $feedback->public_id = (string) Str::uuid();
        }
    });
}
```

### Step 4: Update Feedback Routes

Change only the Feedback URL-facing routes in the pilot.

For example, route parameters should clearly say they are public IDs:

```php
Route::get('feedback/{feedback_public_id}/edit', ...);
```

If using `Route::resource('feedback', ...)`, check generated routes for show, edit, update, and delete behavior.

### Step 5: Resolve public_id In Controller

The controller should receive `feedback_public_id`, find the record, and continue using internal `id` where needed.

Example:

```php
$feedback = Feedback::where('public_id', $feedbackPublicId)->firstOrFail();
```

If existing service or view logic needs numeric ID, pass:

```php
$feedback->id
```

### Step 6: Update Link Builders

Any link that opens Feedback detail, edit, history, or delete actions should use:

```php
$feedback->public_id
```

If Application list has a Feedback button, update only the URL parameter. Keep `application_id` as numeric if it is saved as a foreign key.

### Step 7: Keep Code-Level Execution Stable

This is important.

The implementation should not break current queries like:

```php
Feedback::where('application_id', $applicationId)
```

or joins like:

```text
feedbacks.application_id = applications.id
```

Those remain numeric and should not be changed during the pilot.

Only the public URL lookup changes.

## 7. Code-Level Execution Impact

### What Changes

- URL parameters change from numeric `id` to UUID `public_id`.
- Controllers add one lookup step by `public_id`.
- DataTable/Blade action links send `public_id`.
- Invalid UUID or unknown public ID returns 404.

### What Does Not Change

- Primary key remains `id`.
- Foreign keys remain numeric.
- Existing joins remain numeric.
- Reports can still use numeric IDs internally.
- Filters such as `application_id` can remain numeric unless they are part of a public URL.
- Database relationships do not need to be rebuilt.

### Query Impact

For a URL request, the first lookup changes from:

```php
Feedback::findOrFail($id);
```

to:

```php
Feedback::where('public_id', $publicId)->firstOrFail();
```

After the record is loaded, the rest of the logic can continue with:

```php
$feedback->id
$feedback->application_id
```

Add a unique index on `public_id` so this lookup stays fast.

## 8. Developer Checklist

For each module, developers should check:

- Does the table need `public_id`?
- Are old records backfilled?
- Is `public_id` generated for new records?
- Are public routes using `public_id`?
- Are controllers resolving by `public_id`?
- Are DataTable buttons using `public_id`?
- Are Blade links using `public_id`?
- Are AJAX URLs using `public_id` where visible?
- Are internal joins still using numeric `id`?
- Are permission checks still applied?
- Does invalid `public_id` return 404?
- Does unauthorized access still fail?
- Are tests/manual QA notes updated?

## 9. Policy Note For Future Projects

For future projects, any record that can be opened through a browser URL or public API should not expose the database primary key directly.

The standard should be:

```text
Public URL identifier: public_id
Internal database identifier: id
```

Developers should add `public_id` from the beginning for modules with show, edit, history, approval, report, or API detail routes.

## 10. Why This Is Important

URL binding makes the system safer and cleaner.

It reduces the chance of users guessing records by changing numbers in the URL. It also gives the team a consistent rule for future modules: public links should use public identifiers, while internal database logic can continue using normal IDs.

This is not a complete security solution by itself. Backend permission checks are still required.

## 11. Final Recommendation

Use Feedback as the pilot.

Do the work in a small controlled scope, create a checklist from it, then review with the team before applying the same approach to Building IMS/BIN and other higher-risk modules.
