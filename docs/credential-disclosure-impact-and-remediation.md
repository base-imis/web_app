# Credential Disclosure: Impact Analysis and Remediation Guide

## 1. Issue summary

The public GitHub repository contains application usernames, email addresses, and plaintext passwords in `database/seeders/UsersTableSeeder.php`.

The passwords are passed to `bcrypt()`, but this does not protect the password written in the source file. Anyone who can view the repository can read the original password before it is hashed.

The affected seeder is also called automatically from `database/seeders/DatabaseSeeder.php`. This means the accounts may be created whenever the normal database seeding process is run.

This issue is rated **Critical** in the VAPT report because no login, special permission, or user interaction is needed to obtain the exposed credentials.

> Important: Do not copy the exposed passwords into tickets, documentation, chat messages, commits, or screenshots.

## 2. Impact analysis

### 2.1 Who can exploit it?

Any internet user who can access the public repository or its history can obtain the exposed credentials. An attacker does not need access to the application server or database.

### 2.2 What could an attacker do?

If any exposed account or password is still active, an attacker may be able to:

- log in as a privileged user;
- view confidential municipal or customer information;
- add, modify, or delete application data;
- perform administrative actions;
- create additional accounts or grant permissions;
- disrupt application functions;
- use the password against other systems where it was reused.

### 2.3 Business impact

Possible business consequences include:

- exposure of personal, operational, or municipal information;
- loss of data accuracy and trust;
- service interruption;
- unauthorized administrative changes;
- incident-response and recovery costs;
- reputational damage;
- contractual, audit, or regulatory consequences.

### 2.4 Technical impact

| Security area | Impact | Simple explanation |
|---|---|---|
| Confidentiality | High | An attacker may read information that should be private. |
| Integrity | High | An attacker may change or delete application data. |
| Availability | High | An attacker may interrupt or damage application functions. |
| Privilege | Critical | Some exposed accounts have powerful administrative roles. |
| Exploitation difficulty | Low | The attacker only needs to view a public file. |

### 2.5 Affected environments

Treat every environment as potentially affected until it is checked:

- production;
- staging or UAT;
- testing;
- development;
- developer machines;
- other applications where the same passwords may have been reused.

Deleting the passwords from the latest source file does not remove them from earlier Git commits, forks, cached pages, build logs, or existing clones.

## 3. Required remediation steps

Follow the steps in this order.

### Step 1: Identify every affected account

1. Review `database/seeders/UsersTableSeeder.php`.
2. List every active and commented-out user entry in a restricted incident record.
3. Check whether each username, email address, or password is used in production, staging, testing, or another system.
4. Do not place the actual passwords in the record.

**Expected result:** The team knows which accounts and systems require action.

### Step 2: Change or disable the exposed accounts immediately

1. Disable accounts that are not required.
2. Give every required account a new, unique password.
3. Do not reuse one password for multiple accounts.
4. Do not reuse any old or exposed password.
5. Check other systems and rotate the password there if it was reused.

**Expected result:** The passwords visible in GitHub no longer work anywhere.

### Step 3: End existing sessions

1. Log out all sessions belonging to affected accounts.
2. Revoke their API tokens, personal access tokens, and remember-me sessions.
3. Rotate related keys if an affected account could access them.

Changing a password may not automatically invalidate every existing session, so this must be checked separately.

**Expected result:** A person who previously logged in with an exposed password cannot remain connected.

### Step 4: Review login and activity logs

1. Search authentication logs for the affected usernames and email addresses.
2. Look for unknown IP addresses, unusual login times, failed login bursts, and unexpected administrative actions.
3. Review changes made by those accounts.
4. Preserve relevant logs if suspicious activity is found.
5. Start the incident-response process if compromise is suspected.

**Expected result:** The team determines whether the credentials may already have been misused.

### Step 5: Remove hardcoded credentials from the user seeder

Remove every plaintext password from `database/seeders/UsersTableSeeder.php`, including passwords inside comments.

Do not make this change:

```php
'password' => Hash::make('another-fixed-password'),
```

It is still unsafe because the plaintext password remains in the repository.

The preferred production design is to stop creating privileged users through a shared database seeder. Create production administrators through an approved administrative screen or a one-time command that accepts the password securely at runtime.

**Expected result:** No real or reusable credential is present in the source code.

### Step 6: Separate demo users from production seed data

Create a separate seeder such as `DemoUsersSeeder.php` for local development and automated tests. Do not use it for production accounts.

