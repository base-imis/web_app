# Technical & Impact Analysis: Insecure Direct Object Reference (IDOR) & Broken Access Control

**Document Reference:** `VAPT-FINDING-P24-IDOR`  
**Date:** September 2026  
**Target Audience:** Backend Developers, Software Architects, Security Engineers  
**Source Reference:** VAPT Report Pages 24–28 (*Vulnerability Name: IDOR – Guest User access to sensitive information*)  
**Status:** In Progress / Remediation Specification  

---

## 1. Executive & Technical Summary

During the Web Application Vulnerability Assessment and Penetration Testing (VAPT), multiple endpoints were identified as vulnerable to **Insecure Direct Object References (IDOR)** and **Broken Access Control (OWASP Top 10 - A01:2021)**.

Specifically, the application failed to enforce:
1. **Vertical Authorization (Role-Based Access Control):** Authenticated users with low privileges (such as `Guest`) can directly query administrative and operational detail endpoints.
2. **Horizontal Authorization (Multi-Tenant Isolation):** Service providers can query and manipulate records belonging to competing service providers by simply altering numerical IDs.
3. **Internal Data Endpoint Protection:** Direct JSON data endpoints (`/data`, `/getData`) consumed by AJAX DataTables lacked permission middleware, allowing unauthorized direct scraping of raw records.

---

## 2. Root Cause Analysis in the Codebase

