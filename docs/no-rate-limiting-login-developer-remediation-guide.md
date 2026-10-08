# No Rate Limiting on Login: Developer Remediation Guide

## Scope

This guide implements only the login-rate-limiting remediation tracked by `BASEIMIS-44`.

It covers the web and API login endpoints, limiter keys, responses, expiry, trusted proxies, shared cache requirements, tests, and deployment checks. Password-reset enumeration, CAPTCHA, clickjacking, credential seeding, MFA, and unrelated session changes are intentionally excluded.

## 1. Issue Description/Steps to Recreate Issue

The vulnerable behavior is repeated processing of invalid authentication requests without a complete login-specific limiter.

Use only approved test accounts and a non-production environment:

1. Submit invalid credentials to `POST /login` several times from one client IP.
2. Repeat against `POST /api/login`.
3. Check whether requests continue reaching authentication after the expected threshold.
4. Repeat with different username/email casing, different identifiers from one IP, and one identifier from different approved IPs.
5. Record the web validation response, API status and JSON body, `Retry-After`, and expiry behavior.

## 2. Impacts Created by Existing Issue

- Password guessing, credential stuffing, and password spraying are easier to automate.
- Attackers can consume database and password-hashing resources.
- Compromised accounts can expose protected data and permit actions under legitimate identities.
- An incomplete one-dimensional limit can be bypassed by rotating identifiers or source addresses.

## 3. Remediation or Fix Approaches

### Implemented application control

Laravel defines two named limiters:

- `web-login` for `POST /login` using the `username` field;
- `api-login` for `POST /api/login` using the `email` field.

Each limiter creates three keys:

```text
<channel>-identity-ip:<sha256(normalized identifier)>|<client IP>
<channel>-ip:<client IP>
<channel>-identity:<sha256(normalized identifier)>
```

Normalization consists of trimming surrounding whitespace and converting the identifier to lowercase. Hashing prevents raw usernames and email addresses from appearing in cache keys.

Default controls:

| Control | Requests | Expiry |
|---|---:|---:|
| Identifier + IP | 5 | 1 minute |
| IP across identifiers | 20 | 1 minute |
| Identifier across IPs | 15 | 15 minutes |

These are independent for web and API traffic. All restrictions expire through Laravel's cache-backed rate limiter; there is no permanent account lockout.

### Response contracts

Web limit exceeded:

- redirect back to the existing form;
- add the translated generic throttle message to the `username` validation error;
- preserve the submitted username but never the password;
- include Laravel's rate-limit and `Retry-After` headers.

API limit exceeded:

```json
{
  "status": false,
  "message": "Too many login attempts. Please try again in N seconds."
}
```

The API status is HTTP 429. Messages do not state whether an account exists.

### Alternatives rejected

- Do not use a permanent account lockout.
- Do not depend on an IP-only or account-only key.
- Do not implement the control only in browser JavaScript.
- Do not use blocking delays such as `sleep()`.
- Do not rely only on an Nginx/WAF request count because the application must normalize and protect account identifiers.

## 4. Impact of Remediation to Existing Source Code

### `config/security.php`

All thresholds and expiry windows are defined under `security.login_rate_limit`:

```php
'login_rate_limit' => [
    'identity_ip' => ['max_attempts' => 5, 'decay_minutes' => 1],
    'ip' => ['max_attempts' => 20, 'decay_minutes' => 1],
    'identity' => ['max_attempts' => 15, 'decay_minutes' => 15],
],
```

The values are populated from the documented `LOGIN_RATE_LIMIT_*` environment variables. Avoid adding numeric limits directly to routes or controllers.

### `app/Providers/RouteServiceProvider.php`

`configureRateLimiting()`:

1. Builds normalized and hashed identifier keys.
2. Creates combination, IP-only, and identifier-only limits.
3. Registers separate `web-login` and `api-login` named limiters.
4. Supplies endpoint-appropriate generic responses.

### Routes

```php
Route::post('/login', 'Auth\LoginController@login')
    ->middleware('throttle:web-login')
    ->name('login.perform');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:api-login');
```

