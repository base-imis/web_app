# Dependency fixes and QA guide

Prepared for developers and QA on 6 October 2026. The results below describe the remediation verified on 5 October 2026; they are not a fresh security scan.

## What to do first

Ask QA to test the updated application locally first. After it passes, test the same release on staging with the live server's PHP version and configuration. Deploy to live after staging verification and review of the remaining security risks.

Before local QA starts, a developer must build the updated frontend assets:

```bash
npm run production
```

The verification build used a temporary output folder. The browser may still be loading the old JavaScript and CSS until the normal production build is run. QA should then clear the browser cache or perform a hard refresh and use a test database.

A package is a ready-made component the application uses, for example to read Excel files, generate PDFs, display confirmation boxes, or communicate with another system. These changes update those components and adjust their loading instructions where required.

## Results so far

| Measure | Before | After |
| --- | ---: | ---: |
| Composer security advisories | 64 | 11 |
| Composer packages affected by advisories | 17 | 3 |
| npm vulnerable package entries | 65 | 23 |
| npm critical package entries | 5 | 0 |

Composer counts individual advisories. npm also includes packages affected through their dependencies. These figures are not the count of GitHub Dependabot alerts and should not be added together.

The compatible fixes are implemented locally. Not all findings are resolved, and GitHub default-branch alert closure has not been verified.

## Main package changes and affected features

| Package and change | What it does in plain language | Where the application uses it | What QA should check |
| --- | --- | --- | --- |
| Laravel Excel 3.1.62 to 3.1.70 and PhpSpreadsheet 1.29.8 to 1.30.7 | Read and create spreadsheet files. Their reported findings were removed from the local audit. | Language translation CSV imports; water supply, tax payment, and solid waste payment import commands. Check other spreadsheet workflows too. | Import valid files, reject invalid files, and confirm names, dates, numbers, and totals remain correct. Developer assistance may be needed for command-line imports. |
| Guzzle 7.9.2 to 7.15.5 and PSR7 2.7.0 to 2.13.1 | Let the application talk to other systems and read their replies. | NSD authentication, sending CWIS indicators to NSD, checking NSD publishing status, approved WMS map requests, and building-survey WFS requests through Laravel's HTTP client. | Use test integration endpoints. Check successful replies, unavailable services, invalid credentials, and map data loading. Avoid publishing test indicators to a production NSD endpoint. |
| Knp Snappy 1.5.1 to 1.7.3 | Converts report pages into PDFs. | Application monthly reports, individual application reports, KPI report downloads, and chart PDF downloads. The configured PDF helper uses Snappy. | Generate each report. Check values, charts, page breaks, fonts, images, and downloaded filenames. The real PDF executable needs staging verification. |
| Moment 2.29.4 to 2.31.0 | Helps the application understand and display dates. Older copies pulled in by a date-picker component were also replaced. | Emptying dates, employee employment dates, containment construction dates, and shared date-range controls. | Select, clear, save, and reopen dates. Check date formats, ranges, and translated date displays where used. |
| SweetAlert2 11.15.10 to 11.26.25 | Displays confirmation, success, warning, and error boxes. | Buildings, containments, emptying, sludge collection, service providers, users, roles, utility pages, maps, and other screens. | Test both Confirm and Cancel. Cancelling must do nothing; confirming must perform the intended action once. Check displayed messages. |
| Swiper 8.4.4 to 12.2.0 | Provides sliding image or content panels. Its critical finding was removed. | Loaded in the shared frontend bundle. No explicit slider initialization was found in the searched application source. | Check any slider or carousel the team knows is used. Confirm shared pages load without browser errors. |
| Lodash 4.17.21 to 4.18.1 | A shared JavaScript toolbox for handling lists, objects, and other data. | Loaded globally and used by the frontend/build dependency chain. It is not tied to one specific page. | Check general page interactions, filtering, and browser errors. |
| PostCSS 8.5.1 to 8.5.28, Sass 1.56.1 to 1.105.1, resolve-url-loader 3.1.5 to 5.0.0, and Webpack 5.97.1 to 5.104.1 | Build the JavaScript and styling that browsers receive. These are behind-the-scenes tools. | Shared application assets across dashboard pages. Webpack is explicitly pinned to retain compatibility with Laravel Mix 6. | Check menus, tables, icons, fonts, page layout, and mobile sizing. The production-mode build passed, with Sass deprecation warnings. |
| bs-custom-file-input 1.3.4, now declared directly | Makes a file chooser show the selected filename. This is a dependency declaration and import correction, not a version upgrade. | Initialized in the shared dashboard layout. | Select a file, replace it with another file, and confirm the displayed filename changes correctly. |

Some pages load scripts separately from an internet CDN. Updating an npm package does not update those separate scripts. QA should still test those pages.

## Other packages whose reported findings were resolved

