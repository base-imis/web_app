# Base IMIS GitHub Process Simple Guide

Prepared for: Base IMIS V1.3.0 NSD branch cleanup and release preparation

## 1. What We Were Trying To Do

The goal was to prepare a clean branch for the NSD release work.

The desired final branch name is:

```text
v1.3.0-nsd
```

This branch should contain:

- the original V1.3.0 onesys base
- all NSD changes from `v1.4.0-nsd`
- the NSD migration
- the NSD seeder/permission changes
- the NSD routes, controllers, model, and views
- the updated data dictionary

The old branches should not be deleted immediately. They should be archived only after team lead approval.

## 2. Branches Involved

| Branch | Meaning | Current decision |
|---|---|---|
| `v1.3.0-onesys` | Existing V1.3.0 branch | Used as the base |
| `v1.4.0-nsd` | Branch containing real NSD code changes | Changes were brought into `v1.3.0-nsd` |
| `v1.5.0-cwis` | CWIS branch | No unique code difference from `v1.3.0-onesys`; archive later after approval |
| `master` | Latest combined branch | Already contains NSD changes |
| `v1.3.0-nsd` | New prepared branch | Created locally and now contains NSD changes |

## 3. What Was Checked First

We compared the branches before making changes.

The finding was:

```text
v1.4.0-nsd has real NSD changes.
v1.5.0-cwis has no unique code changes.
master already contains the NSD changes.
```

The comparison showed:

```text
v1.3.0-onesys -> v1.4.0-nsd
14 commits ahead
19 files changed
```

And:

```text
v1.3.0-onesys -> v1.5.0-cwis
0 commits ahead
0 commits behind
no file changes
```

## 4. What Was Done Locally

First, the GitHub branch references were refreshed:

```bash
git fetch --all --prune
```

Then a new local branch was created from `v1.3.0-onesys`:

```bash
git checkout -B v1.3.0-nsd upstream/v1.3.0-onesys
```

Then the NSD changes from `v1.4.0-nsd` were brought in:

```bash
git merge --ff-only upstream/v1.4.0-nsd
```

The merge was clean and fast-forwarded without conflict.

## 5. What The Local Branch Contains Now

The local branch:

```text
v1.3.0-nsd
```

now matches:

```text
upstream/v1.4.0-nsd
```

This means the NSD changes are included.

Verification result:

```text
v1.3.0-nsd...upstream/v1.4.0-nsd = 0 0
```

There is no code difference between local `v1.3.0-nsd` and `upstream/v1.4.0-nsd`.

## 6. NSD Files Included

The NSD work includes changes in these areas:

- NSD dashboard controller
- NSD setting controller
- NSD model
- NSD migration
- NSD permission seeders
- NSD API documentation
- data dictionary
- NSD setting screens
- sidebar/menu update
- web routes

Important files include:

```text
app/Http/Controllers/Fsm/NsdDashboardController.php
app/Http/Controllers/Fsm/NsdSettingController.php
app/Models/Fsm/Nsd.php
database/migrations/2025_04_28_123002_nsd_setting.php
database/seeders/RolePermissions/MunicipalitySanitationDepartmentSeeder.php
documentations/data_dictionary/data_dictionary.md
resources/views/fsm/nsd-setting/create.blade.php
resources/views/fsm/nsd-setting/edit.blade.php
resources/views/fsm/nsd-setting/partial-form.blade.php
resources/views/includes/sidebar.blade.php
routes/web.php
```

## 7. Data Dictionary Fix

The NSD data dictionary entry was only present in the table of contents, but the actual section was missing.

So the missing section was added for:

```text
cwis.nsd_setting
```

The added fields are:

```text
id
nsd_username
city
api_post_url
api_login_url
nsd_password
created_at
updated_at
deleted_at
```

This change is currently a local uncommitted change and should be committed before pushing.

## 8. What Was Not Done Yet

These actions were not done:

- Nothing was pushed to GitHub yet.
- No old branches were deleted.
- No old branches were archived.
- `v1.3.0-onesys` on GitHub was not renamed.
- `master` was not changed.
- The user manual repo was not updated yet.
- The one-page information sheet was not added yet.
- The release note was not published yet.

## 9. Before Pushing

Before pushing, commit only the data dictionary file:

```bash
git add documentations/data_dictionary/data_dictionary.md
git commit -m "Update NSD data dictionary section"
```

Do not use:

```bash
git add .
```

because there are other untracked local files that should not be pushed accidentally.

## 10. Push Step

After committing the data dictionary update, push the branch.

If pushing to the user fork:

```bash
git push origin v1.3.0-nsd
```

If pushing directly to the Base IMIS repository and team lead approval is already given:

```bash
git push upstream v1.3.0-nsd
```

Use the upstream push only if the team confirms it is okay.

## 11. Release Note Commands

The release note should tell the deployment team to run the NSD migration and seeder.

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

## 12. Remaining Work After Web App Push

After the web app branch is pushed, the full task still needs:

- update the user manual repository
- add the one-page information sheet in additional resources
- publish the release note with migration and seeder commands
- ask team lead before archiving old branches

## 13. Archive Plan After Approval

Do not delete old branches immediately.

After team lead approval, old branches can be archived by renaming/copying them under:

```text
archive/v1.4.0-nsd
archive/v1.5.0-cwis
```

This keeps the history available while making it clear those branches are no longer active.

## 14. Simple Final Checklist

| Step | Status |
|---|---|
| Create local `v1.3.0-nsd` branch | Done |
| Bring NSD changes from `v1.4.0-nsd` | Done |
| Verify `v1.3.0-nsd` matches `v1.4.0-nsd` | Done |
| Add missing NSD data dictionary content | Done locally |
| Commit data dictionary change | Pending |
| Push `v1.3.0-nsd` | Pending |
| Update user manual | Pending |
| Add one-page information sheet | Pending |
| Publish release note commands | Pending |
| Archive old branches after lead approval | Pending |
