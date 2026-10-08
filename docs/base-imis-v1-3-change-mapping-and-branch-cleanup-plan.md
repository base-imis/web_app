# Base IMIS V1.3.0 Change Mapping and Branch Cleanup Plan

Prepared for: Base IMIS GitHub repository cleanup
Working goal: Make the V1.3.0 branch the maintained branch for the consolidated NSD integration work, then remove old integration branches only after verification.

## 1. Purpose

This document maps what needs to be checked across the Base IMIS repositories before making V1.3.0 the final maintained branch. The work should confirm that all major NSD integration changes are available in the right repositories and that old branches such as `v1.4.0-nsd` and `v1.5.0-cwis` can be safely removed.

The expected final package should include:

- User manual updates
- Source code changes
- Database queries or migration scripts
- Additional one-page information sheet
- Any supporting deployment or handover notes

## 2. Current Branch Understanding

The current local check showed that `master`, `origin/master`, and `upstream/master` point to the same latest commit. The older branches are already merged into `master`, which means they do not contain unique commits ahead of `master` based on the latest local comparison.

Full branch notes:

| Branch                     | Repository location                 | Last known commit                                                     | Description / likely purpose                                                                                                                                                                                              | Current status compared with`master`                                                  | Recommended action                                                                                                                                                                       |
| -------------------------- | ----------------------------------- | --------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `master`                 | Local,`origin`, and `upstream`  | `017ba62` - Merge pull request #132 from `base-imis/v1.4.0-nsd`   | Main combined branch. This is the current default branch and should be treated as the latest source-code truth unless the team decides otherwise.                                                                         | Same as`origin/master` and `upstream/master`; 0 commits ahead and 0 commits behind. | Keep as the main reference branch. Use this as the baseline when preparing the final V1.3.0 package.                                                                                     |
| `origin/v1.1.0-lang`     | User fork /`origin`               | `a6c39d4` - Merge pull request #47 from `suprit1234/v1.1.0-lang`  | Older language/localization branch in the fork. It likely contains earlier translation or language-related work.                                                                                                          | Already merged into`master`; 0 commits ahead and 249 commits behind.                  | Keep only if the fork needs historical branch references. Otherwise it can be removed after confirming no separate documentation points to it.                                           |
| `origin/v2-dev`          | User fork /`origin`               | `9fc6b7d` - release v1.0.0                                          | Old development/release branch in the fork. It appears to point to an older V1.0.0 release state.                                                                                                                         | Already merged into`master`; 0 commits ahead and 397 commits behind.                  | Candidate for cleanup in the fork after confirming no active deployment uses it.                                                                                                         |
| `origin/v2.2.0-tools`    | User fork /`origin`               | `9fc6b7d` - release v1.0.0                                          | Old tools branch in the fork. It points to the same commit as`origin/v2-dev`, so it is likely duplicated or stale.                                                                                                      | Already merged into`master`; 0 commits ahead and 397 commits behind.                  | Candidate for cleanup in the fork. Check if any team documentation still mentions it before deletion.                                                                                    |
| `upstream/master`        | Base IMIS source repo /`upstream` | `017ba62` - Merge pull request #132 from `base-imis/v1.4.0-nsd`   | Official upstream main branch. This is aligned with local`master` and fork `origin/master`.                                                                                                                           | Same as`master`; 0 commits ahead and 0 commits behind.                                | Keep. This should remain the upstream reference branch.                                                                                                                                  |
| `upstream/v1.0.1-fixes`  | Base IMIS source repo /`upstream` | `611ab45` - Merge pull request #66 from `kreesa/v1.0.1-fixes`     | Older patch/fix branch for the V1.0.1 line. It likely contains fixes that were later merged forward.                                                                                                                      | Already merged into`master`; 0 commits ahead and 382 commits behind.                  | Historical branch. Can be archived or deleted only if the organization no longer preserves release branches.                                                                             |
| `upstream/v1.1.0-lang`   | Base IMIS source repo /`upstream` | `70aedf7` - Merge pull request #101 from `suprit1234/v1.1.0-lang` | Official upstream language/localization branch. It likely contains translation/language work that was merged into later releases.                                                                                         | Already merged into`master`; 0 commits ahead and 125 commits behind.                  | Keep for release history or remove after confirming language work is fully present in`master` and final V1.3.0 documentation.                                                          |
| `upstream/v1.1.1-fixes`  | Base IMIS source repo /`upstream` | `65da5d6` - Merge pull request #122 from `Pri446/v1.1.1-fixes`    | Official upstream patch/fix branch after V1.1.0. It likely contains bug fixes that were merged forward.                                                                                                                   | Already merged into`master`; 0 commits ahead and 103 commits behind.                  | Historical branch. Safe to leave; delete only after release-history policy is agreed.                                                                                                    |
| `upstream/v1.2.0-tools`  | Base IMIS source repo /`upstream` | `731eeed` - Merge pull request #127 from `Pri446/v1.2.0-tools`    | Official upstream tools-related branch for V1.2.0. It likely contains tool/module updates before the V1.3.0 line.                                                                                                         | Already merged into`master`; 0 commits ahead and 25 commits behind.                   | Keep as history or archive after verifying its tool changes are included in final V1.3.0.                                                                                                |
| `upstream/v1.3.0-onesys` | Base IMIS source repo /`upstream` | `47717ba` - Merge pull request #128 from `base-imis/v1.2.0-tools` | Existing V1.3.0-related branch. This is the closest existing branch name to the desired final V1.3.0 release line.                                                                                                        | Already merged into`master`; 0 commits ahead and 16 commits behind.                   | Use this as the target branch only if the team confirms`v1.3.0-onesys` is the official V1.3.0 branch. Otherwise create a clean `v1.3.0` branch/tag from the approved final commit.   |
| `upstream/v1.4.0-nsd`    | Base IMIS source repo /`upstream` | `d3b21b9` - Sidebar fix                                             | NSD integration branch. This is the branch specifically carrying NSD integration work before it was merged into`master`.                                                                                                | Already merged into`master`; 0 commits ahead and 2 commits behind.                    | Do not delete immediately. First verify all NSD source code, DB queries, user manual updates, deployment notes, and one-page handover material are included in the final V1.3.0 package. |
| `upstream/v1.5.0-cwis`   | Base IMIS source repo /`upstream` | `47717ba` - Merge pull request #128 from `base-imis/v1.2.0-tools` | CWIS-named branch. Based on the current local comparison, it points to the same commit as`upstream/v1.3.0-onesys`, so it may be a placeholder, stale branch, or branch created before separate CWIS changes were added. | Already merged into`master`; 0 commits ahead and 16 commits behind.                   | Verify whether there are CWIS-specific documents, DB scripts, or release notes outside Git. If there is no separate CWIS material, this is a strong cleanup candidate.                   |