Update `DatabaseSeeder.php` so demo users are created only in local and testing environments:

```php
if (app()->environment(['local', 'testing'])) {
    $this->call(DemoUsersSeeder::class);
}
```

Add the same protection inside `DemoUsersSeeder.php` so it cannot be run directly in production:

```php
public function run()
{
    if (! app()->environment(['local', 'testing'])) {
        throw new RuntimeException(
            'Demo users cannot be seeded outside local or testing environments.'
        );
    }

    // Create local or test users here.
}
```

**Expected result:** Normal production seeding does not create known or default user accounts.

### Step 7: Supply local passwords outside Git

If developers require a known local password:

1. Store it in each developer's uncommitted `.env` file.
2. Keep only an empty variable name in `.env.example`.
3. Read the value through Laravel configuration.
4. Stop the seeder with a clear error when the value is missing.

Example `.env.example` entry:

```env
DEMO_USER_PASSWORD=
```

Example configuration:

```php
// config/seeding.php
return [
    'demo_password' => env('DEMO_USER_PASSWORD'),
];
```

Example seeder usage:

```php
use Illuminate\Support\Facades\Hash;

$password = config('seeding.demo_password');

if (empty($password)) {
    throw new RuntimeException('DEMO_USER_PASSWORD is required.');
}

$user = User::create([
    'name' => 'Development Administrator',
    'username' => 'demo-admin',
    'email' => 'demo-admin@example.test',
    'password' => Hash::make($password),
]);
```

Never put the real value in `.env.example`. For shared non-development environments, use the deployment platform's secrets manager instead of committing an `.env` file.

**Expected result:** Password values are supplied securely and do not appear in Git.

### Step 8: Remove exposed values from Git history

Do this only after the passwords have been rotated.

1. Back up the repository according to the organization's recovery procedure.
2. Use a history-cleaning tool such as `git filter-repo` or BFG Repo-Cleaner.
3. Remove the exposed values from all branches and tags.
4. Coordinate the required force-push with the repository owners.
5. Ask developers to re-clone the repository or carefully reset their existing clone.
6. Review forks, pull requests, release archives, CI artifacts, and cached copies.

History rewriting affects all contributors and must be coordinated. It does not replace password rotation.

**Expected result:** The exposed values are no longer present in the main repository's reachable history.

### Step 9: Add automatic secret scanning

Add a secret scanner such as Gitleaks or TruffleHog to:

- developers' pre-commit checks;
- pull-request checks;
- the CI/CD pipeline;
- scheduled full-history scans.

Configure the pipeline to fail when likely passwords, tokens, or private keys are detected. Review scanner exceptions carefully so real credentials are not hidden by broad allowlists.

**Expected result:** Future credentials are detected before they reach a public branch.

### Step 10: Test and document the fix

Complete all of the following checks:

- Confirm every exposed password is rejected by the application.
- Confirm affected sessions and tokens no longer work.
- Search active and commented code for hardcoded passwords.
- Scan the full Git history for the exposed values.
- Run production-mode database seeding and confirm it creates no demo users.
- Try to run `DemoUsersSeeder` in production mode and confirm it stops with an error.
- Run local and automated-test seeding and confirm it still works with securely supplied values.
- Run the application's authentication and authorization tests.
- Record who completed each action and when it was verified.

**Expected result:** The team has technical evidence that the exposure is contained and the code cannot recreate the same issue.

## 4. Minimum acceptance criteria

The finding should be considered remediated only when:

- all exposed credentials have been rotated or disabled;
- old sessions and tokens have been invalidated;
- current source code and comments contain no plaintext credentials;
- production seeding cannot create default privileged users;
- local demo-user creation is protected by an environment check;
- real secrets are stored outside Git;
- repository history and related artifacts have been reviewed;
- secret scanning is enabled;
- security and application tests pass;
- suspicious historical activity has been investigated.

## 5. Recommended ownership

| Work item | Suggested owner |
|---|---|
| Rotate or disable accounts | Application administrator |
| Invalidate sessions and tokens | Backend or infrastructure engineer |
| Review login and activity logs | Security and infrastructure teams |
| Refactor seeders | Laravel/backend developer |
| Clean Git history | Repository administrator |
| Add secret scanning | DevOps or CI/CD engineer |
| Validate remediation | QA and security tester |

## 6. Source

This guide addresses the finding **"Credential Disclosure in Public GitHub Repository"** documented on pages 11-13 of the Web Application VAPT report for Innovative Solution Pvt. Ltd.
