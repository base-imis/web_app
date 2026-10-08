# SQL Injection: Impact Analysis and Remediation Plan

Review date: 5 October 2026  
Project: BaseIMIS / `lang_web_app`  
Status: Initial local remediation batch implemented; broader query review and live verification remain pending  
Priority: High, based on the supplied VAPT finding

Implementation update: see [QA scope and batch status](sql-injection-qa-test-scope.md) for the code changes, automated checks, remaining map-query risks, staging requirements, and current workspace merge blocker. The checklist below remains the target plan; it is not a claim that every item is complete.

## 1. Purpose and evidence

This document orders the work needed to address the reported CWIS SQL injection and related unsafe SQL construction found during a targeted source review. It identifies implementation impact, owners, testing, and deployment requirements.

Evidence used:

- Supplied VAPT screenshots describing SQL injection through `GET /cwis/cwis-df-mne/newsurvey?year=...` and PostgreSQL file disclosure through an error response.
- The supplied SQL Injection Remediation Guide.
- Local source inspection during this conversation, with key CWIS and search locations rechecked on the review date.

The VAPT report states that part of `/etc/passwd` was read using `pg_read_file()`. This was not independently reproduced. Unsafe CWIS query concatenation is confirmed in source. Other listed patterns require remediation and controlled verification; this document does not claim that every listed endpoint has been successfully exploited. This is a targeted review, not an exhaustive audit.

The live server configuration, database grants, deployed function definitions, and production exposure have not been inspected. Jira BASEIMIS-38 could not be retrieved; its contents are not independently verified here. Line references are review-time locations and may move.

## 2. Security impact versus remediation impact

| Area | Security impact | Evidence boundary |
|---|---|---|
| Server-file confidentiality | Database-server files can be disclosed through the application | Reported successful read of part of `/etc/passwd`; ordinarily account information, not password hashes |
| Secrets and configuration | Readable files may contain credentials or configuration | Conditional on database privileges, filesystem access, and deployment layout; not demonstrated for other files |
| Application confidentiality | SQL injection may expose data accessible to the runtime database role | Exact data access has not been verified |
| Integrity | Unauthorized operations could alter records, geometry, or reporting inputs | Modification was not demonstrated by the supplied VAPT evidence |
| Availability | Expensive or disruptive queries may affect service operation | No denial of service demonstrated |
| Other environments | Shared code or credentials may expose production or connected services | Requires deployment and credential inventory |

File reads occur on the PostgreSQL server or container, which may differ from the application host. Access is limited by database and operating-system permissions. Excessive privileges increase potential impact but do not automatically establish a Critical severity rating.

Remediation can affect validation responses, search results, map zoom, reporting, imports, model events, and database permissions. These functional risks are separate from security consequences and must be regression-tested.

## 3. Ordered work and ownership

| Order | Work | Owner | Dependency / completion evidence |
|---|---|---|---|
| 1 | Preserve logs, identify affected deployments, contain the known endpoint if needed | DevOps / security | Environment inventory and protected evidence; containment active if patch is delayed |
| 2 | Fix CWIS year validation and parameter binding | Backend developer | Regression test for the reported input path; valid survey generation unchanged |
| 3 | Disable exposed errors and review dangerous database privileges | DevOps / DBA | Can proceed alongside order 2; effective configuration and privilege review |
| 4 | Fix building, map autocomplete, and role searches | Backend developer | Search results and API response contracts preserved |
| 5 | Resolve API route/guard inconsistencies | Backend developer / QA | Effective middleware and session/token tests |
| 6 | Allowlist map identifiers and bind extent values | Backend developer / frontend / QA | Complete supported layer/attribute inventory and map regression checks |
| 7 | Bind KPI filters and verify provider isolation | Backend developer / QA | Baseline comparison for cards, charts, and reports |
| 8 | Bind geometry/KML throughout creation, edits, and imports | Backend developer / DBA / QA | Spatial validation, transactions, and revision-history checks |
| 9 | Review remaining raw SQL and live database functions | Backend developer / DBA | Reviewed request-to-query paths and function definitions |
| 10 | Deploy, validate operational behavior, and retest VAPT | DevOps / QA / VAPT team | Deployment evidence and retest results |

