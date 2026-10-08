# BASEIMIS-20: Final Dashboard and Login Optimization Plan

**Prepared for:** Technical review  
**Date:** 2026-08-12  
**Related Jira:** [BASEIMIS-20 — Dashboard optimization](https://jira.innovativesolution.com.np/browse/BASEIMIS-20)  
**Document status:** Proposed implementation plan — no implementation has been applied yet

## 1. Decision requested

Approval is requested to implement the following solution:

> **Return the authenticated dashboard shell immediately, then load authorized summary boxes and chart groups through separate endpoints. Optimize those endpoints by removing unused queries, consolidating repeated aggregates, rewriting slow SQL, applying scope-aware caching, and adding only indexes proven by query-plan measurements.**

We will **not** calculate or warm dashboard data during login. This is the key decision that fixes the first-login and cold-cache experience.

## 2. Current problem

Users report that login takes a long time. Authentication itself is not the main cause.

The current request flow is:

1. The user submits credentials.
2. Laravel validates the credentials and retrieves the user.
3. The user is redirected to `/`.
4. `/` redirects the authenticated user to `/dashboard`.
5. `HomeController@index` runs all count and chart queries synchronously.
6. The browser displays the dashboard only after all database work finishes.

Because the browser follows the redirects automatically, dashboard processing appears to the user as login delay.

## 3. Verified evidence

The current dashboard request was measured using a representative Municipality Super Admin account on the local PostgreSQL database with warm buffers.

| Measurement | Current result |
|---|---:|
| `HomeController@index` wall time | 1,446 ms |
| SQL statements executed | 78 |
| Total reported database time | 1,379 ms |
| Service calls whose output is not rendered | 16 |
| Queries caused by unused service calls | 27 |
| Time spent on unused service calls | About 396 ms |

Additional findings:

- Permission checks in Blade hide widgets only after their queries have run.
- Six building-use boxes query the same building data separately.
- The road-length spatial query is the largest measured query.
- Existing road, sewer, ward, building, and containment geometry columns already have GiST indexes.
- The current spatial SQL does not use an `ST_Intersects` join predicate effectively.
- The configured cache is file-based, the queue is synchronous, and Redis is not the active cache store.

These are local baseline measurements for comparing changes. Production-like measurements will be captured before release.

## 4. Jira ticket summary

The Jira ticket proposed:

- caching dashboard count boxes for 10 minutes;
- moving slow charts to separate routes;
- adding indexes to dashboard-related tables.

These are useful directions, but the review correctly identified that the earlier proposal did not completely answer:

- how the first request works when the cache is empty;
- when and where indexes should be added;
- how logic will be reused across dashboards;
- whether database-level aggregation is preferable;
- whether Redis warming is safe and appropriate;
- how restricted provider/plant data will be isolated in cache.

The current branch also does not contain the dashboard cache, separate home-dashboard chart routes, or targeted dashboard indexes described in the ticket.

## 5. Answers to technical-review questions

The following subsections are intentionally highlighted so each review question can be checked directly.

### ✅ TECH-LEAD QUESTION 1 ANSWER: How will we fix the initial load after login when the cache is empty?

**Answer:** We will remove dashboard reporting from the blocking login/redirect path.

`GET /dashboard` will return only:

- the authenticated layout;
- navigation and page title;
- loading placeholders;
- URLs for the user's authorized dashboard endpoints.

It will run **zero dashboard aggregate queries**.

After the shell is visible:

1. the browser requests the summary/count boxes;
2. chart groups load independently;
3. no more than two chart-group requests run concurrently;
4. failure in one group does not block or remove the other groups.

**Why this answers the cold-cache concern:** When the cache is empty, only the individual data endpoint is slower. Authentication and the visible dashboard shell do not wait for that calculation.

### ✅ TECH-LEAD QUESTION 2 ANSWER: What is our rule for adding indexes, and when do indexes slow writes?

**Answer:** We will not add indexes based only on column names or table size. An index will be accepted only when all the following are true:

- the column is used frequently in a join, selective filter, or required ordering;
- the table is large enough for an index to outperform a sequential scan;
- `EXPLAIN (ANALYZE, BUFFERS)` shows avoidable scan or join cost;
- the rewritten query shows a measurable before/after benefit;
- the read improvement justifies additional write, storage, vacuum, and maintenance cost.

Indexes slow writes when inserts, deletes, or updates modify indexed columns because PostgreSQL must also update each affected index. There is no universal row-count threshold; the decision depends on query selectivity, read/write frequency, data distribution, and the execution plan.

Candidate areas to test are:

- active buildings by `functional_use_id`, `sanitation_system_id`, and `ward`;
- active containments by `responsible_bin` and `type_id`;
- active applications by `service_provider_id` and `emptying_status`;
- active emptyings by `application_id`, provider, and emptying date;
- active sludge collections by treatment plant and date;
- active building-toilet and toilet-user joins.

We will not add indexes to tiny lookup tables such as functional uses or containment types. We will also not assume that indexing payment `due_year` improves charts that aggregate nearly the entire table.

For production, approved large-table indexes will be created concurrently and checked for duplicates first.

### ✅ TECH-LEAD QUESTION 3 ANSWER: How will the dashboard code be reused?

**Answer:** We will extract reusable query logic from the home controller into a shared dashboard service layer.

Proposed responsibilities:

- `DashboardSummaryService`: reusable count-box aggregates;
- `DashboardReportService`: reusable named report groups;
- focused query methods/objects: grouped building counts, sanitation totals, FSM totals, payment summaries, and spatial summaries;
- controllers: authentication, authorization, filters, cache scope, and response formatting only;
- Blade/frontend code: Chart.js formatting and presentation only.

The home, building, FSM, and utility dashboards will call the same aggregate methods instead of duplicating SQL.

Services will return raw typed data rather than view-formatted strings. Services will not import or call controllers.

### ✅ TECH-LEAD QUESTION 4 ANSWER: What will move to database-level aggregation?

**Answer:** Normal grouped SQL will be used first. Materialized summaries will be introduced only for calculations that remain expensive after query rewrites.

Use direct grouped SQL for:

- all building-use counts in one query;
- building counts by ward;
- sanitation-system counts;
- payment counts by due-year bucket;
- waterborne cases by year;
- FSM totals by provider and status.

Use single-pass/window queries where the current implementation scans the same data twice. The measured containment-per-ward query improved from 72.8 ms to 48.3 ms using this approach.

Consider maintained/materialized summaries for:

- road length by ward;
- sewer length by ward;
- other repeated PostGIS intersection totals;
- expensive distributions that change primarily after imports or edits.

Those summaries will refresh after the relevant import/update or on a schedule—not during login or a dashboard request.

### ✅ TECH-LEAD QUESTION 5 ANSWER: Should we calculate queries and store them in Redis while the user logs in?

**Answer:** No, not in the first implementation.

Current configuration uses:

- file cache;
- synchronous queues;
- no active Redis cache driver.

With a synchronous queue, a cache-warming job still runs inside the request and makes login slower. Warming user-specific data during authentication also increases cache-isolation and authorization risk.

Redis may be introduced later if:

- multiple application nodes require shared cache;
- a real asynchronous queue is configured;
- scheduled or post-write warming is needed;
- cache-isolation tests are in place.

If introduced, global aggregates will be warmed on a schedule or after data imports/writes. User-specific dashboard data will not be warmed during login.

### ✅ TECH-LEAD QUESTION 6 ANSWER: How will cached restricted data remain secure?

**Answer:** Authorization will run before every query and response, including cache hits. Cache keys will include every scope and filter that changes the result.

Example global key:

```text
dashboard:v1:global:{locale}:{report-group}:{filter-version}
```

Example restricted key:

```text
dashboard:v1:scope:{role-scope}:{service-provider-id}:{treatment-plant-id}:{year}:{report-group}:{filter-version}
```

Rules:

- provider/plant-specific data will never use a global key;
- year and other filters will be part of the key;
- endpoints will use existing authentication middleware;
- each widget will be authorized before its query runs;
- user-specific HTML responses will use private/no-store browser caching;
- cache keys will have a version prefix for safe invalidation;
- automated tests will verify that one scope cannot receive another scope's data.

## 6. Final implementation design

### 6.1 Login

- Replace the duplicate credential validation/retrieval with one authentication attempt.
- Preserve blocked-role behavior.
- Preserve remember-me behavior.
- Regenerate the authenticated session appropriately.
- Keep all dashboard queries outside login.

This is a cleanup; dashboard decoupling provides the main performance improvement.

### 6.2 Dashboard shell

```text
GET /dashboard
```

- Uses existing `auth` middleware.
- Returns the page shell and loading states.
- Executes no dashboard count or chart queries.

### 6.3 Summary endpoint

```text
GET /dashboard/summary
```

- Returns all count boxes the user may see.
- Authorizes before executing queries.
- Uses consolidated grouped queries.
- Uses a 10-minute scope-aware cache.
- Loads first after the shell appears.

### 6.4 Report endpoints

```text
GET /dashboard/reports/buildings-sanitation
GET /dashboard/reports/fsm
GET /dashboard/reports/payments
GET /dashboard/reports/utilities-health
```

Each endpoint:

- uses authentication;
- authorizes every contained widget before querying;
- returns only its own chart group;
- uses a 10-minute scoped cache;
- can fail and retry independently.

The browser will run no more than two chart requests concurrently.

## 7. Query changes we will make

### Remove unused work

We will remove all calculations from `HomeController@index` whose results are not rendered. The current measurement found 27 queries and about 396 ms associated with unused service output.

### Consolidate building counts

Six separate functional-use counts will become one grouped aggregate.

Measured comparison:

| Design | Execution time |
|---|---:|
| One individual category count | 41.7 ms |
| One grouped query returning all categories | 15.6 ms |

### Rewrite spatial joins

Road and sewer queries will first restrict geometries with `ST_Intersects` before calculating `ST_Intersection`.

```sql
FROM layer_info.wards w
JOIN utility_info.roads r
  ON ST_Intersects(r.geom, w.geom)
WHERE r.deleted_at IS NULL
```

The road query improved from 706.3 ms to 412.5 ms in the representative measurement. Because it remains expensive, it will not run in the shell request and may become a maintained summary.

### Rewrite duplicate aggregation

Containment category counts and ward totals will use one grouped/window query instead of two scans.

### Preserve index-friendly date filters

Date filtering and joining will use date ranges instead of applying `TO_CHAR()` or similar functions to indexed date columns.

## 8. Implementation phases

### Phase 1 — Baseline and safe cleanup

- Capture baseline request time, SQL count, and slow-query plans on production-like data.
- Remove unused home-dashboard calculations.
- Consolidate repeated building counts.
- Ensure permissions are checked before queries.
- Simplify duplicate login lookup without changing behavior.

### Phase 2 — Non-blocking dashboard

- Create the query-free dashboard shell.
- Add the summary endpoint.
- Add the four report-group endpoints.
- Add loading, independent error, and retry states.
- Limit chart request concurrency to two.

### Phase 3 — Cache and query optimization

- Add 10-minute scope-aware caching.
- Rewrite road/sewer spatial queries.
- Rewrite duplicate aggregations.
- Re-measure every slow query.
- Add only indexes with proven before/after benefit.

### Phase 4 — Validation and rollout

- Run authorization and cache-isolation tests for all applicable roles.
- Test cold and warm cache behavior.
- Test one report endpoint failing while other sections load.
- Compare final measurements with baseline.
- Roll out with logging/monitoring and a rollback plan.

## 9. Acceptance criteria

The work will be considered complete when:

- successful authentication does not execute or wait for dashboard reports;
- `/dashboard` returns its shell with zero dashboard aggregate queries;
- cold cache does not delay the shell;
- summary boxes and charts load independently;
- hidden/unauthorized widgets do not execute queries;
- service-provider and treatment-plant cache data cannot cross scopes;
- repeated visits within 10 minutes use cache;
- all building-use boxes come from one grouped query;
- road/sewer queries use a spatial join predicate;
- unused dashboard calculations are removed;
- one report-group failure does not break the full dashboard;
- blocked-role and remember-me login behavior remain correct;
- before/after query plans are attached for every new index.

## 10. Initial performance targets

Targets will be validated on a production-like dataset:

| Operation | Target |
|---|---:|
| Login POST and redirect before dashboard data | p95 under 500 ms |
| Dashboard shell server time | p95 under 300 ms |
| Summary endpoint, warm cache | p95 under 150 ms |
| Summary endpoint, cold cache | p95 under 750 ms |
| Report-group endpoint, warm cache | p95 under 200 ms |
| Initial shell dashboard aggregate queries | 0 |

Any uncached report query remaining above 500 ms will require a documented materialization or further optimization decision.

## 11. Risks and controls

| Risk | Control |
|---|---|
| Restricted data stored under the wrong cache key | Scope-aware keys plus cross-role automated tests |
| Too many parallel chart requests overload PostgreSQL | Maximum two concurrent report requests |
| Cache displays stale data | Ten-minute TTL first; targeted invalidation after correctness is proven |
| New indexes slow write/import operations | Add only measured indexes; monitor index usage and write performance |
| Async sections change user experience | Loading, error, and retry states; counts load first |
| Refactoring changes dashboard values | Compare endpoint results against current dashboard values before rollout |
| Materialized data becomes stale | Refresh after relevant writes/imports or on a monitored schedule |

## 12. Out of scope for the first delivery

- Deploying Redis only for this ticket
- Warming user-specific data during login
- Changing the session store
- Adding speculative indexes without query-plan evidence
- Materializing every dashboard query
- Reworking unrelated module dashboards beyond sharing the new query services

## 13. Final recommendation

Proceed with the four-phase plan above.

The earlier Jira direction—cache, separate routes, and indexes—will remain part of the solution, but in the correct order:

1. remove unnecessary work;
2. stop dashboard reporting from blocking login;
3. authorize before querying;
4. consolidate and rewrite queries;
5. cache with strict scope isolation;
6. add only measured indexes;
7. consider Redis/materialized summaries only where deployment and performance evidence justify them.

This provides one concrete answer to the review: **the first-login problem is solved structurally by a non-blocking dashboard shell, not by relying on a pre-warmed cache.**
