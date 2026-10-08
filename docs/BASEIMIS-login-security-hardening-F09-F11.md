# BASE-IMIS Login Security Hardening — F09, F10 and F11

**Prepared on:** 31 August 2026  
**Scope:** Browser login, API login, password-reset request and web response headers  
**Status:** Implemented in code; complete automated suite passing

## 1. Executive summary

The three findings can be released together because they affect the public authentication boundary and do not require database-schema changes.

| Finding | Risk | Implemented mitigation |
|---|---|---|
| F09 — No Rate Limiting on Login | Automated password guessing and credential stuffing | Dedicated identity-and-IP rate limits on browser and API login routes, with HTTP `429` and `Retry-After` responses |
| F10 — Clickjacking | An attacker can place BASE-IMIS inside a deceptive frame and trick a user into clicking it | `Content-Security-Policy: frame-ancestors 'none'` and `X-Frame-Options: DENY` on web responses |
| F11 — User Enumeration via Password Reset | Different responses reveal whether an email is registered | The same message and HTTP behavior are returned for existing and nonexistent valid email addresses |

## 2. Implemented technical changes

### 2.1 Dedicated login rate limiting

The browser route `POST /login` and API route `POST /api/login` now share a named login limiter.

Two limits are applied together:

- maximum **5 requests per normalized username/email and IP address per minute**;
- maximum **25 login requests per IP address per minute**.

Usernames and emails are normalized to lowercase before the limiter key is generated. The identity is hashed before it is placed in the cache key. When a limit is exceeded:

- the server returns HTTP `429 Too Many Requests`;
- the response includes `Retry-After`;
- browser users receive a readable wait message;
- API clients receive a JSON error with the same status.

Configuration:

```text
LOGIN_RATE_LIMIT_PER_IDENTITY=5
LOGIN_RATE_LIMIT_PER_IP=25
```

### 2.2 Password-reset request protection

`POST /password/email` now has both response neutralization and request throttling.

Limits:

- maximum **3 requests per normalized email and IP per minute**;
- maximum **20 password-reset requests per IP per hour**.

Configuration:

```text
PASSWORD_RESET_RATE_LIMIT_PER_IDENTITY=3
PASSWORD_RESET_RATE_LIMIT_PER_IP=20
```

For every syntactically valid email, the response is:

> If an account exists for this email address, a password reset link will be sent.

This response is used whether the account exists or not. A real reset notification is still sent only when the email belongs to an eligible account. Invalid email syntax continues to produce a validation error.

### 2.3 Clickjacking protection

A web middleware now adds:

```text
Content-Security-Policy: frame-ancestors 'none'
X-Frame-Options: DENY
```

The CSP directive is the modern control. `X-Frame-Options` provides compatibility with older browsers.

## 3. Impact analysis

### Positive security impact

- Password-guessing volume is reduced on both public login endpoints.
- Changing only the username/email does not bypass the per-IP ceiling.
- Changing only the source IP does not bypass the per-identity-and-IP control as easily at small scale.
- Existing and nonexistent reset accounts are indistinguishable from the response body and status.
- BASE-IMIS cannot be placed inside an attacker's iframe.

### User and operational impact

- A user who submits login more than five times within one minute from the same IP must wait for the displayed retry period.
- Offices using one public IP share the 25-requests-per-minute IP ceiling. This value should be monitored after deployment and adjusted only with security approval.
- Password-reset users always see a neutral message. Support staff should explain that the user must also check spam and confirm the entered address.
- Any legitimate system that currently embeds a BASE-IMIS page in an iframe will stop working. No application-page iframe dependency was found during this review, but QA must confirm integrations.
- Each limited request adds a small cache read/write. This is substantially cheaper than a password-hash check or database-backed authentication attempt.

### Infrastructure conditions that must be verified

