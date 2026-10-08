# Release Notes: Base IMIS v1.3.0 - NSD Integration

Branch: `v1.3.0-nsd`  
Status: Draft for review  
Audience: PM, Technical Team, QA, and Implementation Team  
Reference commit: `d3b21b9`  
Final PR / release tag: TBD after push and review

## 1. Release Summary

This release adds integration between the Base IMIS CWIS Dashboard and the National Sanitation Dashboard (NSD). It allows authorized users to configure NSD connection settings, check indicator publication status in NSD, and push selected CWIS indicator data from IMIS to NSD.

This is intended as an open-source release branch so the NSD integration can be reviewed, reused, and adapted by other cities or projects after approval.

## 2. What Has Changed for Users

- A new **NSD Integration Setting** screen is available under CWIS IMS.
- Users can configure city, NSD authentication URL, NSD send-data URL, username, and password.
- The CWIS Dashboard includes actions to push CWIS indicator data to NSD and check publication status in NSD.
- Users can see published and draft years returned from NSD.
- Already published years are blocked from being pushed again.
- Users receive clear success or error messages for wrong credentials, wrong city/API URL, already published years, or NSD service issues.

## 3. Technical Change Summary

| Area | Changed / Added | Repository Path / Notes |
|---|---|---|
| NSD settings UI | Added create/edit forms for NSD integration settings | `resources/views/fsm/nsd-setting/` |
| CWIS dashboard UI | Added NSD push and status buttons | `resources/views/cwis/cwis-dashboard/chart-layout/cwis-dash-layout.blade.php` |
| Backend controller | Added NSD token, status check, CWIS data fetch, and push-to-NSD flow | `app/Http/Controllers/Fsm/NsdDashboardController.php` |
| Settings controller | Added NSD setting create/update flow and validation | `app/Http/Controllers/Fsm/NsdSettingController.php` |
| Model | Added NSD model | `app/Models/Fsm/Nsd.php` |
| Routes | Added NSD and NSD settings routes | `routes/web.php` |
| Database | Added NSD settings table | `database/migrations/2025_04_28_123002_nsd_setting.php` |
| Permissions | Added/updated NSD permissions in seeders | `database/seeders/PermissionsSeeder.php`, `database/seeders/RolePermissions/` |
| Data dictionary | Added actual `cwis.nsd_setting` table details | `documentations/data_dictionary/data_dictionary.md` |
| Technical docs | Added NSD API technical note | `documentations/code_documents/19 - NSD Api.md` |

## 4. Files to Copy When Applying NSD to Another Active Branch

If another team member is working on a different current branch and needs the NSD integration, use `v1.3.0-nsd` as the reference branch and copy or cherry-pick the NSD-related changes from there.

Recommended source branch:

```text
v1.3.0-nsd
```

Reference commit currently used for the NSD merge:

```text
d3b21b9
```

Important: update the final commit ID after the data dictionary fix is committed and pushed.

### Main Code Files

| Purpose | Copy / compare from `v1.3.0-nsd` |
|---|---|
| NSD dashboard API flow | `app/Http/Controllers/Fsm/NsdDashboardController.php` |
| NSD settings create/update flow | `app/Http/Controllers/Fsm/NsdSettingController.php` |
| NSD model | `app/Models/Fsm/Nsd.php` |
| CWIS dashboard integration service adjustment | `app/Services/Fsm/CwisSettingService.php` |
| Application provider/config update | `config/app.php` |
| NSD routes and settings routes | `routes/web.php` |

### Database and Permission Files

| Purpose | Copy / compare from `v1.3.0-nsd` |
|---|---|
| NSD settings table migration | `database/migrations/2025_04_28_123002_nsd_setting.php` |
| Permission registration | `database/seeders/PermissionsSeeder.php` |
| Guest permission update | `database/seeders/RolePermissions/GuestSeeder.php` |
| Executive permission update | `database/seeders/RolePermissions/MunicipalityExecutiveSeeder.php` |
| IT admin permission update | `database/seeders/RolePermissions/MunicipalityITAdminSeeder.php` |
| Sanitation department permission update | `database/seeders/RolePermissions/MunicipalitySanitationDepartmentSeeder.php` |

### View and Documentation Files

| Purpose | Copy / compare from `v1.3.0-nsd` |
|---|---|
| NSD setting create screen | `resources/views/fsm/nsd-setting/create.blade.php` |
| NSD setting edit screen | `resources/views/fsm/nsd-setting/edit.blade.php` |
| NSD setting shared form | `resources/views/fsm/nsd-setting/partial-form.blade.php` |
| CWIS dashboard buttons and JavaScript | `resources/views/cwis/cwis-dashboard/chart-layout/cwis-dash-layout.blade.php` |
| Sidebar/menu entry | `resources/views/includes/sidebar.blade.php` |
| NSD API technical note | `documentations/code_documents/19 - NSD Api.md` |
| NSD data dictionary section | `documentations/data_dictionary/data_dictionary.md` |

### Suggested Git Commands for Copying Into Another Branch

If the current working branch needs these files copied from `v1.3.0-nsd`, use a controlled checkout for specific paths:

```bash
git checkout v1.3.0-nsd -- app/Http/Controllers/Fsm/NsdDashboardController.php
git checkout v1.3.0-nsd -- app/Http/Controllers/Fsm/NsdSettingController.php
git checkout v1.3.0-nsd -- app/Models/Fsm/Nsd.php
git checkout v1.3.0-nsd -- database/migrations/2025_04_28_123002_nsd_setting.php
git checkout v1.3.0-nsd -- resources/views/fsm/nsd-setting
git checkout v1.3.0-nsd -- resources/views/cwis/cwis-dashboard/chart-layout/cwis-dash-layout.blade.php
git checkout v1.3.0-nsd -- routes/web.php
```

Use this carefully because checking out full files can overwrite local changes in the active branch. If that branch already has edits in the same files, compare first and merge manually.

## 5. Routes Added

The following routes are available under the FSM route group:

```text
POST /fsm/nsd/authenticate
GET  /fsm/nsd/push-nsd/{year}
GET  /fsm/nsd/cwis-data/{year}
GET  /fsm/nsd/cwis-status
GET  /fsm/nsd-setting
GET  /fsm/nsd-setting/create
POST /fsm/nsd-setting
GET  /fsm/nsd-setting/{id}/edit
PUT  /fsm/nsd-setting/{id}
```

## 6. Configuration Required

NSD configuration is entered from the **NSD Integration Setting** screen and stored in:

```text
cwis.nsd_setting
```

The table stores:

```text
nsd_username
nsd_password
city
api_login_url
api_post_url
```

Real credentials must not be committed to GitHub, screenshots, logs, or documentation.

## 7. Deployment Commands

After pulling/deploying this release, run the NSD migration and permission seeder.

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

## 8. API and Data Flow

1. User configures NSD settings in IMIS.
2. IMIS authenticates with NSD and receives a bearer token.
3. IMIS checks NSD metadata for the configured city.
4. User selects a CWIS indicator year from the dashboard.
5. IMIS blocks already published years and allows draft-year data to be pushed.
6. IMIS maps CWIS indicator data and posts it to NSD.
7. User checks status again to verify publication state in NSD.

## 9. QA and Acceptance Checklist

| Check Area | Expected Result | Status |
|---|---|---|
| Branch check | `v1.3.0-nsd` contains all NSD changes from `v1.4.0-nsd` | Pending |
| Migration | `cwis.nsd_setting` table is created successfully | Pending |
| Seeder | NSD permissions are available for required roles | Pending |
| Settings screen | User can create/update NSD settings | Pending |
| Credential validation | Invalid credentials or auth URL show clear error | Pending |
| City/API validation | Wrong city or send-data URL shows clear error | Pending |
| Status check | Published and draft years are shown correctly | Pending |
| Draft-year push | Draft-year CWIS data pushes successfully | Pending |
| Published-year block | Already published year cannot be pushed again | Pending |
| Security check | No secrets are committed | Pending |
| Documentation check | Release note, user manual/workflow, and one-page information sheet are linked | Pending |

## 10. GitHub / Release Notes

- Current prepared branch: `v1.3.0-nsd`
- Reference commit before final data dictionary commit: `d3b21b9`
- Final commit ID should be updated after the data dictionary fix is committed.
- PR link should be added after the branch is pushed.
- Final release tag should be added after team lead approval.

Commit IDs are useful for technical traceability, but they are not mandatory for end users. Keep the branch, PR link, and final release tag visible. Add the final commit ID only after the branch is pushed and reviewed.

## 11. Known Dependencies and Notes

- NSD API credentials and endpoint URLs must be provided by the NSD/admin team.
- The city value configured in IMIS must match the value expected by NSD.
- The integration depends on CWIS M&E data for the selected year.
- Published NSD years are treated as locked and should not be re-published from IMIS.
- If NSD service is unavailable, IMIS should show a user-friendly error.

## 12. Release Notes Skeleton for Future Updates

Use this structure for future Base IMIS release notes:

```text
Title:
Release Notes: Base IMIS <version> - <feature/integration name>

Metadata:
- Branch
- Status
- Audience
- Reference commit
- PR link
- Release tag

1. Release Summary
- What changed?
- Why is it important?
- Who is affected?

2. User-Facing Changes
- New screens
- New buttons/actions
- Changed workflow
- New messages or restrictions

3. Technical Change Summary
- Controllers
- Models
- Views
- Routes
- Migrations
- Seeders
- Documentation

4. Files to Copy or Compare
- Mention source branch
- List controller/model/view/route/migration/seeder files
- Add safe copy/cherry-pick guidance

5. Routes / APIs Added or Changed
- Method and endpoint
- Purpose

6. Configuration and Deployment
- Required settings
- Migration commands
- Seeder commands
- Security note

7. QA and Acceptance Checklist
- Main workflow tests
- Error scenarios
- Permission/security checks

8. Known Dependencies / Risks
- External services
- Credentials
- Data dependencies

9. Final Sign-off
- PM
- Technical Lead
- QA Lead
```

## 13. Final Sign-off

| Role | Name | Approval | Date |
|---|---|---|---|
| Project Manager |  |  |  |
| Technical Lead |  |  |  |
| QA Lead |  |  |  |
