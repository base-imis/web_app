# BASEIMIS-20: New Technical Changes to Resolve Slow Login and Dashboard Loading

**Date:** 2026-08-12  
**Status:** Technical implementation specification  
**Related Jira:** [BASEIMIS-20 — Dashboard optimization](https://jira.innovativesolution.com.np/browse/BASEIMIS-20)  
**Scope:** Login redirect and home dashboard only

## 1. Objective

Change the dashboard so authentication and the first visible page do not wait for dashboard count and chart queries.

The new implementation will:

- keep login processing small;
- return a dashboard shell without aggregate queries;
- load permitted summary boxes and charts through separate endpoints;
- remove unused queries;
- reuse shared dashboard query services;
- cache results for 10 minutes with authorization-safe keys;
- rewrite confirmed slow SQL;
- add only indexes proven by execution plans.

## 2. Target request flow

### Current flow

```text
POST /login
  -> authenticate
  -> redirect /
  -> redirect /dashboard
  -> execute every count and chart query
  -> render dashboard
```

### New flow

```text
POST /login
  -> authenticate
  -> redirect directly to /dashboard
  -> render dashboard shell immediately
  -> request /dashboard/summary
  -> request authorized report groups (maximum two concurrently)
```

The `/dashboard` shell request must execute no dashboard aggregate queries.

## 3. Routes to add or change

Modify `routes/web.php`.

All dashboard routes must remain inside the existing `auth` middleware group.

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])
        ->name('dashboard.index');

    Route::get('/dashboard/summary', [DashboardSummaryController::class, 'show'])
        ->name('dashboard.summary');

    Route::get('/dashboard/reports/{group}', [DashboardReportController::class, 'show'])
        ->whereIn('group', [
            'buildings-sanitation',
            'fsm',
            'payments',
            'utilities-health',
        ])
        ->name('dashboard.reports.show');
});
```

Change the successful login redirect to the named dashboard route instead of redirecting through `/`.

## 4. Login controller change

Modify:

```text
app/Http/Controllers/Auth/LoginController.php
```

Replace the separate credential validation and user retrieval with one authentication operation.

Required behavior:

1. Call `Auth::attempt($credentials, $remember)` once.
2. Return the existing invalid-credential error when authentication fails.
3. Retrieve the authenticated user through `Auth::user()`.
4. Preserve the existing blocked-role check.
5. If a blocked role is found:
   - log the user out;
   - invalidate the session;
   - regenerate the CSRF token;
   - return the existing authorization error.
6. For an allowed user:
   - regenerate the session ID;
   - redirect directly to `route('dashboard.index')`.

Do not execute, dispatch, or warm dashboard queries from the login controller.

## 5. Home controller change

Modify:

```text
app/Http/Controllers/HomeController.php
```

`HomeController@index()` must become a shell-only action.

It may provide:

- page title;
- endpoint URLs;
- list of report groups that the current user may request;
- non-database presentation configuration.

It must not call:

- dashboard aggregate services;
- building, containment, FSM, payment, utility, or health models;
- `DB::select()` or `DB::table()` for dashboard data;
- cache warming jobs.

Expected shape:

```php
public function index(): View
{
    return view('dashboard.indexAdmin', [
        'pageTitle' => __('IMIS Dashboard'),
        'summaryUrl' => route('dashboard.summary'),
        'reportGroups' => $this->reportAccess->allowedGroupsFor(auth()->user()),
    ]);
}
```

## 6. New controllers

### Dashboard summary controller

Create:

```text
app/Http/Controllers/Dashboard/DashboardSummaryController.php
```

Responsibilities:

- validate supported filters such as year;
- determine which count-box groups the user may access;
- build the correct cache scope;
- retrieve summary data from `DashboardSummaryService`;
- return JSON or one summary Blade partial;
- never execute a query for an unauthorized widget.

### Dashboard report controller

Create:

```text
app/Http/Controllers/Dashboard/DashboardReportController.php
```

Responsibilities:

- validate the report-group route value;
- validate filters;
- authorize the group and each included widget;
- build the cache scope;
- retrieve data from `DashboardReportService`;
- return JSON or the matching group partial;
- return an independent error without affecting other groups.

The controllers must not contain raw dashboard SQL.

## 7. New shared service layer

Create:

```text
app/Services/Dashboard/DashboardSummaryService.php
app/Services/Dashboard/DashboardReportService.php
app/Services/Dashboard/DashboardCacheKey.php
app/Services/Dashboard/DashboardAccessService.php
```

### DashboardSummaryService

Provide reusable methods such as:

```php
buildingCounts(): array
sanitationCounts(): array
utilityTotals(): array
fsmTotals(DashboardScope $scope): array
toiletCounts(): array
publicHealthTotals(): array
```

Related values must be calculated together. For example, all functional-use building counts must come from one grouped query.

### DashboardReportService

Provide one method per supported group:

```php
buildingsAndSanitation(DashboardScope $scope, DashboardFilters $filters): array
fsm(DashboardScope $scope, DashboardFilters $filters): array
payments(DashboardScope $scope, DashboardFilters $filters): array
utilitiesAndHealth(DashboardScope $scope, DashboardFilters $filters): array
```

The same service/query methods must be reusable by the home, building, FSM, and utility dashboards.

### DashboardCacheKey

Create cache keys centrally. Controllers and services must not construct cache-key strings independently.

### DashboardAccessService

Map application permissions to summary sections, report groups, and individual widgets. Authorization must occur before each query is selected for execution.

## 8. Data transfer objects

Create small immutable data objects or equivalent validated value objects:

```text
app/Data/Dashboard/DashboardScope.php
app/Data/Dashboard/DashboardFilters.php
```

`DashboardScope` contains:

- role scope;
- service-provider ID when applicable;
- treatment-plant ID when applicable;
- municipality/entity scope if applicable.

`DashboardFilters` contains:

- selected year;
- locale if translated labels affect cached output;
- any future supported filter.

Do not pass raw request objects into the query layer.

## 9. Cache implementation

Use Laravel's cache abstraction. Start with the configured cache driver; Redis is not required for the first delivery.

### TTL

```text
10 minutes
```

### Global data key

```text
dashboard:v1:global:{locale}:{group}:{year-or-all}:{data-version}
```

### Restricted data key

```text
dashboard:v1:scope:{role-scope}:{service-provider-id}:{treatment-plant-id}:{locale}:{group}:{year-or-all}:{data-version}
```

### Required cache rules

- Never put provider-specific or treatment-plant-specific data in a global key.
- Include every filter that changes the returned value.
- Authorize the current request before reading a cached value.
- Cache raw data, not a full user-specific page.
- Use a versioned prefix to invalidate incompatible data shapes.
- Prevent cache stampedes using a cache lock where the configured driver supports it.
- If locking is unavailable, tolerate one recalculation and keep endpoint concurrency limited.
- Do not store authorization decisions in the data cache.

### Cache invalidation

First delivery:

- rely on the 10-minute TTL;
- provide an Artisan command to clear only dashboard keys by version/scope.

Later delivery:

- invalidate affected groups after imports and relevant writes;
- consider scheduled warming for global groups only.

## 10. Dashboard view changes

Modify:

```text
resources/views/dashboard/indexAdmin.blade.php
```

Create:

```text
resources/views/dashboard/partials/loading.blade.php
resources/views/dashboard/partials/error.blade.php
resources/views/dashboard/summary.blade.php
resources/views/dashboard/report-groups/buildings-sanitation.blade.php
resources/views/dashboard/report-groups/fsm.blade.php
resources/views/dashboard/report-groups/payments.blade.php
resources/views/dashboard/report-groups/utilities-health.blade.php
```

The main view must contain placeholders only. Existing cards/chart partials may be reused inside the new response partials.

Each asynchronous section requires:

- a loading state;
- `aria-busy`/accessible status text;
- an independent error state;
- a retry action;
- no full-page failure when one section fails.

## 11. Frontend loader

Create a dedicated dashboard loader, for example:

```text
resources/js/dashboard-loader.js
```

Required behavior:

1. After `DOMContentLoaded`, request the summary endpoint.
2. Render the summary when available.
3. Queue the allowed report groups.
4. Run at most two report requests concurrently.
5. Render each response into its own container.
6. Initialize Chart.js only after the relevant container/data is ready.
7. Display retry UI for a failed group.
8. Detect an expired session and redirect to login.
9. Cancel outstanding requests when navigating away where supported.

Prefer JSON responses and explicit chart initialization for the long-term design. If server-rendered partial HTML is used initially, script execution must be controlled and tested rather than injecting arbitrary inline scripts.

## 12. Remove unused dashboard work

Remove calculations from `HomeController@index` that are not consumed by the home dashboard.

The reviewed code currently calculates unused values including:

- monthly emptying data;
- emptying services by ward;
- monthly application requests by operator;
- FSM feedback and PPE data;
- hotspots by ward;
- emptying requests by structure type;
- containment distributions not present in the current home view;
- next and proposed emptying data;
- duplicate sanitation “others” output;
- tax-code presence by ward;
- direct counts not rendered by the view.

If another module uses these datasets, retain them only in the shared report service and request them from that module's endpoint.

## 13. SQL changes

### 13.1 Consolidate building-use counts

Replace six separate counts with one grouped query.

Target shape:

```sql
SELECT
    CASE
        WHEN fu.name = 'Residential' THEN 'residential'
        WHEN fu.name = 'Commercial' THEN 'commercial'
        WHEN fu.name = 'Industrial' THEN 'industrial'
        WHEN fu.name = 'Educational' THEN 'educational'
        WHEN fu.name ILIKE '%Institution%' THEN 'institution'
        WHEN fu.name IN (
            'Mixed (Residential + Commercial)',
            'Mixed (Residential, Commercial, Office uses)'
        ) THEN 'mixed'
        ELSE 'others'
    END AS category,
    COUNT(*) AS total
