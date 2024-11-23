Version: V1.0.0

# Settings

## User IMS

### Users

**Tables**

Users is under Auth module and uses the following table:

-   *Auth.users*
-   *Auth.roles*
-   *Auth.personal_access_tokens*
-   *Auth.password_resets*
-   *Auth.permissions*
-   *Auth.model_has_permissions*
-   *Auth.model_has_roles*

Users also uses the following table of public schema:

The corresponding tables have their respective models that are named in Pascal Case in singular form. User model is located at app\\Models\\

**Views**

All views used by this module is stored in resources\\views\\auth\\users

-   users.index: lists users records.
-   users.create: opens form and calls partial-form for form contents
-   users.partial-form: creates form content
-   users.edit.blade: opens form and calls partial-form for form contents
-   users.login-activity: displays activity detail of particular record
-   users.show: displays all attributes of particular record

**UserController**

app\\Http\\Controllers\\Auth\\UserController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(UserService)                                    |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                       |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      | users.index compact('users', 'page_title')                                   |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                             |

| **Function**    | create()                                                                            |
|-----------------|-------------------------------------------------------------------------------------|
| **Description** | Returns the form to create new user with dropdown values fetched from the database. |
| **Parameters**  | null                                                                                |
| **Return**      | users.create compact('page_title')                                                  |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                                    |

| **Function**    | store(UserRequest \$request)                                     |
|-----------------|------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data |
| **Parameters**  | UserRequest \$request                                            |
| **Return**      | Success or error message.                                        |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                 |
| **Remarks**     | storeOrUpdate(\$id = null,\$data) Service Class Function Name    |

| **Function**    | show()                                                                                                                                                                                                                                                                 |
|-----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Returns the page displaying individual user data                                                                                                                                                                                                                       |
| **Parameters**  | \$id                                                                                                                                                                                                                                                                   |
| **Return**      | users.show[ 'userDetail' =\> \$userDetail, 'userRoles' =\> \$userRoles, 'page_title' =\> \$page_title, 'treatmentPlants' =\> \$user['treatmentPlants'], 'helpDesks' =\> \$user['helpDesks'], 'serviceProviders' =\> \$user['serviceProviders'], 'status' =\> \$status] |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                                                                                                                                                                                                                       |

| **Function**    | edit()                                                                                                                                              |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Returns the edit form page displaying pre-existing individual user data as well                                                                     |
| **Parameters**  | \$id                                                                                                                                                |
| **Return**      | users.edit compact('page_title', 'user', 'roles', 'treatmentPlants', 'helpDesks', 'serviceProviders','transferStations', 'landfillSites', 'status') |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                                                                                                    |

| **Function**    | update()                                                               |
|-----------------|------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating user data |
| **Parameters**  | UserRequest \$request, \$id                                            |
| **Return**      | Success or error message.                                              |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                       |
| **Remarks**     | storeOrUpdate(Request \$request) (service class function)              |

**UserService**

Location: app\\Services\\Auth\\UserService.php

The Service Class contains all the business logic. It contains all the functions that are being called in the UserController.

|  **Function**   | storeOrUpdate()                                                                                          |
|-----------------|----------------------------------------------------------------------------------------------------------|
| **Description** | Handles the process of adding/updating new user.                                                         |
| **Parameters**  | \$id,\$data                                                                                              |
| **Return**      | Success or error message, stores/updates data to user                                                    |
| **Source**      | app\\Services\\Auth\\UserService.php                                                                     |
| **Logic**       | if \$id is null store new records to database if \$id has some value edit the record of \$id to database |

| **Function**    | getAllData()                                             |
|-----------------|----------------------------------------------------------|
| **Description** | Handles the process of fetching user data for html table |
| **Parameters**  | \$data                                                   |
| **Return**      | Returns data of user table for html table                |
| **Source**      | app\\Services\\Auth\\UserService.php                     |
| **Logic**       | Join required tables Return datatable                    |

**UserRequest**

Location: app\\Http\\Requests\\Auth\\UserRequest.php

UserRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

|  **Function**   | authorize()                                |
|-----------------|--------------------------------------------|
| **Description** | Determines if user is authenticated or not |
| **Parameters**  |                                            |
| **Return**      | Returns true                               |
| **Source**      | app\\Http\\Requests\\Auth\\UserRequest.php |

| **Function**    | message()                                                                                                                         |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Message to be displayed in case of validation error                                                                               |
| **Parameters**  |                                                                                                                                   |
| **Return**      | Return validation message                                                                                                         |
| **Source**      | app\\Http\\Requests\\Auth\\UserRequest.php                                                                                        |
| **Remarks**     | Need to include errors.list.blade that displays the error message in dashboard Format: ‘Field.validation_rule’ =\>”error_message” |

