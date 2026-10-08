# Role and Permission Matrix

Last verified: 2026-08-12

This document describes the role-based access control (RBAC) currently configured in the application. It was checked against the live local permission tables (366 permissions), the role-permission seeders in `database/seeders/RolePermissions`, the global authorization rule in `app/Providers/AuthServiceProvider.php`, and direct `hasRole(...)` checks in the application.

## How to read this document

- **Manage** means the role has create/update/delete-style permissions for the listed area. Exact actions can differ by module.
- **Read** means primarily List, View, Export, Download, History, Service History, or View on map.
- **Selected** means only particular charts or map tools/layers are granted, not every permission in that group.
- **All** means every permission currently registered in that functional area.
- Permission counts are the current direct role assignments. `Super Admin` has zero direct assignments because it receives every permission through the global authorization bypass.
- Dashboard and map access is often permission-name-specific. The detailed matrix therefore marks these as **All** or **Selected** instead of implying that every chart/layer is available.

## Role overview

| Role | Direct permissions | Primary access pattern |
|---|---:|---|
| Super Admin | 0 + global bypass | Unrestricted; every permission is implicitly allowed |
| Municipality - Super Admin | 350 | Nearly all seeded permissions across all municipal modules |
| Municipality - Executive | 240 | Broad read/reporting access; sewer-connection approval and selected deletion; no general data maintenance |
| Municipality - Building Permit Department | 91 | Manages buildings, surveys, low-income communities, and containments; reads utilities |
| Municipality - Building Surveyor | 1 | Survey API only |
| Municipality - Infrastructure Department | 117 | Manages utility networks and sewer connections; reads related building/sanitation data |
| Municipality - Tax Department | 41 | Property-tax imports/exports/lists plus selected maps and dashboards |
| Municipality - Water Billing Unit | 41 | Water-supply billing imports/exports/lists plus selected maps and dashboards |
| Municipality - Solid Waste Management Department | 38 | Solid-waste payment imports/exports/lists plus selected maps and dashboards |
| Municipality - Sanitation Department | 231 | Broad FSM/PT-CT/CWIS access, user administration, NSD submission, dashboards and maps |
| Municipality - IT Admin | 274 | Broad read/audit/export access, user/role/language administration, and all three field APIs |
| Municipality - Public Health Department | 78 | Manages public-health datasets; reads linked community, toilet, CWIS, and water-network data |
| Municipality - Help Desk | 71 | Manages applications and feedback; reads FSM operational data and selected maps/dashboard charts |
| Service Provider - Admin | 114 | Manages provider staff/vehicles/help desk/users; reads and exports provider-scoped FSM data |
| Service Provider - Emptying Operator | 1 | Emptying API only |
| Service Provider - Help Desk | 51 | Manages applications and feedback; reads provider-scoped emptying/containment data |
| Treatment Plant - Admin | 29 | Manages plant tests and sludge collection; reads plant-related operations |
| Guest | 138 | Read-only access to many operational modules plus selected dashboards/maps |

## Functional access matrix

| Role | Administration | Buildings | FSM | Sewer / PT-CT | CWIS / KPI | Utilities | Payments | Public health | Dashboards / maps | API |
|---|---|---|---|---|---|---|---|---|---|---|
| Super Admin | All | All | All | All | All | All | All | All | All | All |
| Municipality - Super Admin | Manage users/roles/languages | Manage | Manage | Manage | Manage | Manage | Manage | Manage | All | Survey, emptying, sewer |
| Municipality - Executive | Read users/roles | Read | Read | Read + approve/delete connections | Read | Read | Read/export | Read | All dashboards; maps except Add Roads tool | None |
| Municipality - Building Permit Department | None | Manage | Manage containments | None | None | Read | None | None | Selected | None |
| Municipality - Building Surveyor | None | None in web UI | None | None | None | None | None | None | None | Survey |
| Municipality - Infrastructure Department | None | Read | Read containments | Manage connections; read PT/CT | None | Manage | None | None | Selected | Sewer connection |
| Municipality - Tax Department | None | None | None | None | None | None | Manage property-tax data exchange | None | Selected | None |
| Municipality - Water Billing Unit | None | None | None | None | None | None | Manage water-billing data exchange | None | Selected | None |
| Municipality - Solid Waste Management Department | None | None | None | None | None | None | Manage solid-waste payment data exchange | None | Selected | None |
| Municipality - Sanitation Department | Manage users | Read | Manage core setup; read operations | Manage PT/CT | Manage | Read/export | None | Read/export | Broad | None |
| Municipality - IT Admin | Manage users/roles/languages | Read/audit | Read/audit | Read | Read | Read/audit | Read/export | Read/audit | Broad | Survey, emptying, sewer |
| Municipality - Public Health Department | None | Read communities | None | Read PT/CT | Read CWIS | Read water network | None | Manage | Selected | None |
| Municipality - Help Desk | None | Read | Manage applications/feedback; read operations | None | None | None | None | None | Selected | None |
| Service Provider - Admin | Manage provider users | Read | Manage provider resources; read operations | None | Read KPI | Read roads | None | None | Selected | None |
| Service Provider - Emptying Operator | None | None | Emptying via API | None | None | None | None | None | None | Emptying |
| Service Provider - Help Desk | None | Read | Manage applications/feedback; read operations | None | None | None | None | None | Selected | None |
| Treatment Plant - Admin | None | None | Manage tests/sludge; read plant operations | None | None | None | None | None | Selected | None |
| Guest | None | Read | Read | Read PT/CT | None | Read | List tax/water records | Read hotspots/cases | Selected | None |

