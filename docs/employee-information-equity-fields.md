# Employee Information: Final EQ Fields

This document lists the fields to expose in the Employee Information module for EQ-5 (gender pay gap), EQ-6.4 (social security), and EQ-6.5 (health insurance). These are proposed additions; this document does not change the database.

## New fields on the employee record

Recommended storage: extend `fsm.employees`.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| workforce_category | VARCHAR(50) | Controlled category identifying sanitation-authority, desludging-service, public/community-toilet, or other eligible sanitation workers. Used to define the EQ-5 workforce. |
| employer_id | Foreign key; type matches employer primary key | Conditional addition: use a common employer register if the existing `service_provider_id` cannot represent sanitation authorities and other employers. Reuse `service_provider_id` for employers it already supports; define one consistent employer relationship. |

## EQ-5: Annual salary records

Show a salary-history section within Employee Information. Store one record per employee per reporting year in a proposed related table, `fsm.employee_annual_salaries`, rather than overwriting a salary on the employee record.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| id | BIGINT, primary key | Unique salary-record identifier. |
| employee_id | Foreign key; type matches `fsm.employees.id` | Links the salary record to the existing employee. |
| reporting_year | INTEGER | Reporting year. Make `(employee_id, reporting_year)` unique. |
| annual_salary_amount | NUMERIC(18,2), nullable | Non-negative annual salary using a documented, consistent reporting basis. NULL means unknown; zero means a confirmed zero salary. |
| currency_code | CHAR(3), optional | Needed only if multiple currencies are allowed. Convert amounts to one reporting currency before averaging. |

Reuse the employee's existing `gender` and `employee_type`. Preserve their reporting-year values if either changes, so historical results remain reproducible.

Calculation: `EQ-5 = (b - a) / b`, where `a` is the average annual salary of women and `b` is the average annual salary of men in the eligible workforce. The supplied indicator is a ratio; do not multiply by 100 unless presenting a separately labelled percentage. Missing salary records are unknown and must not be converted to zero. Report incomplete coverage when salary data is missing. The result is undefined if either gender has no known salary records or the men's average is zero.

The existing `wage` field is monthly remuneration. Do not assume `wage * 12` represents historical annual salary unless that rate applied throughout the year and matches the agreed reporting basis.

## EQ-6.4 and EQ-6.5: Employee coverage records

Show social-security and health-insurance fields within Employee Information. Recommended storage: a proposed related table, `fsm.employee_annual_coverage`, to preserve coverage for each reporting year. The annual record must use an agreed assessment date or period; a current status alone cannot establish full-year coverage.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| id | BIGINT, primary key | Unique coverage-record identifier. |
| employee_id | Foreign key; type matches `fsm.employees.id` | Links coverage to the existing employee. |
| reporting_year | INTEGER | Reporting year. Make `(employee_id, reporting_year)` unique. |
| social_security_covered | BOOLEAN, nullable | EQ-6.4: Yes/No/Unknown for this employee at the agreed reporting assessment. NULL means unknown. |
| social_security_document_id | Document reference; type matches document primary key | Supporting evidence for this employee's social-security coverage. May reference a group document that explicitly includes the employee. |
| health_insurance_covered | BOOLEAN, nullable | EQ-6.5: Yes/No/Unknown for this employee at the agreed reporting assessment. NULL means unknown. |
| health_insurance_document_id | Document reference; type matches document primary key | Supporting evidence for this employee's health-insurance coverage. May reference a group policy that explicitly includes the employee. |
| health_insurance_start_date | DATE, nullable | Coverage start date, when available. |
| health_insurance_end_date | DATE, nullable | Coverage expiry date, if applicable. Must not precede the start date. A missing end date alone does not prove active coverage. |

Use the application's document storage for uploads and link the stored documents through these references.

Both indicators ask whether **all sanitation workers in the city** are covered. Employee data supports a city-wide Yes only when the register includes the entire eligible workforce, including informal workers, and coverage is verified for everyone. Any verified uncovered worker means No; incomplete records cannot establish Yes. Retain the overall annual results and supporting evidence in `cwis.sanitation_worker_policy`.

## Existing fields to reuse

These fields already appear in the Employee Information application code; no duplicate columns are proposed. Their live database definitions have not been verified here.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| gender | String; verify database type | Used to group employees for EQ-5. Use consistent gender values. |
| employee_type | String; verify database type | Existing designation/job type; retain for occupational analysis. |
| wage | Integer in current validation; verify database type | Existing monthly remuneration. Does not preserve annual salary history. |
| service_provider_id | Existing employer foreign key | Reuse for supported service-provider employers. |
| employment_start | Date | Helps identify employees employed during the reporting period. |
| employment_end | Nullable date | Helps identify employment ending during the reporting period; include eligible former employees in historical reports. |
| status | Boolean in current validation | Current active/inactive status; do not use alone to determine historical workforce eligibility. |
| training_status | String; verify database type | Individual training status. Does not establish city-wide training requirements or curriculum content. |

## Indicators kept outside individual employee records

- **EQ-4 and EQ-4a:** the previously proposed annual employee and leadership counts remain in the annual personnel snapshot. They are totals, not attributes of one employee.
- **EQ-6.1, EQ-6.1a, EQ-6.1b, EQ-6.2, EQ-6.3, EQ-6.3a, and EQ-6.3b:** retain in the annual `cwis.sanitation_worker_policy` table. These describe training requirements, curriculum, legal recourse, and union conditions.
- Individual union membership is optional and is not required for the supplied EQ-6.3 formulas.

The related salary and coverage tables can be edited inside the existing Employee Information module; they do not require separate user-facing modules.
