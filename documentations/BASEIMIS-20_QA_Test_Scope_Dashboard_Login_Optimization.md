# BASEIMIS-20: QA Test Scope for Dashboard and Login Optimization

## 1. Document purpose

This document defines the manual and automated QA scope for the dashboard and login optimization changes. It is intended to prove both of the following:

1. The optimized pages load and behave correctly.
2. Every displayed count and chart value still agrees with its underlying source data.

Passing automated loading tests alone is not sufficient for release approval. QA must perform value-by-value reconciliation for each changed dashboard.

## 2. Current verification status

- Automated tests cover important loading, authorization and cache behaviour.
- A complete manual reconciliation of every count box and chart is still required.
- QA must attach evidence for each tested role, scope, filter and cache state.

## 3. Summary of changes requiring QA attention

| Area | Change | Expected data impact | QA priority |
|---|---|---|---|
| Main IMIS Dashboard | Lightweight page shell, asynchronous content loading and scoped caching | No intended calculation change | High |
| Building Dashboard | Permission-scoped queries and removal of unused calculations | No intended visible-value change | High |
| Utility Dashboard | Asynchronous loading and optimized spatial joins | Intended to return equivalent values | High |
| SWM Presence by Ward chart | Chart initializes after asynchronous HTML insertion | No intended data change | Medium |
| Water Supply Presence by Ward chart | Chart initializes after asynchronous HTML insertion | No intended data change | Medium |
| Sludge Collection by Treatment Plant chart | Chart initializes after asynchronous HTML insertion | No intended data change | Medium |
| Treatment Plant Test by Year chart | Corrected compliant/non-compliant field mapping and chart configuration | Displayed values may differ from the old incorrect mapping | Critical |
| Sidebar/dashboard loaders | Loader is scoped to the clicked item and repeated clicks are blocked | No data impact | Medium |
| Login | Submission loader and duplicate-submit prevention | No intended authentication-rule change | High |

## 4. Required test conditions

QA must record the following for every test execution:

- Test date and environment.
- Application version/commit.
- Username and role.
- Municipality/entity scope.
- Service provider scope, when applicable.
- Treatment plant scope, when applicable.
- Locale/language.
- Selected year and other filters.
- Database snapshot or identifiable test dataset.
- Cold-cache or warm-cache state.
- Browser and browser version.
- Page URL.

Run relevant tests with at least these user types:

- Super Administrator.
- Municipality-level user.
- User with limited dashboard permissions.
- Service Provider user.
- Treatment Plant user.

## 5. Source-data reconciliation method

For every count box and chart series:

1. Record the value displayed in the UI.
2. Identify the source table, query conditions and authorization scope.
3. Apply the same municipality, provider, plant, year and UI filters directly to the source data.
4. Apply the same soft-delete and status conditions used by the application.
5. Calculate the expected value independently.
6. Compare the expected value with the displayed value.
7. Save the SQL/query or filter steps, expected value, actual value and screenshot.
8. Mark unexplained differences as failures; do not adjust expected values to match the UI.

## 6. Main IMIS Dashboard scope

### 6.1 Count boxes

- [ ] Verify every visible count box against filtered source records.
- [ ] Verify soft-deleted and otherwise excluded records are not counted.
- [ ] Verify counts with the default year/filter.
- [ ] Verify counts for every supported year/filter combination.
- [ ] Verify zero-result conditions display correctly.
- [ ] Verify restricted users see only values within their authorized data scope.

### 6.2 Charts

- [ ] Verify every label, category, year and data series.
- [ ] Verify each plotted value against the source records.
- [ ] Verify chart totals agree with related count boxes where they use the same dataset.
- [ ] Verify there are no missing or duplicate labels.
- [ ] Verify no value displays as `undefined`, `null` or `NaN`.
- [ ] Verify changing a filter refreshes every affected widget.
- [ ] Verify chart legends, tooltips and export/download functions.

### 6.3 Cache behaviour

