# CWIS Change Impact Guide

## Purpose

This guide explains where to look when changing building, containment, toilet, sewer, drain, lookup, public health, or settings data that feeds CWIS formulas.

Use this together with:

- `docs/cwis-formula-by-formula-study.md`
- `docs/cwis-db-function-formula-deep-study.md`
- `docs/cwis-db-functions-extracted.sql`
- `docs/cwis-safe-sanitation-helper-extracted.sql`
- `docs/cwis-safe-sanitation-helper-parts-extracted.sql`

## Main Dependency Chain

Most CWIS values are not calculated from a single table directly. The common path is:

```text
building_info / fsm / utility_info / public_health source tables
-> execute_select_build_sanisys_nd_criterias_part1/part2/part3()
-> execute_select_build_sanisys_nd_criterias()
-> update_data_into_cwis_table_*_newsan(year)
-> cwis.data_cwis.data_value
-> dashboard / Excel export / NSD push
```

The most important helper is `execute_select_build_sanisys_nd_criterias()`. It creates `safely_managed_sanitation_system = yes/no`, which directly affects `EQ-1`, `SF-1a`, `SF-2a`, `SF-3`, `SF-4a`, `SF-5`, and `SF-6`.

## Quick Impact Table

| If you change... | First place to inspect | Most affected indicators |
|---|---|---|
| `building_info.buildings` | safe-sanitation helper part 1 and main helper | `EQ-1`, `SF-1a`, `SF-1d`, `SF-1f`, `SF-2a`, `SF-3`, `SF-3c`, `SF-3e`, `SF-4a`, `SF-5`, `SF-6` |
| `building_info.build_contains` | helper part 1 and part 2 | `SF-1b`, `SF-1d`, `SF-1f`, `SF-2b`, `SF-2c`, safe/unsafe indicators |
| `fsm.containments` | helper part 1, part 2, safe `CASE` rules | `SF-1b`, `SF-1d`, `SF-1f`, `SF-2b`, `SF-2c`, safe/unsafe indicators |
| `fsm.containment_types` | safe `CASE` rules, `SF-1d`, `SF-1f` | containment safety and FS generation formulas |
| `building_info.sanitation_systems` | safe `CASE` rules and ID-based filters | most formulas using building sanitation |
| `fsm.toilets` | helper part 1/2, CT/PT formulas | `SF-3`, `SF-3b`, `SF-3e`, `SF-4a`, `SF-4b`, `SF-4d` |
| `fsm.build_toilets` | helper part 2, `SF-3`, `SF-3e` | CT dependency and CT distance formulas |
| `utility_info.sewers` | helper part 1/2 safe rules | safe/unsafe indicators, `SF-1f` |
| `utility_info.drains` | helper part 1/2 safe rules | safe/unsafe indicators, `SF-1f` |
| `fsm.applications` | helper part 3 | `SF-1b`, `SF-2b` |
| `fsm.emptyings` | helper part 3, `SF-1c`, `SF-2c`, `SF-7` | emptying/desludging and sludge volume formulas |
| `fsm.sludge_collections` | `SF-1c`, `SF-1e`, `SF-2c` | sludge disposal and collection formulas |
| `fsm.treatment_plants` | `SF-1d`, `SF-1e`, `SF-1f` | treatment capacity formulas |
| `fsm.treatmentplant_tests` | `SF-1g` | treatment compliance |
| `fsm.ctpt_users` | `SF-4d` | public toilet women users |
| `public_health.water_samples` | `SF-9` | fecal coliform compliance |
| `public.site_settings` | master updater, `SF-1d`, `SF-1f` | estimated FS/WW generation |

## If You Change `building_info.buildings`

This is one of the highest-impact CWIS tables.

Important fields:

| Field | Why it matters |
|---|---|
| `bin` | Main join key for toilets, containments, sewers, drains, and CT links. |
| `functional_use_id` | Identifies education, health, CT/PT buildings. |
| `use_category_id` | Distinguishes community toilet and public toilet categories. |
| `construction_year` | Most formulas include only buildings whose construction year is less than or equal to the selected year. |
| `lic_id` | Controls LIC formulas: `EQ-1`, `SF-2a`, `SF-2b`, `SF-2c`. |
| `population_served` | Main population denominator for many formulas. |
| `population_with_private_toilet` | Main numerator for safe private toilet access formulas. |
| `female_population` | Used by `SF-3c`. |
| `sanitation_system_id` | Main sanitation category input for safe/unsafe classification. |
| `sewer_code` | Used to decide whether sewer-connected sanitation is safe. |
| `drain_code` | Used to decide whether drain-connected sanitation is safe. |
| `geom` | Used by `SF-3e` distance calculation. |
| `deleted_at` | Deleted buildings are excluded. |