| **Function**    | rules()                                                                           |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | Contains the validation rules                                                     |
| **Parameters**  |                                                                                   |
| **Return**      | Return validation logic to calling place                                          |
| **Source**      | app\\Http\\Requests\\Auth\\UserRequest.php                                        |
| **Remarks**     | Format for validation rule: ‘field_name’=\>’validation_rule1 \| validation_rule2’ |

**Models**

The models contain the connection between the model and the table defined by

*\$table = ‘auth.users’* as well as the primary key defined by

*primaryKey= ‘id’*

### Roles

**Tables**

Roles *is under Auth module and uses the following table:*

-   *Auth.roles*
-   *Auth.users*
-   *Auth.permissions*
-   *Auth.role_has_permissions*
-   *Auth.model_has_roles*

The corresponding tables have their respective models that are named in Pascal Case in singular form. User model is located at app\\Models\\

**Views**

All views used by this module is stored in resources\\views\\auth\\roles

-   roles.index: lists roles records.
-   roles.create: opens form and calls form for form contents
-   roles.form: creates form content
-   roles.edit.blade: opens form and calls form for form contents

**RoleController**

app\\Http\\Controllers\\Auth\\RoleController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function.

The basic classes of the controller are:

|  **Function**   | \__construct()                                    |
|-----------------|---------------------------------------------------|
| **Description** | Initializes authentication and permissions        |
| **Parameters**  | null                                              |
| **Return**      | null                                              |
| **Source**      | app\\Http\\Controllers\\Auth\\RoleController.php  |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      |  ['page_title'=\>\$page_title,'roles' =\> \$roles]                           |
| **Source**      | app\\Http\\Controllers\\Auth\\RoleController.php                             |

| **Function**    | create()                                                                            |
|-----------------|-------------------------------------------------------------------------------------|
| **Description** | Returns the form to create new role with dropdown values fetched from the database. |
| **Parameters**  | null                                                                                |
| **Return**      | roles.create compact('page_title')                                                  |
| **Source**      | app\\Http\\Controllers\\Auth\\UserController.php                                    |

| **Function**    | store(Request \$request)                           |
|-----------------|----------------------------------------------------|
| **Description** | Handles the process of storing data                |
| **Parameters**  | Request \$request                                  |
| **Return**      | Success or error message.                          |
| **Source**      | app\\Http\\Controllers\\Auth\\RoleController.php   |

| **Function**    | edit()                                                                                        |
|-----------------|-----------------------------------------------------------------------------------------------|
| **Description** | Returns the edit form page displaying pre-existing individual role data as well               |
| **Parameters**  | \$id                                                                                          |
| **Return**      | roles.edit compact('page_title', 'role','permission','rolePermissions','grouped_permissions') |
| **Source**      | app\\Http\\Controllers\\Auth\\RoleController.php                                              |

| **Function**    | update()                                                               |
|-----------------|------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating user data |
| **Parameters**  | Request \$request, \$id                                                |
| **Return**      | Success or error message.                                              |
| **Source**      | app\\Http\\Controllers\\Auth\\RoleController.php                       |

| **Function**    | destroy                                                  |
|-----------------|----------------------------------------------------------|
| **Description** | Soft deletes the roles                                   |
| **Parameters**  | \$id                                                     |
| **Return**      | Redirection to index page with success or failure prompt |
| **Source**      | App\\Http\\Controllers\\Auth\\RolesController.php        |

**Models**

\<-- CODE SNIPPET --\>

*use Spatie\\Permission\\Models\\Role;*

*use Spatie\\Permission\\Models\\Permission;*

**Seeder**

For Seeding permissions we use :

PermissionsSeeder class and file is located at:

Database\\Seeders\\PermissionsSeeder.php

