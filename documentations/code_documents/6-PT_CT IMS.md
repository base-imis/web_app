Version: V1.0.0

#  PT/ CT IMS

## Public / Community Toilets

### Tables

Pubic / Community Toilet is under PT/CT IMS module and uses the following table:

‘fsm.toilets’

The corresponding tables have their respective models that are named in Pascal Case in singular form. Ctpt model is located at app\\Models\\Fsm\\.

### Views

All views used by this module is stored in resources\\views\\fsm\\ct-pt

-   ct-pt.index: lists ct/pt records.

-   ct-pt.create: opens form and calls partial-form for form contents

-   ct-pt.partial-form: creates form content

-   ct-pt.edit.blade: opens form and calls partial-form for form contents

-   ct-pt.show: displays all attributes of particular record

### CtptController

app\\Http\\Controllers\\Fsm\\CtptController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(CtptServiceClass)                               |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                        |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      | fsm.ct-pt.index compact('page_title','ward','status','operational','bin')    |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                              |

| **Function**    | getData()                                                             |
|-----------------|-----------------------------------------------------------------------|
| **Description** | Fetches data using the CtptServiceClass based on the provided request |
| **Parameters**  | Request \$request                                                     |
| **Return**      | The fetched data JsonResponse                                         |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                       |
| **Remarks**     | ctptServiceClass-\>fetchData(\$request)  Service Class Function Name  |

| **Function**    | create()                                                                     |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the form to create a new pt/ct                                       |
| **Parameters**  | null                                                                         |
| **Return**      | fsm/ct-pt.create compact('page_title', 'ward', 'bin','status','operational') |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                              |

| **Function**    | store(CtptRequest \$request)                                     |
|-----------------|------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data |
| **Parameters**  | CtptRequest \$request                                            |
| **Return**      | Success or error message.                                        |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                  |
| **Remarks**     | storeCtptData (\$request) Service Class Function Name            |

| **Function**    | show()                                            |
|-----------------|---------------------------------------------------|
| **Description** | Returns the page displaying individual pt/ct data |
| **Parameters**  | \$id                                              |
| **Return**      | fsm/pt-ct.show                                    |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php   |

| **Function**    | edit()                                                                            |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | Returns the edit form page                                                        |
| **Parameters**  | \$id                                                                              |
| **Return**      | fsm.ct-pt.edit  compact('page_title','ctpt','ward', 'bin','status','operational') |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                                   |

| **Function**    | update()                                                                |
|-----------------|-------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating pt/ct data |
| **Parameters**  | CtptRequest \$request, \$id                                             |
| **Return**      | Success or error message.                                               |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                         |
| **Remarks**     | updateCtptData(Request \$request) (service class function)              |

| **Function**    | destroy()                                       |
|-----------------|-------------------------------------------------|
| **Description** | Handles the process of deleting pt/ct data      |
| **Parameters**  | \$id                                            |
| **Return**      | Redirection with success/failure message        |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php |

| **Function**    | export(Request \$request)                                                |
|-----------------|--------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting pt/ct data |
| **Parameters**  | Request \$request                                                        |
| **Return**      | CSV file containing ct pt data                                           |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                          |
| **Remarks**     | ctptServiceClass-\>exportData(\$data);  (service class function)         |

| **Function**    | history()                                                |
|-----------------|----------------------------------------------------------|
| **Description** | lists all past edits of the record                       |
| **Parameters**  | \$id                                                     |
| **Return**      | fsm/ct-pt.history   compact('page_title', ‘ctpt’)        |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php          |


### CtptServiceClass

Location: app\\Services\\Fsm\\CtptServiceClass.php

The Service Class contains all the business logic. It contains all the functions that are being called in the CtptController.php

|  **Function**   | storeCtptData()                                                   |
|-----------------|-------------------------------------------------------------------|
| **Description** | Handles the process of adding new pt/ct data.                     |
| **Parameters**  | \$request                                                         |
| **Return**      | Success or error message, stores data to pt/ct data               |
| **Source**      | app\\Services\\Fsm\\CtptServiceClass.php                          |

|  **Function**   | updateCtptData()                                                    |
|-----------------|-------------------------------------------------------------------  |
| **Description** | Handles the process of updating pt/ct data.                         |
| **Parameters**  | \$request, $id                                                      |
| **Return**      | Success or error message, updates data to pt/ct data                |
| **Source**      | app\\Services\\Fsm\\CtptServiceClass.php                            |


| **Function**    | fetchData()                                                |
|-----------------|------------------------------------------------------------|
| **Description** | Handles the process of fetching pt/ct data for data tables |
| **Parameters**  | Request \$request                                          |
| **Return**      | Returns data of pt/ct data for datatables                  |
| **Source**      | app\\Services\\Fsm\\CtptServiceClass.php                   |

| **Function**    | exportData()                                                             |
|-----------------|--------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting pt/ct data |
| **Parameters**  | \$data                                                                   |
| **Return**      | CSV file containing  pt/ct data                                          |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                          |

### CtptRequest

Location: app\\Http\\Requests\\Fsm\\CtptRequest.php

CtptRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

### Models

The models contain the connection between the model and the table defined by

*\$table = ‘fsm.toilets’* as well as the primary key defined by

*primaryKey= ‘id’*

