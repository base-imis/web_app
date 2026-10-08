# BASEIMIS-20: Dashboard Performance Baseline

**Captured on:** 2026-08-24  
**Purpose:** Freeze the pre-optimization baseline under repeatable local conditions  
**Implementation state:** Existing synchronous dashboard; no dashboard optimization applied  
**Related Jira:** [BASEIMIS-20 — Dashboard optimization](https://jira.innovativesolution.com.np/browse/BASEIMIS-20)

## 1. Fixed test conditions

| Condition | Recorded value |
|---|---|
| Environment | Local development |
| Application URL | `http://localhost:8000` |
| Web server used for this baseline | Laravel/PHP built-in development server |
| PHP | 8.2.12 |
| Laravel | 8.83.29 |
| PostgreSQL | 14.11, 64-bit |
| Database | `20260727baseimis` |
| Database size | 68,961,059 bytes (about 65.77 MiB) |
| User role | Municipality - Super Admin |
| Municipality/entity scope | Single local municipality dataset; the user record has no municipality ID column |
| Service-provider scope | None (`service_provider_id = null`) |
| Treatment-plant scope | None (`treatment_plant_id = null`) |
| Selected year | None; existing home-dashboard default/all-data behaviour |
| Additional filters | None |
| Cache driver | File |
| Session driver | File |
| Queue connection | Synchronous |
| Dashboard data cache | Not implemented; every dashboard request recalculates dashboard data |
| Database-buffer state | Warm after one discarded warm-up run |
| Browser-asset state | Warm after authenticated login and one discarded reload |
| Measured runs | Five identical runs per measurement layer |

No Redis service or asynchronous queue was active during measurement.

## 2. Dataset snapshot

Only active rows were counted where the table supports soft deletion.

| Dataset | Rows |
|---|---:|
| Buildings | 15,990 |
| Containments | 11,752 |
| Applications | 2,770 |
| Emptyings | 2,770 |
| Sludge collections | 2,791 |
| Feedback records | 2,764 |
| Roads | 2,497 |
| Sewers | 129 |
| Drains | 5 |
| Water-supply lines | 129 |
| Public-health hotspots | 5 |
| Yearly waterborne-case rows | 4 |

## 3. Measurement definitions

Three layers were measured separately:

1. **Controller and rendered-view diagnostic:** A persistent Laravel process invoked `HomeController@index`, rendered the returned Blade view, and listened to database queries. This isolates dashboard application and database work while avoiding per-request framework boot time.
2. **Authenticated HTTP document measurement:** A local authenticated HTTP client requested `/dashboard`. Time to first byte means the time until response headers were available. Document complete means the entire HTML response body was received.
3. **Browser full-load measurement:** An authenticated browser reloaded `/dashboard` and waited for the browser `load` state plus the visible `IMIS Dashboard` heading. This includes the dashboard document, JavaScript, CSS, SVGs, fonts, parsing, and page initialization.

Each layer used one discarded warm-up run followed by five measured runs.

## 4. Controller, Blade, and SQL results

| Run | Controller | Controller + Blade render | SQL count | Reported database time |
|---:|---:|---:|---:|---:|
| 1 | 2,319.467 ms | 2,615.522 ms | 122 | 2,420.940 ms |
| 2 | 2,078.743 ms | 2,390.602 ms | 122 | 2,177.180 ms |
| 3 | 2,171.403 ms | 2,461.411 ms | 122 | 2,263.690 ms |
| 4 | 2,258.336 ms | 2,630.162 ms | 122 | 2,382.530 ms |
| 5 | 2,186.099 ms | 2,500.945 ms | 122 | 2,287.080 ms |

### Summary

| Metric | Minimum | Median | Maximum |
|---|---:|---:|---:|
| Controller wall time | 2,078.743 ms | 2,186.099 ms | 2,319.467 ms |
| Controller + Blade render | 2,390.602 ms | 2,500.945 ms | 2,630.162 ms |
| Database time | 2,177.180 ms | 2,287.080 ms | 2,420.940 ms |
| SQL statements | 122 | 122 | 122 |

The rendered-view measurement performs 48 more SQL statements than the controller-only measurement of 74 statements. This shows that Blade/layout authorization and rendering work must be included in later comparisons; measuring only `HomeController@index` understates the complete server-side query count.

## 5. Authenticated HTTP document results

| Run | Time to first byte | HTML received | Status | Response size |
|---:|---:|---:|---:|---:|
| 1 | 8,362.140 ms | 8,369.479 ms | 200 | 118,656 bytes |
| 2 | 8,563.193 ms | 8,570.013 ms | 200 | 118,656 bytes |
| 3 | 8,622.105 ms | 8,625.407 ms | 200 | 118,656 bytes |
| 4 | 8,725.792 ms | 8,729.139 ms | 200 | 118,656 bytes |
| 5 | 8,946.102 ms | 8,957.243 ms | 200 | 118,656 bytes |

### Summary

| Metric | Minimum | Median | Maximum |
|---|---:|---:|---:|
| Time to first byte | 8,362.140 ms | 8,622.105 ms | 8,946.102 ms |
| HTML document complete | 8,369.479 ms | 8,625.407 ms | 8,957.243 ms |

The median difference between receiving response headers and receiving the complete HTML was only about 3.3 ms. The dashboard delay is therefore server processing, not HTML transfer size.

## 6. Browser full-load results

| Run | Dashboard load complete |
|---:|---:|
| 1 | 9,020 ms |
| 2 | 10,537 ms |
| 3 | 10,777 ms |
| 4 | 9,926 ms |
| 5 | 10,549 ms |

| Minimum | Median | Maximum |
|---:|---:|---:|
| 9,020 ms | 10,537 ms | 10,777 ms |

The browser adds a median of approximately 1.91 seconds after the median HTML document response. This secondary time includes the large JavaScript/CSS bundles, SVGs, fonts, parsing, and dashboard initialization.

## 7. Slowest repeatable SQL areas

| Query area | Observed range | Median/representative result |
|---|---:|---:|
| Road length per ward spatial intersection | 630.68–810.09 ms | 695.38 ms median |
| Residential containment-type proportion by ward | 253.70–305.76 ms | 279.65 ms median |
| Containment type by building functional use | 160.49–209.80 ms | 176.35 ms median |
| Containment type per ward with repeated total aggregation | 79.68–89.72 ms | 89.10 ms median |
| Next-emptying-per-ward/sewer spatial work | About 57–71 ms | Varied by run |

The road query remained the largest repeatable query. Its current SQL uses a Cartesian-style ward/road combination with `ST_Intersection` and no `ST_Intersects` join predicate.

## 8. Comparison with the historical screenshot

The earlier browser capture showed approximately 19.31 seconds for the dashboard document. That remains valid historical evidence for the environment and state in which it was captured.

The controlled baseline captured here produced:

- 8.62 seconds median authenticated HTTP time to first byte;
- 10.54 seconds median full browser load;
- 2.50 seconds median controller plus Blade rendering in a persistent process;
- 122 SQL statements for controller plus rendering.

The historical and current browser numbers must not be mixed in a single before/after percentage because the server process and measurement conditions differ. Future local comparisons must rerun the scripts below with the same environment. A production-like Apache/PHP-FPM baseline must also be captured before release.

## 9. Baseline conclusion

The baseline proves:

1. The primary delay occurs before the browser receives the dashboard HTML.
2. Database work dominates the reusable-process dashboard generation time.
3. The complete render performs substantially more SQL than controller-only measurement reveals.
4. The road spatial query is the largest repeatable individual query.
5. Frontend assets add meaningful secondary delay, but they are not the main cause of the slow initial dashboard response.
6. There is currently no dashboard data cache, so a dashboard cold-cache/warm-cache comparison is not yet applicable. Database buffers and browser assets were warmed consistently instead.

## 10. Reproduction commands

Start the local server:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Run controller, rendered-view, SQL-count, database-time, and slow-query measurements:

```powershell
php scripts\benchmark_dashboard_baseline.php
```

Run authenticated HTTP time-to-first-byte and document measurements:

```powershell
.\scripts\benchmark_dashboard_http.ps1 -MeasuredRuns 5 -WarmupRuns 1
```

When comparing an optimized version, retain the same database, role, filters, warm-up method, server, and number of runs.

## 11. Post-implementation comparison

Steps 3–7 were implemented and the authenticated HTTP workflow was rerun. The
optimized five-run warm medians were:

- dashboard shell complete: 2,711.314 ms;
- authorized cached content complete: 510.394 ms;
- complete dashboard: 3,141.916 ms.

Compared with the original 8,625.407 ms controlled HTTP median, the complete
warm dashboard improved by approximately 63.6%, while the visible shell arrived
approximately 68.6% sooner.

See BASEIMIS-20-dashboard-optimization-implementation-results.md for the
implementation, security design, limitations, and release checks.
