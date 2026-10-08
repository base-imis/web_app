# CWIS Formula-by-Formula Study

## Purpose

This document explains each CWIS formula in a separate, readable way. It focuses on:

- what each indicator means;
- what numerator and denominator are used;
- which DB function calculates it;
- which tables/functions provide the data;
- what filters and settings affect the result;
- caveats QA and PMs should know.

Detailed raw SQL evidence is stored separately:

- `docs/cwis-db-functions-extracted.sql`
- `docs/cwis-safe-sanitation-helper-extracted.sql`
- `docs/cwis-safe-sanitation-helper-parts-extracted.sql`

## Common Calculation Rules

Most indicators are stored in:

- `cwis.data_cwis.data_value`

Most percentage formulas use:

```text
round((numerator / denominator) * 100, 0)
```

Most functions store text `NaN` when:

- numerator is null;
- denominator is null;
- denominator is zero;
- numerator and denominator are both zero.

Important exceptions:

- `EQ-1` is a ratio, not a percentage.
- `SF-3e` is distance in meters, not a percentage.
- `SF-7` is assumption-based and becomes `100` whenever any emptying exists.

## Formula Summary Table

| Code | Indicator | Simple Formula |
|---|---|---|
| `EQ-1` | Equity ratio of LIC safe toilet access to citywide safe toilet access | LIC safe access rate / city safe access rate |
| `SF-1a` | Population with safe individual toilets | Safe private-toilet population / total population |
| `SF-1b` | On-site sanitation desludged | OSS emptied in year / total OSS built up to year |
| `SF-1c` | Collected FS disposed at treatment/designated site | Sludge received at plant / sludge emptied from containments |
| `SF-1d` | FS treatment capacity vs FS generated from NSS | Annual FSTP/co-treatment capacity / estimated annual FS generated |
| `SF-1e` | FS treatment capacity vs FS collected | Annual FSTP/co-treatment capacity / sludge collected |
| `SF-1f` | Wastewater treatment capacity vs wastewater/greywater generated | Annual WWTP capacity / estimated annual WW + greywater |
| `SF-1g` | Treatment tests meeting standards | Passing treatment tests / total treatment tests |
| `SF-2a` | LIC population with safe individual toilets | LIC safe private-toilet population / LIC population |
| `SF-2b` | LIC NSS/IHHLs desludged | LIC OSS emptied in year / LIC OSS built up to year |
| `SF-2c` | LIC-collected FS disposed at treatment/designated site | LIC sludge received at plant / LIC sludge emptied |
| `SF-3` | Dependent population with safe shared CT/PT access | Population using safe CT / population using CT |
| `SF-3b` | CTs with universal design | Active CTs with universal design / active CTs |
| `SF-3c` | Women among CT users | Female population using CT / total population using CT |
| `SF-3e` | Average distance to CT | Average building-to-CT distance in meters |
| `SF-4a` | PTs with safe FS/WW transport/disposal | Safe active PT buildings / active PT buildings |
| `SF-4b` | PTs with universal design | Active PTs with universal design / active PTs |
| `SF-4d` | Women among PT users | Female PT visits / total PT visits |
| `SF-5` | Educational institutions with safe FS/WW management | Safe education buildings / all education buildings |
| `SF-6` | Healthcare facilities with safe FS/WW management | Safe healthcare buildings / all healthcare buildings |
| `SF-7` | Mechanical/semi-mechanical desludging | Emptyings / emptyings |
| `SF-9` | Fecal coliform compliance | Negative water samples / total water samples |

## Shared Safe-Sanitation Helper

Many formulas depend on:

```sql
execute_select_build_sanisys_nd_criterias()
```

This helper combines:

- building information;
- sanitation system;
- toilet links;
- containment links;
- sewer/drain connection;
- latest emptying;
- LIC information;
- population served;
- private-toilet population;
- safe/unsafe sanitation classification.

Core source tables:

- `building_info.buildings`
- `building_info.build_contains`
- `building_info.sanitation_systems`
- `fsm.containments`
- `fsm.containment_types`
- `fsm.toilets`
- `fsm.build_toilets`
- `fsm.applications`
- `fsm.emptyings`
- `utility_info.sewers`
- `utility_info.drains`

The helper returns a derived field:

```text
safely_managed_sanitation_system = yes/no
```

This safe/unsafe field is central to `EQ-1`, `SF-1a`, `SF-2a`, `SF-3`, `SF-4a`, `SF-5`, and `SF-6`.

## `EQ-1`: Equity Ratio

DB function:

```sql
update_data_into_cwis_table_eq_1_newsan(_year)
```

Meaning:

- Compares safe individual toilet access in LIC areas against safe individual toilet access citywide.

Formula:

```text
(LIC population with safe private toilets / total LIC population)
/
(city population with safe private toilets / total city population)
```

Numerator side:

- LIC population with access to safe private toilets.

Denominator side:

- citywide population with access to safe private toilets.

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Filters:

- LIC rows use `lic_id IS NOT NULL`.
- Safe rows use `safely_managed_sanitation_system = 'yes'`.
- All rows use `construction_year <= selected year`.

Output:

- ratio rounded to 3 decimals.
- Not multiplied by 100.

Caveat:

- If division cannot be completed, the function uses `COALESCE(..., 0)`, so output can become `0` rather than `NaN`.

## `SF-1a`: Population With Safe Individual Toilets

DB function:

```sql
update_data_into_cwis_table_sf_1a_newsan(_year)
```

Meaning:

- Measures the percentage of total population with access to safe, private, individual toilets.

Formula:

```text
population_with_private_toilet where sanitation is safe
/
total population served
```

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Numerator:

- `sum(population_with_private_toilet)`
- where `safely_managed_sanitation_system = 'yes'`
- and `construction_year <= selected year`

Denominator:

- `sum(population_served)`
- where `construction_year <= selected year`

Output:

- percent rounded to 0 decimals.

## `SF-1b`: On-Site Sanitation That Has Been Desludged

DB function:

```sql
update_data_into_cwis_table_sf_1b_newsan(_year)
```

Meaning:

- Measures the percentage of onsite sanitation containments emptied/desludged in the selected year.

Formula:

```text
containments with latest emptying in selected year
/
distinct containments built up to selected year
```

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Numerator:

- `count(containment_id)`
- where `latest_emptying_status IS TRUE`
- and `year(latest_emptied_date) = selected year`

Denominator:

- `count(distinct containment_id)`
- where `construction_year <= selected year`

Output:

- percent rounded to 0 decimals.

Caveat:

- Uses latest emptying fields from the helper. It is sensitive to how the latest application/emptying is ranked in `execute_select_build_sanisys_nd_criterias_part3()`.

## `SF-1c`: Collected FS Disposed At Treatment Plant / Designated Site

DB function:

```sql
update_data_into_cwis_table_sf_1c_newsan(_year)
```

Meaning:

- Measures how much emptied sludge actually reached disposal/treatment.

Formula:

```text
sludge volume collected/received at FSTP in selected year
/
sludge volume emptied at containment in selected year
```

Sources:

- `fsm.sludge_collections`
- `fsm.emptyings`

Numerator:

- `sum(fsm.sludge_collections.volume_of_sludge)`
- where `year(date) = selected year`
- and not deleted

Denominator:

- `sum(fsm.emptyings.volume_of_sludge)`
- where `year(emptied_date) = selected year`
- and not deleted

Output:

- percent rounded to 0 decimals.

## `SF-1d`: FS Treatment Capacity As Percentage Of Total FS Generated From NSS

DB function:

```sql
update_data_into_cwis_table_sf_1d_newsan(_year, fs_generation_from_containment_not_connected_to_sewer_lpcd, fs_generation_from_permeable_or_unlined_pit_lpcd)
```

Meaning:

- Compares available annual FSTP/co-treatment capacity against estimated annual FS generated from non-sewered sanitation systems.

Formula:

```text
annual FSTP/co-treatment capacity
/
estimated annual FS generated from eligible non-sewered systems
```

Sources:

- `fsm.treatment_plants`
- `execute_select_build_sanisys_nd_criterias()`
- `public.site_settings`

Numerator:

```text
sum(capacity_per_day for operational FSTP/co-treatment plants) * 365
```

Treatment plant filters:

- `type IN (3, 4)`
- `status IS TRUE`
- `deleted_at IS NULL`

Denominator:

```text
FS from non-sewer containments
+
FS from permeable/unlined pits
```

Non-sewer containment component:

```text
sum(population_served)
* fs_generation_from_containment_not_connected_to_sewer_lpcd
/ 1,000,000
* 365
```

Permeable/unlined pit component:

```text
sum(population_served)
* fs_generation_from_permeable_or_unlined_pit_lpcd
/ 1,000,000
* 365
```

Settings:

- `fs_generation_from_containment_not_connected_to_sewer_lpcd`
- `fs_generation_from_permeable_or_unlined_pit_lpcd`

Output:

- percent rounded to 0 decimals.

## `SF-1e`: FS Treatment Capacity As Percentage Of Total FS Collected

DB function:

```sql
update_data_into_cwis_table_sf_1e_newsan(_year)
```

Meaning:

