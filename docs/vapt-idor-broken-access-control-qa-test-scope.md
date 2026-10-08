# QA Test Scope & Verification Guide: Role & Permission (IDOR) Testing

**Document Reference:** `QA-SCOPE-VAPT-IDOR`  
**Date:** September 2026  
**Target Audience:** QA Engineers, Manual Testers, Release Managers  
**Objective:** Verify that users cannot access data or screens they do not have permission to view, even by typing web links (URLs) directly.  

---

## 1. What is this Testing About? (Simple Explanation)

In simple terms, **IDOR (Insecure Direct Object Reference)** is like having a hotel room keycard:
* When you are given Key #101, you should **only** be able to unlock Room 101.
* If you walk up to Room 102, 103, or the Manager’s Office and turn the door handle, **it must remain locked**.
* If the door opens just because you walked up to it, that is an **IDOR security flaw**.

In our application:
* A **Guest** or unauthorized user should **never** be able to see Admin profile details, citizen phone numbers, or property tax lists just by typing numbers into the browser address bar.
* **Service Provider A** should **never** be able to see **Service Provider B's** emptying orders or customer details.

---

## 2. What QA Needs to Test (and What to Skip)

To save time and release quickly, QA does **not** need to test the entire application. Follow this 3-tier priority:

| Priority Tier | Modules / Endpoints | QA Action Required |
| :--- | :--- | :--- |
| **Tier 1 (Mandatory - 100% Focus)** | 1. User Profiles (`/auth/users/{id}`)<br>2. Emptying Records (`/fsm/emptying/{id}`)<br>3. Application Records (`/fsm/application/{id}`)<br>4. Tax & Utility Data (`/tax-payment/data`, `/watersupply-payment/data`) | **Full test required.** Execute all 6 test cases below. |
| **Tier 2 (Quick Spot-Check - 10 mins)** | 1. Building Surveys (`/building-info/building-surveys/data`)<br>2. Employee Info (`/fsm/employee-infos/data`) | **Quick spot-check.** Paste the data link as Guest and verify it is blocked. |
| **Tier 3 (Excluded - Do Not Test Now)** | System settings, dropdown options, ward lists, language switchers. | **Skip.** No citizen personal data or tenant separation issues here. |

---

## 3. Required Test Accounts

Ensure you have credentials for the following 4 accounts before beginning:

1. **Account 1 — Super Admin:** Municipal administrator (e.g., `superadmin@gmail.com`).
2. **Account 2 — Company A (Service Provider 1):** Operator/Admin belonging to Service Provider A.
3. **Account 3 — Company B (Service Provider 2):** Operator/Admin belonging to a different Service Provider B.
4. **Account 4 — Guest / Low-Privilege User:** Account with minimal/guest rights.

---

## 4. Step-by-Step QA Test Scenarios

### Test Scenario 1: Guest Access to Admin User Profile
* **Goal:** Verify that a Guest cannot view administrator profile details.
* **Role:** Log in as **Account 4 (Guest)**.
* **Action:**
  1. Open a new tab in your browser.
  2. Paste this into the URL address bar:
     ```text
     http://<your-app-url>/auth/users/1
     ```
     *(Also try `/auth/users/2`)*
  3. Press Enter.
* **Expected Result (PASS):**
  - The page displays **"403 Forbidden"** or **"Unauthorized Action"**, or redirects back to the dashboard.
* **Failure (FAIL):**
  - The page opens and displays the administrator's Full Name, Email, Username, or Role.

---

### Test Scenario 2: Guest Access to Citizen Emptying Records
* **Goal:** Verify that a Guest cannot read citizen phone numbers or emptying requests.
* **Role:** Log in as **Account 4 (Guest)**.
* **Action:**
  1. Paste this into the browser URL address bar:
     ```text
     http://<your-app-url>/fsm/emptying/1
     ```
     *(Substitute `1` with any known existing emptying ID number)*
  2. Press Enter.
* **Expected Result (PASS):**
  - The page displays **"403 Forbidden"** or redirects.
* **Failure (FAIL):**
  - The page opens and displays customer details (citizen name, contact number, septic tank details).

---

### Test Scenario 3: Cross-Company Access (Company A vs Company B)
* **Goal:** Verify that Service Provider A cannot view Service Provider B's records.
* **Preparation:**
  1. As Super Admin or Company B, find an Emptying ID that belongs to **Company B** (for example: ID `#75`).
