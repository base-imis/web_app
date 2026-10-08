# VAPT Impact Analysis: No Rate Limiting on Login

## Scope

This impact analysis covers only the **No Rate Limiting on Login** finding tracked by `BASEIMIS-44`.

In scope:

- `POST /login` web authentication;
- `POST /api/login` API authentication;
- Laravel rate-limit keys, responses, expiry, cache storage, and client-IP detection;
- automated and deployment verification for these controls.

Out of scope:

- password-reset user enumeration;
- CAPTCHA;
- clickjacking and CSP headers;
- credential seeding or credential disclosure;
- MFA and unrelated session-security changes.

## 1. Issue Description/Steps to Recreate Issue

### Issue description

The application previously processed repeated web and API authentication requests without a complete login-specific restriction. The web login had no dedicated throttle. The API login inherited the general API limit, but that limit was not account-aware and was not designed for authentication abuse.

This allowed password guessing, credential stuffing, and password spraying to continue until another infrastructure control intervened.

### Safe reproduction steps

1. Use an approved test account and a non-production environment.
2. Send controlled invalid-password requests to `POST /login` from one client IP.
3. Repeat against `POST /api/login`.
4. Record status codes, redirects, validation errors, `Retry-After`, and rate-limit headers.
5. Repeat with case variations of the same username or email.
6. Repeat with different identifiers from one IP and one identifier from different approved IPs.
7. Stop before generating performance or availability impact.

## 2. Impacts Created by Existing Issue

### Security impact

- Automated password guessing could continue without an authentication-specific limit.
- Credential-stuffing tools could test leaked username/password pairs at scale.
- Attackers could rotate usernames to bypass a simple account-only control.
- Attackers could rotate source addresses to bypass a simple IP-only control.
- Repeated password hashing and database access could consume application resources.

### Business impact

- Unauthorized access to user or administrative accounts.
- Exposure or modification of protected application data.
- Actions performed under a legitimate user's identity.
- Increased incident-response, account-recovery, audit, and support costs.

### Severity

The VAPT report rates this finding **Medium**. Exploitation is remote and straightforward, although rate limiting alone does not determine whether a password is correct.

## 3. Remediation or Fix Approaches

### Selected approach: named Laravel login limiters

Define separate `web-login` and `api-login` limiters. Each limiter applies three automatically expiring controls:

| Control | Default threshold | Purpose |
|---|---:|---|
| Normalized identifier + client IP | 5 requests per 1 minute | Stops repeated guessing of one account from one source |
| Client IP | 20 requests per 1 minute | Prevents bypass by rotating usernames or emails |
| Normalized identifier across IPs | 15 requests per 15 minutes | Reduces distributed guessing against one account |

Identifiers are trimmed, converted to lowercase, and SHA-256 hashed before being used in cache keys. Web and API key namespaces remain separate so traffic to one endpoint does not unexpectedly consume the other endpoint's counters.

When a limit is exceeded:

- web login redirects back with a generic validation-style error, preserves only the username field, and includes `Retry-After`;
- API login returns generic JSON with HTTP `429 Too Many Requests` and `Retry-After`.

Neither response states whether the supplied account exists.

### Supporting deployment controls

- Trust only explicitly configured reverse-proxy or load-balancer addresses so `$request->ip()` cannot be spoofed through arbitrary forwarded headers.
- Reject wildcard proxy trust.
- Use a shared cache such as Redis when more than one application server handles requests.
- Keep thresholds and expiry windows in environment-backed application configuration.

### Approaches not selected

- A permanent account lockout was rejected because attackers could deny service to known users.
- An IP-only limit was rejected because distributed attacks can rotate IP addresses.
- An account-only limit was rejected because attackers can rotate identifiers and intentionally lock out users.
- A reverse-proxy-only rule was rejected as the primary control because it cannot consistently apply normalized account-aware keys.

## 4. Impact of Remediation to Existing Source Code

### Files changed

- `.env.example`: documents configurable thresholds, expiry windows, proxy addresses, and shared-cache deployment guidance.
- `config/security.php`: centralizes all login limiter values and trusted-proxy configuration.
- `app/Providers/RouteServiceProvider.php`: defines `web-login` and `api-login`, their keys, and their response contracts.
- `routes/web.php`: applies `throttle:web-login` to `POST /login`.
- `routes/api.php`: applies `throttle:api-login` to `POST /api/login`.
- `app/Http/Middleware/TrustProxies.php`: accepts only explicit proxy IPs/CIDRs and filters wildcard values.
- `tests/Feature/LoginRateLimitingTest.php`: covers the dedicated login-rate-limiting behavior.
- `tests/Feature/LoginSecurityHardeningTest.php`: retains login-security regression coverage with the web validation response.

