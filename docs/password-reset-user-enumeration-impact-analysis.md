# Password Reset User Enumeration: Impact Analysis

## 1. Finding summary

The password-reset request previously returned different public outcomes depending
on whether the submitted email address belonged to an account:

```text
Registered email   -> reset-link success response
Unregistered email -> account-not-found validation response
```

An unauthenticated requester could compare those outcomes and determine which
email addresses were registered. This is user enumeration.

This analysis covers only the disclosure caused by the password-reset response.

## 2. Affected flow

The affected application flow is:

- `GET /password/reset` displays the request form;
- `POST /password/email` processes the submitted address;
- `app/Http/Controllers/Auth/ForgotPasswordController.php` handles the request;
- Laravel's password broker decides internally whether a reset notification can be sent; and
- `resources/lang/en/passwords.php` supplies the public response text.

Laravel's inherited default behavior exposed the broker result by returning a
success response for an existing account and an error response for an unknown
address.

## 3. Security impact

The direct disclosure is confirmation that a specific email address is associated
with an application account. An attacker can use that information to create a
more accurate target list for phishing, password spraying, credential stuffing,
or social engineering.

The finding does not directly disclose:

- passwords;
- reset tokens;
- authenticated sessions;
- full user records; or
- application data available after login.

The VAPT report rates the finding as Medium. Exploitation is remote, simple,
unauthenticated, and requires no action by the targeted user, while the direct
confidentiality impact is limited to account existence.

## 4. Observable differences that matter

The fix must make registered and unregistered valid email requests equivalent in:

- public wording;
- HTTP status;
- redirect destination;
- session status message;
- validation-error structure; and
- JSON status and response shape.

Invalid email syntax may continue to receive normal format-validation feedback,
because that response does not reveal whether an account exists.

## 5. Required remediation behavior

Every valid email address receives this conditional public message:

```text
If an account exists for this email address, a password reset link will be sent.
```

The application still asks the password broker to process the request. The broker
sends a reset notification only when an account exists, but its result is not
returned to the requester.

## 6. Source-code impact

The remediation is intentionally small:

- override `sendResetLinkEmail()` in the application controller;
- add the generic translation used by that controller;
- retain normal email-format validation;
- keep the existing route and password broker; and
- add focused automated tests for equivalent web and JSON responses and notification delivery.

No Laravel vendor file is changed.

## 7. Regression risks

- Returning the broker's status would restore the disclosure.
- Using different web or JSON response structures could preserve an enumeration signal.
- Incorrect broker invocation could prevent registered users from receiving reset links.
- An account-specific translation could reintroduce revealing wording.
- Invalid-format validation must remain distinct from account-existence handling.

## 8. Acceptance criteria

- Registered and unregistered valid emails receive the same public message.
- Both cases have the same HTTP status and redirect destination.
- Both cases have the same session and validation-error structure.
- JSON responses have the same status and exact body shape.
- A registered account receives a reset notification.
- An unregistered address does not produce a reset notification.
- Invalid email syntax still fails normal validation.
- The reset-request flow does not expose the account-not-found translation.
- No file under `vendor/` is modified.

## 9. Source

This analysis addresses **User Enumeration via Password Reset Functionality** on
pages 38–39 of the Web Application VAPT report and Jira ticket `BASEIMIS-47`.
