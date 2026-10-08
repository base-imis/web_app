# Draft Report: D2.5 Upgraded Base IMIS With Localized CWIS Indicators for Bangladesh and Nepal

## Purpose

This draft report documents the proposed approach for deliverable D2.5: **Documentation on Upgraded Base IMIS with localized CWIS indicators for Bangladesh and Nepal with demonstration**.

The purpose of this work is to support country-specific CWIS indicator behavior in the upgraded Base IMIS. The agreed approach is to add a country option in the CWIS settings. Based on the selected country, the system will adjust CWIS dashboard content, generator attributes, indicator labels, and calculated values where localization is required.

## Background

The CWIS module in Base IMIS currently follows a common structure:

1. Indicator definitions are stored in `cwis.data_source`.
2. CWIS input/source data is collected through IMIS modules or CWIS-specific forms.
3. Database functions calculate indicator values.
4. Final calculated results are stored in `cwis.data_cwis`.
5. Dashboards, reports, and exports display values from `cwis.data_cwis`.

For D2.5, the same structure will be retained, but a country-level configuration will be added so the system can support localized CWIS indicators for Bangladesh and Nepal.

## Proposed Country-Based Localization Approach

The key implementation decision is to add `country` as a configurable option in CWIS settings.

Example:

```text
CWIS Settings
    Country: Nepal / Bangladesh
```

After a country is selected, CWIS behavior will be adjusted according to that country.

This may affect:

- indicator labels
- indicator descriptions
- generator form attributes
- required source fields
- available indicator list
- dashboard cards and charts
- calculation functions
- final generated indicator values
- export/report content

## Proposed Flow

The proposed flow is:

```text
Admin selects country in CWIS settings
        ↓
System loads country-specific CWIS configuration
        ↓
CWIS generator displays country-specific attributes
        ↓
User enters or generates data
        ↓
Country-aware DB functions calculate values
        ↓
Final values stored in cwis.data_cwis
        ↓
Dashboard displays country-specific CWIS output
```

## CWIS Settings Change

The CWIS settings module should include a country field.

Recommended field:

```text
country
```

Possible values:

```text
Nepal
Bangladesh
```

Recommended database location:

```text
public.site_settings
```

or the existing CWIS settings table if the project already stores CWIS-specific configuration there.

The country value should be read by:

- CWIS generator page
- CWIS dashboard page
- CWIS calculation/update functions
- export/report logic where required

## Country-Specific CWIS Behavior

### Nepal

When country is selected as Nepal, the system should load the Nepal-specific CWIS configuration.

This may include:

- Nepal-specific indicator labels or translations.
- Nepal-specific data source mappings.
- Nepal-specific dashboard arrangement.
- Nepal-specific generator fields.
- Nepal-specific formula assumptions where approved.
- Nepal-specific reporting format.

### Bangladesh

When country is selected as Bangladesh, the system should load the Bangladesh-specific CWIS configuration.

This may include:

- Bangladesh-specific indicator labels or translations.
- Bangladesh-specific data source mappings.
- Bangladesh-specific dashboard arrangement.
- Bangladesh-specific generator fields.
- Bangladesh-specific formula assumptions where approved.
- Bangladesh-specific reporting format.

## Dashboard Behavior

The CWIS dashboard should respond to the selected country.

Country selection may change:

| Area | Possible Change |
| --- | --- |
| Indicator list | Show only indicators applicable to the selected country |
| Indicator labels | Use country-specific wording |
| Chart values | Display values calculated using country-specific logic |
| Dashboard cards | Show country-relevant indicators and descriptions |
| Empty states | Show missing data messages based on selected country requirements |
| Export data | Export country-specific indicator names and generated values |

The dashboard should not independently calculate values. It should continue to read final values from:

```text
cwis.data_cwis
```

## CWIS Generator Behavior

The CWIS generator should also respond to the selected country.

Country selection may change:

| Area | Possible Change |
| --- | --- |
| Input fields | Country-specific fields are displayed |
| Required fields | Required inputs may differ by country |
| Indicator descriptions | Description/help text changes by country |
| Formula preview | Preview calculations follow country-specific rules |
| Validation | Validation rules reflect the selected country |
| Evidence fields | Required documents may vary by country |

For example:

```text
Country = Nepal
    Show Nepal-specific CWIS generator attributes

Country = Bangladesh
    Show Bangladesh-specific CWIS generator attributes
```

## Calculation Behavior

The official CWIS calculation should remain in the database function layer.

