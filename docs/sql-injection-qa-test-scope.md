# SQL Injection Remediation — QA Test Scope

Prepared: 5 October 2026  
Related plan: [Impact and remediation plan](sql-injection-impact-and-remediation-plan.md)  
Release status: Local implementation batch; not deployed or approved for release

## 1. Objective

Verify that the repaired SQL inputs cannot change query structure while preserving normal BaseIMIS features, formulas, response formats, search behavior, spatial data, and audit history. This scope covers security checks and functional regression after the changes, not just the original CWIS payload.

## 2. Implementation status and limits

Implemented in this batch:

- CWIS year validation before survey database work and a bound calculation-function call.
- Six building searches, four map autocomplete searches, and permission search bindings.
- Duplicate API search-route declarations removed; existing protected declarations retained.
- Extent identifier allowlists and value bindings in containment, building, line, polygon, and point queries.
- KPI year/provider/date bindings; quarterly OR conditions grouped under provider restrictions; missing provider assignment denied for provider users.
- Bound WKT conversion for affected utility/community/hotspot model saves, preserving Eloquent save calls and revision hooks.
- Bound community/hotspot ward lookups, KML validity lookup, and sewer/building geometry conversion.
- Generic errors instead of exception text in the touched hotspot-create and building-survey failure paths.

Not claimed complete:

- Application-wide SQL audit: additional raw SQL remains in map association, buffer, and analytical methods. For example, `MapsService::getContainmentBuildings()` still concatenates its field/value, and `getRoadInaccesibleISummaryInfo()` still interpolates width/range. These require another scoped remediation batch; do not mark all SQL injection issues closed.
- Live PostgreSQL function definitions, role grants, server controls, and exposure investigation belong to the DevOps/DBA work package.
- PostgreSQL/PostGIS integration tests, browser/mobile regression, and VAPT retesting have not been executed here.
- GET-to-POST migration for survey generation is deferred to avoid changing frontend workflow in this batch.
- No arbitrary business year range was introduced; CWIS requires an integer, and KPI optional numeric filters retain their defaults.

Workspace integration blocker: an unresolved merge conflict appeared in `app/Services/Fsm/ApplicationService.php` during implementation. It was not modified by this batch. Resolve and review that conflict before whole-application testing or deployment. Do not deploy conflict markers.

## 3. Test environment and prerequisites

1. Use an isolated staging deployment with PostgreSQL and PostGIS matching production versions. SQLite is not a substitute for spatial/database-function integration.
2. Record commit/release, configuration, PostgreSQL/PostGIS versions, browser, mobile/API client version, tester, and test date.
3. Use sanitized fixtures and a restorable database snapshot. Do not run adversarial or destructive tests against production.
4. Prepare accounts: administrator, permitted CWIS user, role administrator, provider A admin/helpdesk, provider B admin/helpdesk, a low-permission user, a provider user without an assignment, and a logged-out client.
5. Prepare two providers with records on quarter start/end boundaries, multiple years, deleted records, and expected KPI/PDF totals.
6. Prepare map records for every supported layer, valid/null geometry, English/Nepali names, apostrophes, no-match searches, and valid/invalid WKT/KML files.
7. Save a pre-change baseline from a trusted version using the same dataset: CWIS values, search outputs, map bounds, KPI figures, PDF content, saved geometries, revisions, and timestamps.
8. Resolve the workspace merge conflict and ensure the complete patch includes `app/Support/GeometryValue.php` and the new regression test file.

## 4. Expected compatibility

- Valid user workflows, formulas, URLs, JSON keys, search limits, and wildcard semantics should remain unchanged unless listed below.
- Invalid CWIS years and malformed scalar filters now produce validation failures.
- Unsupported map identifiers now fail rather than becoming SQL.
- Invalid, empty, or wrong-type geometry is rejected in the converted storage paths.
- A provider account without a provider assignment is denied KPI access.
- Quarterly records previously exposed through incorrectly grouped OR conditions must no longer cross provider boundaries. This is an intentional security correction, not a formula change.
- Browser validation may redirect with errors; JSON requests should be tested with `Accept: application/json`. Do not require every HTML form failure to return HTTP 422.

## 5. Detailed test cases

Record each case as Not Run, Pass, Fail, or Blocked. Attach sanitized evidence and defect links.

### A. CWIS survey and original finding