1. **Shared limiter store:** In a multi-server deployment, the default cache used by Laravel's rate limiter must be shared, preferably Redis. A server-local file or array cache would enforce separate limits on each node.
2. **Client IP handling:** If BASE-IMIS is behind a reverse proxy or load balancer, the trusted-proxy configuration must produce the real client IP. Otherwise all users may appear to have the proxy's IP, or an unsafe proxy configuration may permit spoofing.
3. **HTTPS:** Authentication and reset endpoints must remain HTTPS-only in the target environment.
4. **Monitoring:** Repeated `429` responses should be monitored for attack patterns and false-positive office lockouts.

### Residual password-reset timing consideration

The response body and status no longer disclose account existence. If the production mailer sends reset email synchronously, a valid account can still take longer than a nonexistent account because only the valid account triggers email delivery. Before security closure, confirm that reset notifications use a real asynchronous production queue and test response-time distributions for existing and nonexistent addresses. Do not claim timing-side-channel closure until that production check passes.

## 4. Files changed

- `app/Providers/RouteServiceProvider.php`
- `app/Http/Middleware/PreventClickjacking.php`
- `app/Http/Kernel.php`
- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `routes/web.php`
- `routes/api.php`
- `config/security.php`
- `.env.example`
- `resources/lang/en/passwords.php`
- `resources/views/errors/429.blade.php`
- `tests/Feature/LoginSecurityHardeningTest.php`

## 5. QA and retest checklist

### F09 — Login rate limit

- [ ] Confirm attempts 1–5 with invalid credentials return the normal generic login error.
- [ ] Confirm attempt 6 for the same case returns HTTP `429` with `Retry-After`.
- [ ] Repeat against `/api/login` and verify a JSON `429` response.
- [ ] Confirm username/email case changes do not bypass the identity limit.
- [ ] Confirm multiple different identities from one IP eventually reach the IP ceiling.
- [ ] Confirm a normal successful login works below the threshold.
- [ ] Confirm limits expire and login becomes available after the retry period.
- [ ] Test from the production proxy/load-balancer path and verify client-IP behavior.

### F10 — Clickjacking

- [ ] Verify the landing page, login flow, reset flow and authenticated dashboard contain both anti-framing headers.
- [ ] Attempt to embed the application in an external-domain iframe and confirm the browser blocks it.
- [ ] Confirm no approved internal integration depends on framing BASE-IMIS pages.

### F11 — Password-reset enumeration

- [ ] Submit one existing valid email and one nonexistent valid email.
- [ ] Confirm both return the same status, redirect behavior and visible message.
- [ ] Confirm only the existing account receives the reset notification.
- [ ] Repeat with JSON requests and compare their status and response schema.
- [ ] Confirm malformed email input still returns validation feedback.
- [ ] Confirm repeated reset requests receive HTTP `429` without revealing account existence.
- [ ] Compare response-time distributions for existing and nonexistent emails through the production mail/queue configuration.

## 6. Deployment and rollback notes

Deployment requires no migration. Before release:

1. set or approve the four rate-limit environment values;
2. confirm the production cache driver and trusted-proxy configuration;
3. rebuild Laravel's configuration cache;
4. run the authentication security tests;
5. capture response headers and matching reset responses as closure evidence.

If emergency rollback is required, remove the named limiter middleware from the affected routes and remove `PreventClickjacking` from the web middleware group. Response neutralization should normally remain because it has low operational risk and directly prevents account disclosure.

## 7. Automated verification result

The complete project test suite passed **17 tests**, including six dedicated authentication-security tests covering:

- clickjacking response headers;
- browser-login rate limiting;
- API-login rate limiting;
- equal reset behavior for existing and nonexistent accounts;
- generic JSON reset behavior;
- continued validation of malformed email addresses.

## 8. Closure evidence to attach

- test output showing the authentication-security test class passing;
- browser-login screenshot of the `429` wait page;
- API-login response showing status `429` and `Retry-After`;
- response headers showing CSP `frame-ancestors 'none'` and `X-Frame-Options: DENY`;
- side-by-side password-reset responses for an existing and nonexistent email;
- confirmation that a reset notification was generated only for the existing account.
