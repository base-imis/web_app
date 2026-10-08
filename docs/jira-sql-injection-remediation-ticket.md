# Proposed Jira ticket: SQL injection remediation, implementation impact, and QA verification

Destination: Task Tracking-BaseIMIS board (resolve project through board configuration/filter before creation).
Related issue supplied by requester: BASEIMIS-38.
Status: Prepared on 7 October 2026; not submitted to Jira. Jira metadata and supported issue type remain to be retrieved.

## Summary

Remediate SQL injection and server-file disclosure in CWIS and related application queries; verify functional impact and deployment controls.

## Problem and evidence

The supplied VAPT report describes SQL injection in the `year` parameter of `GET /cwis/cwis-df-mne/newsurvey`, with PostgreSQL `pg_read_file()` used to disclose part of `/etc/passwd` through an exposed database error. The unsafe CWIS query concatenation was confirmed in local source before remediation. Exploitation of the deployed environment was not independently reproduced.

The local controller/route requires authentication, while the report states no privileges are required. Retesting must resolve this discrepancy. Production exposure, live database permissions, and access to files other than the reported file remain unverified. BASEIMIS-38 is a user-supplied related reference; its current content was not retrieved.

## Implementation completed locally in the preceding batch

1. `app/Http/Controllers/Cwis/CwisMneController.php`: validate integer year before survey database work and bind the argument to `insert_data_into_cwis_table(?)`.
2. `app/Http/Controllers/BuildingSearchController.php`: bind values in all six building searches while preserving search limits and JSON structure.
3. `app/Http/Controllers/MapsController.php`: bind four autocomplete searches and reject unsupported extent layers.
4. `app/Http/Controllers/Auth/RoleController.php`: bind permission-search text.
5. `routes/api.php`: remove duplicate sewer-code/house-number declarations while retaining protected routes.
6. `app/Services/Maps/MapsService.php`: allowlist supported extent identifiers and bind extent values.
7. `app/Http/Controllers/Fsm/KpiDashboardController.php`: bind year, provider and quarterly date values; group OR date conditions under provider restrictions; deny provider users without an assignment.
8. `app/Support/GeometryValue.php`: convert bound WKT to a database-generated geometry value, validate type/shape, and retain Eloquent model saves.
9. Community/hotspot and utility services/controllers: bind affected geometry and ward queries while preserving save hooks.
10. `app/Http/Controllers/Api/BuildingSurveyController.php` and `app/Services/SewerConnection/SewerConnectionService.php`: bind KML/geometry conversion inputs. Remove exception details from the touched building-survey and hotspot-create failure responses.

This describes the prior local batch, not a verified deployed release. Confirm its presence in the final integrated branch before release.

## Remaining implementation and integration work

- Review and remediate additional map association, buffer and analytical SQL. Known examples: `MapsService::getContainmentBuildings()` and `getRoadInaccesibleISummaryInfo()`.
- Complete request-to-query review beyond the initial list; fixed expressions and already-bound queries do not need blanket rewriting.
- Inspect live CWIS function definitions and called functions with the DBA for unsafe dynamic SQL and excessive privileges.
- Verify effective route middleware and session/token clients.
- Resolve and review the unrelated merge conflict observed in `app/Services/Fsm/ApplicationService.php` during the prior batch; recheck its current status before testing.
- Keep any survey GET-to-POST migration separate and coordinate frontend callers; it was not included in the initial batch.

## Security impact analysis

Reported impact is database-server file disclosure. Further disclosure of credentials, application data, or configuration depends on privileges, filesystem permissions, and deployment layout. Data modification, geometry corruption, reporting manipulation, denial of service, and production compromise have not been demonstrated by the supplied evidence and must not be described as confirmed incidents.

File reads occur on the database server/container. Excessive privileges increase potential impact, but do not automatically establish Critical severity. Authentication prerequisites must be verified.

## Functional impact analysis

| Area | Intended preservation / security change | Regression risk |
|---|---|---|
| CWIS | Same valid-year indicators and generation behavior; invalid inputs rejected | Low–medium |
| Search | Same filters, limits, wildcard behavior, languages and response structure | Low |
| Map extent | Same supported feature zoom; unsupported identifiers rejected | Medium–high |
| KPI/PDF | Same formulas and permitted data; unauthorized quarterly rows excluded | Medium–high |
| Geometry/KML | Valid features/imports preserved; invalid shapes rejected | High |
| Revisions/events | Eloquent save hooks retained; verify revisions, actors and timestamps | High if regressed |
| API authentication | Existing intended browser/mobile access preserved | Medium |
| Database permissions | Legitimate writes, PostGIS operations and jobs retained | High if revoked without dependency testing |

No calculation-formula or schema redesign is intended. Spatial equality, SRID, saved geometry, audit history, and reporting baselines must be compared in staging.

## DevOps / DBA scope

- Inventory affected deployments and shared credentials/infrastructure; preserve available logs and correlate VAPT times.
- Restrict the known vulnerable endpoint/environment until patched if necessary.
- Disable public Laravel/PHP debug output, refresh configuration/processes and verify effective generic error responses.
- Audit the actual runtime role, inherited memberships, ownership and explicit function access. Remove unnecessary administrative, server-file and program-execution capabilities after staging validation.
- Separate runtime, migration/owner, backup and administrator identities; preserve required table/sequence/function grants and PostGIS dependencies.
- Review SECURITY DEFINER routines, owners, execution grants and trusted search paths.
- Restrict database network access and service-account filesystem access; protect secrets, logs, backups and repository files from web access.
- Investigate possible prior exposure and rotate exposed or reasonably suspected compromised secrets through approved secret management.

## QA acceptance criteria

- Valid CWIS years produce baseline results; missing/malformed/SQL-expression years do not execute injected SQL.
- Original VAPT finding fails during authorized retest, both without a session and with permitted sessions.
- All six building searches, four map autocomplete modes and role-permission search preserve normal results; apostrophes, English/Nepali text, `%`, `_`, and SQL-looking text are handled correctly.
- Supported map layer/attribute combinations work; unsupported identifiers cannot reach SQL structure.
- KPI cards, quarterly/annual figures and PDFs match authorized baselines. Provider A cannot retrieve provider B records, including quarter-boundary cases.
- Geometry creation/editing/imports preserve expected shape, type, SRID and revisions. Invalid spatial input has controlled failures without unintended partial writes.
- Session and token authentication work as intended after route consolidation.
- Responses disclose no SQL, stack traces, credentials or file contents.
- Runtime role and deployment controls pass DBA/DevOps checks; workers, exports and scheduled jobs remain functional.
- Remaining unsafe query paths are tracked; closure is limited to paths actually repaired and retested.

## Validation and evidence

Prior local isolated regression result: 23 tests, 59 assertions passed in `tests/Unit/SqlInjectionRemediationTest.php`; touched PHP syntax checks passed. Tests used mocks/pretend queries and did not connect to PostgreSQL. PostgreSQL/PostGIS integration, browser/mobile QA, full application regression and VAPT retesting remain pending.

Repository QA scope: `docs/sql-injection-qa-test-scope.md`.
Repository impact/remediation plan: `docs/sql-injection-impact-and-remediation-plan.md`.

Attach final release identifiers, sanitized test results, baseline comparisons, DBA privilege review and VAPT retest evidence before closure. Do not include credentials or sensitive file contents.