## Detailed action matrix by role

The lists below show the permission **types** actually assigned in each permission group. A dashboard or map type can still represent only a selected set of named charts, cards, layers, or tools.

### Super Admin

All permissions are granted implicitly by `Gate::before`. This is independent of the zero direct assignments shown in the database.

### Municipality - Super Admin

| Area | Granted actions |
|---|---|
| Users | Activity, Add, Delete, Edit, Export, List, View |
| Roles | Add, Delete, Edit, List, View |
| Language | Add, Delete, Edit, Export, Import, List, View |
| Buildings / communities | Full Add, Delete, Edit, Export, List, View and map/history actions; surveys also include Download |
| FSM | Full maintenance of help desks, providers, plants, vehicles, containments, applications, emptyings, sludge, feedback, employees, efficiency tests/standards |
| Sewer / PT-CT | Sewer connections: Approve, Delete, List, View on map; full PT/CT and user-log maintenance |
| CWIS / KPI | Add, Edit, Delete where defined, Export, List, View; all KPI dashboard cards/charts |
| Utilities | Delete, Edit, Export, History, List, View, View on map |
| Payments | Export, Import, List for solid waste, property tax, and water supply |
| Public health | Full maintenance and reporting |
| Dashboard / maps | All seeded dashboards, map layers, map tools, and data export |
| API | Survey, Emptying, Sewer Connection |

### Municipality - Executive

| Area | Granted actions |
|---|---|
| Users / Roles | List, View |
| Buildings / communities | Download, Export, List, View, View on map as available |
| FSM | Export, List, View, View on map, and Service History as available |
| Sewer connections | Approve, Delete, List, View on map |
| PT/CT | Export, List, View, View on map |
| CWIS / KPI | Export, List, View; all KPI dashboard cards/charts |
| Utilities | Edit on map, Export, List, View, View on map |
| Payments | List, Export |
| Public health | Export, List, View, View on map |
| NSD | List settings; Show NSD |
| Dashboard / maps | All dashboard groups; all maps except `Add Roads Map Tools` |

### Municipality - Building Permit Department

| Area | Granted actions |
|---|---|
| Building Structures | Add, Delete, Edit, Export, List, View, View on map |
| Building Surveys | Add, Delete, Download, List, View on map |
| Low Income Communities | Add, Delete, Edit, Export, List, View, View on map |
| Containments | Add, Delete, Edit, Export, List, View, View on map |
| Utilities | Roads: List, View, View on map; other networks: List, View |
| Dashboard / maps | Selected main/building/FSM charts and selected map tools/layers; data export |

### Municipality - Building Surveyor

| Area | Granted actions |
|---|---|
| API | API - Survey |

### Municipality - Infrastructure Department

| Area | Granted actions |
|---|---|
| Utilities | Delete, Edit, Export, List, View, View on map; no History |
| Sewer Connections | Approve, Delete, List, View on map |
| PT/CT Toilets | Export, List, View, View on map |
| Buildings / communities / containments | List and View; Building Surveys is List only |
| Dashboard / maps | Utility dashboard plus selected main/building/FSM charts and map tools/layers; data export |
| API | API - Sewer Connection |

### Municipality - Tax Department

| Area | Granted actions |
|---|---|
| Property Tax Collection ISS | Export, Import, List |
| Dashboard / maps | Selected property/building charts and selected map tools/layers |

### Municipality - Water Billing Unit

| Area | Granted actions |
|---|---|
| Water Supply ISS | Export, Import, List |
| Dashboard / maps | Selected water/building charts and selected map tools/layers |

### Municipality - Solid Waste Management Department

| Area | Granted actions |
|---|---|
| SWM Service Payment | Export, Import, List |
| Dashboard / maps | Selected solid-waste/building charts and selected map tools/layers |

### Municipality - Sanitation Department

| Area | Granted actions |
|---|---|
| Users | Activity, Add, Delete, Edit, List, View |
| Buildings / communities | Export, List, View, View on map |
| FSM setup | Manage help desks, providers, treatment plants; Edit/View efficiency standards |
| FSM operations | Export, List, View; containment Service History and map access; tests are read/export |
| PT/CT | Add, Delete, Edit, Export, List, View, View on map; no History |
| CWIS / KPI | Full available actions; all KPI dashboard cards/charts |
| Utilities / public health | Export, List, View, View on map |
| NSD | List/Save settings; Push/Show NSD |
| Dashboard / maps | Broad dashboard and map access; data export |

