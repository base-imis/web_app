# VAPT: User Enumeration Through Password Reset

## 1. Issue Description/Steps to Recreate Issue

### Issue description

The password-reset function returns different public responses depending on whether an email address is registered.

Current behavior:

```text
Registered email   -> reset-link success message
Unregistered email -> account-not-found error
```

An unauthenticated person can compare these responses and determine which email addresses belong to application users. This is called user enumeration or account enumeration.

The relevant application flow is:

- `GET /password/reset` displays the request form.
- `POST /password/email` processes the submitted email address.
- `ForgotPasswordController` uses Laravel UI's `SendsPasswordResetEmails` trait.
- The default trait returns separate success and failure responses.
- `resources/lang/en/passwords.php` contains the account-not-found message.

### Safe steps to recreate the issue

1. Use an approved registered test email address.
2. Submit it through the password-reset form.
3. Record the displayed message, HTTP status, redirect destination, and response structure.
4. Use an approved unregistered email address with valid syntax.
5. Submit it through the same form.
6. Compare the two responses.
7. Confirm that one response indicates that the account does not exist.
8. Repeat with `Accept: application/json` if the endpoint supports JSON responses.
9. Do not use real user addresses without authorization.

## 2. Impacts Created by Existing Issue

### Direct impact

- An attacker can confirm whether an email address is registered.
- The attacker can create a more accurate list of application users.
- Account membership may itself be private information.

### Follow-on impact

A confirmed account list can support:

- targeted phishing;
- credential stuffing;
- password spraying;
- social engineering against users or support staff;
- convincing fake password-reset emails.

The vulnerability does not directly reveal a password, reset token, session, or full user record.

### Business impact

- Increased risk of targeted account attacks.
- More convincing phishing and impersonation attempts.
- Privacy concerns when application membership is sensitive.
- Incident investigation and user-support costs.
- Audit, contractual, or reputational concerns.

### Severity

**Medium in the VAPT report.** The direct confidentiality impact is Low because the response reveals only account existence. Exploitation is still simple, remote, unauthenticated, and requires no action from the targeted user.

## 3. Remediation or Fix Approaches

### Required approach: one generic response

Return the same public message for every validly formatted email address:

```text
If an account exists for this email address, a password reset link will be sent.
```

Required behavior:

1. A registered email receives the generic message.
2. An unregistered email receives the same generic message.
3. Only the registered user receives an actual reset email.
4. The browser is not told whether an email was sent.
5. Both cases use the same HTTP status.
6. Both cases use the same redirect destination.
7. Both cases use the same session message and error structure.
8. JSON requests receive the same status and response shape.

Invalid email syntax may still receive normal format-validation feedback because that does not reveal whether an account exists.

### Laravel implementation

Add one generic translation key to `resources/lang/en/passwords.php` and every other supported language:

```php
'request_received' => 'If an account exists for this email address, a password reset link will be sent.',
```

Override `sendResetLinkEmail()` inside `app/Http/Controllers/Auth/ForgotPasswordController.php`. Do not edit the framework trait under `vendor/`.

Example:

```php
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
    ]);

    $email = strtolower(trim((string) $request->input('email')));

    Password::broker()->sendResetLink([
        'email' => $email,
    ]);

    $message = trans('passwords.request_received');

    if ($request->wantsJson()) {
        return new JsonResponse([
            'message' => $message,
        ], 200);
    }

    return back()->with('status', $message);
}
```

The broker still decides internally whether a reset email can be sent. The controller intentionally does not expose that result to the requester.

### Translation review

Review every supported language and remove wording that reveals:

- that an account was found;
- that an account was not found;
- that the submitted address is registered;
- that a reset email was definitely sent.

All translations must express the same conditional message.

## 4. Impact of Remediation to Existing Source Code

### Files expected to change

- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `resources/lang/en/passwords.php`
- equivalent password translation files for every supported language
- password-reset feature tests

No route redesign is required for this fix. The existing form and password broker can remain in use.

### Expected behavior changes

- Unknown addresses no longer display an account-not-found error.
- Registered and unregistered valid addresses display the same success-style message.
- Registered users still receive a password-reset email.
- Unregistered addresses do not receive an email.
- Web and JSON clients receive equivalent generic behavior.

