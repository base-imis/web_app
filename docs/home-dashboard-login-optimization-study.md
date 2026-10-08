# Home Dashboard and Login-Time Optimization Study

Date: 2026-08-11  
Related issue: BASEIMIS-20 — Dashboard optimization

## Decision

Use one implementation path:

- Keep authentication synchronous and small.
- Make `GET /dashboard` render only the dashboard shell and loading states.
- Load summary/count boxes from one authenticated endpoint after the shell renders.
- Load charts from four authenticated report-group endpoints, with at most two requests running concurrently.
- Cache each summary/report group for 10 minutes with authorization-scope-aware keys.
- Remove queries whose results are not rendered before adding cache.
- Rewrite the confirmed slow SQL before adding new indexes.
- Add only indexes supported by `EXPLAIN (ANALYZE, BUFFERS)` evidence.
- Do not warm Redis during login.

This directly fixes the cold-cache case: login no longer waits for count or chart queries, whether the cache is warm or empty.

## Scope reviewed

- `routes/web.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Requests/LoginRequest.php`
- `app/Http/Controllers/HomeController.php`
- `app/Services/DashboardService.php`
- `resources/views/dashboard/indexAdmin.blade.php`
- dashboard Blade partials included by `indexAdmin.blade.php`
- cache, session, queue, Redis, and database configuration
- live PostgreSQL index metadata, table statistics, and representative query plans

## Current request path

1. `POST /login` validates credentials.
2. `LoginController@login` retrieves the same user again, checks blocked roles, and logs the user in.
3. The response redirects to `/`.
4. The `/` route detects the authenticated user and redirects to `/dashboard`.
5. `HomeController@index` runs all count and chart queries synchronously.
6. The browser receives usable dashboard HTML only after all database work finishes.

The authentication query is not the main delay. The browser follows the redirect chain, so users experience the synchronous dashboard work as “login is slow.”

## Measurements

Measurements were taken against the current local PostgreSQL database using a representative Municipality Super Admin account. PostgreSQL buffers were warm. These numbers are evidence for prioritization, not a production SLA.

| Measurement | Current result |
|---|---:|
| `HomeController@index` wall time | 1,446 ms |
| SQL statements during `index()` | 78 |
| Sum of reported database query time | 1,379 ms |
| Service calls whose values are not rendered | 16 |
| Queries from those unused service calls | 27 |
| Time from those unused service calls | about 396 ms |

### Representative query timings

| Query | Current | Rewritten | Result |
|---|---:|---:|---:|
| One building-use count | 41.7 ms | — | Repeated once per category today |
| All building uses in one grouped query | — | 15.6 ms | Replaces six category scans |
| Buildings per ward | 17.2 ms | — | Suitable for report endpoint/cache |
| Road length per ward | 706.3 ms | 412.5 ms | Add `ST_Intersects` spatial join |
| Containments per ward | 72.8 ms | 48.3 ms | Replace duplicate aggregation with window total |

The same road query took about 409 ms inside the complete controller run. Variation is expected, but it remained the largest single query.

## Confirmed problems

### 1. The dashboard blocks the login redirect

`HomeController@index` calculates counts and every chart before rendering. Cache alone does not solve the first request because a cold cache still performs the work synchronously.

### 2. Work is performed even when the view does not use it

The controller calculates many values that are not referenced by the rendered `indexAdmin.blade.php` view tree, including:

- monthly emptying counts;
- emptying services per ward;
- monthly requests by operators;
- FSM feedback quality and PPE charts;
- hotspots per ward;
- emptying requests by structure type;
- containment type by building use, residential use, and land use;
- containment emptied by ward;
- next/proposed emptying charts;
- the separate sanitation “others” result;
- tax-code presence by ward;
- several direct counts such as total applications and unique emptied containments.

These should be removed from the home request. If another dashboard needs them, that dashboard should call reusable service methods through its own endpoint.

### 3. Authorization happens after query execution

Blade uses `@can` to hide cards and charts, but `HomeController@index` has already run their queries. A user without permission still pays the database cost for hidden widgets.

Query execution must be permission-aware, not only the rendered HTML.

### 4. Building counts scan the same table repeatedly

Six methods count buildings by functional-use category independently. On the reviewed data, one category count took 41.7 ms while one grouped query returning all categories took 15.6 ms.

Use one grouped aggregate and derive the count boxes from its result.

### 5. Spatial queries do not expose the GiST index to the join

Road and sewer length queries use a Cartesian join and call `ST_Intersection` for ward/network combinations. Both geometry columns already have GiST indexes, but the SQL lacks an `ST_Intersects` join predicate.

