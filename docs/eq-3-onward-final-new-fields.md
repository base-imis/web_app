# Final Proposed New Fields: EQ-3 to EQ-6.5

This document consolidates the proposed database additions discussed for EQ-3, EQ-4, EQ-4a, EQ-5, EQ-6.1, EQ-6.1a, EQ-6.1b, EQ-6.2, EQ-6.3, EQ-6.3a, EQ-6.3b, EQ-6.4, and EQ-6.5. These are proposals; no database changes have been made.

EQ-1 and EQ-2 require no new fields based on the saved SQL reviewed in this chat. `population_served` and `lic_id` come from `building_info.buildings`; `safely_managed_sanitation_system` is calculated by `execute_select_build_sanisys_nd_criterias()`. The live database schema has not been verified.

For every foreign key below, use the same data type as the referenced primary key. Document references should use the application's document storage. Nullable Boolean fields represent Yes, No, or Unknown; do not default uncollected information to No.

## EQ-3: Equity of Subsidies

Proposed storage: `cwis.subsidy_records`, with one record per actual paid disbursement. Add only the following four fields. All amounts must use the same reporting currency. Select the reporting period from `disbursement_date`; no separate year field is required for this design.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| subsidy_id | BIGINT, generated identity, primary key | Unique identifier for each subsidy transaction row. |
| subsidy_amount | DECIMAL(12,2) | Non-negative total amount actually paid in this transaction, not a per-household rate. Exact decimal storage with two decimal places. |
| sanitation_category | VARCHAR(10) | Required dropdown with controlled values `NSS` for non-sewered sanitation and `SS` for sewered sanitation; enforce allowed values with a CHECK constraint. |
| disbursement_date | DATE | Payment date used to select the reporting period. |

**Calculation:** `EQ-3 = total NSS subsidies paid / total SS subsidies paid` within the same reporting period. A zero denominator produces an undefined result unless an agreed indicator rule specifies otherwise.

## EQ-4 and EQ-4a: Gender Equity in Sanitation Leadership

Calculate EQ-4 from existing employee gender, employer, and employment-date records. No new employee count columns are needed for EQ-4. For EQ-4a, identify eligible leadership positions using the existing designation; add the leadership flag below only if designation cannot reliably identify them.

### Employee Field for EQ-4a

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| is_leadership_position | BOOLEAN, nullable | Conditional addition to `fsm.employees` for EQ-4a. Yes identifies functional heads and managerial heads as defined in the indicator. Reuse a reliable designation mapping instead if available. NULL means unclassified. Preserve role history for historical reporting. |

### Calculated Annual Snapshot

Proposed storage: `cwis.sanitation_personnel_snapshot`. Calculate the following counts from eligible employee records for the agreed assessment date, then insert or update the annual snapshot. These count fields are calculated outputs, not manually entered attributes on each employee. This table is proposed in this document; it has not been created in the database.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| id | BIGINT, primary key | Unique annual snapshot. |
| year | INTEGER | Reporting year; unique when storing one combined city record per year. |
| women_employee_count | INTEGER | Calculated EQ-4 numerator: count women employed in eligible sanitation-related decision-making bodies. |
| total_employee_count | INTEGER | Calculated EQ-4 denominator: count all employees in those same bodies, including full-time and contract staff. |
| women_leadership_count | INTEGER | Calculated EQ-4a numerator: count women in eligible leadership positions using designation or the leadership flag. |
| total_leadership_count | INTEGER | Calculated EQ-4a denominator: count all people in those leadership positions using the same classification. |
| organization_id | Organization foreign key, conditional | Add only if storing separate organization snapshots. Make `(year, organization_id)` unique instead of `year`. |

**Calculations:**

- `EQ-4 = women_employee_count / total_employee_count * 100`.
- `EQ-4a = women_leadership_count / total_leadership_count * 100`.

