# BASEIMIS-20: Building, FSM, and Utility Dashboard Optimization Study

**Document type:** Technical study and implementation recommendation  
**Status:** Ready for technical review  
**Scope:** Building Dashboard, FSM Dashboard, and Utility Dashboard  
**Important:** This document studies the current code and proposes the safe implementation order. It does not claim that the proposed refactor has already been implemented.

## 1. Executive summary

The three secondary dashboards have two related problems:

1. They calculate too much information before the page is displayed.
2. The same dashboard rules, SQL patterns, filter logic, chart formatting, and browser scripts are repeated in several files.

The highest-value first change is not Redis or a visual loader. It is to stop executing data work that the requested dashboard does not display. The clearest example is the Building Dashboard: its controller calculates many FSM counts, feedback values, containment charts, treatment-plant values, sludge values, and KPIs even though its Blade view only renders building counts, sanitation-system counts, buildings by ward, and building-use composition.

The Utility Dashboard has a different issue. It displays the calculated information, but obtains it through many separate aggregate and spatial queries. Its service repeats almost the same ward/category/chart-building algorithm for roads, drains, sewers, and water supply.

The recommended target is one existing `DashboardService`, as requested during technical review, organized behind small dashboard-group methods. Controllers should only validate the request, determine the authorized scope, and request the groups that the user is allowed to see. Common logic should be consolidated only after output-parity tests are written because some apparently duplicated methods currently apply different business rules.

## 2. Files reviewed

### Controllers

- `app/Http/Controllers/BuildingInfo/BuildingDashboardController.php`
- `app/Http/Controllers/Fsm/FsmDashboardController.php`
- `app/Http/Controllers/UtilityInfo/UtilityDashboardController.php`

### Services

- `app/Services/DashboardService.php`
- `app/Services/BuildingInfo/BuildingDashboardService.php`
- `app/Services/Fsm/FsmDashboardService.php`
- `app/Services/UtilityInfo/UtilityDashboardService.php`

### Main views and included chart views

- `resources/views/dashboard/buildingDashboard.blade.php`
- `resources/views/dashboard/fsmDashboard.blade.php`
- `resources/views/dashboard/utilityDashboard.blade.php`
- `resources/views/dashboard/buildings/`
- `resources/views/dashboard/fsmCharts/`
- `resources/views/dashboard/containments/`
- `resources/views/dashboard/charts/`
- `resources/views/dashboard/fsm-feedback-charts/`
- `resources/views/dashboard/utilityCharts/`

The dashboard routes, authorization checks, model scopes, cache implementation already used by the Home Dashboard, and relevant PostgreSQL/PostGIS indexes were also reviewed.

## 3. Controlled local baseline

The benchmark bootstrapped Laravel, authenticated as the local Municipality Super Admin test user, discarded one warm-up request, and measured three repeated controller/view executions. It measured server-side execution; it did not include browser network transfer, SVG download, JavaScript execution, or chart-paint time.

| Dashboard | Median controller time | Median full render time | Median SQL queries | Median DB time | Approx. HTML |
|---|---:|---:|---:|---:|---:|
| Building | 865.31 ms | 931.10 ms | 66 | 881.58 ms | 45.9 KB |
| FSM | 786.23 ms | 932.61 ms | 59 | 812.88 ms | 93.3 KB |
| Utility | 2452.41 ms | 2538.55 ms | 73 | 2467.84 ms | 90.7 KB |

These results are a development baseline, not a production SLA. They show that SQL work dominates all three responses and that Utility is currently the most expensive secondary dashboard in this dataset.

The benchmark can be repeated with:

```text
php scripts/benchmark_secondary_dashboards.php
```

The same role, database snapshot, filters, and cache state must be used for before/after comparisons.

## 4. Main duplication findings

### 4.1 The same dashboard methods exist in multiple services

The main `DashboardService`, `BuildingDashboardService`, and `FsmDashboardService` share a large set of method names and near-identical query blocks. Examples include:

The method inventory makes the overlap clear: 27 of the 28 methods in `BuildingDashboardService` have a method with the same name in the main `DashboardService`; all 17 methods in `FsmDashboardService` also have a same-named method in the main service and in the Building service. Same names do not by themselves prove identical business behaviour, so this is a migration inventory rather than permission to delete the old methods immediately.

