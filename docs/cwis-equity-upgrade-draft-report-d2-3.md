# Draft Report: D2.3 Upgraded Base IMIS With Global CWIS Indicators

## Purpose

This draft report documents the ongoing CWIS upgrade work for deliverable D2.3: **Upgraded Base IMIS version with all global CWIS indicators (144) and available in GitHub repository as open source**.

The current work focuses on adding the new Equity indicators into Base IMIS. The existing system already supports CWIS generation through database functions and stores final calculated indicator values in `cwis.data_cwis`. The new work extends this process by adding additional Equity indicators, saving their source inputs in a new Equity input table, and calculating their final values through database functions.

## Current CWIS Calculation Flow

The existing CWIS process works as follows:

1. Indicator definitions are stored in `cwis.data_source`.
2. The user generates CWIS data for a selected year.
3. `insert_data_into_cwis_table(year)` creates yearly rows in `cwis.data_cwis` based on `cwis.data_source`.
4. The master CWIS update function runs.
5. Individual indicator functions calculate values indicator by indicator.
6. Final values are updated into `cwis.data_cwis`.
7. Dashboards, exports, and reporting modules read from `cwis.data_cwis`.

This pattern will be preserved for the new Equity indicators.

## New Equity Indicator Scope

The Equity upgrade starts from `EQ-2`, because `EQ-1` already exists in the current CWIS seed data and `EQ-2` is part of the expanded Equity indicator set.

The new/expanded Equity indicators being added are:

| Indicator Code | Indicator Name |
| --- | --- |
| `EQ-2` | Equity of Access to Safely Managed Sanitation |
| `EQ-3` | Equity of Subsidies |
| `EQ-4` | Gender equity in sanitation leadership/workforce |
| `EQ-4a` | Gender equity in sanitation leadership |
| `EQ-5` | Gender pay gap in the sanitation workforce |
| `EQ-6.1` | Training/certification is required to be a sanitation worker |
| `EQ-6.1a` | Training covers labor rights and recourse |
| `EQ-6.1b` | Training covers occupational safety, health risks, and Standard Operating Procedures |
| `EQ-6.2` | All sanitation workers have a formal channel for legal recourse |
| `EQ-6.3` | Workers have the right to unionize |
| `EQ-6.3a` | Operational worker unions exist |
| `EQ-6.3b` | Support is offered by the city to run the union |
| `EQ-6.4` | All sanitation workers are covered by social security |
| `EQ-6.5` | All sanitation workers are covered by health insurance |

## Data Source Seeder Update

The `cwis.data_source` table is populated through the Laravel seeder:

```text
database/seeders/CwisDataSourceSeeder.php
```

The seeder has been updated to add the new Equity indicators from `EQ-2` to `EQ-6.5`.

Important implementation point:

- Existing indicator IDs were not changed.
- `EQ-2` was not hardcoded as ID `2`, because ID `2` is already used by `SF-1a`.
- New Equity rows are inserted using the next available ID.
- Existing rows are updated by `indicator_code` if already present.

This avoids duplicate indicator codes and avoids breaking existing CWIS references.

## New Equity Input Table

A new migration has been added for the Equity source tables:

```text
database/migrations/2026_10_06_000001_create_cwis_equity_indicator_tables.php
```

The tables created are:

```text
cwis.equity_subsidies
cwis.sanitation_personnel_snapshot
cwis.sanitation_worker_policy
```

These tables store source/input values only. They do not replace `cwis.data_cwis`.

### Table Design

Main structure:

| Table | Purpose |
| --- | --- |
| `cwis.equity_subsidies` | EQ-3 total subsidy amount fields for NSS and SS |
| `cwis.sanitation_personnel_snapshot` | EQ-4, EQ-4a, and EQ-5 annual count/salary snapshot fields |
| `cwis.sanitation_worker_policy` | EQ-6.1 to EQ-6.5 shared annual policy and city-wide evidence fields |

The form may still appear as one Equity form in the UI, but the backend should save each section to the table that matches the field dictionary.

## Why One Equity Input Table Is Used

The implementation decision is to keep one user-facing Equity form while storing data in normalized source tables that match the indicator field dictionary.

This means:

```text
One Equity form
        ↓
Equity source tables
        ↓
CWIS DB calculation functions
        ↓
cwis.data_cwis
```

This keeps the user workflow simple while preserving the existing CWIS architecture and keeping repeatable data, such as subsidy records and employee coverage records, in the correct table shape.

## Preview Calculation vs Official Calculation

The form can display calculated preview results immediately after the user enters input values. For example:

```text
EQ-3 preview = total NSS subsidy / total SS subsidy
```

However, this preview should not be treated as the official CWIS result.

The official result should still be calculated in the database function and saved to `cwis.data_cwis`.

Recommended rule:

| Calculation Location | Purpose |
| --- | --- |
| Frontend/UI | Preview only, helps the user verify input |
| Database function | Official calculation used by dashboard, report, export, and audit |

This avoids relying on browser-side calculations for official reporting.

## Proposed Final Equity Generation Flow