Do not delete any branch until the repository-by-repository checklist below is complete.

## 3. Target Repository Map

The Base IMIS GitHub organization currently has separate repositories for code, manuals, deployment notes, and supporting resources. Each repository should be checked because not all release work belongs inside `web_app`.

| Repository                   | Main content                                               | What to confirm for V1.3.0                                                                                   |
| ---------------------------- | ---------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `web_app`                  | Laravel web application source code                        | NSD source code changes, routes, controllers, services, views, configs, migrations, seeders, SQL/query files |
| `user_manual`              | Functional user guides                                     | Updated user-facing steps for NSD features and changed workflows                                             |
| `deployment_documentation` | Deployment, database, Docker, setup, and environment notes | DB setup, query execution steps, migration order, deployment instructions                                    |
| `additional_resources`     | Supporting resources and information sheets                | One-pager, handover sheet, diagrams, reference materials                                                     |
| `mobile_app`               | Mobile application code and docs                           | Confirm whether NSD integration affected mobile workflows                                                    |
| `.github`                  | GitHub organization settings/workflows/templates           | Confirm no branch policy, workflow, or template depends on removed branch names                              |

## 4. Repository-by-Repository Change Mapping

### 4.1 `web_app`

Purpose: Confirm all NSD source code changes are present in the final V1.3.0 branch.

Recommended checks:

- Compare `v1.4.0-nsd` against `master`.
- Compare `v1.5.0-cwis` against `master`.
- Identify files changed by NSD integration.
- Confirm each changed file exists in the final V1.3.0 branch.
- Confirm any migrations, seeders, raw SQL, or DB config files are included.
- Confirm no environment-specific credentials or local-only settings were committed.

