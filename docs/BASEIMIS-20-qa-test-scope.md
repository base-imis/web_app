# BASEIMIS-20: QA Test Scope for Login and Dashboard Optimization

**Prepared for:** QA validation and release sign-off  
**Date:** 2026-08-14  
**Related Jira:** [BASEIMIS-20 — Dashboard optimization](https://jira.innovativesolution.com.np/browse/BASEIMIS-20)  
**Scope:** Login submission protection, dashboard loading, report endpoints, caching, permissions, data accuracy, and frontend assets  
**Current status:** Login-button loading protection is implemented; dashboard optimization remains pending

## 1. QA objective

Confirm that the login and dashboard changes:

- prevent repeated login submissions;
- give the user clear loading feedback;
- keep authentication separate from dashboard report processing;
- display the dashboard shell without waiting for all aggregates;
- load only the reports the authenticated user is permitted to view;
- preserve data accuracy and role behaviour;
- isolate cached information by provider, plant, role, filters, year, and locale;
- improve measured performance without introducing frontend or backend errors.

## 2. Baseline measurements

The following measurements were captured before the dashboard optimization:

| Measurement | Current observation |
|---|---:|
| Login request | Approximately 1.48 seconds |
| Historical dashboard document capture | Approximately 19.31 seconds |
| Controlled dashboard HTTP TTFB (2026-08-24) | 8.62 seconds median |
| Controlled full browser load (2026-08-24) | 10.54 seconds median |
| Controller + Blade render (2026-08-24) | 2.50 seconds median |
| Controller + rendered-view SQL statements | 122 |
| Controller + rendered-view database time | 2.29 seconds median |
| `public/js/app.js` size | Approximately 10.57 MB |
| `app.js` load time in supplied capture | Approximately 2.57 seconds |
| Some dashboard SVG requests | Approximately 1–2 seconds |

The dashboard document is the primary bottleneck. The browser cannot discover and request dashboard SVG files until the server returns the dashboard HTML. See `BASEIMIS-20-dashboard-baseline-2026-08-24.md` for the fixed conditions and five-run evidence.

QA must capture comparable measurements after each implementation phase using the same environment, user role, database, filters, browser, and network conditions.

## 3. Phase 1 — login-button loading and duplicate-submit protection

### 3.1 Functional test cases

| ID | Test scenario | Steps | Expected result |
|---|---|---|---|
| LOGIN-01 | Valid login using the button | Enter valid credentials and click **Log In** | The button becomes disabled, a spinner appears, the label changes to **Signing in...**, and one login request is submitted |
| LOGIN-02 | Valid login using Enter | Enter valid credentials and press Enter in the password field | The same loading state appears and one login request is submitted |
| LOGIN-03 | Repeated clicks | Double-click or repeatedly click **Log In** | Only one `POST /login` request is sent |
| LOGIN-04 | Missing username | Leave the username empty and submit | Browser validation is displayed; the button does not become permanently disabled |
| LOGIN-05 | Missing password | Leave the password empty and submit | Browser validation is displayed; the button does not become permanently disabled |
| LOGIN-06 | Incorrect credentials | Submit an invalid username or password | The error is displayed and the new page shows an enabled **Log In** button |
| LOGIN-07 | Blocked role | Submit valid credentials for a role that is not allowed to log in | Access is rejected, the approved message is displayed, and the button is usable again |
| LOGIN-08 | Remember Me | Log in with **Remember Me** selected | Existing remember-me behaviour continues working |
| LOGIN-09 | Show password | Toggle **Show password** before submission and after a failed login | Password visibility changes correctly and does not affect submission |
| LOGIN-10 | Slow login response | Test with network throttling or a delayed response | The button remains disabled and continues to show **Signing in...** until navigation or failure |
| LOGIN-11 | Browser back/forward cache | Log in, navigate away, and return using the browser Back button | The login button is restored and is not stuck in the loading state |
| LOGIN-12 | Keyboard and focus | Navigate and submit using only the keyboard | Controls remain reachable, and the loading state does not break keyboard operation |

### 3.2 Login Network-panel evidence

For valid, invalid, and repeated-click tests, capture:

- number of `POST /login` requests;
- request duration and response status;
- redirect chain after successful login;
- time at which `/dashboard` starts;
- screenshot or recording of the button loading state;
- relevant browser-console and Laravel-log errors.

### 3.3 Login acceptance criteria

- A valid submission produces exactly one login request.
- The button immediately provides visible processing feedback.
- Client-side validation does not leave the button disabled.
- Failed authentication returns a usable login form.
- Blocked-role and remember-me behaviour remain correct.
- Credentials or sensitive values never appear in logs, page messages, or URLs.

## 4. Phase 2 — dashboard shell and progressive loading

The shell and authenticated report-group endpoint are now implemented. The
current delivery loads the complete authorized dashboard group after the shell.
Tests requiring independent per-widget endpoints remain future enhancements
and must not be reported as passed by this delivery.

| ID | Test scenario | Expected result |
|---|---|---|
| DASH-01 | Successful login redirect | The dashboard shell appears without waiting for all report calculations |
| DASH-02 | Initial report loading | Each pending card or chart displays its own loading placeholder |
| DASH-03 | First report completes | The completed section displays its data without waiting for every other section |
| DASH-04 | One report fails | Only the affected section shows an error and retry control; the rest of the dashboard continues working |
| DASH-05 | Repeated retry clicks | Duplicate API requests are prevented |
| DASH-06 | Filter or year change | Only affected report sections reload and display values for the selected filter |
| DASH-07 | Navigate away while loading | No unhandled JavaScript error occurs and the destination page is not corrupted |
| DASH-08 | Empty dataset | The section displays the approved empty state instead of an endless loader |
| DASH-09 | Slow report | Other authorized sections can still load |
| DASH-10 | Request concurrency | No more than the approved number of report requests run concurrently |
| DASH-11 | Dashboard content without login | Request is rejected or redirected; no dashboard HTML is returned |
| DASH-12 | Successful JSON response | Response has status, HTML, cache metadata, and private/no-store headers |
| DASH-13 | Cached authorized HTML | Same scoped request returns permitted content without report SQL |

The implementation must not use a full-page overlay that hides the entire dashboard until every report completes.

## 5. Cold-cache testing

A cold cache means the requested scoped result is not currently stored.

### 5.1 Preparation

- Clear only the relevant dashboard cache in the test environment.
- Record the tested user, role, provider, plant, year, locale, and filters.
- Confirm that no earlier result for the same cache key is available.

### 5.2 Expected flow

1. Authentication completes without running dashboard report queries.
2. The dashboard shell becomes visible.
3. Authorized widgets display loading placeholders.
4. Report endpoints calculate missing data from the database.
5. Each result is returned to the correct section.
6. Each result is cached using the correct scope.
7. A slow or failed report does not block unrelated sections.

### 5.3 Evidence to record

- login duration;
- dashboard document duration;
- time until the shell is visible;
- time until the first widget displays data;
- time until all authorized widgets finish;
- total request and database-query counts;
- slowest endpoint and SQL query;
- cache miss evidence;
- browser-console and application-log errors.

**Required result:** A cold cache may delay an individual widget, but it must not delay authentication or the initial dashboard shell.

## 6. Warm-cache testing

A warm cache means the same authorized and scoped result is already stored and has not expired.

| ID | Test scenario | Expected result |
|---|---|---|
| CACHE-01 | Repeat the same report request | Authorization is checked and the cached result is returned |
| CACHE-02 | Compare cold and warm results | Values are identical |
| CACHE-03 | Compare timings | Warm-cache widgets are faster than the matching cold-cache widgets |
| CACHE-04 | Cache expiry | The result is recalculated safely after expiry |
| CACHE-05 | Relevant source data changes | The affected cache is invalidated or its version changes |

QA must report cold-cache and warm-cache results separately.

## 7. Permission and role testing

Test the supported dashboard roles, including:

- municipality administrator;
- service-provider administrator;
- service-provider help desk;
- treatment-plant administrator;
- users with partial dashboard permissions;
- roles blocked from login.

For each role, verify:

- only permitted cards and charts are visible;
- unauthorized report endpoints return the approved access-denied response, normally HTTP `403`;
- an unauthorized widget does not execute its database query;
- cached data cannot bypass authorization;
- navigation, filters, and dashboard totals match the role's approved scope.

## 8. Cache isolation and security

Use at least two service providers and two treatment plants.

| ID | Test scenario | Expected result |
|---|---|---|
| SEC-01 | Provider A loads a report | Only Provider A data is returned and cached |
| SEC-02 | Provider B loads the same report | Provider A data is never returned |
| SEC-03 | Plant A and Plant B request the same report | Each plant receives only its own data |
| SEC-04 | User role or scope changes | The application uses the new authorized scope |
| SEC-05 | Direct unauthorized call to a cached endpoint | Access is denied even if a cached result exists |
| SEC-06 | Different years or locales | Separate, correctly filtered results are returned |

Verify that cache identity includes every applicable dimension:

- role or permission scope;
- service-provider ID;
- treatment-plant ID;
- year and other report filters;
- locale;
- report group;
- data or cache version.

## 9. Dashboard data-accuracy testing

For each optimized count, total, and chart:

1. Record the value from the approved current implementation or report.
2. Execute the optimized implementation with the same filters.
3. Compare it with a direct database query or approved source.
4. Test no-data, single-record, and representative large-data cases.
5. Repeat for different roles, providers, plants, and years.

The optimization must not incorrectly change:

- building and sanitation counts;
- FSM totals;
- payment totals;
- road, sewer, drain, or water-supply lengths;
- public-health totals;
- chart labels, categories, and series;
- provider, plant, permission, or year filtering.

For rewritten spatial SQL, attach before-and-after result comparisons as well as query plans.

## 10. SVG, JavaScript, CSS, and visual testing

The dashboard document delay and frontend assets must be measured separately.

QA must verify:

- no SVG, JavaScript, CSS, font, or image request returns `404` or `500`;
- icons appear in the correct cards;
- a missing or invalid icon uses the approved fallback;
- SVG loading does not shift, stretch, or break the card layout;
- browser caching reduces repeat asset loading where configured;
- charts initialize after their data arrives;
- no new JavaScript console error appears;
- production assets are minified and do not include unnecessary development payloads;
- the measured `app.js` and `app.css` sizes are recorded after optimization.

Test with normal loading, hard refresh, disabled cache, and a throttled network.

## 11. Failure and recovery testing

Test controlled failures for:

- database or report-query failure;
- report API HTTP `500`;
- report API HTTP `403`;
- request timeout;
- temporary offline connection;
- unavailable cache service;
- expired user session;
- empty or malformed chart response.

Expected behaviour:

- the complete dashboard does not crash;
- only the affected section displays an error;
- a safe retry action is available where appropriate;
- unrelated sections remain usable;
- the interface does not remain in an endless loading state;
- sensitive exception, SQL, credential, or server information is not displayed.

## 12. Browser, responsive, and accessibility scope

Test at minimum in the browsers supported by the project, including Chrome and Edge, and Firefox if it remains supported.

Verify:

- desktop and smaller laptop layouts;
- keyboard-only login and dashboard navigation;
- readable loading and error messages;
- visible keyboard focus;
- correct disabled-button behaviour;
- spinner/loading states that do not rely only on colour;
- card, chart, sidebar, and navigation behaviour during loading;
- resizing the browser before and after chart rendering.

## 13. Performance comparison report

QA should attach a before-and-after table using the same test conditions:

| Measurement | Before | After | Pass/Fail |
|---|---:|---:|---|
| Login request duration | 1.48 s baseline |  |  |
| Dashboard HTTP TTFB | 8.62 s controlled median; 19.31 s historical capture | 2.71 s shell median | Initial result: Pass |
| Full browser load | 10.54 s controlled median |  |  |
| Time until shell is visible | Dashboard waited for reports | 2.71 s HTTP shell median | Initial result: Pass |
| Time until first widget displays |  |  |  |
| Time until all permitted widgets display |  |  |  |
| Dashboard SQL statement count | 122 controlled median |  |  |
| Dashboard database time | 2.29 s controlled median |  |  |
| Cold-cache total | Not available | 3.36 s content in persistent-process benchmark; repeat in QA browser | Pending QA |
| Warm-cache total | Not available | 3.14 s HTTP median including shell | Initial result: Pass |
| `app.js` transferred/uncompressed size | About 10.57 MB uncompressed baseline |  |  |
| Browser-console errors |  |  |  |
| Application-log errors |  |  |  |

Performance approval must be based on agreed targets and comparable evidence, not only on whether the page feels faster.

## 14. QA sign-off criteria

QA can approve the complete change when:

- one user action produces exactly one login request;
- the login button loading state works for success, failure, validation, keyboard submission, and browser-back cases;
- authentication does not execute or wait for dashboard report calculations;
- the dashboard shell is not blocked by aggregate queries;
- cold-cache and warm-cache results are recorded separately;
- unauthorized widgets do not query or return restricted data;
- cache data cannot cross provider, plant, role, year, locale, or filter scopes;
- optimized data matches the approved source values;
- a failed report does not break the complete dashboard;
- SVGs and frontend assets load without missing files or layout breakage;
- blocked-role and remember-me behaviour remain correct;
- no new browser-console, application-log, security, or accessibility regression is found;
- before-and-after performance evidence is attached to BASEIMIS-20.

## 15. QA evidence checklist

- [ ] Tested build/version and environment
- [ ] Test user roles and data scopes
- [ ] Valid-login Network capture
- [ ] Repeated-click Network capture
- [ ] Invalid and blocked-role results
- [ ] Login-loader screenshot or recording
- [ ] Cold-cache timings
- [ ] Warm-cache timings
- [ ] Dashboard request and SQL counts
- [ ] Role/permission matrix results
- [ ] Provider and plant cache-isolation results
- [ ] Data-accuracy comparison
- [ ] Spatial-query result and plan comparison, when applicable
- [ ] Asset sizes and failed-request check
- [ ] Browser-console output
- [ ] Laravel/application-log output
- [ ] Responsive and keyboard test results
- [ ] Failure/retry test results
- [ ] Final before-and-after comparison
- [ ] QA approval, rejection, or documented exceptions