| ID | Steps / scenario | Expected result |
|---|---|---|
| CW-01 | Generate a survey for a supported year with no existing indicators | Same indicators and values as baseline; expected writes only |
| CW-02 | Open a year with existing indicators | Existing-year behavior and values preserved; no unexpected duplicates |
| CW-03 | Submit missing/blank year, decimal, array, alphabetic text, and SQL-expression text | Controlled validation failure; calculation function not executed |
| CW-04 | VAPT team retests original reported input in authorized staging | No SQL execution via input and no file/error disclosure |
| CW-05 | Repeat with no session, valid session, and low-permission session | Intended authentication/authorization enforced; resolve report's unauthenticated-access discrepancy |
| CW-06 | Save/edit CWIS data and export where supported | Normal save, reload, indicator values, and export behavior preserved |

### B. Search and autocomplete

Run SR-01 through SR-04 for all six building searches, all four map autocomplete types, and role-permission search.

| ID | Steps / scenario | Expected result |
|---|---|---|
| SR-01 | Search an existing BIN, road, house number, sewer, sanitation value, place, or permission | Same authorized records, response keys, and result limit as baseline |
| SR-02 | Search English, Nepali, spaces, and apostrophes such as `O'Reilly` | Correct results without SQL errors |
| SR-03 | Search literal SQL-looking text and a value with no matches | Text is handled as data; no broadened result set or SQL disclosure |
| SR-04 | Check `%`, `_`, empty input, and long values | Existing wildcard behavior retained; controlled responses; no server error |
| SR-05 | Edit a role after permission search | Selected permissions and normal role-save behavior preserved |

### C. API/session authentication

| ID | Steps / scenario | Expected result |
|---|---|---|
| AU-01 | Inspect effective building search routes after cache rebuild | No duplicate sewer/house-number declarations; protected route remains |
| AU-02 | Call all building search APIs with valid/expired/missing token | Intended Sanctum behavior; no fallback to unauthenticated results |
| AU-03 | Use browser search with a valid and expired session | Intended session behavior and response contract preserved |
| AU-04 | Run the revamp/mobile workflows consuming those endpoints | No new login loop, unexpected redirect, or broken JSON parsing |

### D. Map extent and zoom

Required layer matrix: containments (`id`), buildings (`bin`, `house_number`), roads/drains/sewers/water supply (`code`, existing `gid` callers where applicable), communities (`id`), wards/overlay (`ward`, existing `gid` callers), places (`id`, existing `gid` callers), treatment plants/hotspots/water samples/toilets (`id`). Confirm identifiers against the deployed schema and actual clients. If a legitimate combination is missing, report it for a fixed mapping; never enable arbitrary identifiers.

| ID | Steps / scenario | Expected result |
|---|---|---|
| MP-01 | Open each feature from its module's Map link | Same feature, extent, and marker/geometry as baseline |
| MP-02 | Use map search and zoom, including house-number and ward-overlay flows | Same bounds and frontend behavior; existing `atrribute` route key still works |
| MP-03 | Supply an unsupported layer or altered column identifier | Controlled rejection before identifier reaches SQL |
| MP-04 | Put apostrophes/SQL-looking text in feature value | Value stays data; no unrelated features returned |
| MP-05 | Request no-match record and null geometry | No sensitive disclosure; document pre-existing UI limitations separately |
| MP-06 | Run association, nearby-road, buffers, summary and export tools | Regression smoke test; these additional raw-query paths are NOT security-cleared by this batch |

### E. KPI dashboard and PDFs

| ID | Steps / scenario | Expected result |
|---|---|---|
| KP-01 | Compare selected-year and all-year cards/charts against baseline | Same formulas and permitted-record totals |
| KP-02 | Compare all quarters, especially first/last day and timestamps | Dates handled consistently; no binding count/type errors |
| KP-03 | Compare response-time, inclusion, sludge, FSCR, and targets | Same expected figures except removal of unauthorized rows |
| KP-04 | Export PDFs for selected year/provider and default filters | Same report structure and correct authorized figures |
| KP-05 | As provider A, submit provider B ID and omit provider ID | Provider A scope remains enforced |
| KP-06 | Use records from provider B exactly on quarter boundaries | Provider B records excluded for provider A despite OR date branches |
| KP-07 | Use provider account without assignment | Access denied; never aggregate all providers |
| KP-08 | Submit malformed/array/SQL-text year and provider IDs | Controlled validation failure before data queries |

