# Addendum: Code Difference Between V1.3.0 Onesys, NSD, and CWIS

This addendum supports the main document `Base_IMIS_V1_3_Change_Mapping_and_Branch_Cleanup_Plan_Full_Branches.docx`.

## Summary Finding

There is a real code difference between `upstream/v1.3.0-onesys` and `upstream/v1.4.0-nsd`.

`v1.4.0-nsd` has:

- 14 commits ahead of `v1.3.0-onesys`
- 19 changed files
- NSD-specific source code, migration, seeder, route, view, sidebar, API documentation, and data dictionary updates

There is no code difference between `upstream/v1.3.0-onesys` and `upstream/v1.5.0-cwis`.

`v1.5.0-cwis` has:

- 0 commits ahead
- 0 commits behind
- no file changes compared with `v1.3.0-onesys`

`master` already contains the `v1.4.0-nsd` changes. There are no file-level changes between `v1.4.0-nsd` and `master`; `master` only has merge commits on top.

## Changed NSD Files

The files changed from V1.3.0 onesys to V1.4.0 NSD are:

- `app/Http/Controllers/Fsm/NsdDashboardController.php`
- `app/Http/Controllers/Fsm/NsdSettingController.php`
- `app/Models/Fsm/Nsd.php`
- `app/Services/Fsm/CwisSettingService.php`
- `config/app.php`
- `database/migrations/2025_04_28_123002_nsd_setting.php`
- `database/seeders/PermissionsSeeder.php`
- `database/seeders/RolePermissions/GuestSeeder.php`
- `database/seeders/RolePermissions/MunicipalityExecutiveSeeder.php`
- `database/seeders/RolePermissions/MunicipalityITAdminSeeder.php`
- `database/seeders/RolePermissions/MunicipalitySanitationDepartmentSeeder.php`
- `documentations/code_documents/19 - NSD Api.md`
- `documentations/data_dictionary/data_dictionary.md`
- `resources/views/cwis/cwis-dashboard/chart-layout/cwis-dash-layout.blade.php`
- `resources/views/fsm/nsd-setting/create.blade.php`
- `resources/views/fsm/nsd-setting/edit.blade.php`
- `resources/views/fsm/nsd-setting/partial-form.blade.php`
- `resources/views/includes/sidebar.blade.php`
- `routes/web.php`

## Practical Decision

If the team wants V1.3.0 onesys to become the NSD version, the final branch should include the changes from `v1.4.0-nsd` or should be based on current `master`, because `master` already includes NSD.

`v1.5.0-cwis` is a lower-risk cleanup candidate because it has no unique code changes compared with `v1.3.0-onesys`.

Do not delete `v1.4.0-nsd` until the NSD changes listed above are confirmed inside the final V1.3.0 branch or release tag.

## Release Note Commands

Plain PHP/artisan commands:

```bash
php artisan migrate --path=database/migrations/2025_04_28_123002_nsd_setting.php
php artisan db:seed --class="Database\\Seeders\\RolePermissions\\MunicipalitySanitationDepartmentSeeder"
```

Docker Compose commands:

```bash
docker compose run --rm artisan migrate --path=database/migrations/2025_04_28_123002_nsd_setting.php
docker compose run --rm artisan db:seed --class="Database\\Seeders\\RolePermissions\\MunicipalitySanitationDepartmentSeeder"
```