There are two possible implementation approaches:

### Option 1: Country Parameter in Functions

Functions can accept country as an input:

```text
update_data_into_cwis_table_revised_2024(year, country)
```

or:

```text
update_data_into_cwis_table_country_localized(year, country)
```

This makes country selection explicit.

### Option 2: Functions Read Country From Settings

Functions can read the configured country from CWIS settings internally.

Example:

```text
SELECT country FROM site_settings
```

Then the function applies Nepal or Bangladesh logic based on that value.

This is simpler for the UI because the user only selects country once in settings.

## Recommended Calculation Approach

The recommended approach is:

```text
Store country in CWIS settings
        ↓
Laravel passes selected year to generation process
        ↓
DB function reads country setting or receives country from Laravel
        ↓
Country-specific logic is applied
        ↓
Final result is stored in cwis.data_cwis
```

For maintainability, if formulas differ significantly between Nepal and Bangladesh, separate helper functions should be used internally.

Example:

```text
update_data_into_cwis_table_eq_3_nepal(year)
update_data_into_cwis_table_eq_3_bangladesh(year)
```

Then the master function chooses which one to call based on the selected country.

## Data Storage Recommendation

Final generated values should still be stored in:

```text
cwis.data_cwis
```

The selected country should be stored in settings, and if historical reporting needs to preserve country context, the country may also be stored with generated data or audit records.

Recommended minimum:

```text
CWIS settings:
country

cwis.data_cwis:
year
indicator_code
data_value
```

Recommended for stronger audit:

```text
cwis.data_cwis:
year
indicator_code
country
data_value
```

If `country` is not added to `cwis.data_cwis`, the system should ensure one configured country is used consistently for the installation.

## Demonstration Plan

The demonstration for D2.5 can show the following:

1. Open CWIS settings.
2. Select `Nepal`.
3. Open CWIS generator.
4. Show Nepal-specific attributes and labels.
5. Generate CWIS data for a selected year.
6. Open dashboard and show Nepal-specific CWIS output.
7. Change country setting to `Bangladesh`.
8. Open CWIS generator again.
9. Show Bangladesh-specific attributes and labels.
10. Generate or recalculate CWIS data.
11. Open dashboard and show Bangladesh-specific CWIS output.

This demonstrates that the same upgraded Base IMIS can support localized CWIS behavior for both Nepal and Bangladesh.

## Technical Implementation Items

The following implementation items are required:

| Area | Required Work |
| --- | --- |
| CWIS settings | Add country option |
| Seeder/configuration | Add country-specific indicator labels or mappings if required |
| Generator UI | Load fields/attributes based on selected country |
| Validation | Apply country-specific validation rules |
| DB functions | Apply country-specific calculation logic |
| Dashboard | Display country-specific labels, charts, and values |
| Export/report | Use selected country context |
| QA | Test Nepal and Bangladesh flows separately |

## Risks and Review Points

The following points should be reviewed by the technical lead:

1. Whether country is stored globally in settings or per generated CWIS year.
2. Whether `country` should be added to `cwis.data_cwis` for audit/history.
3. Whether Nepal and Bangladesh formulas differ enough to require separate DB functions.
4. Whether indicator labels should be stored in `cwis.data_source` or a separate localized configuration table.
5. Whether changing country after generating data should require recalculation.
6. Whether dashboard should show a warning if values were generated under a different country setting.
7. Whether exports should include the selected country.

## Current Status

Planned or ongoing:

- Country-based CWIS localization approach has been identified.
- Country option will be added to CWIS settings.
- Dashboard behavior will change according to country selection.
- Generator attributes and values will change according to country selection.
- Nepal and Bangladesh will be supported as localized CWIS contexts.

Pending:

- Confirm exact country field location.
- Confirm country-specific indicator differences.
- Confirm whether `country` must be stored in `cwis.data_cwis`.
- Implement country option in settings UI.
- Implement country-aware generator behavior.
- Implement country-aware dashboard behavior.
- Implement or update database functions for country-specific calculation.
- Prepare demonstration data for Nepal and Bangladesh.
- Document final implementation in the GitHub repository.

## Recommendation

The recommended design is:

```text
Add country in CWIS settings
        ↓
Use country to control generator fields and dashboard display
        ↓
Keep official calculation in DB functions
        ↓
Store final values in cwis.data_cwis
        ↓
Document Nepal and Bangladesh behavior separately
```

This keeps the Base IMIS code reusable while allowing localized CWIS indicators for Bangladesh and Nepal.