* **Role:** Log in as **Account 2 (Company A)**.
* **Action:**
  1. As Company A, try to directly access Company B's record:
     ```text
     http://<your-app-url>/fsm/emptying/75
     ```
     *(and try `/fsm/emptying/75/history`)*
  2. Press Enter.
* **Expected Result (PASS):**
  - Access is denied (**403 Forbidden**). Company A cannot see Company B's customer or vehicle data.
* **Failure (FAIL):**
  - Company A can view Company B's job details, customer contact, and vehicle assignment.

---

### Test Scenario 4: Direct Data Table Bypass (Tax & Utility Data)
* **Goal:** Verify that unauthorized users cannot download raw citizen financial data.
* **Role:** Log in as **Account 4 (Guest)** or **Account 2 (Company A)**.
* **Action:**
  1. In the browser address bar, paste each of the following URLs one by one:
     - `http://<your-app-url>/tax-payment/data`
     - `http://<your-app-url>/watersupply-payment/data`
     - `http://<your-app-url>/swm-payment/data`
  2. Press Enter.
* **Expected Result (PASS):**
  - Each URL returns **403 Forbidden** or redirects to dashboard with an error.
* **Failure (FAIL):**
  - The browser displays raw data (JSON text) containing citizen names, tax codes, due amounts, or phone numbers.

---

### Test Scenario 5: Guest Access to Applications (Pilot Module)
* **Goal:** Verify that a Guest cannot view citizen sanitation applications.
* **Role:** Log in as **Account 4 (Guest)**.
* **Action:**
  1. Paste this into the URL bar:
     ```text
     http://<your-app-url>/fsm/application/1
     ```
  2. Press Enter.
* **Expected Result (PASS):**
  - Access is denied (**403 Forbidden**).
* **Failure (FAIL):**
  - The page loads showing applicant name, contact info, and building coordinates.

---

### Test Scenario 6: Normal Operation for Legitimate Admin (Sanity Test)
* **Goal:** Ensure the security fixes did not accidentally lock out real administrators.
* **Role:** Log in as **Account 1 (Super Admin)**.
* **Action:**
  1. Click through the normal menu to open **Users**, **Emptying**, **Applications**, and **Property Tax Collection**.
* **Expected Result (PASS):**
  - All screens open properly with HTTP **200 OK**. Buttons and data display normally.
* **Failure (FAIL):**
  - Legitimate administrators get blocked with unexpected 403 errors.

---

## 5. QA Verification Checklist (Sign-off Sheet)

| ID | Test Scenario | Role Used | Result | Notes |
| :--- | :--- | :--- | :---: | :--- |
| **TC-01** | Direct URL to `/auth/users/{id}` as Guest | Guest | `[ ] PASS  [ ] FAIL` | |
| **TC-02** | Direct URL to `/fsm/emptying/{id}` as Guest | Guest | `[ ] PASS  [ ] FAIL` | |
| **TC-03** | Company A opening Company B's Emptying record | Company A | `[ ] PASS  [ ] FAIL` | |
| **TC-04** | Direct access to `/tax-payment/data` | Guest | `[ ] PASS  [ ] FAIL` | |
| **TC-05** | Direct access to `/watersupply-payment/data` | Guest | `[ ] PASS  [ ] FAIL` | |
| **TC-06** | Direct access to `/swm-payment/data` | Guest | `[ ] PASS  [ ] FAIL` | |
| **TC-07** | Direct URL to `/fsm/application/{id}` as Guest | Guest | `[ ] PASS  [ ] FAIL` | |
| **TC-08** | Normal navigation as Super Admin | Super Admin | `[ ] PASS  [ ] FAIL` | |

---

## 6. Defect Reporting Template for QA

If any test results in a **FAIL**, log the ticket using this template:

```text
Title: [IDOR / Access Control] <Role> can access unauthorized data at <Endpoint>
Severity: High
Component: Access Control / Security

Environment:
- URL: http://<staging-url>
- Logged-in Account: <Username / Role, e.g. Guest>

Steps to Reproduce:
1. Log in as <Role>.
2. In the browser address bar, navigate to: <Exact URL with ID>.
3. Observe page response.

Actual Result:
Page loaded successfully (200 OK) and displayed sensitive data:
- <e.g., Citizen phone number / Admin email address>

Expected Result:
HTTP 403 Forbidden page or redirect with message "You are not authorized to view this resource".
```