Use non-negative counts and a common assessment date and organization scope. Women's counts must not exceed their corresponding totals. Leadership counts must be consistent with employee totals for the same scope. EQ-4 includes employees whose roles are not directly related to sanitation in eligible bodies; exclude NGOs and community organizations as specified in the supplied document. EQ-4a includes the functional and managerial heads described there. A zero denominator produces no data.

Before filling the snapshot, verify that the employee register covers all eligible decision-making bodies; service-provider records alone may be insufficient. Use employment dates and historical employer/role information for the reporting assessment, rather than current status alone. Resolve missing gender or leadership classification before treating the relevant counts as complete. Calculate both percentages from the saved counts; if the application stores indicator results separately, write those percentages to its existing indicator-results table.

## EQ-5: Employee Information Additions

Reuse the existing Employee Information module and `fsm.employees`.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| workforce_category | VARCHAR(50) | Controlled sanitation workforce category, including sanitation-authority, desludging-service, and public/community-toilet staff. |
| employer_id | Employer foreign key, conditional | Add only if the existing `service_provider_id` cannot represent all eligible employers. Use a common employer register and a consistent employer relationship. |

Reuse existing `gender`, `employee_type`, `wage`, `service_provider_id`, `employment_start`, `employment_end`, and `status`. The current `wage` field represents monthly remuneration. Do not create duplicate gender or designation fields.

### Reuse Existing Monthly Remuneration for EQ-5

No new salary input column or separate annual employee salary table is proposed. Reuse Monthly Remuneration (`fsm.employees.wage`) and gender to calculate the averages. The workforce-category and employer additions above are conditional on gaps in existing workforce coverage, not required merely to calculate salaries.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| wage | Existing integer validation; verify database type | Existing Monthly Remuneration. Reuse as the salary source; do not add a duplicate annual salary input. |
| gender | Existing string; verify database type | Existing Employee Gender. Group eligible records into women and men for the two averages. |
| average_salary_of_women_in_sanitation | NUMERIC(18,2), calculated annual output | `a`: average of eligible women's monthly remuneration multiplied by 12, when a consistent annualized salary basis is valid. Store once per reporting year in CWIS reporting storage if these input totals must be retained. Not an employee attribute. |
| average_salary_of_men_in_sanitation | NUMERIC(18,2), calculated annual output | `b`: average of eligible men's monthly remuneration multiplied by 12 on the same basis. Store once per reporting year if required. Not an employee attribute. |

**Calculation:** `EQ-5 = (b - a) / b`. The common factor of 12 cancels, so the same ratio can be calculated from average monthly remuneration when both groups use that same annualization basis. This is a ratio in the supplied specification, not a percentage. Calculate with full precision and round only the displayed or stored outputs.

The annualized approach is valid only when monthly remuneration represents the reporting period and the agreed salary definition. A current monthly wage alone cannot establish actual annual earnings when pay changes, employment covers only part of the year, or relevant additional payments are omitted. Use historical salary evidence for those cases and previous-year reports; do not silently treat today's wage as a past year's wage. Preserve reporting-year gender and job classification if they change. Current active status alone must not exclude eligible former employees. Missing wages must not become zero; report incomplete data. The result is undefined if either group has no known wages or the men's average is zero. Use one common currency and include the full eligible sanitation workforce.

## EQ-6.1 to EQ-6.5: Annual Worker Policies and Overall Results

