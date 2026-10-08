# No Rate Limiting on Login: Impact Analysis

## 1. Finding summary

The application's web login accepts repeated authentication requests without an effective server-side rate limit, progressive delay, or temporary restriction.

This allows an attacker to use an automated tool to try many passwords against one or more accounts. If one attempt uses the correct password, the attacker may gain access to that account.

The VAPT report rates this finding as **Medium** severity. It identifies a **High** possible confidentiality impact because a successful account takeover may expose sensitive information.

## 2. What was confirmed in this project

The following behavior is visible in the current Laravel code:

- The web login is handled by `POST /login` in `routes/web.php`.
- The route does not have login-specific throttle middleware.
- `app/Http/Controllers/Auth/LoginController.php` checks credentials but does not count failed attempts.
- The controller does not add a delay after repeated failures.
- The controller does not temporarily restrict an account or source after repeated failures.
- `app/Http/Requests/LoginRequest.php` validates only that a username and password were supplied.
- The web login accepts either a username or an email address.

The application also has a separate `POST /api/login` endpoint. The general API middleware currently limits unauthenticated API traffic by IP address. However, this is not an authentication-specific, account-aware control and should be tested separately. The VAPT evidence specifically describes the application's web login behavior.

## 3. Simple attack example

An attacker can perform the following actions:

1. Select a known or guessed username.
2. Send a login request with an incorrect password.
3. Immediately send another request with a different password.
4. Repeat the process automatically many times.
5. Gain access if one attempted password is correct.

Without a rate limit, the application processes attempts as quickly as the attacker and server resources allow.

## 4. Main attack scenarios

### 4.1 Brute-force attack

The attacker tries many passwords against one account.

This attack is more likely to succeed when the account uses a short, common, predictable, or previously exposed password.

### 4.2 Credential-stuffing attack

The attacker uses email addresses and passwords leaked from another website.

This is dangerous because users sometimes reuse the same password on several systems. A password leaked elsewhere may therefore work in this application.

### 4.3 Password-spraying attack

The attacker tries a small number of common passwords against many accounts.

This technique can avoid simple controls that only watch one account. Protection must therefore consider both the account identifier and the source of the requests.

### 4.4 Distributed login attack

An attacker can send requests through many IP addresses. An IP-only limit may slow one source while allowing the overall attack to continue.

For this reason, protection should not depend only on the IP address.

## 5. Impact on confidentiality

**Potential impact: High**

If an attacker takes over an account, they may be able to:

- view the account owner's personal information;
- view municipal, customer, operational, or application records;
- download reports or documents available to that user;
- view information available to the user's department;
- access more sensitive information if the compromised account is an administrator.

The exact amount of exposed information depends on the permissions assigned to the compromised account.

## 6. Impact on data integrity

**Report rating: None for this specific test**

The VAPT test demonstrated repeated login attempts. It did not demonstrate that application data was changed.

However, after a successful account takeover, the real integrity impact depends on the account's permissions. A compromised user may be able to:

- create or edit records;
- delete records;
- approve or reject requests;
- change configurations;
- create additional users;
- assign roles or permissions;
- submit actions in the victim's name.

Therefore, integrity impact could become **High** when an administrative or powerful departmental account is compromised, even though the rate-limiting test itself did not prove data modification.

## 7. Impact on availability

**Report rating: None for this specific test**

The report did not demonstrate that the application became unavailable.

At a very high request volume, repeated login attempts could still consume:

- web-server capacity;
- application worker capacity;
- database connections;
- CPU used for password-hash verification;
- logging and storage capacity.

This could slow the login page or other application functions. This is a possible secondary effect and must be verified through controlled performance testing before it is treated as a confirmed availability impact.

## 8. Impact on user accounts

Users may experience:

- unauthorized access to their accounts;
- exposure of information available to them;
- actions performed under their identity;
- forced password resets;
- interruption while the security team investigates;
- loss of confidence in the application.

