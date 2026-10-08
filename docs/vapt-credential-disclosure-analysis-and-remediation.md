# VAPT: Credential Disclosure and Deployment-Safe User Seeding

## Implementation status (3 September 2026)

The repository implementation now follows the environment-driven design requested in BASEIMIS-40 and its 2 September 2026 review comment:

- `config/seed_users.php` is the only place that calls `env()` for seed-user values. It defines the required account inventory and maps every value to a named `SEED_*` variable.
- `.env.example` contains the complete variable inventory with empty values only.
- `SeedUserConfigurationValidator` rejects missing fields, invalid usernames or emails, passwords that do not satisfy the application's minimum structural policy, and case-insensitive duplicate usernames or emails. Its exception contains safe variable names only.
- `php artisan seed-users:validate-config` is non-interactive and exits non-zero on invalid configuration without printing supplied values.
- `UsersTableSeeder` invokes the same validator independently before database writes, performs provisioning in a transaction, creates only missing usernames, hashes passwords only on initial creation, and does not reset existing passwords.
- `scripts/deployment/migrate-with-seed-user-check.sh` enforces validation immediately before `php artisan migrate --seed --force` for deployment automation that invokes the repository wrapper.
- Unit and feature tests cover missing, invalid, duplicate, valid, non-interactive, fail-before-write, role assignment, rerun, and password-preservation behavior.
- The shared fixed password was removed from `database/factories/UserFactory.php`; factory passwords are now random per generated user.

The application repository contains no CI/CD definition or Ansible playbook. The external deployment automation must therefore complete the steps in the next section before this change is released.

### Required external CI/CD changes

1. Store every `SEED_*` value listed in `.env.example` as a protected, masked deployment secret. Do not store the values in Git or interpolate them into command arguments.
2. Make the variables available to Laravel before building or refreshing the configuration cache. A cache built without them will retain empty values even if the process environment is later corrected.
3. Invoke `scripts/deployment/migrate-with-seed-user-check.sh`, or run these commands in this exact fail-fast order:

   ```text
   php artisan seed-users:validate-config
   php artisan migrate --seed --force
   ```

4. Preserve the first command's exit status and do not configure the migration step to run after failure.
5. Mask protected variables, disable shell tracing for secret-bearing steps, and ensure failure handlers, retries, artifacts, and debug output never render environment values.
6. Add an approved secret scanner to pull-request and full-history CI checks. Historical credentials must be rotated before separately coordinated history rewriting.

### Required external Ansible changes

Load the complete `SEED_*` inventory from Ansible Vault or an approved secrets manager into one environment mapping. Apply that same mapping to configuration-cache creation (when used), validation, and migration/seeding. The tasks that receive the mapping must use `no_log: true`:

```yaml
- name: Validate deployment seed-user configuration
  ansible.builtin.command: php artisan seed-users:validate-config
  args:
    chdir: "{{ application_path }}"
  environment: "{{ seed_user_environment }}"
  no_log: true

- name: Run migrations and seeders after successful validation
  ansible.builtin.command: php artisan migrate --seed --force
  args:
    chdir: "{{ application_path }}"
  environment: "{{ seed_user_environment }}"
  no_log: true
```

Do not add debug tasks for `seed_user_environment`. Ensure the play stops on validation failure, does not use `ignore_errors`, and does not place secret values in command lines, generated logs, retry files, or committed inventory. Limit filesystem access to any generated environment or config-cache file and remove obsolete secret material according to the deployment platform's retention policy.

## 1. Issue Description/Steps to Recreate Issue

### Issue description

`database/seeders/UsersTableSeeder.php` currently contains fixed usernames, email addresses, and plaintext passwords. Some accounts receive privileged roles. Passing a fixed password into `bcrypt()` does not protect it because the original value remains readable in the repository.

`database/seeders/DatabaseSeeder.php` calls the user seeder during normal `migrate --seed` deployment. Commented-out credentials are exposed in the same way as active code.

The deployment design must continue to support non-interactive automation, but Git or GitHub must not be used to store or manage production credentials.

### Safe steps to recreate the issue