Do not delay the CWIS patch until every broader module change is complete. Conversely, a passing CWIS retest does not close the application-wide review.

## 4. Application implementation scope

### 4.1 CWIS new survey — first application fix

References:

- `routes/web.php:516`: GET new-survey route.
- `app/Http/Controllers/Cwis/CwisMneController.php:181`: `createIndex()`.
- Same controller, line 206: request year is read and passed to `cwis()`.
- Same controller, line 114: year is concatenated into the database function call.

Required changes:

1. Validate the year at the beginning of `createIndex()`, before database work. Require an integer and the business-approved year range; agree how missing years should behave.
2. Use the validated value throughout. Remove the later assignment from raw `$request->year`.
3. Replace SQL concatenation with binding:

```php
$result = DB::select(
    'SELECT * FROM insert_data_into_cwis_table(?)',
    [$year]
);
```

4. Review other callers of `cwis()` and validate their entry points too.
5. Preserve the existing response shape and calculation results.
6. As a separate route-design improvement, move generation to an authorized, CSRF-protected POST if it writes records; update callers and keep GET for display.

Impact: low to medium for validation and binding; broader frontend/workflow impact if changing the HTTP method. No calculation-formula or schema change is normally required for binding.

Authentication discrepancy: local route-group and controller middleware require authentication, while the VAPT report says no privileges are required. Verify deployed middleware and retest without a session before assigning final attack prerequisites.

### 4.2 Building searches

File: `app/Http/Controllers/BuildingSearchController.php`, lines 31, 43, 54, 66, 77, 88.

Affected searches: BIN, road code, house number, sewer code, preconnected-building search, and sanitation system.

Replace interpolated `ILIKE` expressions with bindings, for example:

```php
Building::whereRaw('bin ILIKE ?', [$bin . '%'])
    ->take(10)
    ->get();
```

Preserve selected columns, result limits, response shape, and current search semantics. Validate input type/length. Explicitly decide whether `%` and `_` remain wildcards; parameterization alone does not make them literal characters.

Impact: low; autocomplete and mobile/API consumers must retain their expected contracts.

### 4.3 Map autocomplete and role permission search

- `app/Http/Controllers/MapsController.php`, lines 868, 871, 875, 879: places, roads, house numbers, and BIN autocomplete.
- `app/Http/Controllers/Auth/RoleController.php:55`: permission search.

Bind search values. Retain existing `DISTINCT`, soft-delete conditions, geometry requirements, limits, case behavior, and return types. Do not add unrelated filtering or pagination changes to the security patch.

Impact: low. Test English/Nepali text, apostrophes, spaces, no matches, wildcard behavior, and empty/oversized values.

### 4.4 Map extent and feature zoom

- `app/Http/Controllers/MapsController.php:238`: `getExtent()` reads request layer, `atrribute`, and value.
- `app/Services/Maps/MapsService.php`: containment around line 228; buildings 263; lines 299; polygons 336; points 399.

The service concatenates column identifiers and values, and some methods also construct table references dynamically.

Required changes:

1. Inventory every layer and attribute used by frontend and API clients.
2. Create a server-owned mapping from each supported layer to its fixed table, allowed columns, and access requirements.
3. Reject unsupported combinations. No arbitrary-identifier fallback.
4. Bind values in every extent, centroid, coordinate, and geometry lookup.
5. Preserve the existing `atrribute` request spelling unless all clients are updated together.
6. Handle missing records and null geometry consistently.

Table/column identifiers cannot be supplied as value placeholders. Only trusted mapped identifiers may enter SQL structure.

Impact: medium to high. Test building/containment zoom, roads, drains, sewers, water supply, community and ward polygons, toilets, treatment facilities, samples, and hotspots where supported by the application.

### 4.5 KPI dashboard and reports

File: `app/Http/Controllers/Fsm/KpiDashboardController.php`.