### Municipality - IT Admin

| Area | Granted actions |
|---|---|
| Users | Activity, Add, Delete, Edit, List, View |
| Roles | Add, List, View |
| Language | Add, Delete, Edit, Export, Import, List, View |
| Buildings / FSM / PT-CT / Utilities / Public health | Primarily Export, History, List, View, View on map; no normal record Edit/Delete |
| Sewer connections | List, View on map |
| Payments | Export, List |
| CWIS / KPI | Export, List, View plus all KPI cards/charts |
| NSD | List/Save settings; Show NSD |
| Dashboard / maps | Broad dashboard and map access; data export |
| API | Survey, Emptying, Sewer Connection |

### Municipality - Public Health Department

| Area | Granted actions |
|---|---|
| Low Income Communities / PT-CT | Export, List, View, View on map |
| CWIS | Export, List, View |
| Water Supply Network | List, View, View on map |
| Water Samples / Hotspots / Yearly Cases | Add, Delete, Edit, Export, List, View, View on map where available; no History |
| Dashboard / maps | Selected main/building charts and selected public-health map tools/layers |

### Municipality - Help Desk

| Area | Granted actions |
|---|---|
| Applications / Feedback | Add, Delete, Edit, Export, List, View |
| Buildings / FSM master data | Primarily List and View; containments include Export, Service History, and View on map |
| Emptyings / Sludge / Providers | Export, List, View |
| Dashboard / maps | Selected FSM charts and selected map layers/tools |

### Service Provider - Admin

| Area | Granted actions |
|---|---|
| Users | Activity, Add, Delete, Edit, List, View; user results are provider-scoped |
| Provider resources | Manage Help Desks, Desludging Vehicles, and Employee Infos except History |
| FSM operations | Read/export assigned applications, emptyings, containments, plants, sludge, feedback, and related data |
| Buildings / communities / roads | List, View, View on map as available |
| KPI | List/View targets and all KPI dashboard cards/charts |
| Dashboard / maps | Selected charts, map layers, and map tools |

### Service Provider - Emptying Operator

| Area | Granted actions |
|---|---|
| API | API - Emptying |

### Service Provider - Help Desk

| Area | Granted actions |
|---|---|
| Applications / Feedback | Add, Delete, Edit, Export, List, View |
| Buildings / Containments | List, View, View on map |
| Emptyings / Sludge | Export, List, View |
| Dashboard / maps | Selected FSM charts, map layers, and map tools |

### Treatment Plant - Admin

| Area | Granted actions |
|---|---|
| Containments | List |
| Providers / Vehicles / Applications / Emptyings | List, View |
| Treatment Plants | List, View, View on map |
| Efficiency Tests | Add, Delete, Edit, Export, List, View |
| Efficiency Standards | Edit, View |
| Sludge Collections | Add, Delete, Edit, Export, List, View |
| Dashboard / maps | Selected sludge chart; containment and sanitation-system map layers |

### Guest

| Area | Granted actions |
|---|---|
| Buildings | Structures: List, View, View on map; Surveys: List |
| FSM | List and View for operational groups; standards are View only |
| PT/CT | List, View, View on map; user logs are List/View |
| Utilities | List, View |
| Payments | List property-tax and water-supply records |
| Public health | Hotspots: List, View, View on map; yearly cases: List, View |
| NSD | List settings; Show NSD |
| Dashboard / maps | Selected charts, count boxes, map layers, and map tools |

## Role-specific scope and behavior

Permissions determine which operation is available, while several services apply additional row-level filters:

- `Super Admin` is granted every authorization ability by `Gate::before` in `app/Providers/AuthServiceProvider.php`.
- `Service Provider - Admin` and `Service Provider - Help Desk` are repeatedly restricted to records belonging to the authenticated user's `service_provider_id`, including maps, applications, dashboards, emptying-related data, and exports.
- `Treatment Plant - Admin` is restricted to treatment-plant-related records in application, sludge collection, dashboard, and effectiveness services.
- User-management results are scoped by the acting role: service-provider admins see their provider users, treatment-plant admins see their plant users, and sanitation/IT/executive roles have their own municipality-oriented filters.
- `Municipality - Executive` has UI restrictions around CWIS data-entry periods even though it has broad read access.
- Some controllers contain role checks in addition to permission middleware. Possessing a permission alone may therefore not reproduce the behavior of the intended role.

## Source of truth

The main files used to maintain this matrix are:

- `database/seeders/PermissionsSeeder.php` — permission names, groups, and action types.
- `database/seeders/RolesSeeder.php` — declared roles and role-permission seeder registration.
- `database/seeders/RolePermissions/*.php` — permission assignments for each role.
- `app/Providers/AuthServiceProvider.php` — global Super Admin authorization bypass.
- `app/Services/**`, `app/Http/Controllers/**`, and `resources/views/**` — role-specific data scoping and UI behavior.

Because permissions may be edited through the Roles UI after seeding, the database is the effective runtime source of truth. Re-check the live role assignments after deploying or rerunning seeders.