### Regression risks

- Incorrect controller logic may stop reset emails for registered users.
- Editing the framework trait under `vendor/` would be lost during dependency updates.
- One translation may accidentally retain account-revealing wording.
- Web and JSON responses may remain different if only one response path is changed.
- Tests that expect the old account-not-found error must be updated.

## 5. Required Testing (Unit, Integration, Browser, Manual, etc.)

### Unit tests

- Confirm the generic translation key exists.
- Confirm the controller normalizes email input consistently.
- Confirm no public response uses the account-not-found translation for valid email syntax.

### Integration tests

- Submit a registered test email and verify the generic response.
- Submit an unregistered test email and verify the same generic response.
- Confirm both responses have the same HTTP status.
- Confirm both responses have the same redirect destination.
- Confirm both responses have the same session message and error structure.
- Confirm a registered user receives a reset notification.
- Confirm an unregistered address receives no notification.
- Confirm invalid email syntax still fails normal validation.
- Confirm the generated reset link for a registered user remains valid.

### JSON/API-style tests

- Send both requests with `Accept: application/json`.
- Confirm both return HTTP `200`.
- Confirm both return the same message and JSON structure.
- Confirm neither response indicates whether an account exists.

### Browser tests

- Submit registered and unregistered test addresses through the visible form.
- Confirm the displayed message is identical.
- Test every supported language.
- Confirm the registered user can complete the password-reset process.

### Manual security tests

- Compare the message, status, redirect, response size, and visible behavior.
- Search translations for account-not-found wording exposed by this flow.
- Re-run the authorized VAPT proof with test accounts.
- Confirm logs do not expose reset tokens or passwords.

## 6. Acceptance Criteria

- [ ] Registered and unregistered valid emails receive the same public message.
- [ ] Both cases use the same HTTP status and redirect.
- [ ] Both cases use the same session and error structure.
- [ ] JSON behavior is consistent.
- [ ] The public account-not-found message is removed from the reset-request flow.
- [ ] Registered users still receive valid reset links.
- [ ] Unregistered addresses receive no reset email.
- [ ] Invalid email syntax still receives format-validation feedback.
- [ ] Every supported language uses generic conditional wording.
- [ ] No framework file under `vendor/` is modified.
- [ ] Unit, integration, JSON, browser, and manual tests pass.
- [ ] Security retesting confirms closure.

## 7. Deployment and Rollback Considerations

1. Deploy the controller and translation changes together.
2. Clear or rebuild Laravel caches if translations or configuration are cached.
3. Test a registered and unregistered test address after deployment.
4. Confirm email delivery still works for registered users.
5. Monitor password-reset errors and mail failures.
6. If email delivery fails, correct the broker or mail configuration while retaining the generic public message.
7. Do not restore account-not-found wording during rollback.

## 8. Relevant Files and References

- `routes/web.php`
- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `resources/views/auth/passwords/email.blade.php`
- `resources/lang/en/passwords.php`
- Laravel UI `SendsPasswordResetEmails` trait, for reference only
- VAPT report: pages 38-39

## 9. Implementation and Verification Record (2026-09-04)

Implemented in application code by overriding `sendResetLinkEmail()` in
`app/Http/Controllers/Auth/ForgotPasswordController.php`. The controller validates
and normalizes the email, asks Laravel's password broker to send the link, deliberately
ignores the broker's account-dependent result, and returns
`passwords.request_received` for every valid email address.

The public message is:

```text
If an account exists for this email address, a password reset link will be sent.
```

Verification covers:

- identical HTTP status, redirect destination, session status, and empty validation-error state for registered and unregistered valid web requests;
- identical HTTP status and exact JSON body for registered and unregistered valid JSON requests;
- normal `422` email-format validation for invalid JSON requests;
- exactly one reset notification when one registered and one unregistered address are submitted;
- the active password translation catalog and the other locale JSON catalogs contain no alternate account-existence response used by this request flow; and
- no Laravel vendor file is changed.

Automated result:

```text
php artisan test --filter=ForgotPasswordControllerTest
Tests: 10 passed
```