Suggested evidence to collect:

| Area                 | Evidence needed                              | Status  | Notes |
| -------------------- | -------------------------------------------- | ------- | ----- |
| Routes/API           | Changed routes or endpoints listed           | Pending |       |
| Controllers/services | Main NSD business logic files listed         | Pending |       |
| Views/assets         | Changed UI pages and JS/CSS listed           | Pending |       |
| Config               | Config changes checked                       | Pending |       |
| Database             | Migrations, seeders, SQL/query files checked | Pending |       |
| Tests/manual QA      | Main workflows verified                      | Pending |       |

Useful Git commands:

```bash
git fetch --all --prune
git diff --name-status master..upstream/v1.4.0-nsd
git diff --name-status master..upstream/v1.5.0-cwis
git log --oneline --decorate --graph --all --max-count=40
```

If the diff from `master` to an old branch is empty or shows the old branch is behind, then the old branch has no unique source code that needs merging.

### 4.2 `user_manual`

Purpose: Confirm the manual explains the final V1.3.0 behavior after NSD integration.

Recommended checks:

- Identify all NSD workflows added or changed.
- Confirm each workflow has a user-facing manual section.
- Confirm screenshots match the current UI.
- Confirm terminology is consistent with the final V1.3.0 branch.
- Confirm old branch names are not used as release names inside the manual unless intentionally documented.

Suggested evidence to collect:

| Manual area       | Check                 | Status  | Notes |
| ----------------- | --------------------- | ------- | ----- |
| New NSD features  | Steps documented      | Pending |       |
| Updated workflows | Old steps replaced    | Pending |       |
| Screenshots       | Match current UI      | Pending |       |
| Roles/permissions | User access explained | Pending |       |
| Known limitations | Included if needed    | Pending |       |

### 4.3 `deployment_documentation`

Purpose: Confirm the system can be deployed from the final V1.3.0 package without needing old branch knowledge.

Recommended checks:

- Confirm deployment instructions point to the correct branch.
- Confirm database setup is complete.
- Confirm any DB queries, migration commands, or stored procedure notes are included.
- Confirm Docker, Apache, PHP, Laravel, scheduler, queue, GeoServer, or environment notes are current.
- Confirm rollback or backup notes exist for DB-heavy changes.

Suggested evidence to collect:

| Deployment area   | Check                                            | Status  | Notes |
| ----------------- | ------------------------------------------------ | ------- | ----- |
| Branch checkout   | Uses V1.3.0 target branch                        | Pending |       |
| Environment setup | `.env` requirements documented without secrets | Pending |       |
| DB setup          | Queries/migrations listed in order               | Pending |       |
| Services          | Queue, scheduler, map/GeoServer notes checked    | Pending |       |
| Release steps     | Clear deployment sequence                        | Pending |       |
| Rollback          | Backup and rollback note included                | Pending |       |

### 4.4 `additional_resources`

Purpose: Confirm the one-page information sheet and supporting documents are available outside the source code repo.

Recommended checks:

- Create or update one-page NSD integration summary.
- Include final branch name, release purpose, major modules, and handover notes.
- Add links to user manual, deployment documentation, and source branch.
- Confirm no sensitive data is included.

Suggested one-pager sections:

| Section             | Content                                      |
| ------------------- | -------------------------------------------- |
| Release name        | Base IMIS V1.3.0 with NSD integration        |
| Purpose             | Short reason for the consolidated release    |
| Major changes       | High-level source code and workflow changes  |
| DB impact           | Whether DB queries/migrations are required   |
| User impact         | Main user-facing workflow changes            |
| Deployment note     | Where deployment instructions live           |
| Branch cleanup note | Which old branches are being removed and why |

### 4.5 `mobile_app`

Purpose: Confirm whether NSD changes affected mobile behavior.

Recommended checks:

- Check if API changes in `web_app` affect mobile requests.
- Confirm mobile docs or config do not reference deleted branches.
- Confirm release notes mention whether mobile work is unchanged or included.

Suggested evidence to collect:

| Area              | Check                        | Status  | Notes |
| ----------------- | ---------------------------- | ------- | ----- |
| API compatibility | Mobile endpoints still work  | Pending |       |
| Config            | No stale branch references   | Pending |       |
| Release note      | Mobile impact stated clearly | Pending |       |