1. Open `database/seeders/UsersTableSeeder.php` using approved repository access.
2. Search for user creation statements and password fields.
3. Confirm that readable password values are passed to a hashing function.
4. Confirm that fixed usernames or email addresses are combined with those passwords.
5. Check `database/seeders/DatabaseSeeder.php` and confirm that the user seeder is called during normal seeding.
6. Review Git history to determine whether earlier commits contain the same values.
7. Do not copy actual credentials into tickets, screenshots, test evidence, logs, or documentation.

## 2. Impacts Created by Existing Issue

### Security impact

- Anyone who can read the repository or its history can obtain the credentials.
- If an exposed account is active, an attacker may authenticate without guessing the password.
- Privileged accounts may allow access to administrative functions and sensitive information.
- Reused passwords may expose staging, production, email, infrastructure, or other applications.
- Re-running the current seeder may recreate known accounts or passwords.

### Business impact

- Unauthorized access to municipal, operational, customer, or personal information.
- Unauthorized creation, modification, deletion, approval, or export of data.
- Service disruption and incident-response costs.
- Audit, contractual, regulatory, and reputational consequences.
- Loss of confidence in the deployment and credential-management process.

### Severity

**Critical.** Reading a public or shared repository requires little effort, and the exposed accounts include privileged roles. The practical impact depends on whether credentials are active or reused, but all exposed values must be treated as compromised.

## 3. Remediation or Fix Approaches

### Approach A: Immediate credential containment

1. Disable accounts that are no longer required.
2. Rotate every exposed password in all environments.
3. Check other systems for password reuse and rotate those credentials.
4. Invalidate active sessions, remember-me sessions, and API tokens.
5. Review authentication and administrative logs for suspicious activity.
6. Preserve relevant evidence if compromise is suspected.

Credential rotation must happen before repository cleanup. Removing a password from Git does not make that password safe again.

### Approach B: Environment-driven production seeding

Production usernames, emails, and passwords must be provided to Laravel through environment variables at deployment time.

Use a clear variable group for every required seeded account. Example names for one account are:

```env
SEED_SUPER_ADMIN_NAME=
SEED_SUPER_ADMIN_USERNAME=
SEED_SUPER_ADMIN_EMAIL=
SEED_SUPER_ADMIN_PASSWORD=
```

Define equivalent variables for every account that must be provisioned automatically. Do not commit real values.

The repository's `.env.example` must contain only required variable names with empty values:

```env
SEED_SUPER_ADMIN_NAME=
SEED_SUPER_ADMIN_USERNAME=
SEED_SUPER_ADMIN_EMAIL=
SEED_SUPER_ADMIN_PASSWORD=
```

Do not include sample production usernames, real email addresses, default passwords, example passwords that may be reused, or encrypted secret values in `.env.example`.

### Approach C: Read environment values through Laravel configuration

Create a configuration file such as `config/seed_users.php`:

```php
<?php

return [
    'accounts' => [
        'super_admin' => [
            'name' => env('SEED_SUPER_ADMIN_NAME'),
            'username' => env('SEED_SUPER_ADMIN_USERNAME'),
            'email' => env('SEED_SUPER_ADMIN_EMAIL'),
            'password' => env('SEED_SUPER_ADMIN_PASSWORD'),
            'role' => 'Super Admin',
        ],

        // Add one configuration entry for each required seeded account.
    ],
];
```

Static role names may remain in source code because they are authorization configuration, not credentials. The production username, email, and password must not be fixed in source code.

Application code should read values with:

```php
config('seed_users.accounts')
```

Do not call `env()` directly throughout the seeder. Central configuration is easier to validate, test, and maintain and remains compatible with Laravel configuration caching.

### Approach D: Add one reusable validation service

Create a service such as:

```text
app/Services/Deployment/SeedUserConfigurationValidator.php
```

The validator should:

1. Load `config('seed_users.accounts')`.
2. Confirm every required account definition exists.
3. Confirm name, username, email, password, and role are present.
4. Validate email syntax.
5. Validate username format and length.
6. Validate the password against the application's minimum password policy.
7. Detect duplicate usernames and email addresses.
8. Throw an exception that identifies only the missing variable or account key.
9. Never include a supplied username, email, or password value in the exception or logs.