FROM building_info.buildings b
LEFT JOIN building_info.functional_uses fu
    ON fu.id = b.functional_use_id
WHERE b.deleted_at IS NULL
GROUP BY category;
```

Verify the exact functional-use names in production data before finalizing the mapping.

### 13.2 Rewrite road and sewer spatial queries

Add an index-supported spatial join condition before calculating intersections.

```sql
SELECT
    w.ward,
    ROUND(SUM(
        ST_Length(
            ST_Transform(ST_Intersection(r.geom, w.geom), 32645)
        )
    )::numeric, 2) AS length
FROM layer_info.wards w
JOIN utility_info.roads r
    ON ST_Intersects(r.geom, w.geom)
WHERE r.deleted_at IS NULL
GROUP BY w.ward
ORDER BY w.ward;
```

Apply the equivalent change to sewers.

Confirm geometry SRIDs and the intended measurement projection before release.

### 13.3 Rewrite containment totals as one pass

Use one grouped query plus a window aggregate for per-ward totals.

```sql
SELECT
    b.ward,
    ct.map_display AS containment_type,
    COUNT(*) AS containment_count,
    SUM(COUNT(*)) OVER (PARTITION BY b.ward) AS ward_total
FROM fsm.containments c
JOIN building_info.buildings b
    ON b.bin = c.responsible_bin