#  PT Users Log

### Tables

PT users log is under PT/CT IMS module and uses the following table:

*‘fsm.ctpt_users’*

The corresponding tables have their respective models that are named in Pascal Case in singular form. CtptUsers model is located at app\\Models\\Fsm\\.

### Views

All views used by this module is stored in resources\\views\\fsm\\ctpt-users

-   ctpt-users.index: lists pt users log records.

-   ctpt-users.create: opens form and calls partial-form for form contents

-   ctpt-users.partial-form: creates form content

-   ctpt-users.edit.blade: opens form and calls partial-form for form contents

-   ctpt-users.show: displays all attributes of particular record

### CtptUserController

app\\Http\\Controllers\\Fsm\\CtptUserController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(CtptUserServiceClass)                           |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php                    |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      | fsm.ctpt-users.index compact(‘page_title','name')                            |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php                          |

| **Function**    | getData()                                                                 |
|-----------------|---------------------------------------------------------------------------|
| **Description** | Fetches data using the CtptUserServiceClass based on the provided request |
| **Parameters**  | Request \$request                                                         |
| **Return**      | The fetched data JsonResponse                                             |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php                       |
| **Remarks**     | CtptUserServiceClass-\>fetchData(\$request)  Service Class Function Name  |

| **Function**    | create()                                              |
|-----------------|-------------------------------------------------------|
| **Description** | Returns the form to create a new pt user log          |
| **Parameters**  | null                                                  |
| **Return**      | fsm/ctpt-users.create compact('page_title', 'name')   |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php   |

| **Function**    | store()                                                          |
|-----------------|------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data |
| **Parameters**  | CtptUserRequest \$request                                        |
| **Return**      | Success or error message.                                        |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php              |
| **Remarks**     | storeData(\$request) Service Class Function Name                 |

| **Function**    | show()                                                      |
|-----------------|-------------------------------------------------------------|
| **Description** | Returns the page displaying individual pt users log data    |
| **Parameters**  | \$id                                                        |
| **Return**      | fsm/ctpt-users.show                                         |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php         |

| **Function**    | edit()                                                            |
|-----------------|-------------------------------------------------------------------|
| **Description** | Returns the edit form page                                        |
| **Parameters**  | \$id                                                              |
| **Return**      | fsm.ctpt-users.edit  compact('page_title','info','name','users') |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptController.php                   |

| **Function**    | update()                                                                          |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating pt users log data    |
| **Parameters**  | CtptUserRequest \$request, \$id                                                   |
| **Return**      | Success or error message.                                                         |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php                               |
| **Remarks**     | updateData(\$request, \$id) (service class function)                              |

| **Function**    | destroy()                                            |
|-----------------|------------------------------------------------------|
| **Description** | Handles the process of deleting pt users log data    |
| **Parameters**  | \$id                                                 |
| **Return**      | Redirection with success/failure message             |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php  |

| **Function**    | export()                                                                           |
|-----------------|------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting pt    users log data |
| **Parameters**  | null                                                                               |
| **Return**      | CSV file containing pt data                                                        |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php                                |
| **Remarks**     | ctptUserServiceClass-\>exportData();  (service class function)                     |

| **Function**    | history()                                                |
|-----------------|----------------------------------------------------------|
| **Description** | lists all past edits of the record                       |
| **Parameters**  | \$id                                                     |
| **Return**      | fsm/ctpt-users.history   compact('page_title', ‘ctpt’)   |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php          |

### CtptUserServiceClass

Location: app\\Services\\Fsm\\CtptServiceClass.php

The Service Class contains all the business logic. It contains all the functions that are being called in the CtptUserController.php

|  **Function**   | fetchData()                                                          |
|-----------------|----------------------------------------------------------------------|
| **Description** | Handles the process of fetching pt users log data for data tables    |
| **Parameters**  | \$request                                                            |
| **Return**      | Returns data of pt users log data for datatables                     |
| **Source**      | app\\Services\\Fsm\\CtptUserServiceClass.php                         |

|  **Function**   | storeData()                                                       |
|-----------------|-------------------------------------------------------------------|
| **Description** | Handles the process of adding new pt users log.                   |
| **Parameters**  | \$request                                                         |
| **Return**      | Success or error message, stores data to pt users log             |
| **Source**      | app\\Services\\Fsm\\CtptUserServiceClass.php                      |

|  **Function**   | updateData()                                                     |
|-----------------|------------------------------------------------------------------|
| **Description** | Handles the process of editing pt users log.                     |
| **Parameters**  | \$request, \$id                                                  |
| **Return**      | Success or error message, updates data to pt users log           |
| **Source**      | app\\Services\\Fsm\\CtptUserServiceClass.php                     |

| **Function**    | exportData()                                                                       |
|-----------------|------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting pt users log data    |
| **Parameters**  |                                                                                    |
| **Return**      | CSV file containing pt users log data                                              |
| **Source**      | app\\Http\\Controllers\\Fsm\\CtptUserController.php                                |

### CtptUserRequest

Location: app\\Http\\Requests\\Fsm\\CtptUserRequest.php

CtptUserRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

### Models

The models contain the connection between the model and the table defined by

*\$table = ‘fsm.ctpt_users* as well as the primary key defined by

*primaryKey= ‘id’*
