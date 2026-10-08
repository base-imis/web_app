# BASE-IMIS Login Security — Developer Implementation Guide for F09, F10 and F11

**Prepared on:** 1 September 2026  
**Audience:** Backend developers, reviewers and QA engineers  
**Current code status:** Implemented and covered by automated tests  
**Database migration:** Not required

## 1. Purpose

This guide explains exactly what was changed, why each change is required, what the developer must check, and how to verify the implementation safely.

The three findings are:

- **F09:** No Rate Limiting on Login;
- **F10:** Clickjacking;
- **F11:** User Enumeration via Password Reset Functionality.

These changes should be reviewed and released together because they protect the same public authentication boundary.

## 2. Before starting

The developer should confirm:

- the current branch contains the login loading-button changes;
- the application starts successfully;
- the configured cache driver is available;
- the mailer and queue settings for password reset are known;
- the production proxy/load-balancer arrangement is documented;
- no approved BASE-IMIS integration requires pages to be displayed inside an iframe.

Do not use production user credentials while testing rate limits. Use a controlled QA account and approved test IP/environment.

## 3. F09 — Login rate limiting

### 3.1 What the developer must implement or verify

The rate limiter is defined in:

```text
app/Providers/RouteServiceProvider.php
```

The limiter must:

1. read `username` for browser login or `email` for API login;
2. trim the value and convert it to lowercase;
3. hash the normalized identity before using it in the limiter cache key;
4. apply an identity-and-IP limit;
5. apply a second overall IP limit;
6. return HTTP `429` after the threshold is exceeded;
7. return a `Retry-After` response header;
8. return an HTML wait page for browser login;
9. return a JSON error for API login.

Current default limits:

| Limit | Default value | Purpose |
|---|---:|---|
| Login attempts for one identity and IP | 5 per minute | Slows password guessing against one account |
| All login attempts from one IP | 25 per minute | Slows attacks that rotate through many usernames |

The routes must use the named limiter:

```text
POST /login      -> throttle:login
POST /api/login  -> throttle:login
```

Route files:

```text
routes/web.php
routes/api.php
```

### 3.2 Configuration

The values are defined through:

```text
config/security.php
.env.example
```

Production environment variables:

```text
LOGIN_RATE_LIMIT_PER_IDENTITY=5
LOGIN_RATE_LIMIT_PER_IP=25
```

Do not increase these values only to make a failed test pass. Any production adjustment should be based on observed legitimate traffic and security approval.

### 3.3 Developer verification

1. Submit invalid credentials five times using the same normalized identity and IP.
2. Confirm the ordinary generic credential error is returned for attempts 1–5.
3. Submit attempt 6.
4. Confirm HTTP status `429`.
5. Confirm the response includes `Retry-After`.
6. Repeat the same test against `/api/login` and confirm the error is JSON.
7. Repeat using uppercase and lowercase versions of the same identity and confirm case changes do not bypass the limit.
8. Try different usernames from one IP and confirm the overall IP ceiling works.
9. Wait for the retry period and confirm login becomes available again.

### 3.4 Important infrastructure check

Laravel rate limiting uses the cache. In a multi-server deployment, the cache must be shared between application nodes. Redis is the preferred option for this deployment pattern.

If each node uses its own file cache, an attacker can receive a separate allowance from every node. If an array cache is used, limits disappear after the request and provide no production protection.

The developer must also confirm that `request()->ip()` resolves to the real client address through the approved trusted proxy. A wrong proxy configuration can cause every office user to share one proxy IP limit or can allow unsafe forwarded-IP handling.

## 4. F10 — Clickjacking protection

### 4.1 What the developer must implement or verify

The middleware is:

```text
app/Http/Middleware/PreventClickjacking.php
```

It is registered in the web middleware group in:

```text
app/Http/Kernel.php
```

Every web response must contain:

```text
Content-Security-Policy: frame-ancestors 'none'
X-Frame-Options: DENY
```

The middleware preserves an existing Content Security Policy and appends `frame-ancestors 'none'` only when the directive is absent.

### 4.2 Why both headers are required

- `Content-Security-Policy: frame-ancestors 'none'` is the modern browser control.
- `X-Frame-Options: DENY` protects older browser implementations.

Together they prevent an attacker from placing BASE-IMIS inside an invisible or deceptive frame and tricking an authenticated user into clicking it.

### 4.3 Developer verification

Check the following responses:

1. landing page;
2. login flow;
3. forgot-password page;
4. password-reset response;
5. authenticated dashboard;
6. a validation-error response;
7. the HTTP `429` page.

For each response, confirm both headers are present. Then create a controlled external HTML page containing a BASE-IMIS iframe and confirm the browser refuses to display the application.