The final flow for the new Equity indicators should be:

1. Add the new indicator definitions in `cwis.data_source`.
2. User opens the CWIS Equity form for a selected year.
3. User enters values for `EQ-2` through `EQ-6.5`.
4. The form saves the raw/source values into the relevant Equity source tables.
5. User generates or recalculates CWIS data for that year.
6. `insert_data_into_cwis_table(year)` ensures rows exist in `cwis.data_cwis`.
7. The master update function calls each Equity indicator function.
8. Each function reads required source values from the relevant Equity source tables.
9. Each function calculates the indicator result.
10. Each function updates the final value in `cwis.data_cwis`.
11. Dashboard, export, and reporting use `cwis.data_cwis`.

## Required Database Functions

The following new or updated functions are expected:

```text
update_data_into_cwis_table_eq_2_newsan(_year integer)
update_data_into_cwis_table_eq_3_newsan(_year integer)
update_data_into_cwis_table_eq_4_newsan(_year integer)
update_data_into_cwis_table_eq_4a_newsan(_year integer)
update_data_into_cwis_table_eq_5_newsan(_year integer)
update_data_into_cwis_table_eq_6_1_newsan(_year integer)
update_data_into_cwis_table_eq_6_1a_newsan(_year integer)
update_data_into_cwis_table_eq_6_1b_newsan(_year integer)
update_data_into_cwis_table_eq_6_2_newsan(_year integer)
update_data_into_cwis_table_eq_6_3_newsan(_year integer)
update_data_into_cwis_table_eq_6_3a_newsan(_year integer)
update_data_into_cwis_table_eq_6_3b_newsan(_year integer)
update_data_into_cwis_table_eq_6_4_newsan(_year integer)
update_data_into_cwis_table_eq_6_5_newsan(_year integer)
```

A master Equity update function should call these individual functions:

```text
update_data_into_cwis_table_equity_newsan(_year integer)
```

This master function can then be called from the existing CWIS update process.

## Example Formula Mapping

| Indicator | Source Columns | Formula / Logic |
| --- | --- | --- |
| `EQ-2` | `eq2_lic_safely_managed_population`, `eq2_total_safely_managed_population` | LIC safely managed sanitation access divided by total safely managed sanitation access |
| `EQ-3` | `cwis.equity_subsidies.total_subsidies_amount_nss`, `total_subsidies_amount_ss` | NSS subsidy amount divided by SS subsidy amount |
| `EQ-4` | `cwis.sanitation_personnel_snapshot.women_employee_count`, `total_employee_count` | Women employees divided by total employees, multiplied by 100 |
| `EQ-4a` | `cwis.sanitation_personnel_snapshot.women_leadership_count`, `total_leadership_count` | Women leadership employees divided by total leadership employees, multiplied by 100 |
| `EQ-5` | `cwis.sanitation_personnel_snapshot.average_salary_of_women_in_sanitation`, `average_salary_of_men_in_sanitation` | Male average salary minus female average salary, divided by male average salary |
| `EQ-6.1` to `EQ-6.5` | `cwis.sanitation_worker_policy` boolean and evidence columns | Yes/No result based on the stored value and supporting evidence |

## Current Implementation Status

Completed:

- Reviewed the existing CWIS process.
- Confirmed the role of `cwis.data_source`.
- Added new Equity indicator rows from `EQ-2` to `EQ-6.5` in `CwisDataSourceSeeder`.
- Ran the seeder successfully.
- Added migration for the new Equity source tables.
- Kept final calculated values in the existing `cwis.data_cwis` table.

Pending:

- Run the new migration in the target database.
- Wire the Equity form save action to the relevant Equity source tables.
- Add backend validation for each Equity field.
- Add file upload handling for evidence/document fields.
- Create individual database functions for `EQ-2` to `EQ-6.5`.
- Add master Equity update function.
- Connect the master Equity update function into the existing CWIS generation/recalculation process.
- Add a recalculation action for cases where Equity input changes after CWIS data has already been generated.
- Test dashboard/export output after new indicators are generated.

## Technical Review Points For Team Lead

The following decisions need review:

1. Confirm that the one-table annual Equity input design is acceptable.
2. Confirm whether one record per year is enough or whether versioning/history of submitted Equity forms is required.
3. Confirm whether evidence files should be stored as file paths in this table or linked through a common document table.
4. Confirm whether frontend preview calculations should be shown for all quantitative indicators.
5. Confirm the exact handling of zero denominators, missing values, and incomplete evidence.
6. Confirm whether `EQ-6.3` should represent right to unionize, union existence, or both, because the source wording is not fully consistent.
7. Confirm when the Equity master update function should run: during full CWIS generation only, or also through a separate recalculation button.

## Recommendation

The recommended implementation is to keep the official CWIS architecture unchanged:

```text
cwis.data_source = indicator list
Equity source tables = raw Equity form input
database functions = official calculations
cwis.data_cwis = final calculated indicator output
```

This approach keeps the new Equity form simple for users while preserving the current dashboard and export process.