The seeder and the CI/CD pre-check must use the same validation service or equivalent shared rules so their decisions remain consistent.

### Approach E: Add a non-interactive CI/CD pre-check

Create a non-interactive Artisan command such as:

```text
php artisan seed-users:validate-config
```

The command should:

- call the reusable validation service;
- exit with code `0` when configuration is valid;
- exit with a non-zero code when a required value is missing or invalid;
- print only safe variable names or account keys;
- never print secret values;
- require no prompt or keyboard input.

The CI/CD pipeline must run the check before migrations and seeders:

```text
1. Provision deployment environment variables
2. Run php artisan seed-users:validate-config
3. Stop deployment when validation fails
4. Run php artisan migrate --seed --force
```

The password check should use the application's approved minimum policy, for example minimum length and required character categories. The policy must be maintained in one reusable place rather than duplicated with different rules in CI and the application.

### Approach F: Provision values securely with Ansible

The Ansible deployment should obtain values from Ansible Vault or an approved external secrets manager and pass them to the Laravel command environment before validation, migration, and seeding.

Conceptual Ansible example:

```yaml
- name: Validate seed user configuration
  ansible.builtin.command: php artisan seed-users:validate-config
  args:
    chdir: "{{ application_path }}"
  environment: "{{ seed_user_environment }}"
  no_log: true

- name: Run database migrations and seeders
  ansible.builtin.command: php artisan migrate --seed --force
  args:
    chdir: "{{ application_path }}"
  environment: "{{ seed_user_environment }}"
  no_log: true
```

Required Ansible behavior:

- Secrets come from Vault or an approved secrets manager, not Git variables committed in the repository.
- `no_log: true` is applied to tasks that handle secret values.
- Secrets are passed through the process environment, not command-line arguments.
- Validation runs before `migrate --seed`.
- A failed validation stops the playbook.
- Deployment output does not render the environment mapping.
- Debug tasks never print the secret variables.
- The same environment values are available to any required `config:cache` step.

The CI/CD and Ansible files are not present in this repository, so those changes must be made and reviewed in the separate deployment-automation repository or platform configuration.

### Approach G: Make the Laravel seeder independently fail closed

The seeder must not rely only on the CI/CD check. It must call the validator again before creating any user:

```php
public function run(
    SeedUserConfigurationValidator $validator
): void {
    $accounts = config('seed_users.accounts', []);

    $validator->validateOrFail($accounts);

    foreach ($accounts as $account) {
        $this->createAccountIfMissing($account);
    }
}
```

If a required value is missing or invalid, the seeder must stop with a non-zero deployment result before creating a partial set of accounts.

The error may identify a missing variable name, such as `SEED_SUPER_ADMIN_PASSWORD`, but it must never include the value.

### Approach H: Keep `migrate --seed` automated and idempotent

The deployment must continue to work without interactive input:

```text
php artisan migrate --seed --force
```

The user seeder should be idempotent:

1. Locate an existing user by an approved stable identifier.
2. Create the user only when missing.
3. Assign or synchronize the required role safely.
4. Do not reset an existing user's password on every deployment.
5. Use a separate approved credential-rotation workflow for existing accounts.
6. Treat changes to production usernames or emails as controlled configuration changes to avoid duplicate privileged users.

Example creation pattern:

```php
$user = User::firstOrCreate(
    ['username' => $account['username']],
    [
        'name' => $account['name'],
        'email' => $account['email'],
        'password' => Hash::make($account['password']),
    ]
);

$user->syncRoles([$account['role']]);
```

The password is used only when the account is first created. Re-running the deployment must not replace a password that a user or administrator changed later.

### Approach I: Prevent secrets in logs and deployment output

Apply all of these protections:

- Never print environment values in CI/CD output.
- Disable shell command tracing around secret-handling steps.
- Mark CI/CD variables as masked and protected where supported.
- Use Ansible `no_log: true` for tasks handling secrets.
- Do not pass secrets as Artisan command arguments.
- Do not include configuration arrays in exceptions or debug output.
- Do not call `dd()`, `dump()`, `var_dump()`, or broad context logging on the seeder configuration.
- Configure application errors so production responses do not expose environment or configuration data.
- Review artifacts, cached configuration, support bundles, and failed-job payloads for accidental disclosure.

