# Draft Report: D2.4 Documentation and Incorporation of Localized CWIS Indicators on IMIS

## Purpose

This draft report documents the work related to deliverable D2.4: **Documentation and incorporation of localized CWIS indicators on IMIS for municipalities where IMIS customization is done by ISPL**.

The purpose of this work is to record how CWIS indicators have been localized, incorporated, and maintained across customized IMIS deployments. The focus is on municipalities where ISPL has customized IMIS and where CWIS indicator logic needs to reflect local data availability, municipal reporting needs, and implementation-specific database structures.

## Background

Base IMIS includes the CWIS module for monitoring sanitation indicators through a structured data flow:

1. CWIS indicator definitions are maintained in `cwis.data_source`.
2. Source data is collected from IMIS modules or CWIS-specific input forms.
3. Database functions calculate indicator values.
4. Final values are stored in `cwis.data_cwis`.
5. Dashboards, exports, and reports read from `cwis.data_cwis`.

This architecture allows global CWIS indicators to be implemented while still allowing local adjustments where municipalities have customized workflows, forms, or database structures.

## Localized CWIS Indicator Incorporation

Localized CWIS indicator incorporation means adapting the CWIS indicator implementation to match the available municipal data and the customized IMIS deployment.

This may include:

- Adding or updating indicator definitions.
- Mapping indicator formulas to local IMIS tables.
- Creating municipality-specific source fields.
- Updating database functions to calculate indicators from available data.
- Adjusting dashboard or export behavior.
- Documenting assumptions, limitations, and formula changes.
- Preserving final output in `cwis.data_cwis` for consistent reporting.

## Municipal Deployments Covered

CWIS implementation and localization work has been incorporated in the following customized IMIS deployments:

| Municipality / Deployment | Implementation Context |
| --- | --- |
| Amalaxmi IMIS code base | CWIS indicator logic has been incorporated into the customized Amalaxmi IMIS code base. |
| Birendranagar web app | CWIS indicator logic has been incorporated into the customized Birendranagar web application. |

These deployments show that the CWIS module is not only part of the Base IMIS upgrade but has also been adapted into municipality-specific applications maintained or customized by ISPL.

## Current CWIS Architecture Used For Localization

The localized CWIS implementation follows the same core architecture as Base IMIS:

```text
Municipality source data
        ↓
CWIS source/input tables
        ↓
CWIS calculation functions
        ↓
cwis.data_cwis
        ↓
Dashboard / report / export
```

This keeps municipal customization manageable because the source data and formula logic may vary, but the final reporting table remains consistent.

## Data Source Layer

The `cwis.data_source` table acts as the indicator master list.

It stores:

- outcome category
- indicator code
- indicator label
- indicator identity used by dashboards and exports

For localized implementations, this table must contain the indicators required by the municipality. If a municipality uses additional localized indicators, those indicators should be added through seeders or controlled database scripts.

## Calculation Layer

CWIS calculations are handled through database functions.

The current pattern is:

1. A master update function runs for the selected year.
2. The master function calls individual indicator functions.
3. Each individual function calculates one indicator.
4. The result is updated into `cwis.data_cwis`.

This pattern is useful for localization because each indicator can be adjusted without changing the dashboard or export layer.

## Output Layer

The final calculated values are stored in:

```text
cwis.data_cwis
```

This table remains the main output table for:

- CWIS dashboard
- CWIS chart views
- CSV/Excel export
- NSD or external reporting where applicable
- generated annual CWIS values

For localized municipalities, this table should remain the common final output location even if source forms and formulas differ.

## Localized Indicator Documentation Requirement

For each customized municipality, localized CWIS documentation should include:

| Documentation Item | Description |
| --- | --- |
| Municipality name | Name of the municipality or deployment |
| Indicator code | CWIS indicator code such as `EQ-1`, `SF-1a`, or localized code |
| Indicator name | Human-readable indicator name |
| Formula | Calculation rule used in the deployment |
| Source tables | IMIS tables used by the formula |
| Source fields | Specific fields used for numerator, denominator, or logic |
| Function name | Database function responsible for calculation |
| Output table | Usually `cwis.data_cwis` |
| Local assumption | Any municipality-specific interpretation |
| Evidence / remarks | Notes about data limitations or verification |