### Expected behavior changes

- Requests below all configured thresholds continue to reach the existing login controllers.
- Excessive web attempts receive the same generic throttle message regardless of account existence.
- Excessive API attempts receive HTTP 429 with a generic JSON message.
- Counters expire automatically according to the configured window.
- Forwarded client IP headers affect keys only when the immediate proxy is explicitly trusted.

### Regression risks

- An IP threshold that is too low may affect legitimate users sharing a public address.
- Incorrect proxy addresses may cause all users to share a proxy IP or cause the application to ignore the real client IP.
- Local file caches do not share counters across multiple application servers.
- Changing configuration without rebuilding Laravel's configuration cache may leave old values active.
- A high account-across-IP threshold may provide weak protection; a low value may allow deliberate temporary lockouts.

## 5. Required Testing (Unit, Integration, Browser, Manual, etc.)

### Automated integration tests

- Confirm both routes use their specifically named limiter.
- Confirm the configured number of web and API attempts is allowed.
- Confirm the next web attempt produces a validation-style redirect with `Retry-After`.
- Confirm the next API attempt produces generic JSON, HTTP 429, and `Retry-After`.
- Confirm uppercase/lowercase identifier variants share a key.
- Confirm different identifiers and different IP addresses use correctly separated combination keys.
- Confirm changing identifiers cannot bypass the IP control.
- Confirm changing IP addresses cannot bypass the account control.
- Confirm web and API counters are separate.
- Confirm a restriction expires automatically.
- Confirm untrusted and wildcard forwarded headers are ignored while an explicitly trusted proxy supplies the client IP.

### Browser and manual tests

- Confirm a valid web login still succeeds below the limits.
- Confirm the blocked web response appears through the existing validation UI and does not expose account existence.
- Confirm remember-me and existing role restrictions still behave as before.
- Confirm a valid API login still issues the expected token below the limits.
- Confirm the API client handles HTTP 429 and `Retry-After`.

### Deployment tests

- Verify consecutive requests routed to different application nodes share the same limiter counters.
- Verify `$request->ip()` matches the real client address behind the production proxy chain.
- Monitor legitimate shared-network traffic and HTTP 429 volume after deployment.

## 6. Completion Checklist

- [x] `POST /login` uses the named `web-login` limiter.
- [x] `POST /api/login` uses the named `api-login` limiter.
- [x] Keys use a normalized, hashed identifier and client IP.
- [x] An IP-only control prevents username rotation bypass.
- [x] An account-across-IP control reduces distributed guessing.
- [x] Web throttling uses a generic validation-style response with retry metadata.
- [x] API throttling returns generic JSON with HTTP 429 and retry metadata.
- [x] Restrictions expire automatically.
- [x] Limits and expiry windows are configurable in one location.
- [x] Trusted-proxy configuration accepts explicit addresses and rejects wildcards.
- [x] Automated tests cover allowed, blocked, expiry, separation, web, API, and proxy behavior.
- [ ] DevOps has configured and verified the exact production proxy IPs/CIDRs.
- [ ] DevOps has confirmed all application nodes use one shared cache store.
- [ ] Security testing has repeated the controlled VAPT test and accepted the result.

## 7. Deployment and Rollback Considerations

1. Set the six `LOGIN_RATE_LIMIT_*` environment values or accept the documented defaults.
2. Set `TRUSTED_PROXIES` to the exact proxy/load-balancer IPs or CIDRs. Do not use `*` or `**`.
3. For multiple application servers, set `CACHE_DRIVER=redis` (or another supported shared store) on every node and use the same cache connection and prefix.
4. Rebuild Laravel's configuration cache.
5. Validate the real client IP and shared counters in staging.
6. Deploy during a monitored window and review 429 volume and support reports.
7. If thresholds require tuning, change configuration consistently across all nodes; do not remove every login protection.

## 8. References

- Jira: `BASEIMIS-44`
- Developer guide: `docs/no-rate-limiting-login-developer-remediation-guide.md`
- `app/Providers/RouteServiceProvider.php`
- `app/Http/Middleware/TrustProxies.php`
- `config/security.php`
- `routes/web.php`
- `routes/api.php`
- `tests/Feature/LoginRateLimitingTest.php`
