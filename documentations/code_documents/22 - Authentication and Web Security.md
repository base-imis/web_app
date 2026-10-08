Version: V1.0.0

# Authentication and Web Security

This document describes the browser and API login entry points, login submission behavior, authentication rate limiting, password-reset privacy, trusted-proxy handling, and clickjacking protection used by BASE IMIS.

## Authentication Entry Points

| Purpose | Method and route | Handler |
|---|---|---|
| Browser login page | `GET /login` | `Auth/LoginController@show` |
| Browser login submission | `POST /login` | `Auth/LoginController@login` |
| API login submission | `POST /api/login` | `Api/AuthController@login` |
| Password-reset request page | `GET /password/reset` | `Auth/ForgotPasswordController@showLinkRequestForm` |
| Password-reset email request | `POST /password/email` | `Auth/ForgotPasswordController@sendResetLinkEmail` |

Existing authentication controllers remain responsible for credential validation, allowed roles, session creation, remember-cookie behavior, API token creation, redirects, and generic invalid-credential responses.

## Browser Login Submission Behavior

The browser login form is located at `resources/views/auth/login.blade.php`.

After the browser confirms that required fields are valid and the form is submitted:

1. The form is marked as submitting.
2. The submit button is disabled.
3. A spinner is displayed.
4. The button text changes from `Log In` to `Signing in...`.
5. The button receives `aria-busy="true"`.
6. A repeated submission is cancelled while the first request is active.

The form state is reset on the browser `pageshow` event. This prevents a disabled button from remaining visible when the user returns through Back or Forward navigation.

This client-side behavior prevents accidental duplicate submissions and provides progress feedback. It is not a security boundary and does not replace server-side rate limiting or authentication validation.

## Login Rate Limiting

The browser and API login routes use separate named Laravel rate limiters defined in `app/Providers/RouteServiceProvider.php`:

- `web-login` protects `POST /login`.
- `api-login` protects `POST /api/login`.

Separate limiter namespaces prevent browser and API traffic from consuming each other's counters while applying the same layered policy.

### Default Limits

| Scope | Default attempts | Default period | Threat addressed |
|---|---:|---:|---|
| Normalized identity and client IP | 5 | 1 minute | Repeated guessing against one account from one address |
| Client IP | 20 | 1 minute | Bypass by rotating account identifiers from one address |
| Normalized identity | 15 | 15 minutes | Bypass by targeting one account from multiple addresses |

The submitted username or email is trimmed and converted to lowercase. A SHA-256 hash of the normalized value is stored in the limiter key rather than the plain identifier. Passwords are not included in rate-limit keys.

### Browser Response

When the browser login exceeds a limit, the application redirects back to the login page with:

- The username field preserved.
- The password excluded from flashed input.
- A generic throttling message.
- The `Retry-After` response header.

### API Response

When API login exceeds a limit, the application returns HTTP `429 Too Many Requests`, a `Retry-After` header, and JSON in the following form:

```json
{
    "status": false,
    "message": "Too many login attempts. Please try again in 60 seconds."
}
```

The number of seconds reflects the active lockout and may differ from the example.

### Configuration

`config/security.php` reads the following environment values:

```dotenv
LOGIN_RATE_LIMIT_IDENTITY_IP_ATTEMPTS=5
LOGIN_RATE_LIMIT_IDENTITY_IP_DECAY_MINUTES=1
LOGIN_RATE_LIMIT_IP_ATTEMPTS=20
LOGIN_RATE_LIMIT_IP_DECAY_MINUTES=1
LOGIN_RATE_LIMIT_IDENTITY_ATTEMPTS=15
LOGIN_RATE_LIMIT_IDENTITY_DECAY_MINUTES=15
```

Tune these values through deployment configuration. Do not hard-code environment-specific thresholds in controllers or routes.

## Trusted Proxy Handling

Rate limiting depends on a trustworthy client IP. `app/Http/Middleware/TrustProxies.php` reads the comma-separated `TRUSTED_PROXIES` value from `config/security.php`.

```dotenv
TRUSTED_PROXIES=10.0.0.10,10.0.1.0/24
```

Only known proxy addresses or CIDR ranges should be configured. The application removes wildcard values such as `*` and `**` so an untrusted client cannot choose its apparent address through forwarded headers.

When PHP receives requests directly, leave `TRUSTED_PROXIES` empty.

## Shared Cache Requirement

Laravel stores login rate-limit counters in the configured cache. A deployment with multiple application instances must use a shared cache such as Redis. File cache stores counters independently on each server and therefore does not provide a consistent distributed limit.