Proposed storage: `cwis.sanitation_worker_policy`. One city-wide record per reporting year. Show EQ-6.1 in a separate **Training Policy (EQ-6.1)** section on the same Employee Information form, alongside the individual training fields described below. Load the shared policy for the selected reporting year when an employee form is opened; save edits to that one yearly policy record, not to each employee. Other city-wide policy indicators can remain in the Worker Policies section. These values are not repeated on every employee.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| id | BIGINT, primary key | Unique annual policy record. |
| reporting_year | INTEGER, unique | Shared year for all indicators below. |
| training_certification_required | BOOLEAN, nullable | EQ-6.1: training/certification is required to work in sanitation. |
| training_requirement_document_id | Document foreign key, nullable | Evidence establishing the training requirement. |
| covers_labor_rights_and_recourse | BOOLEAN, nullable | EQ-6.1a: required training covers both labor rights and recourse. |
| labor_rights_document_id | Document foreign key, nullable | Evidence from training content covering both topics. |
| covers_safety_health_and_sop | BOOLEAN, nullable | EQ-6.1b: training covers occupational safety, health risks, and standard operating procedures. |
| safety_health_sop_document_id | Document foreign key, nullable | Curriculum evidence covering all three topics. |
| legal_recourse_available_to_all | BOOLEAN, nullable | EQ-6.2: all sanitation workers, regardless of employment formality, have a formal channel for legal recourse. |
| legal_recourse_document_id | Document foreign key, nullable | Evidence that the channel covers all eligible workers, including informal workers. |
| worker_union_exists | BOOLEAN, nullable | EQ-6.3: union existence, following the supplied data points and registration evidence. See the specification issue below. |
| worker_union_registration_document_id | Document foreign key, nullable | Registration evidence for the sanitation workers' union. |
| worker_union_operational | BOOLEAN, nullable | EQ-6.3a: the union is operating. |
| worker_union_operation_document_id | Document foreign key, nullable | Meeting minutes or other operational evidence. |
| city_supports_worker_union | BOOLEAN, nullable | EQ-6.3b: the city supports running the union. |
| worker_union_support_document_id | Document foreign key, nullable | Evidence of city support. |
| all_workers_social_security_covered | BOOLEAN, nullable | EQ-6.4: overall coverage of all sanitation workers in the city. |
| social_security_coverage_document_id | Document foreign key, nullable | Evidence establishing workforce-wide social-security coverage. |
| all_workers_health_insurance_covered | BOOLEAN, nullable | EQ-6.5: overall coverage of all sanitation workers in the city. |
| health_insurance_coverage_document_id | Document foreign key, nullable | Evidence establishing workforce-wide health-insurance coverage. |

**Applicability and evidence:**

- EQ-6.1a and EQ-6.1b apply only when EQ-6.1 is Yes. Otherwise display Not applicable; if the parent is unknown, applicability is unresolved.
- EQ-6.3a and EQ-6.3b apply only when EQ-6.3 is Yes under the supplied specification. EQ-6.2, EQ-6.4, and EQ-6.5 are independent.
- A document upload alone does not establish Yes: its contents must substantiate the particular indicator for the reporting period.
- One document may support multiple indicators when its contents cover them.

### Training Fields on the Same Employee Information Form

The form has two distinct sections: **Employee Training** for the individual's completion status and certificate, and **Training Policy (EQ-6.1)** for the shared yearly requirement and official evidence. Individual training completion does not determine EQ-6.1.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| training_received | BOOLEAN, nullable | Individual employee field: Yes/No/Unknown. Reuse or migrate the existing `training_status` field where suitable instead of maintaining duplicate status fields. Preserve existing descriptive training information during any migration. |
| training_certificate_document_id | Document foreign key, nullable | Individual employee evidence. Show the upload when training received is Yes. Require a certificate only when the applicable process requires certification; training may be completed without a certificate. |
| reporting_year | INTEGER | Policy-section year selector; loads the shared annual policy record. This is not a new per-employee policy column. |
| training_certification_required | BOOLEAN, nullable | Shared EQ-6.1 policy field listed above. Store once per reporting year in `cwis.sanitation_worker_policy`. |
| training_requirement_document_id | Document foreign key, nullable | Shared policy evidence listed above: an official document establishing the mandatory training/certification requirement. An employee certificate alone cannot establish this requirement. |

The policy fields above repeat the annual table's fields only to show their placement in the same form; do not create duplicate database columns. Opening or saving an individual employee must not create another copy of the yearly policy. Allow shared policy edits only to users with the appropriate policy-management permission. Keep individual training saves and shared policy saves clearly identified within the form.