JOIN fsm.containment_types ct
    ON ct.id = c.type_id
WHERE c.deleted_at IS NULL
GROUP BY b.ward, ct.map_display
ORDER BY b.ward, ct.map_display;
```

### 13.4 Use index-friendly date predicates

Replace predicates such as:

```sql
TO_CHAR(emptied_date, 'YYYY') = :year
EXTRACT(YEAR FROM created_at) = :year
```

with ranges:

```sql
emptied_date >= :year_start
AND emptied_date < :next_year_start
```

This allows PostgreSQL to use date indexes when selective.

### 13.5 Parameterize raw SQL

Replace interpolated role IDs and year strings with query bindings. No user, provider, plant, or year value should be concatenated into SQL.

## 14. Candidate database indexes

Indexes must be added only after the final rewritten query is measured with and without the candidate index.

Candidates:

```sql
CREATE INDEX CONCURRENTLY ...
ON building_info.buildings (functional_use_id)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON building_info.buildings (sanitation_system_id)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON building_info.buildings (ward)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.containments (responsible_bin, type_id)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.applications (service_provider_id, emptying_status)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.emptyings (application_id)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.emptyings (service_provider_id, emptied_date)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.sludge_collections (treatment_plant_id, date)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.build_toilets (toilet_id, bin)
WHERE deleted_at IS NULL;