### A. Missing Method Protection in [`UserController.php`](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Auth/UserController.php)
* **Location:** [`app/Http/Controllers/Auth/UserController.php` (Lines 31–41)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Auth/UserController.php#L31-L41)
* **Root Cause:**
  ```php
  public function __construct(UserService $userService)
  {
      $this->middleware('auth');
      $this->middleware('permission:List Users', ['only' => ['index','getData']]);
      $this->middleware('permission:Add User', ['only' => ['create','store']]);
      $this->middleware('permission:Edit User', ['only' => ['edit','update']]);
      $this->middleware('permission:Delete User', ['only' => ['destroy']]);
      $this->middleware('permission:Export Users to CSV', ['only' => ['export']]);
      $this->middleware('permission:View User Login Activity', ['only' => ['getLoginActivity']]);
      $this->userService = $userService;
  }
  ```
  The **`show`** method (`GET /auth/users/{id}`) was **completely omitted** from the permission middleware.
* **Exploitation Mechanism:**
  Any authenticated user (including a `Guest`) can issue `GET /auth/users/1` or `/auth/users/2` and receive the full user profile including username, email address, assigned roles, user type, and account status.

---

### B. Missing Permission Middleware in [`EmptyingController.php`](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Fsm/EmptyingController.php)
* **Location:** [`app/Http/Controllers/Fsm/EmptyingController.php` (Lines 30–33)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Fsm/EmptyingController.php#L30-L33)
* **Root Cause:**
  ```php
  public function __construct(EmptyingService $emptyingService)
  {
      $this->emptyingService = $emptyingService;
  }
  ```
  `EmptyingController` has **zero permission middleware** declared in its constructor.
* **Exploitation Mechanism:**
  - `show($id)` simply executes `Emptying::find($id)` without verifying:
    1. Does the requester hold `View Emptying` permissions?
    2. If the requester belongs to Service Provider A (`auth()->user()->service_provider_id`), does the emptying record belong to Service Provider A?
  - Competing service providers or guest users can enumerate `/fsm/emptying/{id}` to access customer personal information, phone numbers, and service locations.

---

### C. Missing Permission Middleware in [`ApplicationController.php`](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Fsm/ApplicationController.php)
* **Location:** [`app/Http/Controllers/Fsm/ApplicationController.php` (Lines 26–29)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Fsm/ApplicationController.php#L26-L29)
* **Root Cause:**
  ```php
  public function __construct(ApplicationService $applicationService)
  {
      $this->applicationService = $applicationService;
  }
  ```
  No middleware is declared. While `index()` uses UI-level checks (`can('Add Application')`) to hide buttons, the underlying resource routes (`show`, `history`, `pdf`, `destroy`) remain unprotected.

---

### D. Unprotected Data Endpoints in Payment & Info Controllers
* **Affected Controllers:**
  - [`TaxPaymentController.php` (Line 31)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/TaxPaymentInfo/TaxPaymentController.php#L31)
  - [`WaterSupplyController.php` (Line 30)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/WaterSupplyInfo/WaterSupplyController.php#L30)
  - [`SwmServicePaymentController.php` (Line 33)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/SwmPaymentInfo/SwmServicePaymentController.php#L33)
  - [`BuildingSurveyController.php` (Line 41)](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/BuildingInfo/BuildingSurveyController.php#L41)
* **Root Cause:**
  The controllers protected the `index` view action but forgot to include the DataTables AJAX data retrieval action (`getData`):
  ```php
  // VULNERABLE: Only protects HTML view, leaves JSON backend exposed
  $this->middleware('permission:List Property Tax Collection', ['only' => ['index']]);
  ```
* **Exploitation Mechanism:**
  An attacker or unauthorized user queries `GET /tax-payment/data` or `GET /watersupply-payment/data` directly. The server returns JSON arrays containing citizen names, phone numbers, property identifiers (BIN), and tax liabilities.

---

### E. Over-Privileged Default Role Configuration in [`GuestSeeder.php`](file:///c:/xampp/htdocs/lang_web_app/database/seeders/RolePermissions/GuestSeeder.php)
* **Location:** `database/seeders/RolePermissions/GuestSeeder.php` (Lines 37 & 49)
* **Root Cause:**
  The `Guest` role was explicitly granted `List` and `View` permissions for sensitive operational entities:
  ```php
  $createdRole->givePermissionTo(Permission::all()->whereIn('group', [
      'Containments', 'Service Providers', 'Employee Infos', 'Desludging Vehicles',
      'Treatment Plants', 'Treatment Plant Efficiency Tests', 'Applications',
      'Emptyings', 'Sludge Collections', 'Feedbacks', 'Treatment Plant Efficiency Standards', 'Help Desks'
  ])->whereIn('type', ['List', 'View']));
  ```
  Consequently, even when permission middleware is enforced, users holding the `Guest` role retain access in the database authorization matrix.

---

## 3. Impact Assessment

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           ATTACK VECTOR TREE                            │
├─────────────────────────────────────────────────────────────────────────┤
│ 1. Low-Privilege / Guest Login                                         │
│    ├──► GET /auth/users/{id}        ──► Exposes Admin PII & Roles       │
│    ├──► GET /fsm/emptying/{id}      ──► Exposes Customer Names & Phone  │
│    └──► GET /tax-payment/data       ──► Exposes Municipal Tax Database  │
│                                                                         │
│ 2. Authenticated Service Provider                                       │
│    └──► GET /fsm/emptying/{comp_id} ──► Cross-Tenant Intelligence Leak  │
└─────────────────────────────────────────────────────────────────────────┘
```

### Technical Impact:
1. **Broken Object-Level Authorization (BOLA):** Direct reliance on sequential numeric keys (`{id}`) combined with absent ownership verification allows attackers to iterate integers (`1, 2, 3...`) and dump all records.
2. **Horizontal Privilege Escalation:** Service providers can inspect competitor transactions, volumes, vehicle deployments, and client databases.
3. **Mass Data Scraping:** Unprotected `getData` endpoints expose complete municipal datasets without rate limits or permission checks.

### Business & Legal Impact:
1. **Breach of Personally Identifiable Information (PII):** Exposes citizen phone numbers, building locations, and household sanitation habits.
2. **Municipal Governance & Trust:** Violates government data protection mandates and destroys public confidence in municipal digitisation systems.
3. **Commercial Disruption:** Compromises fair bidding and privacy among registered private desludging operators.

---

## 4. Developer Technical Remediation Guide

### Step 1: Secure Controller Constructors with Permission Middleware

#### 1. [`UserController.php`](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Auth/UserController.php)
```php
public function __construct(UserService $userService)
{
    $this->middleware('auth');
    $this->middleware('permission:List Users', ['only' => ['index', 'getData']]);
    $this->middleware('permission:View User', ['only' => ['show']]); // <-- Add View User
    $this->middleware('permission:Add User', ['only' => ['create', 'store']]);
    $this->middleware('permission:Edit User', ['only' => ['edit', 'update']]);
    $this->middleware('permission:Delete User', ['only' => ['destroy']]);
    $this->middleware('permission:Export Users to CSV', ['only' => ['export']]);
    $this->middleware('permission:View User Login Activity', ['only' => ['getLoginActivity']]);
    $this->userService = $userService;
}
```

#### 2. [`EmptyingController.php`](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Fsm/EmptyingController.php)
```php
public function __construct(EmptyingService $emptyingService)
{
    $this->middleware('auth');
    $this->middleware('permission:List Emptyings', ['only' => ['index', 'getData']]);
    $this->middleware('permission:View Emptying', ['only' => ['show']]);
    $this->middleware('permission:Add Emptying', ['only' => ['create', 'store']]);
    $this->middleware('permission:Edit Emptying', ['only' => ['edit', 'update']]);
    $this->middleware('permission:Delete Emptying', ['only' => ['destroy']]);
    $this->middleware('permission:Export Emptyings', ['only' => ['export']]);
    $this->middleware('permission:View Emptyings History', ['only' => ['history']]);
    $this->emptyingService = $emptyingService;
}
```

#### 3. [`ApplicationController.php`](file:///c:/xampp/htdocs/lang_web_app/app/Http/Controllers/Fsm/ApplicationController.php)
```php
public function __construct(ApplicationService $applicationService)
{
    $this->middleware('auth');
    $this->middleware('permission:List Applications', ['only' => ['index', 'getData']]);
    $this->middleware('permission:View Application', ['only' => ['show', 'history', 'applicationReport']]);
    $this->middleware('permission:Add Application', ['only' => ['create', 'store']]);
    $this->middleware('permission:Edit Application', ['only' => ['edit', 'update']]);
    $this->middleware('permission:Delete Application', ['only' => ['destroy']]);
    $this->middleware('permission:Export Applications', ['only' => ['export']]);
    $this->applicationService = $applicationService;
}
```

#### 4. Protect `getData` in Payment Controllers
```php
// TaxPaymentController.php
$this->middleware('permission:List Property Tax Collection', ['only' => ['index', 'getData']]);

// WaterSupplyController.php
$this->middleware('permission:List Water Supply Collection', ['only' => ['index', 'getData']]);

// SwmServicePaymentController.php
$this->middleware('permission:List SWM Service Payment Collection', ['only' => ['index', 'getData']]);

// BuildingSurveyController.php
$this->middleware('permission:List Building Surveys', ['only' => ['index', 'getData']]);
```

---

### Step 2: Implement Tenant / Multi-Tenancy Scoping in `show()`

Even with permissions, object ownership must be verified. In `EmptyingController@show`:
```php
public function show($id)
{
    $emptying = Emptying::findOrFail($id);
    $user = Auth::user();

    // If the authenticated user belongs to a specific Service Provider, enforce tenancy
    if (!empty($user->service_provider_id)) {
        if ($emptying->service_provider_id !== $user->service_provider_id) {
            abort(403, __('Unauthorized: You do not have permission to view records from other service providers.'));
        }
    }

    $page_title = __("Emptying Details");
    $formFields = $this->emptyingService->getShowFormFields($emptying);
    $indexAction = url()->previous();
    return view('layouts.show', compact('page_title', 'formFields', 'emptying', 'indexAction'));
}
```

---

### Step 3: Sanitize the `Guest` Role Permissions in `GuestSeeder.php`

In [`database/seeders/RolePermissions/GuestSeeder.php`](file:///c:/xampp/htdocs/lang_web_app/database/seeders/RolePermissions/GuestSeeder.php#L37):
1. Remove `Emptyings`, `Applications`, and `Property Tax Collection ISS` from permissions granted to `Guest`.
2. Clear permission cache:
   ```bash
   php artisan permission:cache-reset
   ```

---

### Step 4: Defense-in-Depth — Non-Enumerable Public IDs

In accordance with [`docs/url-binding-start-work-plan.md`](file:///c:/xampp/htdocs/lang_web_app/docs/url-binding-start-work-plan.md):
* Migrate user-facing routes from sequential integer IDs (`/fsm/emptying/{id}`) to UUID-based public identifiers (`/fsm/emptying/{public_id}`).
* Internal database foreign keys and joins continue using `id`, but public endpoints reject integer enumeration.
