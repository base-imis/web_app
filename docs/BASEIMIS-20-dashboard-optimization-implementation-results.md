# BASEIMIS-20: Dashboard Optimization Implementation Results

**Date:** 24 August 2026  
**Implemented scope:** Steps 3, 4, 5, 6, 7, and 9  
**Step 2:** Skipped on request; dashboard cache unit tests were still added.

## 1. Outcome

The home dashboard no longer runs every dashboard report before returning the
page. It now returns a lightweight dashboard frame, displays a clear loading
state, and requests the authorized dashboard content through a protected JSON
endpoint.

| Measurement | Before | After | Change |
|---|---:|---:|---:|
| Dashboard HTML/shell median | 8,625 ms | 2,711 ms | 68.6% faster |
| Complete warm dashboard median | 8,625 ms | 3,142 ms | 63.6% faster |
| Warm dashboard-content response | Not available | 510 ms HTTP / 12.6 ms persistent process | New |
| Warm dashboard data SQL | 122 statements for old complete render | 2 authorization queries; 0 report queries | Report SQL removed |

The historical 19.31-second browser result remains historical evidence, but it
was collected under different server conditions and is not used for the
percentage calculation above.

## 2. What changed

### Direct query and permission cleanup

- Road and sewer ward-length queries now use ST_Intersects as an explicit join
  condition before ST_Intersection.
- Existing GiST indexes were verified on roads.geom, sewers.geom, and wards.geom.
- Repeated sidebar permission-group checks were reduced to one joined query per
  user object.
- Dashboard authorization signatures use two fixed SQL queries instead of
  repeatedly loading permission relationships.

The spatial query remains the slowest cold-cache query. The join is now
index-compatible, but further execution-plan work is still warranted.

### Lightweight dashboard shell

GET /dashboard now renders the normal layout plus a loading status, accessible
busy state, empty content target, and no-JavaScript warning. The former
dashboard body is an asynchronous partial.

### Authenticated JSON contract

GET /dashboard/content is inside the auth middleware group and returns status,
authorized HTML, cache hit/miss state, and the 180-second TTL. The response is
marked Cache-Control: private, no-store.

### Progressive loading

The page frame appears first. Dashboard content is then loaded without another
navigation, and returned chart initializers execute after insertion.

On failure, the page remains usable and shows an inline error with Retry.
Expired sessions return the user to login.

This delivery separates the shell from the complete dashboard report group. It
does not yet split every chart into an independent HTTP request.

### Secure 3-minute cache

The cache key includes:

- cache/data version;
- user ID;
- service-provider ID;
- treatment-plant ID;
- roles and effective permissions;
- application locale;
- accepted dashboard filters.

User ID is retained as a final isolation boundary. Authorized HTML is never
shared between users with different keys.

Freshness behavior:

- fresh TTL: 180 seconds by default;
- stale safety window: 1,800 seconds by default;
- after fresh TTL, the last scoped response can be returned immediately and
  refreshed after the HTTP response;
- a cache lock prevents simultaneous refresh;
- relevant Eloquent save, delete, and restore events advance the namespace
  version immediately.

Environment settings are DASHBOARD_CACHE_TTL_SECONDS and
DASHBOARD_CACHE_STALE_TTL_SECONDS. The implementation uses Laravel's configured
cache driver, works with the current file driver, and is Redis-ready. It does
not use Redis-specific wildcard deletion.

## 3. Controlled Step 9 results

### Fixed conditions

- PHP 8.2.12; Laravel 8.83.29; PostgreSQL 14.11
- Database: 20260727baseimis
- Role: Municipality - Super Admin; user ID: 2
- No provider, plant, year, or report-filter restriction
- Cache driver: file
- Five HTTP runs after one discarded warm-up

### Warm HTTP results

| Run | Shell complete | Content complete | Dashboard complete |
|---:|---:|---:|---:|
| 1 | 2,897.788 ms | 616.450 ms | 3,514.238 ms |
| 2 | 2,711.314 ms | 430.603 ms | 3,141.916 ms |
| 3 | 2,249.670 ms | 448.077 ms | 2,697.748 ms |
| 4 | 2,824.399 ms | 581.824 ms | 3,406.224 ms |
| 5 | 2,621.585 ms | 510.394 ms | 3,131.979 ms |

Median:

- shell TTFB: 2,708.504 ms;
- shell complete: 2,711.314 ms;
- cached content complete: 510.394 ms;
- total dashboard complete: 3,141.916 ms.

### Persistent-process cache results

- cold content: 3,356.986 ms and 76 SQL statements;
- warm content median: 12.643 ms and 2 SQL statements;
- warm report SQL: zero;
- the remaining statements calculate the current authorization signature.

## 4. Cache invalidation behavior

The version advances when dashboard-related Eloquent models are saved, deleted,
or restored. Adding a building through the normal application model flow makes
the old dashboard key unreachable.

Important limitation: direct database edits, raw SQL imports, or integrations
that bypass Eloquent do not emit these events. They become visible through the
short refresh cycle unless the integration explicitly calls the dashboard
cache invalidation method.

## 5. Verification completed

- PHP syntax checks passed.
- Blade templates compiled successfully.
- Cache unit tests passed: 3 tests and 8 assertions.
- Authenticated HTTP shell and JSON requests returned HTTP 200.
- Cold-cache and warm-cache benchmarks completed.
- Tested geometry columns use SRID 4326 and have GiST indexes.

The in-app visual check reached the login boundary. Credentials were not
entered through browser automation, so QA must still perform the signed-in
visual check for chart drawing and browser-console errors.

## 6. Files changed

- app/Http/Controllers/HomeController.php
- app/Services/DashboardService.php
- app/Models/User.php
- app/Providers/AppServiceProvider.php
- routes/web.php
- config/dashboard.php
- resources/views/dashboard/indexAdmin.blade.php
- resources/views/dashboard/_content.blade.php
- tests/Unit/DashboardServiceCacheTest.php
- scripts/benchmark_dashboard_optimized.php
- scripts/benchmark_dashboard_http.ps1
- scripts/check_dashboard_spatial_indexes.php

## 7. Release checks still required

1. Compare every displayed total and chart with the old dashboard.
2. Test municipality, provider, plant, and partial-permission users.
3. Prove cached HTML never crosses users or scopes.
4. Add a building/application and verify immediate invalidation.
5. Test console errors, retry, session expiry, and responsive charts.
6. Repeat performance testing under production Apache/PHP-FPM.
7. If Redis is enabled, repeat isolation, expiry, lock, and invalidation tests.

## 8. Chart initialization correction

After the first UI test, some dynamically inserted charts displayed an empty
canvas. Four existing chart partials registered their setup using
DOMContentLoaded, but that event had already completed before the asynchronous
HTML arrived.

The affected chart setup functions now execute when inserted. The dashboard
loader also handles any older cached partial that still registers a late
DOMContentLoaded callback. The dashboard cache namespace was advanced after
this correction so users do not retain the earlier response.

The treatment-plant chart required one additional correction: its nested
dataset loop had an invalid closing token after the asynchronous conversion,
and it referenced a database property that does not exist. The initializer now
has valid function boundaries, converts PostgreSQL numeric strings to numbers,
and maps the legacy SQL fields to compliant and non-compliant series correctly.