### Approach J: Clean repository history and prevent recurrence

1. Rotate credentials first.
2. Coordinate history cleanup using an approved backup and change process.
3. Review branches, tags, forks, releases, CI artifacts, and cached copies.
4. Require affected developers to re-clone or safely reset after history rewriting.
5. Add Gitleaks, TruffleHog, or equivalent scanning to pre-commit and CI.
6. Configure exceptions narrowly and review them; do not create broad allowlists.

## 4. Impact of Remediation to Existing Source Code

### Application repository changes

- Rewrite `database/seeders/UsersTableSeeder.php` to use configuration and validation.
- Keep `database/seeders/DatabaseSeeder.php` compatible with automated seeding.
- Add `config/seed_users.php`.
- Add `app/Services/Deployment/SeedUserConfigurationValidator.php` or an equivalent service.
- Add a non-interactive Artisan validation command.
- Add only empty required keys to `.env.example`.
- Add unit, integration, and deployment-focused tests.
- Add secret-scanning configuration where repository CI configuration is available.

### External deployment changes

- CI/CD must obtain protected variables and run the pre-check.
- Ansible must provision the environment safely before Laravel commands.
- Ansible tasks handling secrets must use `no_log: true`.
- Production must use an approved secret store or encrypted automation inventory.
- Deployment documentation must describe required variable names without values.

### Expected behavior changes

- `migrate --seed` remains non-interactive.
- Deployment fails before migration/seeding when required variables are missing or invalid.
- The Laravel seeder independently fails if CI/CD validation is skipped.
- Fresh environments create required accounts using deployment-provided values.
- Existing accounts are not assigned the seed password again during every deployment.
- Operators must provision valid variables before running deployments.

### Regression and operational risks

- Deployment will fail if Ansible does not pass every required variable.
- `config:cache` may use missing or stale values if built before environment provisioning.
- Incorrect stable identifiers may create duplicate privileged accounts.
- Updating passwords through an idempotent seeder may unintentionally reset user passwords; avoid this behavior.
- Strict password validation may reject existing deployment values and require planned rotation.
- Masking and `no_log` reduce troubleshooting details, so errors must safely identify variable names without values.
- Tests or local environments that relied on fixed credentials must supply test-specific environment values.

## 5. Required Testing (Unit, Integration, Browser, Manual, etc.)

### Unit tests

- Validator accepts a complete valid account configuration.
- Validator rejects each missing required variable.
- Validator rejects invalid email and username formats.
- Validator rejects passwords below the application's minimum policy.
- Validator rejects duplicate usernames and email addresses.
- Error messages contain variable names or account keys but never values.
- Seeder hashes the password before storage.
- Seeder does not reset the password of an existing account.

### Integration tests

- `php artisan seed-users:validate-config` exits `0` with valid environment values.
- The command exits non-zero when each required value is missing.
- `php artisan migrate --seed --force` succeeds non-interactively with valid values.
- Direct seeder execution fails independently when configuration is incomplete.
- Failure occurs before any partial user set is committed.
- Re-running the seeder does not create duplicate users.
- Re-running the seeder does not change an existing password.
- Required roles are assigned correctly.
- Laravel configuration caching works when Ansible provisions variables first.

### CI/CD tests

- The pre-check runs before migration and seeding.
- Missing variables stop deployment.
- Invalid password policy stops deployment.
- Valid variables allow deployment to continue.
- Pipeline logs do not contain secret values.
- Masked variables remain hidden on failure and retry paths.
- Secret scanning blocks a controlled dummy-secret fixture without exposing it.

### Ansible tests

- Values are loaded from Vault or the approved secret manager.
- Validation receives all required variables.
- Migration and seeding receive the same variables.
- A validation failure stops the playbook before database changes.
- `no_log: true` prevents values from appearing in normal and verbose output.
- No debug, callback, retry, or failed-task output reveals secrets.

### Browser and authentication tests

