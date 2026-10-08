# Clickjacking: Developer Remediation Guide

## 1. Goal

Prevent unauthorized websites from displaying this application inside an HTML frame.

The preferred result is:

```http
Content-Security-Policy: frame-ancestors 'none'
X-Frame-Options: DENY
```

Use this policy when the application does not need to be embedded anywhere.

If legitimate same-site framing is required, use:

```http
Content-Security-Policy: frame-ancestors 'self'
X-Frame-Options: SAMEORIGIN
```

Do not allow framing until the business owner confirms that it is required.

## 2. Important decision before coding

Ask one question:

```text
Does any approved website need to display this application inside an iframe?
```

Choose one policy:

| Business requirement | CSP policy | Compatibility header |
|---|---|---|
| No framing is required | `frame-ancestors 'none'` | `X-Frame-Options: DENY` |
| Only this same origin may frame pages | `frame-ancestors 'self'` | `X-Frame-Options: SAMEORIGIN` |
| Specific external origins must frame pages | List exact HTTPS origins in `frame-ancestors` | Do not use obsolete `ALLOW-FROM`; verify compatibility carefully |

For the current application, start with **no framing** unless a real integration is identified.

## 3. Why both headers are recommended

`Content-Security-Policy` with `frame-ancestors` is the modern and flexible protection.

`X-Frame-Options` provides compatibility protection for older clients.

When both are present, configure them to express the same rule:

- CSP `'none'` with `DENY`;
- CSP `'self'` with `SAMEORIGIN`.

Do not send conflicting policies.

## 4. Recommended Laravel implementation

### Step 1: Create security-header middleware

Create this file:

```text
app/Http/Middleware/SecurityHeaders.php
```

Use the following implementation for an application that must never be framed:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'none'"
        );

        $response->headers->set('X-Frame-Options', 'DENY');

        return $response;
    }
}
```

This protection is delivered as an HTTP response header. Do not place `frame-ancestors` in an HTML `<meta>` tag because browsers do not enforce that directive from a meta tag.

**Expected result:** Laravel can add the same anti-framing headers to every response.

### Step 2: Register the middleware globally

Open:

```text
app/Http/Kernel.php
```

Add the middleware to the global `$middleware` list:

```php
protected $middleware = [
    // Existing middleware...
    \App\Http\Middleware\SecurityHeaders::class,
];
```

Global registration is recommended because clickjacking protection should cover more than the one URL identified by the VAPT test.

**Expected result:** Normal pages, authentication pages, FSM pages, redirects, and error responses receive the headers when they pass through Laravel.

### Step 3: Preserve an existing CSP if one is introduced

The current project scan did not find an application-level CSP. If another component later adds a CSP, do not overwrite it accidentally.

There should normally be one clear CSP header containing all required directives, for example:

```http
Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; object-src 'none'
```

The developer must merge `frame-ancestors` into the approved policy rather than replacing unrelated directives.

**Expected result:** Clickjacking protection does not break or weaken other CSP protections.

### Step 4: Handle legitimate framing safely

If the application must be framed by the same origin, use:

```php
$response->headers->set(
    'Content-Security-Policy',
    "frame-ancestors 'self'"
);

$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
```

If a specific external system must frame the application, list only its exact HTTPS origin:

```http
Content-Security-Policy: frame-ancestors 'self' https://trusted.example.com
```

Rules for trusted origins:

- use exact HTTPS origins;
- do not use `*`;
- do not trust every subdomain unless necessary;
- document the owner and reason for each allowed origin;
- review allowed origins regularly;
- remove an origin when the integration ends.

`X-Frame-Options: ALLOW-FROM` is obsolete and is not a reliable solution for allowing one external site.

**Expected result:** Only explicitly approved origins can embed the application.

## 5. Optional web-server implementation

Headers may also be configured at the reverse proxy or web server. Choose one controlled source of truth and avoid duplicate or conflicting headers.

### Apache example

If Apache `mod_headers` is enabled:

```apache
Header always set Content-Security-Policy "frame-ancestors 'none'"
Header always set X-Frame-Options "DENY"
```

### Nginx example

```nginx
add_header Content-Security-Policy "frame-ancestors 'none'" always;
add_header X-Frame-Options "DENY" always;
```

Server-level configuration can also protect static HTML and responses that do not pass through Laravel.

If the application middleware and web server both set these headers, verify that the final response is not duplicated or contradictory.

## 6. Do not rely on these incomplete fixes

The following controls are useful but do not fix clickjacking by themselves:

- CSRF tokens;
- SameSite cookies;
- authentication middleware;
- JavaScript that tries to break out of frames;
- hiding sensitive buttons with CSS;
- checking the `Referer` header;
- adding a confirmation message without blocking framing.

Browser-enforced response headers are the primary control.

## 7. Review sensitive actions

Anti-framing headers should be the first fix. Also review sensitive actions such as:

- creating or editing FSM applications;
- deleting records;
- approving or rejecting work;
- changing users, roles, or permissions;
- changing configuration;
- exporting sensitive information.

For high-risk actions, consider:

- a clear confirmation screen;
- password or MFA re-verification;
- short authorization windows;
- audit logging;
- least-privilege permissions.

These are defense-in-depth controls. They do not replace the headers.

## 8. Automated test examples

Create a feature test such as:

```text
tests/Feature/SecurityHeadersTest.php
```

Example for a no-framing policy:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_home_response_blocks_framing(): void
    {
        $response = $this->get('/');

        $response->assertHeader(
            'Content-Security-Policy',
            "frame-ancestors 'none'"
        );

        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_login_response_blocks_framing(): void
    {
        $response = $this->get('/login');

        $response->assertHeader(
            'Content-Security-Policy',
            "frame-ancestors 'none'"
        );

        $response->assertHeader('X-Frame-Options', 'DENY');
    }
}
```