CREATE INDEX CONCURRENTLY ...
ON fsm.ctpt_users (toilet_id)
WHERE deleted_at IS NULL;
```

Before creating an index:

1. save the original `EXPLAIN (ANALYZE, BUFFERS)` plan;
2. test the rewritten SQL without the candidate index;
3. create/test the index in a non-production environment;
4. save the new plan and write-performance observation;
5. retain only indexes with meaningful benefit;
6. check for equivalent existing indexes.

Laravel migrations must account for PostgreSQL's requirement that `CREATE INDEX CONCURRENTLY` not run inside a transaction.

## 15. Materialized summary decision

Do not materialize every chart.

After SQL rewrites and index testing, introduce maintained summaries only for queries that remain above the agreed threshold, especially:

- road length by ward;
- sewer length by ward;
- repeated PostGIS intersection totals.

Possible implementation:

```text
dashboard.ward_utility_length_summary
```

Suggested columns:

- ward;
- utility type;
- total length;
- calculated timestamp;
- source/data version.

Refresh after the related utility import/update or through a scheduled asynchronous command. Never refresh during login or the dashboard shell request.

## 16. Tests to add

### Feature tests

Create tests covering:

- successful login redirects directly to the dashboard;
- blocked roles still cannot log in;
- remember-me remains functional;
- dashboard shell requires authentication;
- dashboard shell executes no dashboard aggregate queries;
- summary endpoint requires authentication;
- each report group requires authentication;
- invalid report groups return 404;
- unauthorized widgets are neither returned nor queried;
- service-provider data is scoped correctly;
- treatment-plant data is scoped correctly;
- one report failure does not affect other endpoint responses.

### Cache tests

- first request calculates and caches data;
- second request within 10 minutes is a cache hit;
- cache expires correctly;
- different years use different keys;
- different providers use different keys;
- different treatment plants use different keys;
- global and restricted keys cannot collide;
- authorization is still evaluated on a cache hit.

### Query/service tests

- grouped building counts match current correct totals;
- containment window totals match current correct totals;
- road/sewer results match current values within an approved tolerance;
- soft-deleted records are excluded;
- provider and plant filters are parameterized and applied;
- empty datasets return zero/empty chart structures safely.

### Frontend tests

- summary loads before charts;
- no more than two report requests run simultaneously;
- loading states are visible;
- a failed group displays retry UI;
- retry replaces the error with rendered data;
- session expiry redirects to login;
- charts initialize once without duplicate handlers.

## 17. Instrumentation

Add structured timing for:

- login POST;
- dashboard shell;
- summary endpoint;
- each report group;
- cache hit/miss;
- query count and slowest query per endpoint.

Do not log credentials, session tokens, SQL bindings containing sensitive values, or cached restricted data.

Recommended fields:

```text
request_id
endpoint
role_scope
report_group
cache_status
query_count
database_time_ms
total_time_ms
```

## 18. Performance acceptance targets

Validate against production-like data:

| Operation | Target |
|---|---:|
| Login POST and redirect before dashboard data | p95 under 500 ms |
| Dashboard shell | p95 under 300 ms |
| Dashboard aggregate queries in shell | 0 |
| Summary, warm cache | p95 under 150 ms |
| Summary, cold cache | p95 under 750 ms |
| Report group, warm cache | p95 under 200 ms |

An uncached report query remaining above 500 ms must have a documented follow-up decision for materialization or further optimization.

## 19. Rollout plan

1. Capture production-like baseline metrics.
2. Implement shared services and tests.
3. Remove unused queries.
4. Add the shell and endpoints behind a feature flag.
5. Enable for test/staging roles.
6. Validate values, permissions, cache isolation, and performance.
7. Enable in production gradually.
8. Monitor endpoint time, database load, cache misses, and errors.
9. Apply proven indexes separately from the application release where possible.
10. Decide on materialized summaries only after post-release measurements.

## 20. Rollback plan

The feature flag must allow the application to return to the original synchronous dashboard while preserving authentication.

Rollback actions:

- disable the asynchronous dashboard flag;
- stop requesting the new endpoints;
- retain or clear versioned dashboard cache safely;
- roll back application services/controllers independently of database indexes;
- remove a new index only after verifying no other workload depends on it;
- do not drop materialized summaries during an emergency application rollback.

## 21. Definition of done

- Login redirects directly to the dashboard without dashboard computation.
- Dashboard shell contains no count/chart SQL.
- Summary and four report groups load independently.
- Query execution is permission-aware.
- Unused home-dashboard queries are removed.
- Repeated aggregates use shared grouped queries.
- Cache keys isolate provider, plant, year, locale, and report group.
- Road/sewer SQL uses spatial join predicates.
- Every added index has documented before/after plans.
- Cold-cache, warm-cache, authorization, and failure tests pass.
- Performance targets are measured and reported.
- Feature-flag rollback is tested.

## 22. Files expected to change

### Modify

```text
routes/web.php
app/Http/Controllers/Auth/LoginController.php
app/Http/Controllers/HomeController.php
app/Services/DashboardService.php (split/deprecate incrementally)
resources/views/dashboard/indexAdmin.blade.php
resources/js/app.js or the current dashboard JS entry point
```

### Create

```text
app/Http/Controllers/Dashboard/DashboardSummaryController.php
app/Http/Controllers/Dashboard/DashboardReportController.php
app/Services/Dashboard/DashboardSummaryService.php
app/Services/Dashboard/DashboardReportService.php
app/Services/Dashboard/DashboardAccessService.php
app/Services/Dashboard/DashboardCacheKey.php
app/Data/Dashboard/DashboardScope.php
app/Data/Dashboard/DashboardFilters.php
resources/js/dashboard-loader.js
resources/views/dashboard/partials/loading.blade.php
resources/views/dashboard/partials/error.blade.php
resources/views/dashboard/summary.blade.php
resources/views/dashboard/report-groups/*.blade.php
tests/Feature/Auth/LoginDashboardRedirectTest.php
tests/Feature/Dashboard/DashboardShellTest.php
tests/Feature/Dashboard/DashboardSummaryTest.php
tests/Feature/Dashboard/DashboardReportTest.php
tests/Feature/Dashboard/DashboardCacheIsolationTest.php
tests/Unit/Services/Dashboard/*.php
database/migrations/*_add_verified_dashboard_indexes.php (only after plan approval)
```

## 23. Final technical decision

The issue will be solved by **decoupling dashboard reports from authentication and the initial page response**.

Caching and indexing are supporting optimizations. They are not the mechanism used to make first login responsive. Cold-cache login is protected because the dashboard shell does not run report queries at all.