The API route continues to receive the general `throttle:api` middleware through the API middleware group. `api-login` is the additional authentication-specific protection.

### `app/Http/Middleware/TrustProxies.php`

`TRUSTED_PROXIES` accepts a comma-separated list of exact proxy addresses or CIDRs. Empty configuration trusts no forwarded address. `*` and `**` are filtered out because trusting every direct client would allow forwarded-IP spoofing.

### Cache behavior

Laravel stores limiter counters in the configured default cache store. A local file cache is acceptable for a single application node. Multiple nodes must use the same shared cache, normally Redis, with the same connection and cache prefix.

## 5. Required Testing (Unit, Integration, Browser, Manual, etc.)

Run the focused automated tests:

```text
php artisan test --filter="LoginRateLimitingTest|LoginSecurityHardeningTest"
```

Coverage includes:

- route-to-limiter assignment;
- requests allowed up to each threshold;
- web validation-style blocking behavior;
- API HTTP 429 JSON behavior;
- `Retry-After` headers;
- case-insensitive identifier normalization;
- identifier/IP combination-key separation;
- IP-only protection against identifier rotation;
- identifier-only protection across IP addresses;
- independent web/API counters;
- automatic expiry;
- trusted, untrusted, and wildcard proxy handling.

Manual staging verification:

1. Confirm a valid web and API login succeeds below the threshold.
2. Confirm the sixth same-identifier/same-IP request is blocked with the expected endpoint response.
3. Confirm a web error displays through the existing validation area and never retains the password.
4. Confirm an API client receives HTTP 429 and respects `Retry-After`.
5. Confirm known and unknown account attempts receive the same public authentication and throttle wording.
6. Confirm `$request->ip()` reports the actual test client behind the deployed proxy chain.
7. In a multi-node environment, alternate requests between nodes and confirm the counter remains shared.

## 6. Completion Checklist

- [x] Separate named web and API login limiters are registered.
- [x] Both login routes apply the correct named limiter.
- [x] Keys contain a normalized, hashed identifier and client IP.
- [x] An IP-only limit prevents bypass through username/email rotation.
- [x] An identifier-only limit reduces bypass through IP rotation.
- [x] Web blocking uses a generic validation-style response and retains no password.
- [x] API blocking returns generic JSON with HTTP 429.
- [x] `Retry-After` is returned.
- [x] Every restriction expires automatically.
- [x] Limits and expiry windows are environment-configurable.
- [x] Trusted proxies are explicit and wildcard trust is rejected.
- [x] Focused automated tests pass.
- [ ] DevOps has verified the production proxy chain.
- [ ] DevOps has configured a shared cache when multiple application nodes are used.
- [ ] Security has completed controlled retesting.

## 7. Deployment and Rollback

1. Configure the six `LOGIN_RATE_LIMIT_*` variables consistently on every node.
2. Configure exact `TRUSTED_PROXIES` values supplied by DevOps.
3. Use `CACHE_DRIVER=redis` or another shared store for multiple application servers.
4. Rebuild the Laravel configuration cache after environment changes.
5. Verify the real client IP and shared counters in staging.
6. Monitor HTTP 429 volume and reports from legitimate shared networks.
7. Tune thresholds through configuration when evidence supports a change.
8. During rollback, retain at least one server-side login throttle while correcting the application configuration.

## 8. Configuration Reference

```dotenv
LOGIN_RATE_LIMIT_IDENTITY_IP_ATTEMPTS=5
LOGIN_RATE_LIMIT_IDENTITY_IP_DECAY_MINUTES=1
LOGIN_RATE_LIMIT_IP_ATTEMPTS=20
LOGIN_RATE_LIMIT_IP_DECAY_MINUTES=1
LOGIN_RATE_LIMIT_IDENTITY_ATTEMPTS=15
LOGIN_RATE_LIMIT_IDENTITY_DECAY_MINUTES=15
TRUSTED_PROXIES=
```

DevOps must also review:

- `CACHE_DRIVER` and Redis availability when multiple nodes are used;
- identical cache prefixes and connections across nodes;
- the exact load-balancer, ingress, CDN, or reverse-proxy addresses;
- configuration-cache rebuilding during deployment.