Affected indicators: `EQ-1`, `SF-1a`, `SF-1b`, `SF-1d`, `SF-1f`, `SF-2a`, `SF-2b`, `SF-2c`, `SF-3`, `SF-3c`, `SF-3e`, `SF-4a`, `SF-5`, `SF-6`.

Useful check:

```sql
SELECT bin, functional_use_id, use_category_id, construction_year,
       lic_id, population_served, population_with_private_toilet,
       sanitation_system_id, sewer_code, drain_code,
       safely_managed_sanitation_system
FROM execute_select_build_sanisys_nd_criterias()
WHERE bin = 'PUT_BIN_HERE';
```

Be careful:

- Null or wrong `construction_year` can exclude the building from year-based formulas.
- Changing `sanitation_system_id` can change safe/unsafe status.
- Changing `lic_id` affects LIC and equity formulas.
- Changing population fields affects both numerator and denominator values.

## If You Change `building_info.build_contains`

This table connects buildings to containments.

Important fields: `bin`, `containment_id`, `deleted_at`.

Affected indicators: `SF-1b`, `SF-1d`, `SF-1f`, `SF-2b`, `SF-2c`, plus safe/unsafe indicators where containment type changes classification.

Useful check:

```sql
SELECT b.bin, bc.containment_id, c.type_id, c.construction_date
FROM building_info.buildings b
LEFT JOIN building_info.build_contains bc ON b.bin = bc.bin AND bc.deleted_at IS NULL
LEFT JOIN fsm.containments c ON c.id = bc.containment_id AND c.deleted_at IS NULL
WHERE b.bin = 'PUT_BIN_HERE';
```

Be careful:

- Soft-deleting a link can make a building appear to have no containment.
- Changing the containment link can change safe/unsafe status and emptying history.

## If You Change `fsm.containments`

Containments affect safe sanitation, FS generation, and desludging.

Important fields:

| Field | Why it matters |
|---|---|
| `id` | Joined from `building_info.build_contains`. |
| `type_id` | Used in hard-coded safety and generation rules. |
| `construction_date` | Exposed through helper and emptying context. |
| `size` | Exposed through helper; may matter for future formulas. |
| `deleted_at` | Deleted containments are ignored. |

Affected indicators: `SF-1b`, `SF-1d`, `SF-1f`, `SF-2b`, `SF-2c`, and helper-safe formulas like `EQ-1`, `SF-1a`, `SF-2a`, `SF-3`, `SF-4a`, `SF-5`, `SF-6`.

Useful check:

```sql
SELECT containment_id, containment_type_id, sanitation_system_id,
       sewer_code, sewer_connected_to_tp,
       drain_code, drain_cover_type, drain_surface_type, drain_connected_to_tp,
       safely_managed_sanitation_system
FROM execute_select_build_sanisys_nd_criterias()
WHERE containment_id = 'PUT_CONTAINMENT_ID_HERE';
```

Be careful:

- `type_id` controls formula behavior, not only display.
- Containment type `9` is treated as permeable/unlined pit in `SF-1d` and `SF-1f`.

## If You Change Lookup IDs

This is very high risk because many formulas use numeric IDs directly.

| Meaning | Hard-coded ID usage |
|---|---|
| Community toilet dependency | `sanitation_system_id = 9` |
| Shared containment | `sanitation_system_id = 11` |
| Public toilet building | `functional_use_id = 8`, `use_category_id = 35` |
| Community toilet building | `functional_use_id = 8`, `use_category_id = 34` |
| Educational institution | `functional_use_id = 3` |
| Health institution | `functional_use_id = 4` |
| WWTP | `treatment_plants.type IN (1,2)` |
| FSTP/co-treatment | `treatment_plants.type IN (3,4)` |