### 4.6 `.github`

Purpose: Confirm GitHub settings do not depend on branch names that will be removed.

Recommended checks:

- Check workflows, branch protection notes, issue templates, and PR templates.
- Confirm default branch remains correct.
- Confirm branch cleanup does not break CI or release automation.

Suggested evidence to collect:

| Area           | Check                                       | Status  | Notes |
| -------------- | ------------------------------------------- | ------- | ----- |
| Workflows      | No old branch-only workflow references      | Pending |       |
| Templates      | Release/PR templates still accurate         | Pending |       |
| Default branch | `master` or agreed final branch confirmed | Pending |       |
| Protection     | Required checks reviewed                    | Pending |       |

## 5. Recommended Consolidation Workflow

Use this workflow before making any GitHub branch deletion.

### Step 1: Refresh all repositories

For each repository:

```bash
git fetch --all --prune
git status
git branch -a -vv
```

### Step 2: Confirm the target branch

Decide whether the final V1.3.0 branch should be named:

- `v1.3.0-onesys`, if keeping the existing naming style
- `v1.3.0`, if the organization wants a cleaner final release branch name

The team should choose one official branch name and use it consistently across repositories and documentation.

### Step 3: Compare old integration branches

For `web_app`:

```bash
git diff --name-status master..upstream/v1.4.0-nsd
git diff --name-status master..upstream/v1.5.0-cwis
git log --oneline master..upstream/v1.4.0-nsd
git log --oneline master..upstream/v1.5.0-cwis
```

Expected safe result:

- No commits ahead of `master`
- No files that exist only in the old branch
- No release documents missing from the documentation repositories

### Step 4: Update documentation repositories

Before deleting old branches, update:

- `user_manual`
- `deployment_documentation`
- `additional_resources`

Each repository should mention the final V1.3.0 branch and should not require users to know about `v1.4.0-nsd` or `v1.5.0-cwis`.

### Step 5: Tag the final release

After code and docs are aligned, create a release tag:

```bash
git tag v1.3.0-final
git push origin v1.3.0-final
```

Use the tag as the stable reference for PM, QA, and deployment handover.

### Step 6: Delete old branches after approval

Only after checklist sign-off:

```bash
git push origin --delete v1.4.0-nsd
git push origin --delete v1.5.0-cwis
```

If the branches are in `upstream`, deletion must be done in the upstream repo by someone with permission.

## 6. Branch Deletion Readiness Checklist

Do not delete `v1.4.0-nsd` or `v1.5.0-cwis` until every item below is complete.

| Check                                                | Owner                  | Status  | Notes |
| ---------------------------------------------------- | ---------------------- | ------- | ----- |
| `web_app` source code checked against old branches | Developer lead         | Pending |       |
| DB migrations/queries confirmed                      | Backend/DB owner       | Pending |       |
| User manual updated                                  | Documentation owner    | Pending |       |
| Deployment documentation updated                     | DevOps/developer lead  | Pending |       |
| One-page information sheet added                     | PM/documentation owner | Pending |       |
| Mobile impact checked                                | Mobile/API owner       | Pending |       |
| GitHub workflows/settings checked                    | GitHub repo owner      | Pending |       |
| Final V1.3.0 branch name agreed                      | PM/tech lead           | Pending |       |
| Final release tag created                            | GitHub repo owner      | Pending |       |
| Team sign-off received                               | PM/tech lead           | Pending |       |

## 7. Suggested Final GitHub Structure

Recommended final branch structure:

```text
master
v1.3.0 or v1.3.0-onesys
```

Branches that can be removed after verification:

```text
v1.4.0-nsd
v1.5.0-cwis
```

Old branches should be deleted only after the final V1.3.0 branch, release tag, and documentation repositories are updated.

## 8. Notes for PM and Team Leads

This cleanup is doable and low-risk if handled as a controlled release consolidation. The main risk is not code loss, because the branch comparison shows the old branches are already merged into `master`. The real risk is missing supporting material: user manual updates, DB queries, deployment instructions, or one-page handover information that may exist outside the code branch.

The safest decision is to treat `master` as the current code truth, prepare the official V1.3.0 branch or tag from it, update the documentation repositories, and then remove the old branches only after checklist sign-off.