- New deployment-provisioned accounts can log in with their supplied credentials.
- Exposed historical credentials no longer work.
- Required roles and permissions are correct.
- Existing users keep their current password after a repeated deployment.
- Logout, session invalidation, and API-token behavior remain correct.

### Manual security tests

- Search active code and comments for fixed credentials.
- Scan reachable Git history with an approved secret scanner.
- Review `.env.example` and confirm all credential values are empty.
- Inspect CI/CD and Ansible output and confirm secrets are absent.
- Confirm application and deployment exceptions never include supplied values.
- Review all affected environments and reused systems.

## 6. Acceptance Criteria

- [ ] Every exposed credential is rotated or disabled.
- [ ] Old sessions and tokens are invalidated.
- [ ] No fixed production username/password combination remains in source code.
- [ ] `.env.example` contains required variable names with empty values only.
- [ ] Production credential values are supplied outside Git/GitHub.
- [ ] CI/CD runs a non-interactive pre-check before migrations and seeding.
- [ ] Missing or invalid variables stop deployment with a non-zero result.
- [ ] Passwords are checked against the approved minimum policy.
- [ ] Ansible provisions values before validation and `migrate --seed`.
- [ ] Ansible secret-handling tasks use `no_log: true`.
- [ ] Laravel reads credentials through application configuration.
- [ ] The seeder independently validates configuration and fails closed.
- [ ] `php artisan migrate --seed --force` remains fully automated.
- [ ] Repeated deployment creates no duplicate accounts and does not reset existing passwords.
- [ ] Secrets are absent from pipeline, Ansible, application, and debug logs.
- [ ] Repository and reachable history pass approved secret scanning.
- [ ] Unit, integration, deployment, authentication, and manual tests pass.
- [ ] Security retesting confirms closure.

## 7. Deployment and Rollback Considerations

### Deployment order

1. Rotate exposed credentials and invalidate sessions/tokens.
2. Store new deployment values in Ansible Vault or the approved secrets manager.
3. Deploy the Laravel configuration, validator, Artisan pre-check, and seeder changes.
4. Provision environment variables through Ansible.
5. Build or refresh Laravel configuration cache only after values are available.
6. Run `php artisan seed-users:validate-config`.
7. Stop immediately if validation fails.
8. Run `php artisan migrate --seed --force`.
9. Run authentication and role verification.
10. Review deployment logs for accidental disclosure.
11. Clean Git history through a separately coordinated process.

### Rollback rules

- Never roll back to source code containing fixed credentials.
- Never restore an exposed password.
- Preserve rotated credentials and session invalidation during rollback.
- If seeding fails, correct environment provisioning or application validation before retrying.
- Use database backups and normal migration rollback procedures where applicable.
- Do not bypass validation merely to complete deployment.

## 8. Required Environment Variable Inventory

For every required seeded account, define and document empty placeholders using a consistent naming pattern:

```text
SEED_<ACCOUNT>_NAME
SEED_<ACCOUNT>_USERNAME
SEED_<ACCOUNT>_EMAIL
SEED_<ACCOUNT>_PASSWORD
```

Role names may remain in Laravel configuration when they are not confidential. If roles differ by deployment, supply and validate role variables as well.

The final inventory must match the accounts that the approved deployment is required to create. Remove obsolete accounts rather than continuing to provision them.

## 9. Required Deliverables

- Updated Laravel configuration and seeder.
- Shared configuration validator.
- Non-interactive Artisan pre-check command.
- Updated `.env.example` with empty values only.
- CI/CD pre-check configuration.
- Ansible Vault or secret-manager mapping.
- Ansible validation and migration/seeding tasks with `no_log: true`.
- Unit, integration, deployment, and authentication tests.
- Secret-scanning configuration and passing scan evidence.
- Deployment and rollback documentation.
- Security retest evidence.

## 10. Relevant Files and References

- `database/seeders/UsersTableSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- proposed `config/seed_users.php`
- proposed `app/Services/Deployment/SeedUserConfigurationValidator.php`
- proposed non-interactive Artisan command
- `.env.example`
- `.gitignore`
- external CI/CD configuration
- external Ansible playbook, inventory, Vault, or secrets-manager configuration
- VAPT report: pages 11-13