### Optional Right-to-Unionize Fields

The supplied EQ-6.3 title measures the **right to unionize**, but its data points and formula assess **union existence and registration**. These are different concepts. The proposed core columns above follow the data points; resolve this inconsistency before finalizing indicator computation. If both concepts are collected, add:

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| right_to_unionize | BOOLEAN, nullable | Whether sanitation workers have the right to unionize; separate from whether a union exists. |
| right_to_unionize_document_id | Document foreign key, nullable | Evidence establishing that right. |

## EQ-6.4 and EQ-6.5: Individual Fields in Employee Information

These individual coverage fields belong in the employee form. Preserve annual records in proposed storage `fsm.employee_annual_coverage` rather than overwriting historical coverage.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| id | BIGINT, primary key | Unique employee coverage record. |
| employee_id | Employee foreign key | References `fsm.employees.id`. |
| reporting_year | INTEGER | Make `(employee_id, reporting_year)` unique. Use an agreed reporting assessment date or period. |
| social_security_covered | BOOLEAN, nullable | EQ-6.4: this employee's coverage; Yes/No/Unknown. |
| social_security_document_id | Document foreign key, nullable | Evidence of this employee's coverage; a group document may be used if it includes them. |
| health_insurance_covered | BOOLEAN, nullable | EQ-6.5: this employee's coverage; Yes/No/Unknown. |
| health_insurance_document_id | Document foreign key, nullable | Evidence of this employee's health-insurance coverage. |
| health_insurance_start_date | DATE, nullable | Coverage start date when known. |
| health_insurance_end_date | DATE, nullable | Coverage expiry if applicable; cannot precede the start date. A missing expiry date does not by itself establish active coverage. |

Annual snapshots establish coverage at the agreed assessment; proving continuous coverage throughout a year requires coverage history for that entire period.

A city-wide Yes for EQ-6.4 or EQ-6.5 requires complete workforce coverage, including informal workers, and verified coverage for everyone. Any verified uncovered worker establishes No. An incomplete register or missing evidence cannot establish Yes. The annual overall fields in `cwis.sanitation_worker_policy` must agree with individual evidence for the same scope and period.

## Optional Individual Union Membership

These fields may be added to `fsm.employees` if individual membership tracking is wanted. They are not required to calculate the supplied EQ-6.3 indicators.

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| is_union_member | BOOLEAN, nullable | Whether this employee belongs to a union. |
| union_id | Union foreign key, nullable | References a union register; include only if such a register is maintained. |

## Corrections to the Supplied Indicator Sheets

- EQ-6.1a's description and logical statement repeat the training-requirement text. They should refer to labor-rights and recourse coverage and its evidence.
- The heading for the city-support union indicator says EQ-6.3a, but its code is EQ-6.3b. Correct the heading to EQ-6.3b.
- EQ-6.5's logical statement references training fields. Replace these with health-insurance coverage and evidence fields.
- EQ-6.3's right-to-unionize versus union-existence mismatch requires a definition decision, not just a field-name correction.

## Module Placement

| Field name | Data type | Notes |
| ---------- | --------- | ----- |
| Subsidy Records | Module/form | EQ-3 payment records. |
| Annual Personnel Snapshot | Module/form | EQ-4 and EQ-4a aggregate counts. |
| Employee Information | Existing module/form | Existing gender/employer/employment data for EQ-4; conditional leadership flag for EQ-4a; existing monthly remuneration and gender for EQ-5, with workforce classification only if needed; individual training status/certificate and a separate shared Training Policy (EQ-6.1) section on the same form; EQ-6.4 and EQ-6.5 individual coverage; optional individual union membership. |
| Worker Policies | Shared annual storage/section | EQ-6.1 through EQ-6.3b policy/union evidence, plus EQ-6.4 and EQ-6.5 overall city results. EQ-6.1 is shown on the employee form while remaining stored once per year in the shared policy table. |