Required SQL shape:

```sql
FROM layer_info.wards w
JOIN utility_info.roads r
  ON ST_Intersects(r.geom, w.geom)
WHERE r.deleted_at IS NULL
```

The measured road query improved from 706.3 ms to 412.5 ms with this change alone. It is still expensive and should remain outside the initial dashboard response.

### 6. Some aggregations scan the same data twice

`getContainmentTypesPerWard()` separately calculates category counts and ward totals. A single grouped query with a window total reduced execution from 72.8 ms to 48.3 ms and halved shared buffer hits in the measurement.

### 7. Login performs a duplicate credential lookup

`LoginController@login` calls `Auth::validate($credentials)` and then calls `retrieveByCredentials($credentials)` again. Replace this with one `Auth::attempt(...)`, followed by the existing blocked-role check and logout when required.

This is a valid cleanup, but dashboard decoupling has much higher impact.

## Recommended endpoint design

### Initial response

`GET /dashboard`

- Authenticate through existing middleware.
- Return title, layout, placeholders, and endpoint URLs only.
- Do not run dashboard aggregate queries.

### Summary endpoint

`GET /dashboard/summary`

- Return all permitted count boxes in one response.
- Consolidate related counts into grouped SQL.
- Cache for 10 minutes.
- Load first after the shell is visible.

### Report-group endpoints

- `GET /dashboard/reports/buildings-sanitation`
- `GET /dashboard/reports/fsm`
- `GET /dashboard/reports/payments`
- `GET /dashboard/reports/utilities-health`

Each endpoint must:

- use `auth` middleware;
- authorize every included widget before its query runs;
- return only its own chart data or partial HTML;
- cache its result for 10 minutes;
- fail independently so one report does not block the others.

The browser should load no more than two report groups concurrently to avoid replacing one large request with a database request storm.

## Cache design and data isolation

Current local configuration uses:

- `CACHE_DRIVER=file`;
- `SESSION_DRIVER=file`;
- `QUEUE_CONNECTION=sync`;
- no active Redis client setting.

Use Laravel's configured cache abstraction first. File cache is acceptable for one application node. Redis is justified when the deployment has multiple application nodes or needs shared background refreshes.

### Required cache-key scope

Global municipal aggregates:

```text
dashboard:v1:global:{locale}:{report-group}:{filter-version}
```

Service-provider or treatment-plant data:

```text
dashboard:v1:scope:{role-scope}:{service-provider-id}:{treatment-plant-id}:{year}:{report-group}:{filter-version}
```

Rules:

- Never store restricted provider/plant data under one global key.
- Include every filter that changes the result, especially year.
- Re-run authorization on every endpoint request, even for cache hits.
- Return `Cache-Control: private, no-store` for user-specific HTML responses.
- Use versioned key prefixes so deployments can invalidate old shapes safely.
- Start with a 10-minute TTL; add targeted invalidation after correctness is proven.

### Why not warm Redis during login

Do not dispatch dashboard calculations from the login request in the current setup:

- the queue driver is `sync`, so a “background” job would still block login;
- Redis is not currently configured as the cache driver;
- precomputing user-scoped results during login increases authorization and key-isolation risk;
- the fast-shell design already makes cold-cache login responsive.

If Redis and an asynchronous queue are introduced later, warm global aggregates on a schedule or after imports/writes—not while a user is authenticating.

## Database-level aggregation plan

Use ordinary grouped SQL first. Introduce materialized summaries only for computations that remain slow after query rewrites.

### Direct grouped queries

Use for:

- building counts by functional use;
- building counts by ward;
- sanitation-system counts;
- payment-status counts by due-year bucket;
- waterborne cases by year;
- FSM counts by provider/status.

### Materialized or maintained summaries

Consider for:

- road length per ward;
- sewer length per ward;
- other repeated PostGIS intersection totals;
- expensive multi-table containment distributions that change only after imports or edits.

Refresh these summaries after the corresponding GIS import/update completes, or on a scheduled job. Do not refresh them during dashboard or login requests.

## Index study

The current database already has GiST geometry indexes on buildings, containments, roads, sewers, toilets, treatment plants, and wards. The main spatial issue is query shape, not a missing geometry index.

The largest reviewed tables are approximately:

| Table | Live rows | Current relevant index gap |
|---|---:|---|
| `building_info.buildings` | 15,990 | functional use, sanitation system, ward for active rows |
| `taxpayment_info.tax_payment_status` | 15,990 | no due-year index, but full aggregation may prefer scan |
| `swm_info.swmservice_payment_status` | 15,640 | same consideration |
| `fsm.containments` | 11,752 | responsible BIN/type for active rows |
| `watersupply_info.watersupply_payment_status` | 4,365 | same consideration |
| `fsm.applications` | 2,770 | provider/status for active rows |
| `fsm.emptyings` | 2,770 | application/provider/date for active rows |
| `fsm.sludge_collections` | 2,791 | treatment plant/date for active rows |

### Candidate indexes to validate

Do not create all of these automatically. Test each candidate with the final rewritten query.

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

Do not add indexes to `functional_uses.name`, `containment_types.dashboard_display`, or similar tiny lookup tables for this ticket. Their reviewed row counts are 14 and 17, so sequential scans are appropriate.

Do not assume a `due_year` index will accelerate payment charts. Those charts aggregate nearly the whole status table; a sequential scan or pre-aggregated summary can be faster.

### Index rule of thumb

Add an index only when all are true:

- the column participates in a frequent join, selective filter, or required ordering;
- the table is large enough for the planner to benefit;
- `EXPLAIN (ANALYZE, BUFFERS)` shows avoidable scan/join cost;
- the measured read improvement is worth the extra write, storage, vacuum, and maintenance cost.

Indexes slow writes when inserts, deletes, or updates change indexed columns because PostgreSQL must update every affected index. The practical threshold is not a fixed row count: validate the exact workload. Review `pg_stat_user_indexes` after a representative usage period and remove indexes that remain unused.

For production PostgreSQL, create large-table indexes concurrently and outside a transaction. Test migration rollback and duplicate-index detection before release.

## Code reuse design

Create one dashboard query/service layer used by the home dashboard and module dashboards:

- `DashboardSummaryService` for count-box aggregates;
- `DashboardReportService` for named report groups;
- small query objects or methods for reusable aggregates;
- controllers responsible only for authorization, filters, cache scope, and response format.

Do not import or call controllers from services. `DashboardService` currently imports `HomeController`; remove that dependency during refactoring.

Return raw typed arrays/numbers from services. Keep Chart.js label quoting and presentation formatting in resources/views or frontend code.

## Delivery sequence

1. Add request/query timing around login redirect and dashboard endpoints.
2. Remove all unused `HomeController@index` calculations.
3. Consolidate the six building-use count queries into one grouped query.
4. Make query execution permission-aware.
5. Add the fast dashboard shell and the summary endpoint.
6. Add the four report-group endpoints and limit browser concurrency to two.
7. Add 10-minute, scope-aware caching using the configured cache driver.
8. Rewrite road/sewer spatial SQL with `ST_Intersects`.
9. Rewrite duplicated containment aggregations as single-pass queries.
10. Re-run `EXPLAIN (ANALYZE, BUFFERS)` and add only proven indexes.
11. Simplify the duplicate login credential lookup.
12. Consider Redis/async warming only if deployment topology requires shared cache.

## Acceptance criteria

- Successful authentication is not blocked by dashboard aggregate queries.
- `/dashboard` returns its shell without executing count/chart SQL.
- Count boxes appear independently and use a 10-minute scoped cache.
- Charts load by report group and one failed group does not break the page.
- No endpoint queries or returns widgets the current user cannot access.
- Service-provider and treatment-plant data never share global cache keys.
- Cold-cache and warm-cache behavior are both tested.
- A repeated dashboard visit within 10 minutes produces cache hits.
- The six building-use boxes come from one grouped query.
- Road/sewer plans contain a spatial join predicate and use existing GiST indexes where selected by PostgreSQL.
- No unused dashboard values are calculated.
- Login retains blocked-role behavior and remember-me behavior.
- Tests cover super admin, service provider, treatment plant, restricted user, cache isolation, endpoint failure, and cache expiry.

## Performance targets

Use these as initial acceptance targets on a production-like dataset:

- login POST plus redirect response before dashboard data: p95 under 500 ms, excluding external network latency;
- dashboard shell server time: p95 under 300 ms;
- summary endpoint warm cache: p95 under 150 ms;
- summary endpoint cold cache: p95 under 750 ms;
- each report-group endpoint warm cache: p95 under 200 ms;
- no single uncached report query above 500 ms without a documented materialization plan;
- initial controller query count reduced from 78 to zero dashboard aggregate queries.

## Out of scope for the first implementation

- changing the session store;
- deploying Redis solely for this ticket;
- creating speculative indexes without before/after plans;
- moving every dashboard calculation into a materialized view;
- warming user-specific dashboard data during login.