These packages mostly work underneath Laravel rather than appearing as individual page features. The local audit no longer reports the original findings for these packages.

| Package | Version change | What it supports | Relevant checks |
| --- | --- | --- | --- |
| Symfony HTTP Foundation | 5.4.48 to 5.4.50 | Incoming requests, responses, redirects, and downloads | Login/logout, protected pages, form submissions, redirects, and downloads |
| Symfony Routing | 5.4.48 to 5.4.53 | Finding the correct page or action for a URL | Page links, form destinations, and protected routes |
| Symfony MIME | 5.4.45 to 5.4.52 | File-type and email-content handling | Upload validation and test email delivery |
| Symfony international-domain support, polyfill-intl-idn | 1.31.0 to 1.43.0 | International domain-name handling in dependent libraries | Relevant email/domain handling and external integrations |
| Symfony Process | 5.4.47 to 5.4.51 | Starting external programs | PDF generation and relevant scheduled/server commands |
| Symfony YAML | 6.4.13 to 6.4.47 | Reading configuration used by dependencies and tools | Application startup and deployment commands |
| League CommonMark | 2.6.1 to 2.10.3 | Turning Markdown text into formatted HTML | No direct application call was found; this package is used through the dependency stack |
| PHPUnit | 9.6.22 to 9.6.38 | Running automated tests | Developer verification; not a customer-facing feature |
| PsySH | 0.12.7 to 0.12.24 | Providing the developer console | Developer console checks; not a customer-facing feature |

Other supporting dependencies changed as Composer resolved compatible versions. The impact PDF and evidence JSON contain the full Composer package change inventory.

## Where the application uses these packages

Paths below are relative to the repository root.

| Feature | Representative source locations |
| --- | --- |
| Language CSV import | `app/Http/Controllers/Language/LanguageController.php`, particularly `import_translates`; language import routes in `routes/web.php` |
| Water supply import | `app/Console/Commands/WaterSupplyDataImport.php` |
| Tax payment import | `app/Console/Commands/TaxPaymentDataImport.php` |
| Solid waste payment import | `app/Console/Commands/SwmPaymentDataImport.php` |
| NSD and CWIS integration | `app/Http/Controllers/Fsm/NsdDashboardController.php`; NSD authentication, push, data, and status routes in `routes/web.php` |
| Approved WMS map requests | `app/Services/Maps/ApprovedWmsService.php` |
| Building-survey WFS requests | `app/Http/Controllers/Api/BuildingSurveyController.php` |
| Application PDF reports | `app/Services/Fsm/ApplicationService.php`; application report and monthly PDF routes in `routes/web.php` |
| KPI PDF reports | `app/Http/Controllers/Fsm/KpiDashboardController.php` |
| Chart PDF download | `app/Http/Controllers/ChartController.php` |
| PDF engine configuration | `config/app.php` maps the PDF helper to Snappy; `config/snappy.php` configures the external PDF executable |
| Emptying date controls | `resources/views/fsm/emptying/create.blade.php` and `edit.blade.php` |
| Employee employment dates | `resources/views/fsm/employee-infos/create.blade.php` and `edit.blade.php` |
| Containment construction date | `resources/views/fsm/containments/index.blade.php` |
| Confirmation boxes | Representative views include `resources/views/building-info/buildings/index.blade.php`, `resources/views/fsm/containments/index.blade.php`, `resources/views/maps/index.blade.php`, `resources/views/users/index.blade.php`, and `resources/views/roles/index.blade.php` |
| Shared file-input initialization | `resources/views/layouts/dashboard.blade.php` |
| Shared frontend loading and styling | `resources/js/app.js`, `resources/js/bootstrap.js`, and `resources/sass/app.scss` |

## What was changed

1. Updated `composer.lock` to install patched PHP dependencies. `composer.json` was not changed.
2. Updated `package.json` and `package-lock.json` for patched frontend and build dependencies.
3. Changed Swiper's JavaScript and CSS imports to the newer supported entry points.
4. Changed the file-input plugin to load from its own package instead of an unavailable AdminLTE plugin path.
5. Pinned Webpack to the patched version compatible with Laravel Mix 6.
6. Required patched Moment and added an npm override so the older date-picker dependency uses that patched version too.
7. Isolated `ApplicationService` in the security-header tests. The real service queries PostgreSQL during construction, which caused failures in the SQLite test environment. A strict mock avoids that constructor query and fails if a guest reaches a business-service method. Application business behavior was not changed by this test adjustment.

Application and test files affected:

- `composer.lock`
- `package.json`
- `package-lock.json`
- `resources/js/app.js`
- `resources/sass/app.scss`
- `tests/Feature/Http/Middleware/PreventClickjackingTest.php`