This documentation is important because municipalities may not have identical IMIS data coverage or identical operational workflows.

## Amalaxmi Implementation Summary

The Amalaxmi IMIS code base includes CWIS implementation work as part of the customized municipal deployment.

The implementation follows the CWIS pattern:

```text
Amalaxmi IMIS data
        ↓
CWIS calculation functions
        ↓
cwis.data_cwis
        ↓
CWIS dashboard/reporting
```

The CWIS logic in the Amalaxmi code base should be documented indicator by indicator so that each formula can be reviewed, tested, and compared with the global CWIS definition.

Important documentation points for Amalaxmi:

- Which global CWIS indicators are active.
- Which indicators are localized.
- Which municipal source tables are used.
- Which assumptions are applied due to available data.
- Whether all calculated values are stored in `cwis.data_cwis`.
- Whether dashboard and export output match the calculated values.

## Birendranagar Implementation Summary

The Birendranagar web app also includes CWIS implementation work as part of the municipality-specific IMIS customization.

The implementation should follow the same output principle:

```text
Birendranagar source data
        ↓
CWIS calculation functions
        ↓
cwis.data_cwis
        ↓
CWIS dashboard/reporting
```

Important documentation points for Birendranagar:

- Confirm the active CWIS indicator list.
- Confirm any localized indicators or formula differences.
- Map each formula to its source table and source field.
- Confirm generated values in `cwis.data_cwis`.
- Confirm dashboard, report, and export consistency.

## Recommended Documentation Format

For each municipality, the documentation should be maintained in a repeatable format.

Recommended format:

```text
Municipality:
Indicator Code:
Indicator Name:
Global CWIS Formula:
Localized Formula:
Source Table:
Source Field:
Calculation Function:
Output Table:
Assumptions:
Testing Status:
Remarks:
```

This will make it easier to compare CWIS implementation across Base IMIS, Amalaxmi, Birendranagar, and future municipalities.

## Testing and Validation Approach

For each localized CWIS implementation, the following validation should be performed:

1. Confirm indicator exists in `cwis.data_source`.
2. Confirm required source fields exist in the municipal database.
3. Run the CWIS generation process for a test year.
4. Confirm calculated values are inserted or updated in `cwis.data_cwis`.
5. Compare function output with manual formula calculation.
6. Confirm dashboard values match `cwis.data_cwis`.
7. Confirm export values match `cwis.data_cwis`.
8. Document any local assumptions or missing source data.

## Risks and Review Points

The following points should be reviewed by the technical lead:

1. Localized formulas may differ from global formulas if source data is incomplete.
2. Municipal deployments may use different table structures or field names.
3. Some indicators may require proxy data where direct source data is unavailable.
4. Database functions must be version-controlled and documented.
5. Dashboard and export layers should not calculate values independently.
6. `cwis.data_cwis` should remain the final source of calculated indicator values.
7. Any municipality-specific change should be clearly separated from the reusable Base IMIS implementation.

## Current Status

Completed or ongoing:

- CWIS indicator implementation is present in the Base IMIS code base.
- CWIS has also been implemented in the Amalaxmi IMIS code base.
- CWIS has also been implemented in the Birendranagar web app.
- New Equity indicator upgrade work is being prepared for Base IMIS.
- Indicator definitions and source input structure are being documented.

Pending:

- Prepare municipality-wise indicator mapping for Amalaxmi.
- Prepare municipality-wise indicator mapping for Birendranagar.
- Compare localized formulas against global CWIS formula definitions.
- Identify any municipality-specific deviations.
- Store finalized documentation in the GitHub repository.
- Validate dashboard/export consistency for each deployment.

## Recommendation

The recommended approach for D2.4 is:

```text
Document each municipality separately
        ↓
Map every CWIS indicator to source data and function logic
        ↓
Keep final output in cwis.data_cwis
        ↓
Record local assumptions and differences
        ↓
Version-control the documentation and SQL/function changes
```

This will support technical review, future maintenance, and transparent comparison between global CWIS definitions and municipality-specific IMIS implementations.