- Compares annual treatment capacity with actual sludge collected.

Formula:

```text
annual FSTP/co-treatment capacity
/
total sludge volume collected in selected year
```

Sources:

- `fsm.treatment_plants`
- `fsm.sludge_collections`

Numerator:

- `sum(capacity_per_day) * 365`
- for operational treatment plants with `type IN (3,4)`.

Denominator:

- `sum(sludge_collections.volume_of_sludge)`
- where `year(date) = selected year`
- and not deleted.

Output:

- percent rounded to 0 decimals.

## `SF-1f`: Wastewater Treatment Capacity

DB function:

```sql
update_data_into_cwis_table_sf_1f_newsan(_year, average_water_consumption_lpcd, waste_water_conversion_factor, greywater_conversion_factor_connected_to_sewer, greywater_conversion_factor_not_connected_to_sewer)
```

Meaning:

- Compares annual WWTP capacity against estimated wastewater, greywater, and supernatant generated from sewered and non-sewered systems.

Formula:

```text
annual WWTP capacity
/
estimated annual wastewater + greywater + supernatant generated
```

Sources:

- `fsm.treatment_plants`
- `execute_select_build_sanisys_nd_criterias()`
- `public.site_settings`

Numerator:

- `sum(capacity_per_day) * 365`
- for operational WWTPs with `type IN (1,2)`.

Denominator components:

1. Wastewater from IHHLs directly connected to sewers.
2. Greywater/supernatant from onsite containment connected to sewers.
3. Greywater/supernatant from onsite containment not connected to sewers.
4. Greywater from households relying on pit/open sanitation groups.

Common component pattern:

```text
sum(population_served)
* average_water_consumption_lpcd
/ 1,000,000
* conversion_factor / 100
* 365
```

Settings:

- `average_water_consumption_lpcd`
- `waste_water_conversion_factor`
- `greywater_conversion_factor_connected_to_sewer`
- `greywater_conversion_factor_not_connected_to_sewer`

Output:

- percent rounded to 0 decimals.

## `SF-1g`: Treatment Effectiveness Against Standards

DB function:

```sql
update_data_into_cwis_table_sf_1g_newsan(_year, bod_standard, tss_standard, ecoli_standard)
```

Meaning:

- Measures the percentage of treatment plant tests that meet prescribed standards.

Formula:

```text
treatment plant samples passing BOD, TSS, and E. coli limits
/
total treatment plant samples
```

Sources:

- `fsm.treatmentplant_tests`
- `public.treatment_plant_performance_efficiency_test_settings`

Numerator:

- count of tests where:
  - `bod <= bod_standard`
  - `tss <= tss_standard`
  - `ecoli <= ecoli_standard`
  - test year is selected year

Denominator:

- count of all treatment plant tests in selected year.

Output:

- percent rounded to 0 decimals.

## `SF-2a`: LIC Population With Safe Individual Toilets

DB function:

```sql
update_data_into_cwis_table_sf_2a_newsan(_year)
```

Meaning:

- Measures safe individual toilet access only for LIC population.

Formula:

```text
LIC population_with_private_toilet where sanitation is safe
/
total LIC population served
```

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Numerator filters:

- `safely_managed_sanitation_system = 'yes'`
- `lic_id IS NOT NULL`
- `construction_year <= selected year`

Denominator filters:

- `lic_id IS NOT NULL`
- `construction_year <= selected year`

Output:

- percent rounded to 0 decimals.

## `SF-2b`: LIC, NSS, IHHLs That Have Been Desludged

DB function:

```sql
update_data_into_cwis_table_sf_2b_newsan(_year)
```

Meaning:

- Measures LIC onsite sanitation containments emptied/desludged in the selected year.

Formula:

```text
distinct LIC containment ids emptied in selected year
/
distinct LIC containment ids built up to selected year
```

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Numerator filters:

- `latest_emptying_status IS TRUE`
- `year(latest_emptied_date) = selected year`
- `sanitation_system_id IN (3,4)`
- `lic_id IS NOT NULL`

Denominator filters:

- `construction_year <= selected year`
- `sanitation_system_id IN (3,4)`
- `lic_id IS NOT NULL`

Output:

- percent rounded to 0 decimals.

## `SF-2c`: LIC-Collected FS Disposed At Treatment Plant / Designated Site

DB function:

```sql
update_data_into_cwis_table_sf_2c_newsan(_year)
```

Meaning:

- Measures whether sludge collected from LIC areas reached treatment/disposal.

Formula:

```text
LIC sludge volume received at treatment/disposal
/
LIC sludge volume emptied from containments
```

Sources:

- `fsm.sludge_collections`
- `fsm.emptyings`
- `fsm.applications`
- `fsm.containments`
- `building_info.build_contains`
- `building_info.buildings`

LIC filter:

- `building_info.buildings.lic_id IS NOT NULL`

Year filters:

- numerator uses `sludge_collections.date`.
- denominator uses `emptyings.emptied_date`.

Output:

- percent rounded to 0 decimals.

## `SF-3`: Dependent Population With Access To Safe Shared Facilities

DB function:

```sql
update_data_into_cwis_table_sf_3_newsan(_year)
```

Meaning:

- Measures whether people dependent on community toilets are connected to safely managed community toilet systems.

Formula:

```text
population using safely managed community toilets
/
population using community toilets
```

Sources:

- `execute_select_build_sanisys_nd_criterias()`
- `fsm.build_toilets`
- `fsm.toilets`

Numerator filters:

- `sanitation_system_id = 9`
- linked toilet is an active community toilet
- `safely_managed_sanitation_system = 'yes'`
- `construction_year <= selected year`

Denominator filters:

- `sanitation_system_id = 9`
- `construction_year <= selected year`

Output:

- percent rounded to 0 decimals.

## `SF-3b`: CTs With Universal Design

DB function:

```sql
update_data_into_cwis_table_sf_3b_newsan(_year)
```

Meaning:

- Measures community toilets with universal design facilities.

Formula:

```text
active community toilets with universal design
/
active community toilets
```

Source:

- `fsm.toilets`

Filters:

- `lower(type) = 'community toilet'`
- `status IS TRUE`
- `deleted_at IS NULL`
- numerator adds `separate_facility_with_universal_design = TRUE`

Output:

- percent rounded to 0 decimals.

Caveat:

- `_year` is not used. This is current inventory, not historical year-specific inventory.

## `SF-3c`: Women Among CT Users

DB function:

```sql
update_data_into_cwis_table_sf_3c_newsan(_year)
```

Meaning:

- Estimates the share of women among the population dependent on community toilets.

Formula:

```text
female population using community toilets
/
total population using community toilets
```

Source:

- `building_info.buildings`

Filters:

- `sanitation_system_id = 9`
- `deleted_at IS NULL`
- `construction_year <= selected year`

Output:

- percent rounded to 0 decimals.

Caveat:

- Uses building population (`female_population`, `population_served`), not CT visit records.

## `SF-3e`: Average Distance From House To Closest CT

DB function:

```sql
update_data_into_cwis_table_sf_3e_newsan(_year)
```

Meaning:

- Calculates average physical distance from houses dependent on community toilets to their linked community toilet.

Formula:

```text
average ST_Distance(building geometry, community toilet geometry)
```

Sources:

- `building_info.buildings`
- `fsm.build_toilets`
- `fsm.toilets`

Spatial logic:

- transforms both geometries to EPSG:3857;
- calculates `ST_Distance`;
- averages and rounds the distance.

Filters:

- `building.sanitation_system_id = 9`
- `toilet.type = Community Toilet`
- `building.construction_year <= selected year`
- building and toilet are not deleted.

Output:

- average distance in meters.
- Stores `0` when no distance is available.

## `SF-4a`: PTs Where FS/WW Is Safely Transported Or Disposed

DB function:

```sql
update_data_into_cwis_table_sf_4a_newsan(_year)
```

Meaning:

- Measures public toilet buildings whose sanitation is safely managed.

Formula:

```text
safe active public toilet buildings
/
active public toilet buildings
```

Sources:

- `execute_select_build_sanisys_nd_criterias()`
- `fsm.toilets`

Filters:

- `functional_use_id = 8`
- `use_category_id = 35`
- linked toilet `status IS TRUE`
- `construction_year <= selected year`
- numerator adds `safely_managed_sanitation_system = 'yes'`

Output:

- percent rounded to 0 decimals.

## `SF-4b`: PTs With Universal Design

DB function:

```sql
update_data_into_cwis_table_sf_4b_newsan(_year)
```

Meaning:

- Measures public toilets with universal design facilities.

Formula:

```text
active public toilets with universal design
/
active public toilets
```

Source:

- `fsm.toilets`

Filters:

- `lower(type) = 'public toilet'`
- `status IS TRUE`
- `deleted_at IS NULL`
- numerator adds `separate_facility_with_universal_design = TRUE`

Output:

- percent rounded to 0 decimals.

Caveat:

- `_year` is not used. This is current inventory, not historical year-specific inventory.

## `SF-4d`: Women Among PT Users

DB function:

```sql
update_data_into_cwis_table_sf_4d_newsan(_year)
```

Meaning:

- Measures female usage of public toilets based on CT/PT user logs.