The same shared-cache requirement applies to main-dashboard cache versions and refresh locks. Confirm cache availability and failure behavior before releasing authentication or dashboard caching changes.

## Password Reset Enumeration Protection

`app/Http/Controllers/Auth/ForgotPasswordController.php` overrides the public password-reset request response.

The controller:

1. Validates the email input.
2. Trims and lowercases the email.
3. Calls the existing Laravel password broker.
4. Sends a reset notification only when an eligible account exists.
5. Returns the same public acknowledgement for registered, unregistered, and soft-deleted accounts.

The generic message is defined in `resources/lang/en/passwords.php`:

> If an account exists for this email address, a password reset link will be sent.

Browser and JSON clients receive equivalent wording. The public response must not include account-not-found text or otherwise confirm whether the email belongs to an account.

This change does not modify reset tokens, notification delivery, database tables, or the password broker's eligibility rules.

## Clickjacking Protection

`app/Http/Middleware/PreventClickjacking.php` applies the approved anti-framing policy to HTML responses:

```http
Content-Security-Policy: frame-ancestors 'none'
X-Frame-Options: DENY
```

The middleware is registered in the web middleware group in `app/Http/Kernel.php`. `app/Exceptions/Handler.php` applies the same policy to handled and framework-rendered HTML errors.

The middleware:

- Preserves existing non-framing Content Security Policy directives.
- Removes conflicting `frame-ancestors` directives before adding the approved value.
- Replaces conflicting `X-Frame-Options` values.
- Avoids duplicate framing headers.
- Protects normal pages, redirects, and HTML error responses.
- Does not add document-framing headers to non-HTML responses.

The application cannot be embedded in a frame under the current policy. If a future approved integration requires framing, Security must review the permitted origins and change the policy narrowly. Do not remove anti-framing protection globally.

The approved header values are configured in `config/security_headers.php`.

## Error and Privacy Requirements

Authentication responses must follow these requirements:

- Invalid browser credentials use one generic error for every identifier.
- Invalid API credentials use one generic JSON error.
- Password values are never flashed back to the session.
- Password-reset responses do not reveal account existence.
- Throttling responses do not reveal whether the supplied identity is valid.
- Application exceptions are not returned to public authentication clients.

## Database and Feature Impact

These authentication and security controls add no database migration, schema change, or data backfill.

They modify the following externally visible behavior:

- Excessive login attempts receive a temporary rate-limit response.
- The application cannot be embedded in another site's frame.
- Password-reset requests always receive a generic public acknowledgement.
- The browser login button displays a progress state and blocks duplicate submission.

No existing valid credential, role, permission, session, API token, or password-reset token format is removed by these controls.

## Automated Tests

| Test | Primary coverage |
|---|---|
| `tests/Feature/LoginRateLimitingTest.php` | Route middleware, thresholds, bypass resistance, web and API separation, expiry, and trusted proxies |
| `tests/Feature/LoginSecurityHardeningTest.php` | Browser and API limits and anti-framing headers |
| `tests/Feature/Http/Controllers/LoginControllerTest.php` | Login controls, validation, normalization, generic errors, roles, and API tokens |
| `tests/Feature/Http/Controllers/ForgotPasswordControllerTest.php` | Equivalent password-reset responses, eligible notification behavior, and unsafe-looking inputs |
| `tests/Feature/Http/Middleware/PreventClickjackingTest.php` | Public, authenticated, redirect, error, existing CSP, duplicate-header, and non-HTML behavior |

## Deployment Checklist

1. Confirm the approved rate-limit thresholds.
2. Configure only approved reverse proxies.
3. Use Redis or another shared cache when more than one application instance serves requests.
4. Rebuild Laravel configuration and route caches through the approved deployment procedure.
5. Verify browser login below and above the configured threshold.
6. Verify API login returns HTTP 429 and `Retry-After` above the threshold.
7. Compare registered and unregistered password-reset responses.
8. Verify public pages, authenticated pages, redirects, 403 responses, 404 responses, and handled errors contain one approved anti-framing policy.
9. Run the automated authentication and security test suites.
10. Complete an independent VAPT retest before closing the findings.

## Rollback

Rollback requires reverting the application release and restoring the previous deployment configuration. No database rollback is required because these changes add no migration.

If rate limiting causes false lockouts, adjust the approved environment thresholds after reviewing legitimate traffic. Do not disable the limiter as the first response. If a framing integration fails, obtain security approval for a narrow CSP origin list rather than removing the headers.