- emptying services by ward;
- monthly requests by operator;
- number of emptyings by month;
- costs paid by containment owner;
- FSM service-quality and PPE charts;
- sludge collection by treatment plant;
- building-use and buildings-per-ward charts;
- containment-type charts;
- proposed and next emptying charts;
- sewer length and hotspot charts.

FSM already injects both `FsmDashboardService` and the main `DashboardService`. This means consolidation has started informally, but ownership is unclear.

**Recommended change:** Keep one public `DashboardService`. Give it clear entry points such as:

```php
getBuildingSummary(DashboardContext $context)
getBuildingCharts(DashboardContext $context)
getFsmSummary(DashboardContext $context)
getFsmOperations(DashboardContext $context)
getFsmFeedback(DashboardContext $context)
getUtilityRoads(DashboardContext $context)
getUtilitySewers(DashboardContext $context)
getUtilityDrains(DashboardContext $context)
getUtilityWaterSupply(DashboardContext $context)
```

This follows the review instruction not to introduce many new service layers. Private helper methods can keep the single service understandable without creating additional public services.

### 4.2 Year and scope logic are repeated

Building and FSM repeat most of the same code in separate `if (year)` and `else` branches. Provider and treatment-plant filtering is also assembled repeatedly through raw condition strings.

**Recommended change:** Build one validated context object containing:

- user ID and effective permission set;
- service-provider ID, when applicable;
- treatment-plant ID, when applicable;
- municipality/entity scope, when applicable;
- selected year or `null` for all years;
- dashboard name and data group.

Queries should apply this context through query-builder conditions and bound parameters, not concatenated SQL strings.

### 4.3 Chart transformation code is repeated

Utility chart methods repeatedly:

1. load wards;
2. load categories;
3. run a grouped query;
4. initialize zero values;
5. assign colours;
6. build Chart.js datasets.

Many Blade partials also repeat canvas setup, Chart.js initialization, loading/error handling, and image-export logic.

**Recommended change:** Keep SQL methods responsible for returning clean typed data. Use one internal dataset formatter and one shared browser chart helper. Labels should be ordinary strings, not strings already wrapped in JavaScript quote characters.

## 5. Dashboard-specific findings

### 5.1 Building Dashboard

The visible Building page renders:

- total and functional-use building count boxes;
- sanitation-system count boxes;
- ward-wise building distribution;
- building-use composition.

The controller additionally calculates FSM applications, emptying services, sludge collection, service providers, treatment plants, feedback, PPE, monthly requests, future emptyings, containment charts, sewer length, hotspots, costs, and KPI information. Those results are passed to the view but the Building view does not include the related sections.

This is the safest and most valuable duplicate-work removal. After a feature test confirms the visible values, remove the unused calls and variables from the Building controller. The controller also contains a redundant extra opening brace in `index()` and dead year-selection JavaScript even though the view does not display the year filter.

### 5.2 FSM Dashboard

The FSM view legitimately displays many groups, so it should not be reduced as aggressively as Building. However:

- calculations happen before Blade `@can` checks, so hidden widgets still execute SQL;
- the year and all-years paths duplicate most controller logic;
- data ownership is split between `FsmDashboardService` and `DashboardService`;
- some building values are prepared even though the FSM view does not render building count boxes;
- functional-use rules use hard-coded numeric IDs in some places and names in others;
- similar counts do not always use the same source table or date rule.

The permission check must move before data resolution. An unauthorized widget should neither be returned nor calculated.

### 5.3 Utility Dashboard

The Utility controller performs many separate `SUM` queries for roads, sewers, drains, and water supply before requesting all chart queries. These can be reduced to one conditional-aggregate query per asset family.

Important correctness issues to address while optimizing:

- `Roadline::where('carrying_width', [3, 5])` and `[5, 8]` are not range conditions. Use explicitly agreed, non-overlapping boundary rules and `whereBetween`/comparison conditions.
- Count-box sums do not consistently exclude soft-deleted rows, while several chart queries do. This can make cards and charts disagree.
- Diameter category SQL uses `> 300` in some queries and `>= 300` in others. The exact value `300` can be classified differently.
- `getDrainLengthPerWardChart()` uses a ward/drain Cartesian product without an `ST_Intersects` join before calculating intersections. This can multiply spatial work and distort totals.
- Several methods execute separate ward, category, and result queries even though a single grouped result can supply the required matrix.
- Fixed colour arrays may be exhausted when new database categories are added.

PostGIS index verification found GiST indexes on `geom` for wards, roads, sewers, drains, and water supplies. Therefore, the first spatial fix is to write index-usable joins consistently and compare `EXPLAIN (ANALYZE, BUFFERS)` plans. New spatial indexes should not be added blindly because the relevant GiST indexes already exist.

## 6. Why duplicated methods cannot be deleted blindly

Methods with the same or similar name do not always have identical behaviour. Examples found during review include:

- emptying-related counts use `applications` in one place and distinct `emptyings.application_id` in another;
- some year rules use “created in this year,” while others use “created up to this year”;
- functional-use selection is sometimes based on numeric IDs and sometimes on names;
- soft-delete conditions and the treatment-plant/provider scope are not consistently applied;
- category boundaries differ for the exact value `300`.

Before consolidating a method, capture its current output using a fixed dataset. The product owner or domain owner must decide which rule is correct when the outputs differ. Code duplication should be removed only after that decision is represented by a test.

## 7. Recommended target flow

```text
Dashboard request
  -> validate dashboard, group, year, and filters
  -> create authorized DashboardContext
  -> check permission for requested group
  -> DashboardService group method
  -> scope-safe cache lookup
  -> optimized SQL/data mapping on a miss
  -> typed JSON response
  -> shared loader and chart renderer
```

The initial page should render the layout and permitted placeholders quickly. Independent groups can then load in parallel. A slow Utility spatial chart must not prevent simple count boxes from appearing.

## 8. Cache and authorization design

Caching is useful after unnecessary queries are removed and the remaining SQL is optimized. It must not be used to hide incorrect or wasteful queries.

Use group/widget caching with approximately a three-minute fresh period and background refresh where the configured driver supports it. A cache key must include every value that changes the authorized result, including:

- dashboard and group;
- user/effective permissions;
- municipality/entity;
- service provider;
- treatment plant;
- year and other filters;
- locale, if labels are cached;
- cache namespace/version.

Never cache one complete privileged dashboard payload and then serve it to another user merely because they share a role. Authorization must be applied both before cache access and while constructing the cache identity.

When Building, FSM, or Utility source records change, invalidate or version the affected group. Examples:

- a building change invalidates Building summary/charts and any dependent Home group;
- an application, emptying, sludge, feedback, provider, vehicle, or treatment-plant change invalidates its FSM groups;
- a road, sewer, drain, or water-supply change invalidates the matching Utility group.

## 9. Exact implementation order

### Phase 1 — Protect behaviour with tests

1. Add feature tests for all three dashboard routes and representative roles.
2. Add snapshot/contract assertions for visible counts, chart labels, datasets, empty states, year filters, and scope filters.
3. Add authorization tests proving hidden groups are not fetched.
4. Add SQL regression fixtures for boundary value `300`, null values, soft-deleted records, and geometries crossing ward boundaries.

### Phase 2 — Remove proven unused work

1. Reduce Building controller data to the four visible sections.
2. Remove Building’s dead year-selector script if no year filter is required.
3. Remove unused building values from the FSM response.
4. Re-run the benchmark and compare query count and output tests.

This phase is expected to give the quickest low-risk improvement.

### Phase 3 — Correct Utility calculations

1. Agree on non-overlapping width/diameter boundaries.
2. Apply consistent soft-delete rules.
3. Replace the drain Cartesian product with an `ST_Intersects` spatial join.
4. Compare rewritten spatial output to controlled fixtures.
5. Review execution plans and confirm the existing GiST indexes are used.

### Phase 4 — Consolidate service logic