The middleware test file already existed as an untracked local file before this task. Preserve and review its previous content when preparing a commit. Other unrelated local work remains in the workspace.

## What has already passed

| Verification | Result | Limitation |
| --- | --- | --- |
| Existing unit suite | 66 tests and 272 assertions passed | Does not cover every screen or integration |
| Login and security-header feature tests | 44 tests and 385 assertions passed | Used SQLite in memory and isolated dependencies; does not replace PostgreSQL/PostGIS integration tests |
| Spreadsheet smoke check | XLSX write/read roundtrip passed | Does not cover every real import template or export |
| HTTP smoke check | Guzzle mock response handling passed | Did not send real requests to NSD or other live services |
| PDF smoke check | Basic Dompdf PDF rendering passed | Does not prove Snappy/wkhtmltopdf reports work or resolve Dompdf security findings |
| Date smoke check | Moment strict date parsing passed | Browser date-picker behavior still needs QA |
| Production asset build | Passed using equivalent Mix configuration with temporary output paths | Normal local/staging public assets still need rebuilding |
| Composer validation | Manifest and lockfile valid, with existing version-constraint warnings | Remaining security advisories are still present |

These checks support QA. They do not replace testing actual screens, representative data, and integrations.

## QA checklist

Use test accounts, test files, and a test database. For each item, record Pass or Fail and evidence.

- [ ] Updated frontend assets are built, and the browser cache is cleared.
- [ ] Login and logout work for the expected roles.
- [ ] Guests and users without permission cannot open protected pages or perform restricted actions.
- [ ] Dashboard pages load, filters work, and displayed values remain correct.
- [ ] Map layers load, map interactions work, and errors are understandable when a test service is unavailable.
- [ ] Language CSV import works with a valid file and handles invalid input correctly.
- [ ] Spreadsheet imports retain correct dates, numbers, identifiers, and totals.
- [ ] Relevant exports open correctly and match the selected records.
- [ ] Application monthly PDFs, individual application PDFs, KPI PDFs, and chart PDFs have correct content and layout.
- [ ] Date pickers support selecting, clearing, saving, and reopening values correctly.
- [ ] Confirmation boxes perform the action once when confirmed and do nothing when cancelled.
- [ ] File chooser labels display the selected filename correctly.
- [ ] Menus, tables, fonts, icons, and layouts work at desktop and mobile widths.
- [ ] NSD/CWIS integration works against an approved test endpoint, including failure handling.
- [ ] Relevant email and scheduled-command behavior works in the test environment.
- [ ] The important workflows pass again on staging with the live runtime configuration.

Suggested result record:

| Page or feature | Action and test data | Expected result | Actual result | Pass or Fail | Screenshot or notes |
| --- | --- | --- | --- | --- | --- |
| Example: Emptying create | Select a proposed emptying date, save, then reopen | The same date is displayed | Fill in during QA | Fill in during QA | Attach evidence |

## What is still unresolved

Eleven Composer advisories and 23 npm package findings remain. The remaining npm counts are 12 high, 6 moderate, and 5 low; there are no critical npm entries in the recorded audit.

| Remaining area | Why more work is needed |
| --- | --- |
| Laravel | The application is constrained to Laravel 8. Several patched versions are outside that range. A supported framework migration must also update compatible authentication, mail, helper, and other packages. |
| Dompdf | The installed wrapper requires Dompdf 2, but the remaining findings require Dompdf 3.1.6 or later. The wrapper and engine need migration and report-layout verification. Snappy findings were resolved; Dompdf findings were not. |
| Flysystem | Laravel 8 and the existing backup package constrain the storage library to older versions. A framework/storage/backup migration is needed. |
| Older frontend dependency chains | Laravel Mix, the development server, AdminLTE/Summernote, notifier, browser crypto, and other dependency chains retain findings. These require upgrades, replacement, or a justified exception where a fix is unavailable. |

Do not use an unrestricted production dependency update or force major transitive versions merely to make an audit count zero.

Local QA passing means the tested changes appear to work. It does not mean every security finding is resolved or that production deployment is automatically approved.

## Deployment note

The fixes have not been pushed, merged, or deployed. Your deployment restriction on lockfiles must be addressed before release: agree on either an approved prepared release built from the tested lockfiles or a tested server-side installation procedure that installs the intended versions. Omitting the lockfiles without an alternative does not reproduce the verified dependency set.

Keep the complete previous release available for rollback, including its dependencies and compiled assets. Complete staging testing before applying the reviewed release to live. After changes reach the upstream default branch, recheck the original Dependabot alerts.

## Supporting files

- Impact report: `output/pdf/Dependabot_Remediation_Impact_Report.pdf`
- Detailed package versions and advisory evidence: `docs/security/dependabot-remediation-evidence.json`
- Original manifest backups, audit snapshots, and verification logs: `tmp/dependabot-remediation/`