Before changing lookup IDs:

1. Search extracted SQL for the ID.
2. Update PostgreSQL functions if the ID meaning changes.
3. Recalculate affected CWIS years.
4. Compare old/new outputs.

Search example:

```powershell
rg -n "sanitation_system_id = 9|functional_use_id = 8|use_category_id = 35|containment_type_id IN" docs/cwis-*.sql
```

## If You Change `building_info.sanitation_systems`

The formulas care about the numeric `sanitation_system_id` stored on buildings.

| ID | Formula meaning |
|---:|---|
| `1` | Sewer network; safe when connected to treatment plant. |
| `2` | Drain network; safe when connected to treatment plant or closed/lined. |
| `3` | Septic tank/onsite containment group; safety depends on containment type and sewer/drain link. |
| `4` | Pit/holding tank group; safety depends on containment type and sewer/drain link. |
| `5` | Treated as safe directly. |
| `6` | Treated as safe directly. |
| `7`, `8` | Used in `SF-1f` pit/open sanitation greywater estimate. |
| `9` | Community toilet dependency. |
| `11` | Shared containment. |

Affected indicators: most formulas using `execute_select_build_sanisys_nd_criterias()`, especially `EQ-1`, `SF-1a`, `SF-1d`, `SF-1f`, `SF-2a`, `SF-2b`, `SF-3`, `SF-4a`, `SF-5`, `SF-6`.

Useful check:

```sql
SELECT id, sanitation_system
FROM building_info.sanitation_systems
ORDER BY id;
```

## If You Change `fsm.toilets`

Toilets affect CT/PT, universal design, and distance indicators.

Important fields: `id`, `bin`, `type`, `status`, `separate_facility_with_universal_design`, `geom`, `deleted_at`.

Affected indicators: `SF-3`, `SF-3b`, `SF-3e`, `SF-4a`, `SF-4b`, `SF-4d`.

Useful check:

```sql
SELECT id, bin, type, status, separate_facility_with_universal_design, deleted_at
FROM fsm.toilets
WHERE lower(type) IN ('community toilet', 'public toilet')
ORDER BY type, id;
```

Be careful:

- `SF-3b` and `SF-4b` do not use selected year. They calculate current active CT/PT inventory.
- Inconsistent `type` text can exclude toilets from formulas.

## If You Change `fsm.build_toilets`

This table links buildings to CT/PT toilets.

Affected indicators: `SF-3`, `SF-3e`, and helper part 2 community toilet mapping.

Useful check:

```sql
SELECT bt.bin, bt.toilet_id, t.type, t.status
FROM fsm.build_toilets bt
JOIN fsm.toilets t ON t.id = bt.toilet_id
WHERE bt.bin = 'PUT_BIN_HERE'
  AND bt.deleted_at IS NULL;
```

Be careful:

- Removing a link can remove a dependent building from community toilet calculations.
- `SF-3e` distance depends on this link.

## If You Change `utility_info.sewers`

Sewers affect safe sanitation and wastewater calculations.

Important fields: `code`, `treatment_plant_id`, `deleted_at`.

Affected indicators: all safe/unsafe indicators and `SF-1f`.

Be careful:

- If `sewer_code` does not match `utility_info.sewers.code`, the helper treats the building as not connected.
- Removing `treatment_plant_id` can make sewer-connected sanitation unsafe.

## If You Change `utility_info.drains`

Drains affect safe sanitation and greywater/supernatant logic.

Important fields: `code`, `treatment_plant_id`, `cover_type`, `surface_type`, `deleted_at`.

Affected indicators: all safe/unsafe indicators and `SF-1f`.

Be careful:

- Safe drain rules check lowercase text: `closed` and `lined`.
- A drain can qualify either by treatment plant connection or by closed/lined condition, depending on sanitation and containment type.

## If You Change `fsm.applications`

Applications feed latest emptying status.

Important fields: `containment_id`, `application_date`, `emptying_status`, `deleted_at`.

Affected indicators: `SF-1b`, `SF-2b`.

Where to inspect:

- `execute_select_build_sanisys_nd_criterias_part3()`

Be careful:

- Latest status is ranked by `application_date`, not directly by `emptied_date`.

## If You Change `fsm.emptyings`