### 4.4 Expected impact

Any legitimate integration that embeds a BASE-IMIS web page in an iframe will stop working. If a real approved iframe requirement is discovered, do not remove all protection. Replace `'none'` with a narrowly approved `frame-ancestors` allowlist after a security review.

## 5. F11 — Password-reset user enumeration

### 5.1 What the developer must implement or verify

The neutral reset response is implemented in:

```text
app/Http/Controllers/Auth/ForgotPasswordController.php
resources/lang/en/passwords.php
```

For every syntactically valid email address, the user must see:

> If an account exists for this email address, a password reset link will be sent.

The response must be the same when:

- the account exists;
- the account does not exist;
- the request is sent through the normal browser form;
- the client requests JSON.

The backend still sends a real reset notification only for an eligible existing account.

Invalid email syntax should continue to return validation feedback. Neutralizing account existence does not mean accepting malformed input.

### 5.2 Password-reset rate limiting

The reset route uses:

```text
throttle:password-reset
```

Current limits:

| Limit | Default value | Purpose |
|---|---:|---|
| Requests for one email and IP | 3 per minute | Prevents repeated reset-email abuse |
| All reset requests from one IP | 20 per hour | Reduces enumeration and notification flooding |

Environment variables:

```text
PASSWORD_RESET_RATE_LIMIT_PER_IDENTITY=3
PASSWORD_RESET_RATE_LIMIT_PER_IP=20
```

### 5.3 Developer verification

1. Submit an existing QA email.
2. Record the HTTP status, redirect, session message and rendered message.
3. Submit a nonexistent but valid email.
4. Confirm the status, redirect and message are identical.
5. Confirm the existing account receives a reset notification.
6. Confirm the nonexistent account does not cause a notification.
7. Repeat both requests with an `Accept: application/json` header.
8. Confirm the JSON status and schema are identical.
9. Submit malformed input and confirm email-format validation still works.
10. Exceed the reset limit and confirm HTTP `429` without an account-existence message.

### 5.4 Timing-side-channel check

The code makes the visible response and status identical. Production must also use an asynchronous queue for reset email. With a synchronous mailer, an existing account can take longer because it sends email while a nonexistent account does not.

The developer must:

1. verify the production queue is not `sync`;
2. verify a queue worker processes password-reset notifications;
3. compare multiple response timings for existing and nonexistent test emails;
4. avoid declaring F11 completely closed until no practically useful timing difference remains.

## 6. Automated tests

The security tests are located at:

```text
tests/Feature/LoginSecurityHardeningTest.php
```

They cover:

- clickjacking headers;
- browser-login throttling;
- API-login throttling;
- equal reset responses for existing and nonexistent emails;
- generic JSON reset responses;
- invalid email validation.

Run:

```text
php artisan test --filter=LoginSecurityHardeningTest
php artisan test
```

Current verified result:

```text
Focused security tests: 6 passed
Complete project suite: 17 passed
```

## 7. Deployment steps

1. Review and approve the four rate-limit values.
2. Confirm the production cache is shared across all application nodes.
3. Confirm trusted-proxy configuration returns the real client IP.
4. Confirm reset notifications use a real asynchronous queue.
5. Deploy the application changes.
6. Rebuild the Laravel configuration cache.
7. Restart queue workers if queue configuration or application code requires it.
8. Run the focused security tests.
9. Perform the manual QA checks in Sections 3–5.
10. Monitor `429` frequency and authentication failures after release.
11. Attach closure evidence to the security finding tracker.

## 8. Closure evidence

Attach the following evidence separately for each finding:

### F09

- browser request showing HTTP `429`;
- API response showing JSON `429`;
- `Retry-After` header;
- evidence that attempts work again after expiry;
- confirmation of shared-cache and trusted-proxy configuration.

### F10

- response headers from public and authenticated pages;
- screenshot showing external iframe embedding was blocked;
- confirmation that approved integrations do not need framing.

### F11

- side-by-side existing/nonexistent reset responses;
- proof that only the existing account received email;
- JSON response comparison;
- reset-rate-limit evidence;
- timing comparison through the production queue configuration.

## 9. Rollback guidance

If the login limits cause a severe production lockout:

1. confirm whether the real client IP is being resolved correctly;
2. correct proxy or shared-cache configuration before increasing thresholds;
3. temporarily adjust the environment limits only with approval;
4. rebuild the configuration cache after the adjustment.

If iframe protection breaks an approved integration, use a narrow CSP origin allowlist after security review. Do not remove anti-clickjacking protection globally.

The neutral password-reset response should normally remain during rollback because it has low functional impact and prevents direct account disclosure.
