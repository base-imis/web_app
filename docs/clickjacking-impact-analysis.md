# Clickjacking: Impact Analysis

## 1. Finding summary

Clickjacking is a user-interface deception attack. An attacker places the real application inside a hidden or transparent frame on a malicious website. The attacker then places misleading text, buttons, or images over the real application.

When the victim clicks what appears to be a harmless button, the click is actually delivered to the legitimate application inside the hidden frame.

The VAPT report found that the affected application response did not contain either of these protections:

- a restrictive Content Security Policy using `frame-ancestors`;
- an `X-Frame-Options` response header.

The report tested this URL:

```text
GET /fsm/application/data
```

The current workspace uses an authenticated FSM route group and contains application-management actions. The current source code does not contain an application-level `frame-ancestors` or `X-Frame-Options` configuration. A deployment proxy may add headers separately, but the VAPT response confirms that effective protection was missing on the tested environment.

## 2. Simple attack example

A clickjacking attack can work like this:

1. The victim logs in to the legitimate application.
2. The victim leaves the authenticated session active.
3. The attacker sends the victim a link to a malicious website.
4. The malicious page loads the legitimate application inside a nearly invisible frame.
5. The attacker places a fake button over a real application button.
6. The victim clicks the fake button.
7. The browser sends the click to the real application using the victim's authenticated session.

The attacker does not need to know the victim's password. The attack abuses the session that already exists in the victim's browser.

## 3. Why authentication does not stop it

Authentication protects the page from people who are not logged in. Clickjacking targets a user who is already authenticated.

When the application is loaded in a frame:

- the browser may include the victim's session cookie;
- the application recognizes the victim;
- the victim's permissions remain active;
- the real button can perform the normal action when clicked.

The same-origin policy usually prevents the malicious website from directly reading the framed application's content. However, the attacker may not need to read it. The attacker only needs to position the frame and convince the victim to click.

## 4. Why CSRF protection alone is not sufficient

Cross-site request forgery protection checks whether a state-changing request contains a valid security token.

In a clickjacking attack, the victim is clicking the real application page. The real form or button may already contain the correct CSRF token. Therefore, the application can accept the action even though the victim was visually deceived.

CSRF protection should remain enabled, but anti-framing headers are also required.

## 5. Confirmed impact from the report

The report confirms that:

- the affected page could be embedded in an attacker-controlled frame;
- no authentication is required to prepare the attack page;
- the attack is technically simple;
- the victim must visit the attacker's page and interact with it;
- the attacker may cause an authenticated user to perform an unintended action.

The report rates the finding as **Medium** severity with **Low integrity impact**.

## 6. Potential actions an attacker may target

The exact impact depends on which pages and controls can be framed and what permissions the victim has.

Possible targets include:

- opening or selecting application records;
- submitting a form;
- approving or rejecting an item;
- editing a record;
- deleting a record;
- changing a setting;
- exporting information;
- triggering another action available to the logged-in user.

The VAPT report demonstrates framing risk. Each sensitive action must be tested before claiming that a specific transaction can be completed through clickjacking.

## 7. Confidentiality impact

**Report rating: None**

The malicious page normally cannot directly read the contents of the framed application because browsers enforce the same-origin policy.

For that reason, the report does not claim direct information disclosure.

Possible visual data exposure may still require separate testing, especially if the attacker can trick the victim into revealing information through predictable interface behavior. This was not demonstrated by the report.

## 8. Integrity impact

**Report rating: Low**

The main confirmed risk is an unintended action performed with the victim's existing permissions.

The real integrity impact depends on the target:

- clicking a navigation button has little impact;
- changing an ordinary record may have moderate impact;
- approving, deleting, or changing a sensitive record may have high impact;
- targeting an administrator may affect users, roles, settings, or large amounts of data.

The report assigns Low integrity impact because it did not demonstrate a high-impact state change. The application team should review all sensitive controls before deciding whether the practical impact is higher.

## 9. Availability impact

**Report rating: None**

The report did not demonstrate interruption of the service.

An availability impact would require an accessible action capable of disabling, deleting, or disrupting important functions. That scenario was not proven by the reported test.

## 10. Impact on users

An affected user may:

- unknowingly perform an application action;
- modify or delete information unintentionally;
- approve a transaction they did not intend to approve;
- appear responsible for an action initiated by the attacker;
- lose confidence in the application after an incident.

The risk is higher for users with broad permissions.

## 11. Business impact

Possible business consequences include:

- unauthorized or incorrect changes to operational records;
- approval or submission of unintended transactions;
- time spent investigating actions recorded under a legitimate user;
- difficulty proving whether an action was intentional;
- recovery and data-correction work;
- loss of trust from users, municipalities, or customers;
- audit, contractual, or reputational concerns.

## 12. Risk-rating explanation

| Risk factor | Report value | Simple meaning |
|---|---|---|
| Attack vector | Network | The attack page can be delivered over the internet. |
| Attack complexity | Low | Building a page with a hidden frame is technically simple. |
| Privileges required | None | The attacker does not need an application account. |
| User interaction | Required | The victim must visit the malicious page and click. |
| Scope | Unchanged | The action occurs inside the affected application. |
| Confidentiality | None | Direct reading of application data was not demonstrated. |
| Integrity | Low | The victim may be tricked into an unintended action. |
| Availability | None | Service interruption was not demonstrated. |
| Overall severity | Medium | Exploitation is simple, but it depends on an authenticated victim clicking. |

## 13. Factors that increase the risk

The practical risk becomes higher when:

- the victim is already logged in;
- login sessions remain active for a long time;
- the target user is an administrator;
- sensitive actions require only one click;
- destructive actions do not require confirmation;
- confirmation screens are predictable and can also be framed;
- MFA is required only during login and not for sensitive actions;
- the application allows broad cross-origin framing;
- sensitive pages use stable layouts that are easy to align beneath fake controls.

## 14. Factors that reduce the risk

The risk is reduced when:

- CSP blocks all unapproved framing;
- `X-Frame-Options` is present as compatibility protection;
- sensitive actions require clear confirmation;
- high-risk actions require password or MFA re-verification;
- sessions expire appropriately;
- users have only the permissions they need;
- unusual sensitive actions generate alerts.

These controls complement anti-framing headers. They do not replace them.

## 15. Project-specific scope to review

At minimum, the technical team should review:

- the report's affected FSM application page;
- FSM application list and detail pages;
- create, edit, update, and delete actions;
- approval or workflow actions;
- user and role administration pages;
- configuration pages;
- export and report pages;
- authentication and password-management pages;
- error pages and redirects.

Because anti-clickjacking protection is normally applied globally, testing should not stop after checking only the reported URL.

## 16. Impact-analysis conclusion

The application can be displayed inside a hostile page because effective anti-framing response headers are missing. This lets an attacker visually hide the legitimate interface and attempt to make an authenticated victim click real controls.

The report's Medium rating is reasonable for the demonstrated behavior because the attack requires user interaction and did not prove direct data disclosure, major data modification, or service interruption.

The practical impact may become higher if administrative, approval, edit, or delete functions can be triggered through a framed page. Those actions should be tested in an authorized test environment after global anti-framing protection is implemented.

## 17. Source

This analysis addresses the finding **"Clickjacking"** documented on pages 36-37 of the Web Application VAPT report for Innovative Solution Pvt. Ltd.