Review anchors: year conditions at 55/60; provider fragment at 92; selected-year queries around 148–188; quarterly queries around 284–360; annual queries around 440–456.

Required changes:

- Validate filter values at request entry points and preserve documented defaults.
- Bind year/provider values in all raw queries, including helper methods and report paths.
- Replace the concatenated provider condition with query-builder conditions or fixed SQL plus bindings.
- Preserve the existing provider restriction derived from the authenticated user's role.
- Reject or safely handle a provider user without a provider assignment; do not silently broaden access.
- Keep existence validation separate from authorization. Verify any schema-qualified validation syntax against the configured Laravel connection.

Impact: medium to high. Compare KPI cards, quarterly/annual charts, response time, sludge, inclusion, FSCR, and PDFs against a fixed baseline dataset. Security refactoring must not change formulas or permitted data scope.

### 4.6 Geometry storage, calculations, and imports

| File / anchor | Required scope |
|---|---|
| `app/Services/LayerInfo/LowIncomeCommunityServiceClass.php:102` | Ward intersections, intersection area, geometry create/update |
| `app/Services/PublicHealth/HotspotServiceClass.php:136` | Same operations for hotspot creation/update |
| `app/Services/UtilityInfo/RoadlineService.php:118` | Road geometry storage |
| `app/Services/UtilityInfo/DrainService.php:120` | Drain geometry storage |
| `app/Services/UtilityInfo/SewerLineService.php:123` | Sewer geometry storage |
| `app/Services/UtilityInfo/WaterSupplysService.php:118` | Water-supply geometry storage |
| `app/Http/Controllers/UtilityInfo/RoadlineController.php:270` | Geometry edit path |
| `app/Http/Controllers/UtilityInfo/DrainController.php:247` | Geometry edit path |
| `app/Http/Controllers/UtilityInfo/SewerLineController.php:236` | Geometry edit path |
| `app/Http/Controllers/UtilityInfo/WaterSupplysController.php:226` | Geometry edit path |
| `app/Http/Controllers/Api/BuildingSurveyController.php:137` | Imported KML passed to PostGIS |
| `app/Services/SewerConnection/SewerConnectionService.php:134` and 157 | Geometry conversion, including stored values reinserted into SQL |

Bind WKT/KML in all calculation and storage queries. Validate expected type, validity, emptiness, size, and coordinate/SRID assumptions. Setting SRID 4326 labels coordinates; it does not transform coordinates from another reference system.

Malformed spatial input may raise a database error before a validity check returns. Convert expected parse/validation failures into controlled responses, avoid exposing SQL, and ensure any failed transaction is rolled back before further database work.

The spatial models use `RevisionableTrait`. Preserve model events, timestamps, authorization, soft-delete behavior, and audit history. Do not copy a direct `DB::update()` example over model saves without an explicit preservation strategy.

Impact: high. Test all create/edit/import paths, geometry calculations, rollback behavior, and revision history.

### 4.7 API route and authorization review

`routes/api.php:45` declares the sewer-code route outside the Sanctum group; another declaration is around line 156. The building search controller itself applies `auth`, so this does not alone prove anonymous access.

Inspect effective route registration and guards, consolidate duplicate declarations, and verify browser sessions, API tokens, logged-out access, and low-permission users. Coordinate any guard change with mobile clients.

Impact: medium. Authentication fixes can affect existing integrations if their expected guard changes.

### 4.8 Remaining SQL and database functions

Trace request-derived values into raw SQL across `app/`, including values retrieved from storage and reused in SQL. Review dynamic query builders in MapsController/MapsService beyond the listed anchors. Search results are review leads, not proof of exploitability.

Do not automatically replace fixed SQL expressions or already-bound queries. Existing examples around `MapsController.php:772`, 780 and `MapsService.php:139` already use value bindings.

Review live CWIS functions and their dependencies with the DBA. `docs/cwis-db-functions-extracted.sql` is a reference snapshot, not proof of current live definitions. An `EXECUTE` statement is not automatically unsafe; inspect whether data is bound with `USING` and how identifiers are constructed.