| **Function**    | run()                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
|-----------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Run the database seeds. \<-- CODE SNIPPET for permission for Users --\> *\$grouped_permissions = [*  *[*  *"group" =\> "Users",*  *"perms" =\> [*  *[*  *"type" =\> "List",*  *"name" =\> "List Users"*  *],*  *[*  *"type" =\> "View",*  *"name" =\> "View User"*  *],*  *[*  *"type" =\> "Add",*  *"name" =\> "Add User"*  *],*  *[*  *"type" =\> "Edit",*  *"name" =\> "Edit User"*  *],*  *[*  *"type" =\> "Delete",*  *"name" =\> "Delete User"*  *],*  *[*  *"type" =\> "Activity",*  *"name" =\> "Login Activity User"*  *],*  *]*  *],* *foreach (\$grouped_permissions as \$group) {*  *foreach (\$group['perms'] as \$permission){*  *\$existPermission = DB::table('auth.permissions')*  *-\>where('name', \$permission['name'])*  *-\>first();*  *if (!\$existPermission) {*  *Permission::create([*  *'name' =\> \$permission['name'],*  *'type' =\> \$permission['type'],*  *'group' =\> \$group['group']*  *]);*  *}*  *}*  *}* |
| **Parameters**  | null                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| **Return**      | null                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| **Source**      | Database\\Seeders\\PermissionsSeeder.php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |

For Seeding roles we use :

RolesSeeder class and file is located at :

Database\\Seeders\\RolesSeeder.php

| **Function**    | run()                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Run the database seeds. \<-- CODE SNIPPET for all roles --\> *\$roles = [*  *[*  *'name' =\> 'Super Admin'*  *],*  *[*  *'name' =\> 'Municipality - Executive',*  *],*  *[*  *'name' =\> 'Municipality - Building Permit Department',*  *],*  *[*  *'name' =\> 'Municipality - Building Surveyor (Ward)',*  *],*  *[*  *'name' =\> 'Municipality - Infrastructure Department',*  *],*  *[*  *'name' =\> 'Municipality - Revenue Department',*  *],*  *[*  *'name' =\> 'Municipality - Sanitation Department',*  *],*  *[*  *'name' =\> 'Municipality - IT Admin',*  *],*  *[*  *'name' =\> 'Municipality - Public Health Department',*  *],*  *[*  *'name' =\> 'Municipality - Help Desk',*  *],*  *[*  *'name' =\> 'Service Provider - Admin',*  *],*  *[*  *'name' =\> 'Service Provider - Help Desk',*  *],*  *[*  *'name' =\> 'Service Provider - Emptying Operator',*  *],*  *[*  *'name' =\> 'Treatment Plant',*  *],*  *[*  *'name' =\> 'Solid Waste - Admin',*  *],*  *[*  *'name' =\> 'Solid Waste - Transfer Station',*  *],*  *[*  *'name' =\> 'Solid Waste - Landfill',*  *],*  *[*  *'name' =\> 'External',*  *],*  *];*                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| **Parameters**  | null                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| **Return**      | null                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| **Source**      | Database\\Seeders\\RoleSeeder.php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| **Logic**       | There are multiple switch cases if are defined by the roles array and name values passed to the array. According to this, different parts of the function run their logic of data retrieval and storage.  Dynamic Conditions: If case is Municipality – Executive then:  Give all the permissions except Add, Edit, Delete. If case is Municipality - Building Permit Department then:  Give permission to groups that are Building Structures,Building Surveys,Containments  Give permission to groups that are Roadlines,Drains,Sewers  Give permission to groups that are Maps, Dashboard If case is Municipality - Building Surveyor (Ward) Give permission to group API If case is Municipality - Infrastructure Department then : Give permission to groups Roads,Drains,Sewers,Maps If case is Municipality - Infrastructure Department then : Give permission to groups Roads,Drains,Sewers,Maps If case is Municipality - Revenue Department then : Give permission to groups Maps,Property Tax Collection ISS, Water Supply ISS,Buildings Structures If case is Municipality - Sanitation Department Help Desks,Service Providers,Employee Infos Treatment Plants','Hotspots','CT/PT','Toilet Users','Users','Containments', 'KPI Target', 'KPI Dashboard' 'Building Structures','Building Surveys', 'Sewers, Applications','Emptyings','Feedbacks','Sludge Collections','Vacutug Types','Treatment Plant Effectiveness Treatment Plant Effectiveness','Treatment Plant Test Maps If case is Municipality - IT Admin then : Give all permissions except type Edit and Delete If case is Municipality - Public Health Department then : Give permission to group Yearly Waterborne Cases,Hotspots,Dashboard If case is Municipality - Help Desk then : Give permission to groups Building Structures,Containments,Applications,Feedbacks Maps If case is Service Provider – Admin then : Give permissions to group Building Structures,Containments,Kpi Target,Feedbacks,Sludge Collections,Applications, Emptyings,Vacutug Types,Employee Infos,Users, Dashboard, KPI Dashboard If case is Service Provider - Help Desk then : Give permission to groups Building Structures,Kpi Target,Containments, Applications,Feedbacks,Maps If case is Service Provider - Emptying Operator then : Give permission to group API If case is Treatment Plant then : Give permissions to group Sludge Collections, Applications, Treatment Plant Effectiveness, Maps, Dashboard If case is External then :  Building Structures,Roads,Drains,Building Surveys,Employee Infos,Public Health,Sanitation System Types,Treatment Plant Test,Sanitation System Technology,Property Tax Collection ISS,Water Supply ISS,Sewers,WaterSupply Network,Help Desks,Service Providers,Treatment Plants,Vacutug Types,Treatment Plant Effectiveness,CT/PT,Toilet Users,Hotspots,Emptyings,Containments,Feedbacks,Sludge Collections,Users,Roles,Application, Maps  Type View,List,Map |