Emptyings affect emptying count, sludge volume, and latest emptied date.

Important fields: `application_id`, `emptied_date`, `volume_of_sludge`, `deleted_at`.

Affected indicators: `SF-1b`, `SF-1c`, `SF-2b`, `SF-2c`, `SF-7`.

Be careful:

- `SF-7` is based entirely on count of non-deleted emptyings in the year.
- `volume_of_sludge` changes affect `SF-1c` and `SF-2c` ratios.

## If You Change `fsm.sludge_collections`

Sludge collections represent sludge reaching treatment/disposal.

Important fields: `application_id`, `date`, `volume_of_sludge`, `deleted_at`.

Affected indicators: `SF-1c`, `SF-1e`, `SF-2c`.

Be careful:

- If sludge was emptied but not collected/recorded at plant, disposal percentages go down.
- Wrong collection dates move values to the wrong year.

## If You Change `fsm.treatment_plants`

Treatment plant capacity affects treatment capacity indicators.

Important fields: `type`, `capacity_per_day`, `status`, `deleted_at`.

Affected indicators: `SF-1d`, `SF-1e`, `SF-1f`.

| Type | Formula meaning |
|---:|---|
| `1`, `2` | WWTP capacity for `SF-1f`. |
| `3`, `4` | FSTP/co-treatment capacity for `SF-1d` and `SF-1e`. |

Be careful:

- Capacity is annualized as `capacity_per_day * 365`.

## If You Change Treatment Tests Or Standards

Affected indicator: `SF-1g`.

Tables:

- `fsm.treatmentplant_tests`
- `public.treatment_plant_performance_efficiency_test_settings`

Important fields: `date`, `bod`, `tss`, `ecoli`, `bod_standard`, `tss_standard`, `ecoli_standard`.

Be careful:

- A test passes only when all three standards pass.
- The function uses `LIMIT 1` for active standard settings; multiple active rows can make behavior ambiguous.

## If You Change `fsm.ctpt_users`

Affected indicator: `SF-4d`.

Important fields: `toilet_id`, `date`, `no_female_user`, `no_male_user`.

Be careful:

- `SF-4d` uses PT usage logs.
- `SF-3c` does not use this table; it uses building population for CT users.

## If You Change `public_health.water_samples`

Affected indicator: `SF-9`.

Important fields: `sample_date`, `water_coliform_test_result`, `deleted_at`.

Be careful:

- Only text value `negative` counts after lowercase conversion.
- Values like `safe`, `absent`, or `pass` do not count unless the DB function is changed.

## If You Change `public.site_settings`

Settings affect estimated generation formulas.

| Setting | Affected formula |
|---|---|
| `average_water_consumption_lpcd` | `SF-1f` |
| `waste_water_conversion_factor` | `SF-1f` |
| `greywater_conversion_factor_connected_to_sewer` | `SF-1f` |
| `greywater_conversion_factor_not_connected_to_sewer` | `SF-1f` |
| `fs_generation_from_containment_not_connected_to_sewer_lpcd` | `SF-1d` |
| `fs_generation_from_permeable_or_unlined_pit_lpcd` | `SF-1d` |

Be careful:

- Changing settings does not automatically refresh existing `cwis.data_cwis` values.
- Missing setting rows can cause `NaN`.

## Recalculation After Any Source Change

The normal wrapper does not recalculate if year rows already exist:

```sql
SELECT insert_data_into_cwis_table(2025);
```

If rows already exist, use the updater directly:

```sql
SELECT update_data_into_cwis_table_revised_2024(2025);
```

Recommended workflow:

1. Save current values.

```sql
SELECT indicator_code, data_value
FROM cwis.data_cwis
WHERE year = 2025
ORDER BY indicator_code;
```

2. Apply source table or lookup change.

3. Rerun formula updater.

```sql
SELECT update_data_into_cwis_table_revised_2024(2025);
```

4. Compare output.

```sql
SELECT indicator_code, label, data_value
FROM cwis.data_cwis
WHERE year = 2025
ORDER BY indicator_code;
```

5. Check for new `NaN` values.

```sql
SELECT indicator_code, label, data_value
FROM cwis.data_cwis
WHERE year = 2025
  AND lower(data_value) = 'nan'
ORDER BY indicator_code;
```