## 5. DevOps and DBA work package

### 5.1 Deployment containment and errors

- Inventory test/production releases and shared credentials/infrastructure.
- Preserve access, application, and database logs before rotation.
- Restrict the test environment or temporarily block the vulnerable route if patch deployment is delayed.
- Set `APP_DEBUG=false` in externally accessible environments; use the appropriate `APP_ENV`.
- Rebuild Laravel configuration cache through the normal deployment process and refresh long-running processes as needed.
- Verify effective PHP error-display settings and generic HTTP errors, not just `.env` contents.

### 5.2 Runtime database privileges

Identify the role used by actual application connections. Read-only initial checks:

```sql
SELECT current_database(), current_user, session_user, version();

SELECT rolname, rolsuper, rolcreaterole, rolcreatedb,
       rolreplication, rolbypassrls
FROM pg_roles
WHERE rolname = current_user;

SELECT rolname AS privileged_role
FROM pg_roles
WHERE rolname IN (
    'pg_read_server_files',
    'pg_write_server_files',
    'pg_execute_server_program'
)
AND pg_has_role(current_user, oid, 'MEMBER');
```

Also inspect direct/inherited memberships, roles the account can assume, ownership, explicit function grants, and effective permissions for the installed PostgreSQL version. The checks above are an initial audit, not a complete privilege proof.

Target state:

- Dedicated runtime identity without administrative, file-access, program-execution, or unnecessary schema-creation privileges.
- Separate migration/owner, backup, and administration identities.
- Only required table, sequence, schema, and function privileges, based on real application usage.
- No indirect membership path to an owner/administrative role.
- Reviewed default grants so future migrations do not reintroduce broad access.

Test reductions in staging before live rollout. CWIS needs writes and PostGIS functionality; do not make the account read-only or remove PostGIS. Avoid blanket revocations without a dependency inventory.

### 5.3 Privileged functions

Inspect live `insert_data_into_cwis_table()` and called functions, file/program-access functions and wrappers, overloads, owners, execution grants, and `SECURITY DEFINER` routines. Necessary definer routines need a narrowly privileged owner, trusted search path, and restricted callers. Coordinate SQL-body fixes with developers.

### 5.4 Network and filesystem

- Restrict PostgreSQL network access to approved application, worker, backup, monitoring, and administrative sources.
- Review firewall rules, `pg_hba.conf`, broad ranges, and `trust` entries; use appropriately authenticated/encrypted inter-host connections.
- Keep test and production access separated.
- Point the web root at Laravel `public`; prevent downloads of secrets, repositories, logs, and backups.
- Run PHP and PostgreSQL under separate non-root service accounts with necessary filesystem access only.
- Review container mounts and prevent unnecessary database-account access to application secrets or deployment keys.

Changing `/etc/passwd` permissions is not the fix. Remove unauthorized database file-access capabilities.

### 5.5 Exposure investigation and secrets

Correlate suspicious requests/errors with VAPT times and available logs. Determine whether other readable files or shared credentials may have been exposed. Rotate exposed or reasonably suspected compromised secrets through the approved secret-management process; update dependent services and retire old credentials. Do not include secrets in tickets or evidence.

Do not automatically rotate Laravel `APP_KEY`; assess encrypted-data and session dependencies first. Absence of suspicious logs is not proof that no exposure occurred.

## 6. Change-impact matrix

| Area | Expected intended behavior | Regression risk | Main safeguard |
|---|---|---|---|
| CWIS | Valid years generate the same indicators; invalid input is rejected | Low–medium | Fixed dataset and survey-generation tests |
| Searches | Same valid results and response contract | Low | Preserve filters, limits, language and wildcard behavior |
| Map extent | Only supported identifiers accepted; authorized zoom remains functional | Medium–high | Complete client layer/attribute inventory |
| KPI/PDFs | Same authorized data and calculations | Medium–high | Baseline figures and provider-isolation tests |
| Geometry/KML | Valid features save/import correctly; invalid inputs fail cleanly | High | PostgreSQL/PostGIS integration tests and transaction checks |
| Audit history | Revisions and timestamps remain accurate | High if model saves are bypassed | Model-event and revision assertions |
| API guards | Intended session/token access works; unauthorized requests fail | Medium | Client authentication regression tests |
| Validation responses | Browser forms show errors; API clients handle controlled failures | Medium | Test redirects and JSON separately; do not assume every failure is HTTP 422 |
| Database role | Required workflows work with reduced privileges | High if changed blindly | Staging privilege inventory and functional tests |
| Database schema | Binding normally needs no schema migration | Low | Avoid unrelated structural changes |

