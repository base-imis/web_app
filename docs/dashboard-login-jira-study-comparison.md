# Dashboard and Login Optimization — Jira Study Comparison

Date: 2026-08-11  
Jira issue: [BASEIMIS-20 — Dashboard optimization](https://jira.innovativesolution.com.np/browse/BASEIMIS-20)

## Purpose

This document separates three things that were previously mixed together:

1. what the Jira ticket originally reported;
2. what the Jira review comments said was still missing;
3. what was independently verified in the current code and database.

It then gives one recommended solution for both dashboard performance and the slow-login experience.

## Executive summary

The Jira ticket proposed three useful optimizations:

- cache dashboard count boxes for 10 minutes;
- move expensive charts to separate routes;
- add indexes for dashboard queries.

These ideas are directionally correct, but they do not fully address the first-login problem. A cold cache still makes the user wait if the dashboard queries run before the page is returned.

The current workspace also does not contain the dashboard cache, separate chart routes, or targeted dashboard indexes described as completed in Jira. The current home dashboard still calculates all counts and charts synchronously.

The better solution is to return a lightweight dashboard shell immediately after authentication, then load authorized summary and chart groups independently. Caching, query rewrites, and indexes should optimize those endpoints, but they must not be part of the blocking login path.

## Part 1: What the Jira ticket studied

### Problem identified in Jira

The ticket identified that:

- dashboard loading time was high;
- count boxes and chart data were loaded together;
- some count queries lacked suitable indexes;
- performance would get worse as municipal data increased.

### Work described in Jira

The ticket says the following work was done:

- Laravel's default cache was used for dashboard count boxes.
- Count values were cached for 10 minutes.
- Slow charts were moved to separate routes.
- Database indexes were added to count-query tables.

### Expected result described in Jira

- Faster initial dashboard response
- Faster repeated count-box loading
- Heavy chart queries no longer blocking the main dashboard
- Faster count queries through indexing

### Testing described in Jira

- Count boxes were checked.
- Ten-minute cache reuse was checked.
- Separate chart routes were checked.
- Index creation was checked.
- Dashboard speed was compared with the earlier version.

## Part 2: What the Jira review said was missing

The supervisor agreed that the general impact analysis was useful, but raised four unresolved questions.

### 1. No concrete indexing rule

The earlier study mentioned that indexes improve reads and may slow writes, but did not define:

- which exact columns should be indexed;
- how table size and query selectivity affect the decision;
- when write overhead becomes unacceptable;
- how the benefit would be proved before deployment.

### 2. Cold-cache login was not solved

The most important concern was the first request after login.

A 10-minute cache helps only after a value has been calculated. If the first authenticated request still calculates every count and chart before returning HTML, users still experience a slow login.

### 3. Code reuse was unclear

The earlier study did not define how the home dashboard, building dashboard, FSM dashboard, and utility dashboard would share the same aggregate-query logic.

### 4. Database-level aggregation needed deeper study

The supervisor asked whether repeated PHP/service processing should be replaced by grouped database queries, database views, or stored/materialized summaries.

### Redis suggestion

The review suggested calculating queries and storing results in Redis while the landing page loads or while the user logs in. It also correctly noted that security and data impact required deeper analysis.

### Final Jira feedback

The later response document was rejected for being too broad and generic. The reviewer requested:

- short, direct bullet points;
- one final way forward;
- exact next actions;
- a clear explanation of how each concern would be resolved.

## Part 3: What was independently studied now

The current study examined:

- the login controller and request validation;
- the `/` to `/dashboard` redirect chain;
- every query executed by `HomeController@index`;
- dashboard service methods;
- the complete Blade include tree for `indexAdmin.blade.php`;
- permission checks;
- cache, queue, session, and Redis configuration;
- live PostgreSQL indexes and table statistics;
- actual query execution plans and timings.

No optimization implementation was applied as part of this study.

## Current login and dashboard flow

The current request sequence is:

1. The user submits `POST /login`.
2. Credentials are validated.
3. The same user is retrieved again.
4. Blocked roles are checked.
5. The user is logged in and redirected to `/`.
6. The `/` route redirects the authenticated user to `/dashboard`.
7. `HomeController@index` calculates all dashboard counts and charts.
8. The dashboard HTML is finally returned.

The browser follows these redirects automatically. As a result, dashboard-query time appears to users as login time.

## Verified performance results

The existing controller was measured using a representative Municipality Super Admin account on the current local PostgreSQL database with warm buffers.

| Metric | Measured result |
|---|---:|
| Dashboard controller wall time | 1,446 ms |
| SQL statements | 78 |
| Reported database query time | 1,379 ms |
| Unused service calls | 16 |
| Queries made by unused service calls | 27 |
| Time spent by unused service calls | about 396 ms |

These values are local measurements for comparison, not production service-level guarantees.

## Verified code problems

### Queries are executed before permission checks

The Blade template uses `@can` to hide unauthorized widgets, but the controller has already calculated their values. Users therefore pay the query cost even when they cannot see the widget.

### Many calculated values are never rendered

Examples include:

- monthly emptying data;
- emptying service by ward;
- monthly request by operator;
- FSM feedback and PPE charts;
- hotspots by ward;
- emptying requests by structure type;
- several containment distribution charts;
- proposed and next emptying charts;
- extra sanitation and tax-presence results.

The unused service calls alone produced 27 avoidable queries in the measurement.

### Building categories are counted separately

Residential, commercial, industrial, educational, mixed, and institutional building counts scan the same building data repeatedly.

Measured comparison:

| Query design | Execution time |
|---|---:|
| One individual category count | 41.7 ms |
| One grouped query returning all categories | 15.6 ms |

One grouped query should supply every building count box.

### Spatial SQL does not use the available index effectively

Road and sewer length queries calculate geometry intersections without an `ST_Intersects` join condition.

The database already has GiST geometry indexes. Adding the spatial join condition improved the measured road query from 706.3 ms to 412.5 ms.

The query remains expensive, so it should also be moved out of the initial response or pre-aggregated.

### Containment aggregation repeats work

The containment-per-ward calculation scans and aggregates the same data for category counts and ward totals.

A single grouped query with a window total improved the measurement from 72.8 ms to 48.3 ms and reduced shared-buffer work.

### Login performs a duplicate user lookup

The login controller calls `Auth::validate()` and then `retrieveByCredentials()` with the same credentials. This can become one `Auth::attempt()` operation while preserving blocked-role and remember-me behavior.

This should be corrected, but it is not the principal source of the delay.

## Database and runtime findings

The current local runtime uses:

- file cache;
- file sessions;
- synchronous queue execution;
- no active Redis cache configuration.

Therefore, dispatching a cache-warming job during login would still run synchronously and make login slower.

The database already has GiST indexes for major geometry columns. Important relational join/filter columns are not consistently indexed, especially in applications, emptyings, sludge collections, building sanitation/functional-use relationships, and toilet-user relationships.

Small lookup tables should not receive unnecessary indexes. PostgreSQL correctly uses sequential scans for tables containing only a few rows.

## Comparison of the approaches

| Area | Earlier Jira approach | Better verified approach |
|---|---|---|
| Initial response | Cache counts in the dashboard request | Return a query-free dashboard shell |
| Cold cache | Still potentially blocks login | Data loads after the shell is visible |
| Charts | Separate slow chart routes | Four authorized report-group endpoints |
| Request volume | Not defined | Maximum two report requests concurrently |
| Permissions | Mainly handled in Blade | Authorize before executing queries |
| Unused queries | Not clearly addressed | Remove all unused controller calculations first |
| Building counts | Cache repeated queries | Replace with one grouped query, then cache |
| Spatial queries | Add indexes | Fix SQL to use existing GiST indexes first |
| Other SQL | General optimization statement | Measured single-pass aggregation rewrites |
| Cache keys | Not defined | Global or provider/plant-scoped keys |
| Redis | Consider during login | Do not use during login; optional later for shared cache |
| Queue | Not considered | Current sync queue cannot perform background warming |
| Indexing | Add indexes | Add only after before/after query-plan proof |
| Code reuse | Unclear | Shared summary/report services used by all dashboards |

## Final recommended solution

### 1. Keep login small

- Replace duplicate validate/retrieve operations with one authentication attempt.
- Preserve role restrictions, remember-me behavior, session regeneration, and error responses.
- Do not calculate dashboard data inside the login request.

### 2. Return the dashboard shell immediately

`GET /dashboard` should return:

- the dashboard layout;
- navigation and title;
- loading placeholders;
- URLs for authorized data endpoints.

It should execute no dashboard count or chart queries.

### 3. Load summary boxes separately

Create one authenticated endpoint:

```text
GET /dashboard/summary
```

It should:

- check permissions before querying;
- use grouped queries;
- return all permitted count boxes;
- cache results for 10 minutes.

### 4. Load four report groups

```text
GET /dashboard/reports/buildings-sanitation
GET /dashboard/reports/fsm
GET /dashboard/reports/payments
GET /dashboard/reports/utilities-health
```

Load no more than two groups simultaneously. A failure in one group must not stop the other dashboard sections.

### 5. Isolate cached data

Global municipal values can use global cache keys. Restricted values must include their authorization scope.

Example restricted key:

```text
dashboard:v1:scope:{role}:{service-provider-id}:{treatment-plant-id}:{year}:{report-group}
```

Never store provider-specific or treatment-plant-specific data under one global key.

### 6. Fix query design before adding indexes

- Remove unused queries.
- Consolidate repeated counts.
- Add `ST_Intersects` to spatial joins.
- Replace duplicate aggregates with single-pass/window queries.
- Avoid applying functions such as `TO_CHAR(column)` to indexed date columns in filters and joins; use date ranges.
- Measure the rewritten queries again.

### 7. Add only proven indexes

Candidate areas include:

- active buildings by functional use, sanitation system, and ward;
- active containments by responsible BIN and type;
- active applications by provider and emptying status;
- active emptyings by application, provider, and emptying date;
- active sludge collections by treatment plant and date;
- active building-toilet and toilet-user joins.

Each index must be accepted only when `EXPLAIN (ANALYZE, BUFFERS)` proves a useful improvement on production-like data.

### 8. Use Redis only when deployment needs it

Redis is not required to solve the login issue. The fast-shell design solves both warm-cache and cold-cache login behavior.

Use Redis later only if:

- more than one application node needs a shared cache;
- an asynchronous queue is configured;
- scheduled or post-write cache warming is required;
- cache-key isolation has automated tests.

## Index rule of thumb

Create an index only when:

- the column is used frequently in a join, selective filter, or ordering;
- the table is large enough for an index to beat a sequential scan;
- the final query plan shows avoidable scan or join cost;
- measured read improvement is worth slower inserts, updates, deletes, vacuum work, and storage.

There is no universal row-count threshold. Index decisions must use the actual query, table distribution, read/write frequency, and execution plan.

For production PostgreSQL, large indexes should be created concurrently and outside a transaction after duplicate-index checks.

## Proposed implementation order

1. Record baseline request time, query count, and slow-query plans.
2. Remove unused home-dashboard calculations.
3. Consolidate repeated building counts.
4. Move authorization before queries.
5. Add the lightweight dashboard shell.
6. Add the summary endpoint.
7. Add the four report-group endpoints.
8. Add 10-minute scope-aware caching.
9. Rewrite spatial and duplicate-aggregation SQL.
10. Re-run query plans and add only proven indexes.
11. Simplify the duplicate login user lookup.
12. Add role, cache-isolation, cold-cache, and failure tests.

## Acceptance checks

- Login does not wait for dashboard aggregates.
- Dashboard shell performs no dashboard SQL.
- Cold cache does not delay the shell.
- Warm summary and report requests use cache.
- Hidden widgets do not execute queries.
- Restricted roles cannot read another provider/plant's cached data.
- All displayed building categories use one grouped query.
- Road and sewer SQL use spatial join predicates.
- No unused values are calculated.
- Each report group can fail and retry independently.
- Blocked login roles and remember-me behavior remain correct.

## Conclusion

The earlier Jira study identified the correct general tools—cache, separate routes, and indexes—but it did not completely solve the cold-cache login path or define authorization, cache isolation, reusable query architecture, and evidence-based indexing.

The better solution is not “more cache during login.” It is to remove dashboard reporting from the blocking login/redirect response, then optimize and cache independently authorized report endpoints.