1. Introduce the validated `DashboardContext` value object.
2. Move shared, proven-equivalent methods into the existing `DashboardService`.
3. Route Building, FSM, and Utility group entry points through that service.
4. Delete an old service method only when its parity tests pass and no call sites remain.
5. Remove the separate dashboard services after all their supported behaviour has migrated.

### Phase 5 — Reduce query count

1. Replace repeated count-box queries with conditional aggregates.
2. Share a single grouped query where multiple charts use the same filtered base data.
3. Select only required columns.
4. Remove redundant category subqueries and unused computed columns.
5. record query count, DB time, and slowest queries after every group change.

### Phase 6 — Load authorized groups independently

1. Render the dashboard shell and permitted placeholders first.
2. Add allowlisted JSON endpoints for Building, FSM, and Utility groups.
3. Load independent permitted groups in parallel.
4. Show a loader, empty state, retry state, and accessible error message per group.
5. Disable filter/submit buttons while their request is active and prevent duplicate requests.
6. Destroy or update an existing Chart.js instance before re-rendering.

### Phase 7 — Add scoped caching and invalidation

1. Reuse the authorization-safe cache key builder.
2. Cache each group, not a whole cross-permission dashboard.
3. Add mutation-triggered invalidation/version changes.
4. Test cold cache, warm cache, stale refresh, invalidation, role separation, provider separation, and treatment-plant separation.

### Phase 8 — Remove repeated front-end code

1. Create a shared year/filter component where a filter is actually needed.
2. Use a generic loading/error/empty-state component.
3. Move Chart.js creation and image export to a shared module.
4. Keep chart Blade files responsible for markup/configuration only.

## 10. Required test scope

### Unit tests

- Dashboard context validation and scope construction.
- Permission-to-group mapping.
- Cache-key uniqueness for users, permissions, municipality, provider, plant, year, filters, and group.
- Category boundaries, null handling, and soft-delete handling.
- Chart dataset formatting and stable numeric types.
- Invalidation mapping for each changed model.

### Feature/integration tests

- Building, FSM, and Utility routes require authentication.
- Each role receives only permitted groups.
- Provider and treatment-plant users cannot receive another scope through query parameters or cache reuse.
- Selected year and all-years results match their agreed definitions.
- A session-expired JSON request returns a detectable unauthorized response, not login-page HTML reported as successful chart data.
- One failed group does not erase successful groups.
- Empty datasets render an empty state without a JavaScript error.

### SQL/spatial tests

- Soft-deleted features are excluded consistently.
- Values exactly on category boundaries appear once.
- A line crossing two wards contributes only its intersecting length to each ward.
- A feature outside all wards follows the agreed rule.
- New and old results match for approved business rules.
- Execution plans use appropriate indexes and do not introduce unintended full scans or Cartesian joins.

### Performance tests

- Repeat three to five times with the same role, dataset, filters, and cache state.
- Measure cold and warm group responses separately.
- Record controller/full-response time, SQL count, database time, slowest queries, response size, time to first useful section, and time to complete dashboard.
- Test concurrent users and confirm cache refresh does not create a thundering herd.

## 11. Acceptance criteria

The secondary-dashboard optimization is complete when:

- Building no longer executes FSM/KPI queries for sections it does not render;
- unauthorized groups are not calculated;
- common dashboard logic has one tested owner in `DashboardService`;
- Utility width/diameter, deleted-row, and spatial rules are consistent and tested;
- no cross-user, cross-role, cross-provider, cross-plant, or cross-municipality cache leakage is possible;
- every cacheable group has a tested invalidation path;
- loading, empty, error, retry, and session-expiry states work;
- unit and feature tests pass;
- controlled before/after measurements show lower SQL count and response time without changed approved totals.

## 12. Recommendation for the first coding task

Start with the Building Dashboard only:

1. write tests for its four visible data sections;
2. remove the unused FSM, feedback, treatment, containment, sludge, sewer, hotspot, and KPI calculations;
3. remove dead year-filter code if the product owner confirms Building has no year filter;
4. rerun the benchmark;
5. deploy behind the normal review process and compare production timing.

This is a small, understandable pilot. It demonstrates the benefit of stopping unused work before the team undertakes the broader shared-service and asynchronous-loading refactor.