- [ ] Clear the applicable dashboard cache and load the page once.
- [ ] Reload with a warm cache and confirm all business values are identical.
- [ ] Confirm warm-cache loading is faster than cold-cache loading.
- [ ] Add, edit or delete a source record through the supported application workflow.
- [ ] Confirm the affected cached dashboard value is updated or invalidated as designed.
- [ ] Verify behaviour during and after the fresh-cache period.
- [ ] Verify filters do not reuse results generated for a different filter.
- [ ] Verify users from different municipalities, providers or plants never receive one another's cached results.
- [ ] Verify role or permission changes cannot expose previously authorized cached content.

## 7. Building Dashboard scope

### 7.1 Count boxes

Manually reconcile:

- [ ] Total Buildings.
- [ ] Commercial.
- [ ] Residential.
- [ ] Mixed.
- [ ] Industrial.
- [ ] Educational.
- [ ] Institution.
- [ ] Others.
- [ ] Every displayed sanitation-system count.

Validate the `Others` result independently:

```text
Others = Total eligible buildings - explicitly displayed building-use categories
```

- [ ] Confirm a building is not included in more than one mutually exclusive category.
- [ ] Confirm soft-deleted buildings are excluded.
- [ ] Confirm sanitation-system visibility/exclusion rules are applied correctly.

### 7.2 Building charts

- [ ] Reconcile Ward-Wise Distribution of Buildings for every ward.
- [ ] Reconcile Building Use Composition for every category.
- [ ] Confirm the total of applicable chart categories agrees with the expected building total.
- [ ] Test wards and categories containing zero records.
- [ ] Verify partial-permission users do not receive restricted widgets or data.
- [ ] Confirm removal of unused FSM/KPI calculations did not remove any required Building Dashboard output.

## 8. Utility Dashboard scope

Manually verify all available utility cards and charts, including:

- [ ] Total road length.
- [ ] Road length by surface type.
- [ ] Road length by width.
- [ ] Road length by hierarchy.
- [ ] Ward-wise road length.
- [ ] Sewer-network length and ward distribution.
- [ ] Water-supply-network values.
- [ ] Drain-network values.
- [ ] Every available utility filter.

### 8.1 Spatial-query verification

Road and sewer spatial joins were optimized. Although the output is intended to remain equivalent, QA must verify:

- [ ] Correct geometry and SRID handling.
- [ ] Correct output unit, especially metres versus kilometres.
- [ ] Correct ward assignment.
- [ ] No duplicated length from overlapping ward geometries.
- [ ] Expected treatment of invalid, empty or missing geometries.
- [ ] Expected treatment of features crossing multiple wards.
- [ ] Sum of ward values agrees with the independently calculated expected total.
- [ ] Before/after results are equal within the approved rounding tolerance.

For values displayed to two decimal places, use an initial tolerance of `±0.01` unless the product owner or GIS owner approves a different tolerance.

## 9. Treatment Plant Test by Year chart

This chart has the highest data-validation priority because its compliant/non-compliant mapping was corrected.

For every available treatment plant and year:

- [ ] Count compliant source records independently.
- [ ] Count non-compliant source records independently.
- [ ] Compare both values with their chart series.
- [ ] Confirm the two series have not been reversed.
- [ ] Confirm `Compliant + Non-compliant = Total applicable test records`.
- [ ] Test a year with no records.
- [ ] Test a year containing only compliant records.
- [ ] Test a year containing only non-compliant records.
- [ ] Test multiple treatment plants.
- [ ] Test a user restricted to one treatment plant.
- [ ] Verify whole-number axis presentation.
- [ ] Verify legends and tooltips use the correct classification names.
- [ ] Verify no value is missing, undefined or `NaN`.

## 10. Chart initialization regression scope

The following charts received lifecycle changes so that they initialize after asynchronous content is inserted:

- SWM Presence by Ward.
- Water Supply Presence by Ward.
- Sludge Collection by Treatment Plant.

For each chart:

