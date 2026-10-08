# VAPT: Clickjacking

## 1. Issue Description/Steps to Recreate Issue

### Issue description

The tested application response does not provide effective anti-framing protection. It is missing a restrictive Content Security Policy using `frame-ancestors` and does not provide a compatible `X-Frame-Options` header.

An attacker can place the application inside a hidden or transparent iframe on another website and position misleading controls above real application controls. An authenticated victim may then click the real control without realizing it.

The VAPT report tested `GET /fsm/application/data`. The current project has authenticated FSM application routes with create, edit, update, delete, report, and export functions. Source review found no application-level anti-framing header configuration.

### Safe reproduction steps

1. Use an approved test environment and test account.
2. Create a harmless local HTML page hosted from a different origin.
3. Add an iframe whose source is the affected application page.
4. Log in with the approved test user.
5. Open the harmless test page.
6. Confirm whether the application renders inside the iframe.
7. Inspect the response headers for CSP `frame-ancestors` and `X-Frame-Options`.
8. Do not place deceptive controls over production actions or test without authorization.

## 2. Impacts Created by Existing Issue

### Security impact

- An attacker can visually hide the real application interface.
- An authenticated user may unintentionally submit, edit, approve, or delete information.
- The action is recorded using the victim's valid session and permissions.
- Authentication and CSRF protection do not fully stop the attack because the victim interacts with the real framed page.

### Business impact

- Unauthorized or incorrect changes to operational records.
- Actions appearing to have been performed intentionally by a legitimate user.
- Investigation, data correction, audit, and recovery costs.
- Loss of user trust and reputational harm.

### Severity

**Medium in the VAPT report.** The attack is simple and requires no attacker account, but the victim must visit the malicious page and interact with it. The report demonstrated framing risk with Low integrity impact; actual impact may be higher for administrative or destructive actions.

## 3. Remediation or Fix Approaches

### Approach A: Block all framing through Laravel

Use this policy when the application does not need iframe embedding:

```http
Content-Security-Policy: frame-ancestors 'none'
X-Frame-Options: DENY
```

1. Create global `SecurityHeaders` middleware.
2. Set both response headers.
3. Register the middleware in `app/Http/Kernel.php`.
4. Apply it to success, redirect, authorization, not-found, and handled error responses.

### Approach B: Allow only same-origin framing

Use this only when a confirmed same-origin feature requires framing:

```http
Content-Security-Policy: frame-ancestors 'self'
X-Frame-Options: SAMEORIGIN
```

### Approach C: Allow exact trusted external origins

1. List only approved HTTPS origins in CSP `frame-ancestors`.
2. Do not use wildcard origins.
3. Do not use obsolete `X-Frame-Options: ALLOW-FROM`.
4. Document the owner and business reason for every allowed origin.
5. Test that unapproved origins remain blocked.

### Approach D: Define clear Laravel and Nginx ownership

Laravel must be the source of truth for the application CSP and anti-framing policy:

- Laravel emits `Content-Security-Policy`.
- Laravel emits the compatible `X-Frame-Options` value.
- Nginx must not add, replace, or conflict with those two headers on Laravel responses.

DevOps must review the effective Nginx configuration to confirm that no `add_header`, proxy rule, inherited server block, or included configuration changes these values.

Nginx may continue to own server-level security headers that must apply independently of application behavior, such as HSTS or `X-Content-Type-Options`, according to the approved infrastructure baseline. Those headers must be documented separately and must not create a second CSP source of truth.

General review of unrelated application actions is outside this Clickjacking ticket. The required scope is anti-framing policy, header ownership, approved framing exceptions, and verification.

## 4. Impact of Remediation to Existing Source Code

### Files expected to change

- new `app/Http/Middleware/SecurityHeaders.php`
- `app/Http/Kernel.php`
- new `config/security_headers.php`
- security-header feature tests
- Nginx configuration review to remove duplicate or conflicting CSP and `X-Frame-Options` behavior
- supporting baseline: `docs/clickjacking-csp-security-headers-baseline.md`

### Expected behavior changes

- Browsers will refuse to display the application inside unapproved iframes.
- Any legitimate portal, dashboard, or integration that embeds the application may stop working until explicitly allowed.
- Browser developer tools may show CSP frame-blocking messages.
- Nginx remains responsible only for separately approved server-level headers.

### Regression risks

- An overly strict policy may break an approved embedding integration.
- Conflicting CSP or `X-Frame-Options` values may produce inconsistent browser behavior.
- Replacing an existing CSP rather than merging directives may weaken or break unrelated security controls.
- Applying headers only to the reported URL may leave other pages exposed.
- Nginx may unintentionally override Laravel headers through an included or inherited configuration block.

## 5. Required Testing (Unit, Integration, Browser, Manual, etc.)

### Unit and feature tests

- Assert the expected CSP header.
- Assert the matching `X-Frame-Options` header.
- Test the reported route and representative public and authenticated HTML routes.
- Test normal, redirect, authorization, not-found, and handled error responses.

### Integration tests

- Confirm Laravel emits one intended CSP and one compatible `X-Frame-Options` value.
- Confirm Nginx does not add, replace, duplicate, or conflict with those headers.
- Confirm existing CSP directives remain present.
- Confirm separately approved Nginx security headers remain present.

### Browser tests

- Try to frame the reported FSM page from an unapproved origin and confirm the browser blocks it.
- Repeat for a small representative set of authenticated and public HTML pages to confirm global middleware coverage.
- If framing is required, confirm only approved origins work.
- Test supported browsers and inspect console messages.

### Manual security tests

- Verify the deployed production headers directly.
- Confirm the application cannot be rendered by an unapproved framing origin.
- Inspect the effective Nginx configuration for conflicting CSP or `X-Frame-Options` rules.
- Re-run the authorized VAPT proof after deployment.

## 6. Acceptance Criteria

- [ ] The business owner confirms whether any legitimate framing is required.
- [ ] CSP contains an approved `frame-ancestors` policy.
- [ ] `X-Frame-Options` matches the CSP policy where appropriate.
- [ ] Laravel is the documented owner of CSP and anti-framing headers.
- [ ] Nginx does not add, replace, duplicate, or conflict with Laravel CSP and `X-Frame-Options`.
- [ ] Separately approved Nginx security headers remain intact.
- [ ] Headers are applied globally to required Laravel HTML responses.
- [ ] Existing CSP directives are preserved.
- [ ] The reported FSM page cannot be framed by an unapproved origin.
- [ ] Representative public and authenticated HTML pages cannot be framed by an unapproved origin.
- [ ] Approved integrations still work, if any exist.
- [ ] Automated browser/header tests pass.
- [ ] Security retesting confirms closure.

## 7. Deployment and Rollback Considerations

1. Inventory legitimate framing before deployment.
2. Test the final policy in staging.
3. Review the effective Nginx configuration and included server blocks.
4. Deploy during a monitored release window.
5. Check browser console errors, response headers, and integration failures.
6. If an approved integration breaks, add only its exact HTTPS origin after review.
7. Do not remove all anti-framing protection as a rollback; move to the narrowest approved policy.

## 8. Relevant Files and References

- `routes/web.php`
- `app/Http/Kernel.php`
- proposed `app/Http/Middleware/SecurityHeaders.php`
- proposed `config/security_headers.php`
- Nginx deployment configuration maintained outside this application repository
- Supporting baseline: `docs/clickjacking-csp-security-headers-baseline.md`
- VAPT report: pages 36-37
