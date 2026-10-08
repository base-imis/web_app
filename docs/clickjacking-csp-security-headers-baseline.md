# Clickjacking CSP and Security-Headers Baseline

## 1. Purpose

This document defines the minimum Clickjacking protection for Base IMIS and the ownership boundary between Laravel and Nginx.

## 2. Intended Application Baseline

Base IMIS must not be embedded in an iframe by default.

Laravel must emit:

```http
Content-Security-Policy: frame-ancestors 'none'
X-Frame-Options: DENY
```

`frame-ancestors 'none'` is the primary modern control. `X-Frame-Options: DENY` is the compatible fallback. Both values express the same no-framing policy.

This focused CSP directive can be merged into a broader application CSP later. Existing CSP directives must not be removed or replaced accidentally.

## 3. Configuration Ownership

### Laravel ownership

Laravel is the source of truth for:

- `Content-Security-Policy`;
- the `frame-ancestors` policy;
- `X-Frame-Options`;
- application-specific trusted framing origins, if ever approved.

Recommended locations:

```text
config/security_headers.php
app/Http/Middleware/SecurityHeaders.php
app/Http/Kernel.php
```

The middleware should be registered globally for Laravel web responses.

### Nginx ownership

Nginx must not add, replace, duplicate, or conflict with the CSP and `X-Frame-Options` emitted by Laravel.

DevOps must inspect the effective Nginx configuration, including imported files and inherited server/location blocks, for:

```text
add_header Content-Security-Policy ...
add_header X-Frame-Options ...
proxy_hide_header Content-Security-Policy
proxy_hide_header X-Frame-Options
more_set_headers ...
more_clear_headers ...
```

Nginx may own separately approved server-level security headers that must apply consistently regardless of application behavior, for example:

```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Content-Type-Options: nosniff
Referrer-Policy: strict-origin-when-cross-origin
```

These examples require infrastructure and compatibility review before enforcement. They must not create a second application CSP source of truth.

## 4. Permitted Exceptions and Trusted Origins

### Current baseline

No trusted framing origin is approved. The required policy is:

```text
frame-ancestors 'none'
```

### Same-origin exception

If a documented feature requires same-origin framing, change both values consistently:

```http
Content-Security-Policy: frame-ancestors 'self'
X-Frame-Options: SAMEORIGIN
```

### Approved external origin

If a specific external system must frame Base IMIS, use an exact HTTPS allowlist:

```http
Content-Security-Policy: frame-ancestors 'self' https://trusted.example.com
```

Requirements for an exception:

1. Written business owner approval.
2. Security review of the framing origin.
3. Exact HTTPS origin; no wildcard.
4. Documented owner, reason, environment, and expiry/review date.
5. Browser testing showing approved origin allowed and unapproved origin blocked.
6. Review of `X-Frame-Options`, because it cannot reliably express an external-origin allowlist.

Do not use obsolete `X-Frame-Options: ALLOW-FROM`.

## 5. Laravel Implementation Requirement

The global middleware should obtain values from configuration and set one value for each header:

```php
$response->headers->set(
    'Content-Security-Policy',
    config('security_headers.content_security_policy')
);

$response->headers->set(
    'X-Frame-Options',
    config('security_headers.x_frame_options')
);
```

The baseline configuration should resolve to:

```php
return [
    'content_security_policy' => "frame-ancestors 'none'",
    'x_frame_options' => 'DENY',
];
```

Do not place `frame-ancestors` in an HTML meta tag. Browsers require this directive in the HTTP response header.

## 6. Basic Verification Requirements

### Automated checks

- Assert one CSP header is present.
- Assert it contains `frame-ancestors 'none'` unless an exception is approved.
- Assert one `X-Frame-Options` header is present with `DENY`.
- Test the reported FSM route and representative public/authenticated HTML responses.
- Fail CI when the expected values are missing or duplicated.

### Browser checks

1. Load the reported page normally and confirm it works.
2. Attempt to frame it from a different approved test origin.
3. Confirm the browser blocks rendering.
4. Confirm the browser console identifies CSP or `X-Frame-Options` enforcement.
5. Repeat in supported browsers.

### Deployment checks

1. Inspect production response headers after deployment.
2. Confirm only one CSP and one compatible `X-Frame-Options` value are effective.
3. Review the effective Nginx configuration for conflicts.
4. Confirm separately approved Nginx headers remain present.
5. Re-run the VAPT Clickjacking test.

## 7. Change-Control Rule

Any future change to CSP, `frame-ancestors`, `X-Frame-Options`, or trusted framing origins requires:

- documented reason;
- developer and DevOps review;
- security approval for new origins;
- automated and browser verification;
- confirmation that Laravel and Nginx remain non-conflicting.