Add authenticated tests for the FSM page using a test user with the required permissions.

**Expected result:** CI fails if the headers are removed later.

## 9. Manual verification steps

Perform these checks in an approved test environment.

### Test 1: Check the affected response headers

1. Log in with an approved test account.
2. Open the affected FSM page.
3. Open browser developer tools.
4. Select the page request in the Network panel.
5. Confirm the response contains the intended CSP header.
6. Confirm the response contains the intended `X-Frame-Options` header.

### Test 2: Use a harmless framing test page

Create a local test page on a different origin containing:

```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Authorized framing test</title>
</head>
<body>
    <iframe
        src="https://test-baseimis.innovativesolution.com.np/fsm/application/data"
        width="1200"
        height="800">
    </iframe>
</body>
</html>
```

Open the test page and confirm the browser refuses to display the application in the frame. The browser console should report that framing was blocked by CSP or `X-Frame-Options`.

Do not publish the test page or use it against environments without authorization.

### Test 3: Check different response types

Verify headers on:

- a normal `200` response;
- a redirect response;
- a `401` or login-required response;
- a `403` response;
- a `404` response;
- an application error response in the test environment.

### Test 4: Check important pages

Test at least:

- the reported FSM page;
- the actual current FSM application route;
- login and password-reset pages;
- application create and edit pages;
- user and role administration;
- configuration pages;
- reports and exports that render in a browser.

### Test 5: Check intended integrations

If an approved origin must frame the application:

1. Confirm the approved origin works.
2. Confirm an unapproved origin is blocked.
3. Confirm HTTP origins are not allowed when HTTPS is required.
4. Confirm an attacker-controlled subdomain is not included accidentally.

## 10. Deployment steps

1. Confirm whether any legitimate framing is required.
2. Select `'none'`, `'self'`, or an exact allowlist.
3. Add and register the middleware.
4. Add automated header tests.
5. Test all important pages in the test environment.
6. Verify integrations, dashboards, or portals are not broken.
7. Deploy during a monitored release window.
8. Check real production response headers.
9. Review browser console and application errors.
10. Ask the security tester to repeat the clickjacking test.

## 11. Common mistakes to avoid

- Protecting only the one URL listed in the report.
- Adding `frame-ancestors` inside an HTML meta tag.
- Using only JavaScript frame-busting code.
- Using the obsolete `X-Frame-Options: ALLOW-FROM` directive.
- Allowing all origins with `*`.
- Sending CSP `'none'` together with `X-Frame-Options: SAMEORIGIN`.
- Overwriting an existing CSP and breaking its other directives.
- Setting headers only on successful `200` responses.
- Assuming CSRF protection stops clickjacking.
- Forgetting pages served directly by the web server or another service.
- Testing only while logged out.

## 12. Completion checklist

The developer work is complete when:

- [ ] The business owner has confirmed whether framing is required.
- [ ] CSP includes an approved `frame-ancestors` policy.
- [ ] `X-Frame-Options` matches the CSP policy where appropriate.
- [ ] The headers are applied globally or to every required page.
- [ ] Existing CSP directives have been preserved.
- [ ] The reported FSM page cannot be framed by an unapproved origin.
- [ ] Other sensitive pages cannot be framed by an unapproved origin.
- [ ] Headers are present on success, redirect, authorization, not-found, and error responses.
- [ ] Automated tests verify the headers.
- [ ] Approved framing integrations still work, if any exist.
- [ ] Sensitive actions have confirmation and audit controls where appropriate.
- [ ] Production headers have been verified after deployment.
- [ ] The security tester has retested and closed the finding.

## 13. Final expected behavior

Before the fix:

```text
Malicious website -> embeds application -> application is displayed
```

After the fix:

```text
Malicious website -> tries to embed application -> browser blocks the frame
```

The browser must receive the protection in the application's HTTP response headers. A visual or browser-only workaround inside the page is not sufficient.
