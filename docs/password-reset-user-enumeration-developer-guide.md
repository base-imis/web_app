# Password Reset User Enumeration: Developer Guide

## 1. Goal and scope

This guide covers only `BASEIMIS-47`: preventing the password-reset request page
from revealing whether an account exists.

The public message for every valid email address is:

```text
If an account exists for this email address, a password reset link will be sent.
```

Abuse-prevention controls are owned by their separate Jira ticket and are not
implemented or designed in this guide.

## 2. Files involved

- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `resources/lang/en/passwords.php`
- `tests/Feature/Http/Controllers/ForgotPasswordControllerTest.php`
- `tests/Fixtures/ForgotPassword/forgot_password.json`
- `routes/web.php`, reviewed only to confirm the existing endpoint

Do not edit Laravel files under `vendor/`.

## 3. Required behavior

For registered and unregistered valid email addresses, keep these public details
equivalent:

- HTTP status;
- redirect destination;
- session status message;
- validation-error structure; and
- JSON status and exact response shape.

Only an existing account receives a reset notification. The controller must not
return the password broker's account-dependent result.

Invalid email syntax continues to receive normal validation feedback because that
does not disclose account existence.

## 4. Translation

Define the conditional response in `resources/lang/en/passwords.php`:

```php
'request_received' => 'If an account exists for this email address, a password reset link will be sent.',
```

Do not use wording that says:

- the account was found;
- the address is not registered; or
- an email was definitely sent.

The other supported locale catalogs do not define a password-reset response, so
Laravel uses the English fallback. If a locale-specific password catalog is added
later, its `request_received` value must remain conditional and non-revealing.

## 5. Application controller override

Override the inherited method in
`app/Http/Controllers/Auth/ForgotPasswordController.php`:

```php
public function sendResetLinkEmail(Request $request)
{
    $this->validateEmail($request);

    $email = strtolower(trim((string) $request->input('email')));

    $this->broker()->sendResetLink(['email' => $email]);

    $message = trans('passwords.request_received');

    return $request->wantsJson()
        ? new JsonResponse(['message' => $message], 200)
        : back()->with('status', $message);
}
```

The method intentionally ignores the value returned by `sendResetLink()`. Laravel's
broker still looks up the account and sends a notification only when it finds a
matching user.

## 6. Automated tests

Keep this vulnerability's tests isolated in:

```text
tests/Feature/Http/Controllers/ForgotPasswordControllerTest.php
```

The focused suite must verify:

1. A registered email receives the generic web response.
2. An unregistered valid email receives the same response.
3. Both web responses have the same status, redirect, session message, and empty error state.
4. Registered and unregistered JSON requests return status `200` and the exact same body.
5. Missing, malformed, and non-string email values return the normal `422` validation structure.
6. Exactly one reset notification is sent when one registered and one unregistered address are submitted.
7. Uppercase input for a registered address is normalized before broker lookup.
8. A soft-deleted address receives the generic response and no notification.

Run:

```text
php artisan test --filter=ForgotPasswordControllerTest
```

## 7. Manual verification

In an approved test environment:

1. Submit one registered test address.
2. Submit one unregistered but valid address from the same reset form.
3. Confirm the visible message, HTTP status, redirect, and error presentation are identical.
4. Repeat both requests with `Accept: application/json` and compare the exact bodies.
5. Confirm only the registered test user receives a usable reset email.
6. Submit an invalid address and confirm format validation still appears.

Do not use production user addresses without authorization.

## 8. Deployment and rollback

Deploy the controller, translation, and focused test together. Clear application
caches if translations are cached, then repeat the registered/unregistered smoke
test in the target environment.

If reset delivery fails, correct the mail or broker configuration without restoring
account-specific public wording. A rollback must retain the generic response.

## 9. Acceptance checklist

- [ ] Registered and unregistered valid emails receive the same generic message.
- [ ] Web status, redirect, session status, and error state are equivalent.
- [ ] JSON status and exact response body are equivalent.
- [ ] The broker result is not exposed publicly.
- [ ] Only a registered account receives a reset notification.
- [ ] Invalid email syntax still fails validation.
- [ ] Translation wording is conditional and non-revealing.
- [ ] Tests are isolated from unrelated security findings.
- [ ] No Laravel vendor file is modified.