- [ ] Confirm it appears on the first dashboard load.
- [ ] Confirm it appears after refresh and browser Back/Forward navigation.
- [ ] Confirm it is not initialized twice.
- [ ] Confirm its values match the independently filtered source records.
- [ ] Confirm its values match the pre-optimization result for the same dataset and scope.
- [ ] Verify legends, tooltips, fullscreen and export/download controls.

## 11. Asynchronous loading and sidebar scope

Run these checks for every dashboard route changed by the optimization:

- [ ] The loader appears only on the clicked navigation item.
- [ ] Other sidebar items do not incorrectly show a loader.
- [ ] Repeated clicks on the active loading item do not send duplicate requests.
- [ ] Dashboard links are temporarily protected from repeated navigation while a request is pending.
- [ ] The loader disappears after successful navigation.
- [ ] The loader resets after network, authorization or server failure.
- [ ] An empty or failed content response produces a recoverable message rather than a permanently blank dashboard.
- [ ] Session expiry is handled correctly; login-page HTML is not rendered inside a dashboard section.
- [ ] Refresh and browser Back/Forward navigation work correctly.
- [ ] Browser console contains no JavaScript errors.
- [ ] Network requests do not remain pending indefinitely.

## 12. Login regression scope

- [ ] Valid credentials authenticate successfully.
- [ ] Invalid credentials show the expected generic error.
- [ ] Unknown usernames do not receive a user-enumerating response.
- [ ] Required-field validation works.
- [ ] Double-clicking **Sign In** creates only one login submission.
- [ ] The Sign In button is disabled while its request is pending.
- [ ] The button loader resets after validation or authentication failure.
- [ ] Intended redirect behaviour after login remains correct.
- [ ] Session and remember-me behaviour remain correct.
- [ ] Authentication guards, events and audit behaviour remain unchanged.
- [ ] Login rate limiting returns the expected response when the limit is exceeded.
- [ ] Browser Back/Forward navigation does not leave the login button permanently disabled.

## 13. Performance scope

For each dashboard, perform at least three comparable runs using the same role, filters and dataset.

Capture:

- Cold-cache response time.
- Warm-cache response time where caching applies.
- Time to first byte.
- Time until the dashboard shell appears.
- Time until all count boxes and charts appear.
- Request count.
- SQL query count and total database time where tooling is available.
- Slowest queries.
- JavaScript console errors.

Compare results with the recorded pre-optimization baseline. Performance improvement must not be accepted if displayed data is incomplete or incorrect.

## 14. Acceptance criteria

The release may pass QA only when:

- Every integer count matches the independently filtered source records exactly.
- Every chart label and data series matches its source data.
- Spatial and decimal values agree within an approved tolerance.
- Cold-cache and warm-cache loads return identical business values.
- No unauthorized widget or cached value crosses a user, role, municipality, provider or plant boundary.
- No chart is blank, duplicated, reversed or displays invalid values.
- Loaders are scoped correctly and duplicate requests are blocked.
- Login behaviour and security controls have not regressed.
- Each test contains sufficient evidence to reproduce the result.
- Every discrepancy is resolved or formally accepted before release.

## 15. QA evidence template

Use one row for each count box or chart series tested.

| Test ID | Page/widget | Role and scope | Filters | Cache state | Source query/filter evidence | Expected | Actual | Result | Screenshot/notes |
|---|---|---|---|---|---|---:|---:|---|---|
| QA-001 | Example count box | Municipality user / Municipality A | Year 2026 | Cold | Attach query | 100 | 100 | Pass | Attach screenshot |

## 16. Defect-report requirements

For every failed reconciliation, include:

- Page URL and widget/chart name.
- User role and complete data scope.
- Selected filters and locale.
- Cold/warm cache state.
- Expected and actual values.
- Source query or manual filter steps.
- Screenshot and relevant network response.
- Browser-console or server error where applicable.
- Reproduction steps.

An unexplained count or chart difference must be treated as a release blocker because it may indicate incorrect filtering, stale cache, spatial-query variance or authorization leakage.