Administrators and accounts with broad permissions have the highest potential impact.

## 9. Business impact

A successful attack may cause:

- unauthorized disclosure of sensitive information;
- fraudulent or unauthorized application activity;
- loss of trust from users, municipalities, or customers;
- incident investigation and recovery costs;
- operational disruption during password resets and account reviews;
- audit, contractual, or regulatory concerns;
- reputational damage to the organization.

The business impact increases when the compromised account can access a large amount of data or perform administrative actions.

## 10. Risk-rating explanation

| Risk factor | Report value | Simple meaning |
|---|---|---|
| Attack vector | Network | The attack can be performed remotely. |
| Attack complexity | Low | Common automated tools can send the requests. |
| Privileges required | None | The attacker does not need an existing account. |
| User interaction | None | A victim does not need to click or approve anything. |
| Scope | Unchanged | The immediate security impact remains within this application. |
| Confidentiality | High | Successful account access may expose sensitive information. |
| Integrity | None | The test did not demonstrate data changes. |
| Availability | None | The test did not demonstrate service interruption. |
| Overall severity | Medium | Exploitation is easy, but the attacker must still find a valid password. |

## 11. Likelihood and consequence analysis

| Scenario | Likelihood without protection | Possible consequence | Overall concern |
|---|---|---|---|
| Password guessing against a weak account | Medium | Account takeover | High |
| Credential stuffing with leaked passwords | Medium to high | Account takeover and data exposure | High |
| Password spraying across many users | Medium to high | One or more account compromises | High |
| Attack against an administrator | Medium | Broad unauthorized access | Critical business concern |
| Heavy request volume affecting performance | Low to medium | Slow login or application response | Medium, but not confirmed by the report |

These values are qualitative. Actual likelihood depends on password quality, MFA coverage, internet exposure, user count, logging, and attacker interest.

## 12. Combined risk with the credential-disclosure finding

The VAPT report also identifies credentials exposed in a public GitHub repository. The two findings increase the overall authentication risk:

- exposed credentials may give an attacker immediate login information;
- missing rate limiting makes it easier to test other passwords and accounts;
- known usernames and email addresses improve the attacker's targeting;
- privileged accounts can produce a much larger impact after compromise.

These findings should be remediated together as part of one authentication-security review.

## 13. Affected assets to review

The technical team should review:

- the web login route: `POST /login`;
- the API login route: `POST /api/login`;
- privileged and administrative accounts;
- accounts whose usernames or emails are publicly known;
- accounts using reused or weak passwords;
- session and API token handling;
- authentication and application activity logs;
- reverse-proxy, firewall, and load-balancer controls.

## 14. Evidence to collect during remediation

To understand the real impact and later prove the fix, record:

- how many failed attempts are currently accepted;
- whether the response time changes after repeated failures;
- whether controls apply by account, IP address, or both;
- whether the same limit works across multiple application servers;
- whether successful login resets the correct counters;
- whether excessive failures generate logs and alerts;
- whether web and API login endpoints are both protected;
- whether existing evidence shows suspicious historical attempts.

Testing must use approved test accounts and a controlled request rate. Do not test against real user accounts without authorization.

## 15. Impact-analysis conclusion

The immediate weakness is that an unauthenticated attacker can send repeated login attempts without effective restriction. The most important consequence is account takeover followed by unauthorized access to sensitive information.

The report's **Medium** rating is reasonable because missing rate limiting alone does not reveal a password. However, the practical impact can become **High or Critical** when:

- users have weak or reused passwords;
- credentials have already been exposed;
- the target account has administrative permissions;
- MFA is not enabled;
- suspicious login activity is not monitored.

For this project, the absence of throttling on the web login is supported by the current route and controller implementation. The web and API login paths should both be included in the remediation and verification scope.

## 16. Source

This analysis addresses the finding **"No Rate Limiting on Login"** documented on pages 34-36 of the Web Application VAPT report for Innovative Solution Pvt. Ltd.