### F. Geometry editing, imports, and revisions

Run applicable cases for roads, drains, sewers, water supply, communities, hotspots, building-survey KML, and sewer-connection geometry display.

| ID | Steps / scenario | Expected result |
|---|---|---|
| GE-01 | Create valid line/multiline or polygon/multipolygon feature | Feature saves, displays correctly, and has expected SRID/type |
| GE-02 | Edit geometry through drawing tools and reload | Geometry and related fields match expected values; no coordinate shift |
| GE-03 | Edit only non-geometry fields | Existing geometry remains unchanged |
| GE-04 | Submit malformed WKT, wrong type, empty geometry, or self-intersecting polygon | Controlled rejection; no partial feature changes or leaked SQL |
| GE-05 | Check inside/outside municipality boundary and ward behavior | Existing boundary/ward rules remain intact; compare to baseline |
| GE-06 | Submit invalid geometry, then valid geometry in the same workflow | No lingering aborted transaction; later valid request succeeds |
| GE-07 | Compare before/after saved spatial values using PostGIS equality and SRID checks | Equivalent geometry; raw EWKB text formatting alone is not a functional failure |
| GE-08 | Inspect revisions, actor, timestamps, and model-driven cache updates after save | Hooks continue to run; audit entry identifies actual edit correctly |
| GE-09 | Import valid and invalid KML through the mobile/API workflow | Existing valid upload path works; invalid input is controlled and cleaned up |
| GE-10 | Load building/sewer geometry in sewer-connection map view | Same WKT geometry and response fields |
| GE-11 | Force a safe staging storage/database failure | No exception details in touched error responses; no unintended partial saves |

### G. Application smoke and DevOps verification

| ID | Steps / scenario | Expected result |
|---|---|---|
| OP-01 | Login, navigation, building CRUD, FSM application flow, dashboards | Main features remain usable after integrated merge resolution |
| OP-02 | Run workers/scheduled jobs and representative exports | Required database permissions retained |
| OP-03 | Check deployed Laravel/PHP error settings and controlled failing request | No public SQL, paths, stack traces, credentials, or file contents |
| OP-04 | DBA checks runtime memberships, grants, privileged functions, and harmless controlled file-access test | No unauthorized server-file/program access |
| OP-05 | Verify firewall, DB connection rules, public web root, and blocked secret/log paths | Approved access only; secrets not publicly retrievable |
| OP-06 | Review exposure investigation and credential actions | Findings and any rotation recorded without secrets in evidence |

## 6. Automated checks and recorded results

Run the isolated suite from the repository root:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Unit/SqlInjectionRemediationTest.php --do-not-cache-result
```

Recorded local result: **23 tests, 59 assertions passed**. These tests use a minimal container, mocked DB operations, and PostgreSQL query-builder pretend mode. They do not boot `.env`, connect to PostgreSQL, execute PostGIS, verify HTTP middleware, or prove production safety.

PHP syntax checks passed for the touched controllers/services and the new geometry helper. The full application suite was not run: the shared workspace has an unresolved merge conflict, and the existing PHPUnit configuration does not establish an isolated test database. Configure a disposable database and resolve the merge before running database-dependent tests.

Staging result: **Not Run**. VAPT result: **Not Run**. Do not copy automated-unit-test success into those fields.

## 7. Defect and sign-off format

For each failure record: case ID, environment/release, role, sanitized inputs, steps, expected/actual result, screenshots or sanitized response, relevant log timestamp, and defect link. Never attach credentials, real secret-file contents, or unnecessary personal data.

Release gates:

- [ ] Merge conflict resolved and complete diff reviewed.
- [ ] All applicable functional cases executed with baseline comparison.
- [ ] No unexplained change to formulas, geometry, search outputs, or revisions.
- [ ] Authentication/provider isolation and malicious-input checks passed.
- [ ] PostgreSQL/PostGIS integration verified on staging.
- [ ] DevOps evidence attached and operational smoke tests passed.
- [ ] Remaining raw-query findings separately tracked and risk reviewed; no claim of application-wide closure.
- [ ] VAPT retest signed off for the precise repaired paths.

QA sign-off: __________  Developer sign-off: __________  DevOps/DBA sign-off: __________  VAPT reference: __________
