# URL Binding Technical Steps With Schema-Wise Migration

Date: 2026-08-04

Project: IMIS Revamp / `lang_web_app`

## 1. Purpose

This document explains how to add `public_id` using a schema-wise migration approach.

The goal is to avoid creating one migration for every tiny table, but also avoid one risky migration for the whole application.

Recommended approach:

```text
One migration per selected schema/module group.
```

Example:

```text
add_public_id_to_selected_fsm_tables
add_public_id_to_selected_building_info_tables
add_public_id_to_selected_public_health_tables
```

## 2. Main Rule

Only add `public_id` to tables that are used in user-facing URLs or public API URLs.

Do not add `public_id` to every table just because it has an `id`.

## 3. Recommended public_id

Use UUID.

Example:

```text
7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
```

Laravel generation:

```php
(string) Str::uuid()
```

## 4. Good Migration Scope

Good:

```text
Add public_id to selected FSM tables that appear in URLs.
```

Example selected FSM tables:

```text
fsm.feedbacks
fsm.emptyings
fsm.sludge_collections
```

Maybe later:

```text
fsm.applications
fsm.containments
fsm.ctpts
fsm.service_providers
```

Avoid:

```text
Add public_id to every table in the database.
```

That is harder to review, harder to test, and harder to roll back.

## 5. Suggested Migration File

Example file name:

```text
database/migrations/YYYY_MM_DD_HHMMSS_add_public_id_to_selected_fsm_tables.php
```

## 6. Example Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddPublicIdToSelectedFsmTables extends Migration
{
    private array $tables = [
        'fsm.feedbacks',
        'fsm.emptyings',
        'fsm.sludge_collections',
    ];

    public function up()
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('public_id')->nullable()->unique();
            });
        }

        foreach ($this->tables as $tableName) {
            $this->backfillPublicIds($tableName);
        }
    }

    public function down()
    {
        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('public_id');
            });
        }
    }

    private function backfillPublicIds(string $tableName): void
    {
        DB::table($tableName)
            ->whereNull('public_id')
            ->orderBy('id')
            ->select('id')
            ->chunkById(1000, function ($records) use ($tableName) {
                foreach ($records as $record) {
                    DB::table($tableName)
                        ->where('id', $record->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                }
            });
    }
}
```

## 7. Why chunkById Is Used

For old records, we need to fill `public_id`.

Instead of loading all rows at once, `chunkById(1000)` loads records in batches.

This is safer for bigger tables.

For example, if a table has 50,000 rows:

```text
50,000 rows / 1,000 batch size = 50 batches
```

This is better than loading all 50,000 rows in memory at once.

## 8. What Happens To Old Records

Old records are not deleted or changed in their existing data.

Before:

```text
id | application_id | comments
1  | 25             | Good service
```

After:

```text
id | application_id | comments      | public_id
1  | 25             | Good service  | 7b6f0c8a-9a2f-4f01-a14d-85c96d6a92c1
```

Only the new `public_id` column is filled.

## 9. Important Production Safety Note

For small or medium tables, backfilling inside migration is acceptable.

For very large tables, do not backfill everything inside the migration.

Better large-table approach:

1. Migration adds nullable `public_id`.
2. Separate Artisan command backfills data in batches.
3. Verify all records have `public_id`.
4. Later migration makes the column required if needed.

## 10. Shared Model Trait

Use a shared trait so every selected model generates `public_id` the same way.

Suggested file:

```text
app/Models/Concerns/HasPublicId.php
```

Example:

```php
<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicId
{
    protected static function bootHasPublicId()
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
        });
    }
}
```

Then in each model:

```php
use App\Models\Concerns\HasPublicId;

class Feedback extends Model
{
    use HasPublicId;
}
```

## 11. Route And Controller Rule

Migration alone does not complete URL binding.

After adding `public_id`, routes and controllers must use it.

Before:

```php
Feedback::findOrFail($id);
```

After:

```php
Feedback::where('public_id', $feedbackPublicId)->firstOrFail();
```

Internal logic can still use:

```php
$feedback->id
$feedback->application_id
```

## 12. What Should Stay Numeric

Keep these as numeric IDs:

```text
primary key id
foreign keys
application_id
joins
reports
internal filters
revision/history IDs
```

Only public URLs should use `public_id`.

## 13. Verification Queries

After migration, check each table.

Example:

```sql
select count(*)
from fsm.feedbacks
where public_id is null;
```

Expected:

```text
0
```

Check sample values:

```sql
select id, public_id
from fsm.feedbacks
order by id desc
limit 10;
```

## 14. Recommended Rollout

1. Start with selected FSM tables only.
2. Add `public_id` with one schema-wise migration.
3. Backfill records in chunks.
4. Add shared `HasPublicId` trait.
5. Update only one small module route first, such as Feedback.
6. Test it fully.
7. Then continue route changes module by module.

## 15. Final Recommendation

Schema-wise migration is okay if it is limited to selected URL-facing tables.

Best option:

```text
Schema-wise migration for selected tables
+ shared trait for UUID generation
+ route/controller rollout one module at a time
```

Avoid one global migration for every table in the project.