Formula:

```text
sum(no_female_user)
/
sum(no_female_user + no_male_user)
```

Sources:

- `fsm.ctpt_users`
- `fsm.toilets`

Filters:

- `lower(toilets.type) = 'public toilet'`
- `year(ctpt_users.date) = selected year`
- toilet `status IS TRUE`
- toilet not deleted

Output:

- percent rounded to 0 decimals.

## `SF-5`: Educational Institutions With Safe FS/WW Management

DB function:

```sql
update_data_into_cwis_table_sf_5_newsan(_year)
```

Meaning:

- Measures sanitation safety in educational institution buildings.

Formula:

```text
educational institution buildings with safely managed sanitation
/
all educational institution buildings
```

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Filters:

- `functional_use_id = 3`
- `construction_year <= selected year`
- numerator adds `safely_managed_sanitation_system = 'yes'`

Output:

- percent rounded to 0 decimals.

## `SF-6`: Healthcare Facilities With Safe FS/WW Management

DB function:

```sql
update_data_into_cwis_table_sf_6_newsan(_year)
```

Meaning:

- Measures sanitation safety in healthcare facility buildings.

Formula:

```text
health institution buildings with safely managed sanitation
/
all health institution buildings
```

Main source:

- `execute_select_build_sanisys_nd_criterias()`

Filters:

- `functional_use_id = 4`
- `construction_year <= selected year`
- numerator adds `safely_managed_sanitation_system = 'yes'`

Output:

- percent rounded to 0 decimals.

## `SF-7`: Mechanical / Semi-Mechanical Desludging

DB function:

```sql
update_data_into_cwis_table_sf_7_newsan(_year)
```

Meaning:

- Intended to measure percentage of desludging services completed mechanically or semi-mechanically.

Actual formula:

```text
emptyings in selected year
/
emptyings in selected year
```

Source:

- `fsm.emptyings`

Filters:

- `year(emptied_date) = selected year`
- `deleted_at IS NULL`

Output:

- `100` when at least one emptying exists.
- `NaN` when no emptying exists.

Caveat:

- The function assumes every IMIS emptying is mechanical or semi-mechanical. It does not check a vehicle/method field.

## `SF-9`: Fecal Coliform Compliance

DB function:

```sql
update_data_into_cwis_table_sf_9_newsan(_year)
```

Meaning:

- Measures water samples that are compliant for fecal coliform.

Formula:

```text
water samples with negative coliform result
/
all water samples
```

Source:

- `public_health.water_samples`

Numerator filters:

- `lower(water_coliform_test_result) = 'negative'`
- `year(sample_date) = selected year`
- `deleted_at IS NULL`

Denominator filters:

- `year(sample_date) = selected year`
- `deleted_at IS NULL`

Output:

- percent rounded to 0 decimals.

## Formula Risk Notes

| Risk | Affected indicators | Why it matters |
|---|---|---|
| Existing year rows are not recalculated by wrapper | All | `insert_data_into_cwis_table(year)` skips calculation if any rows already exist for that year. |
| Safe classification uses hard-coded IDs | Many | Lookup ID changes can silently affect results. |
| `_year` ignored | `SF-3b`, `SF-4b` | Historical reports may show current toilet inventory. |
| Assumption-based value | `SF-7` | Always 100 if emptying records exist. |
| CT users from population, not logs | `SF-3c` | Differs from `SF-4d`, which uses usage logs. |
| Text `NaN` stored | Most percentage formulas | Exports/NSD/dashboard must handle text values. |
| Construction year required | Many population/building formulas | Null or wrong construction year changes inclusion. |

## QA Checklist

1. Pick one year and list all stored values:

```sql
SELECT indicator_code, label, data_value
FROM cwis.data_cwis
WHERE year = 2025
ORDER BY indicator_code;
```

2. Check invalid formula outputs:

```sql
SELECT year, indicator_code, label, data_value
FROM cwis.data_cwis
WHERE lower(data_value) = 'nan'
ORDER BY year DESC, indicator_code;
```

3. Recalculate an existing year directly:

```sql
SELECT update_data_into_cwis_table_revised_2024(2025);
```

4. Check CWIS constants:

```sql
SELECT name, value
FROM public.site_settings
WHERE category = 'cwis_setting'
ORDER BY id;
```

5. Check safe-sanitation helper output:

```sql
SELECT bin, sanitation_system_id, containment_type_id, lic_id,
       population_served, population_with_private_toilet,
       latest_emptying_status, latest_emptied_date,
       safely_managed_sanitation_system
FROM execute_select_build_sanisys_nd_criterias()
LIMIT 50;
```