## 7. QA acceptance scenarios

- [ ] Valid CWIS years produce the expected indicators and preserve existing-year behavior.
- [ ] Missing, malformed, array, and out-of-range numeric inputs follow the intended validation contract.
- [ ] The original VAPT input no longer executes as SQL and exposes no server-file content.
- [ ] Apostrophes and SQL-looking text in free-text searches are treated as data or rejected by documented constraints.
- [ ] Search limits, soft-delete filters, English/Nepali behavior, and wildcard semantics are preserved.
- [ ] Every supported map layer/attribute works; unknown identifiers are rejected before unsafe SQL construction.
- [ ] Missing/null geometry and no-record cases have controlled responses.
- [ ] All supported spatial types and imports work; malformed/oversized input fails without partial writes or exposed SQL.
- [ ] Audit revisions, timestamps, and model events are preserved.
- [ ] KPI/PDF baseline values match; provider users cannot broaden their scope by changing or omitting filters.
- [ ] Logged-out, low-permission, session-authenticated, and token-authenticated requests behave correctly.
- [ ] Runtime role cannot perform unauthorized server-file access, verified by the DBA using a harmless controlled test.
- [ ] Workers, scheduled jobs, exports, and backups retain required operation after role/config changes.
- [ ] Responses reveal no stack traces, SQL, bindings, secrets, or file contents.

Use PostgreSQL/PostGIS for database integration tests; SQLite cannot validate the relevant spatial functions and PostgreSQL behavior. Perform adversarial tests in an authorized test environment with non-sensitive fixtures. Run existing relevant tests plus the broader suite before deployment where supported; record blockers rather than claiming unrun checks passed.

## 8. Deployment, rollback, and closure

Before deployment: preserve protected configuration/grant snapshots, confirm recoverable backups, record baseline results, and validate the patch with reduced runtime privileges in staging.

Deploy small coherent batches. Record release identifiers and configuration changes per environment. Refresh caches/processes as applicable and run smoke tests after each batch. Do not revert to exposed vulnerable code as a routine rollback; use a known-safe release or keep the affected endpoint restricted while correcting a regression. Restore only necessary permissions if a privilege change breaks a legitimate dependency, and document the exception.

Close the original VAPT finding only after the patched endpoint and deployed controls pass the authorized retest, including the authentication discrepancy. Close the broader remediation work only after the listed modules and remaining query review have their own evidence.

Required closure evidence:

- [ ] Reviewed application change set and deployed release identifiers.
- [ ] Effective debug/error configuration checks.
- [ ] Sanitized database role, membership, ownership, and function-access review.
- [ ] Network/filesystem access review.
- [ ] Regression results, including spatial and audit-history checks.
- [ ] Exposure investigation summary and any credential actions.
- [ ] VAPT retest evidence and tracked residual issues.

## 9. References

- [OWASP SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html): parameterization and identifier allowlists.
- [Laravel 8 deployment](https://laravel.com/framework/docs/8.x/deployment): configuration caching and debug exposure.
- [PostgreSQL predefined roles](https://www.postgresql.org/docs/current/predefined-roles.html): server-file/program capabilities; consult the matching installed-version documentation.
- [PostgreSQL function security](https://www.postgresql.org/docs/current/sql-createfunction.html): definer privileges and secure search paths.

Classification: CWE-89. Remediation priority: High. Final severity and unauthenticated reachability require evidence from the deployed environment.
