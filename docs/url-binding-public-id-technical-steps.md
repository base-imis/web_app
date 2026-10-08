# URL Binding Public ID Technical Steps

Date: 2026-06-26

Project: IMIS Revamp / `lang_web_app`

## Purpose

This document explains what the `public_id` should be and how we should implement it step by step.

The main idea is simple:

```text
Do not expose database id in URL.
Expose public_id in URL.
Keep database id for internal work.
```

## What Should public_id Be?

Use a UUID.

Example:

```text
7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
```

Recommended database column:

```php
$table->uuid('public_id')->nullable()->unique();
```

## Why UUID?

UUID is good for this case because:

- it does not reveal record count;
- users cannot easily guess the next record;
- it is supported well in Laravel;
- it can be added without changing the existing primary key;
- existing joins and relations can keep using `id`.

## What Not To Do

Do not replace the current `id` column.

Do not use encrypted numeric IDs as the first option.

Do not use simple encoded IDs like Base64, because they can be decoded.

Do not change all modules in one shot.

## Final URL Pattern

Before:

```text
/fsm/application/12/history
```

After:

```text
/fsm/application/7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1/history
```

## Step 1: Start With One Module

Start with:

```text
FSM Application
```

Main table:

```text
fsm.applications
```

Main model:

```text
app/Models/Fsm/Application.php
```

Main controller:

```text
app/Http/Controllers/Fsm/ApplicationController.php
```

Main service:

```text
app/Services/Fsm/ApplicationService.php
```

## Step 2: Add public_id Column

Create a new migration:

```text
database/migrations/YYYY_MM_DD_HHMMSS_add_public_id_to_fsm_applications_table.php
```

Example:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fsm.applications', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->unique();
        });
    }

    public function down()
    {
        Schema::table('fsm.applications', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
```

Keep it nullable first because old records do not have values yet.

## Step 3: Backfill Old Records

After adding the column, fill `public_id` for existing records.

This can be done through a command, seeder, or one-time script.

Example logic:

```php
use App\Models\Fsm\Application;
use Illuminate\Support\Str;

Application::whereNull('public_id')
    ->chunkById(100, function ($applications) {
        foreach ($applications as $application) {
            $application->public_id = (string) Str::uuid();
            $application->save();
        }
    });
```

After backfill, every existing application should have a `public_id`.

## Step 4: Generate public_id For New Records

Update:

```text
app/Models/Fsm/Application.php
```

Example:

```php
use Illuminate\Support\Str;

protected static function booted()
{
    static::creating(function ($application) {
        if (empty($application->public_id)) {
            $application->public_id = (string) Str::uuid();
        }
    });
}
```

This means every new application automatically gets a public ID.

## Step 5: Decide How To Resolve public_id

There are two options.

### Option A: Manual Resolve

Example:

```php
$application = Application::where('public_id', $publicId)->firstOrFail();
```

This is recommended for the first phase because the old services still expect numeric IDs.

### Option B: Laravel Route Model Binding

Example:

```php
public function getRouteKeyName()
{
    return 'public_id';
}
```

This is clean, but it can affect all routes for that model. Use it only after checking the full module.

## Step 6: Update Routes

Update:

```text
routes/web.php
```

Before:

```php
Route::get('application/{id}/history', 'ApplicationController@history')->name('application.history');
```

After:

```php
Route::get('application/{application_public_id}/history', 'ApplicationController@history')->name('application.history');
```

Also update:

```text
application/{id}/application-report
emptying/create/{id}
feedback/editFeedback/{id}
sludge-collection/create/{id}
```

These routes are connected to the application flow.

## Step 7: Update Controller Methods

Update:

```text
app/Http/Controllers/Fsm/ApplicationController.php
```

Before:

```php
public function history($id)
{
    return $this->applicationService->getApplicationHistory($id);
}
```

After:

```php
public function history($applicationPublicId)
{
    $application = Application::where('public_id', $applicationPublicId)->firstOrFail();

    return $this->applicationService->getApplicationHistory($application->id);
}
```

This keeps the service unchanged at first.

## Step 8: Update Service Generated Links

Update:

```text
app/Services/Fsm/ApplicationService.php
```

Many table action buttons are generated here.

Before:

```php
route('application.history', [$model->id])
```

After:

```php
route('application.history', [$model->public_id])
```

Same rule for:

```text
view
edit
history
report
emptying create
feedback create
sludge collection create
```

## Step 9: Update API Route

Update:

```text
routes/api.php
app/Http/Controllers/Api/ApiServiceController.php
```

Before:

```text
/api/revamp/application/{id}
```

After:

```text
/api/revamp/application/{application_public_id}
```

Controller should resolve:

```php
$application = Application::where('public_id', $applicationPublicId)->firstOrFail();
```

If mobile app or another client still uses numeric ID, keep old route temporarily and remove it later.

## Step 10: Validate public_id

Add a route pattern if needed:

```php
Route::pattern('application_public_id', '[0-9a-fA-F-]{36}');
```

This helps prevent random values from reaching deeper logic.

## Step 11: Add Index And Make It Required Later

First migration:

```php
$table->uuid('public_id')->nullable()->unique();
```

After backfill and testing, a later migration can make it not nullable.

Final target:

```text
public_id must be unique and not null
```

## Step 12: Test Properly

Test these URLs:

```text
/fsm/application/{public_id}
/fsm/application/{public_id}/edit
/fsm/application/{public_id}/history
/fsm/application/{public_id}/application-report
/fsm/emptying/create/{application_public_id}
/fsm/feedback/editFeedback/{application_public_id}
/fsm/sludge-collection/create/{application_public_id}
/api/revamp/application/{application_public_id}
```

Also test:

- numeric ID should not expose the record;
- invalid UUID should show 404;
- user permissions still work;
- list buttons open correct records;
- old records and new records both work;
- exports/reports still work.

## Step 13: Repeat Module By Module

After FSM Application is stable, repeat the same pattern for:

```text
fsm.emptyings
fsm.feedbacks
fsm.sludge_collections
fsm.containments
fsm.service_providers
building_info.buildings
building_info.building_surveys
auth.users
language.languages
```

Do not convert every table immediately. Convert only the tables that appear in public URLs or API URLs.

## Important Rule For Developers

When building URLs:

```php
$model->public_id
```

When joining tables or saving foreign keys:

```php
$model->id
```

That rule will avoid most mistakes.

## Rollout Plan

1. Add `public_id` to `fsm.applications`.
2. Backfill old application records.
3. Generate `public_id` on new application creation.
4. Update application URLs and buttons.
5. Update connected emptying, feedback, and sludge collection create routes.
6. Update API application detail route.
7. Test browser and API flows.
8. Deploy with old numeric route support only if required.
9. Remove old numeric routes after users/API clients are updated.

## Expected Time

For FSM Application pilot:

```text
2 to 4 working days
```

For complete FSM module:

```text
2 to 3 weeks
```

For full app:

```text
4 to 6 weeks
```
