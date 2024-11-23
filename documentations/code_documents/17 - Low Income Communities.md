Version: V1.0.0

### Low Income Communities

**Tables**

The low income communities module uses the following table:

-   low_income_communities: stores primary information of low income communities.

The corresponding tables have their respective models that are named in Pascal Case in singular form. The low income communities modules are located at app\\Http\\Models\\LayerInfo\\ LowIncomeCommunity.

**Views**

All views used by this module is stored in resources\\views\\layer-info\\low-income-communities

-   low-income-communities.create: opens form and calls partial-form for form content.
-   low-income-communities.edit: opens from and calls partial-form for form contents.
-   low-income-communities.history: lists all past edits of the record.
-   low-income-communities.index: lists of treatment plant records.
-   low-income-communities.show lists of treatment plant.
-   low-income-communities.partial-form: creates form content for addition of new low income communities or edit low income communities.

### LowIncomeCommunityController

app\\Http\\Controllers\\ LayerInfo\\LowIncomeCommunityController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(LowIncomeCommunityServiceClass)                 |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\ LayerInfo\\LowIncomeCommunityController.php   |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      | layer-info/low-income-communities.index  compact(‘page_title')               |
| **Source**      | app\\Http\\Controllers\\ LayerInfo\\LowIncomeCommunityController.php         |

| **Function**    | getData()                                                                           |
|-----------------|-------------------------------------------------------------------------------------|
| **Description** | Fetches data using the LowIncomeCommunityServiceClass based on the provided request |
| **Parameters**  | Request \$request                                                                   |
| **Return**      | The fetched data JsonResponse                                                       |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php                 |
| **Remarks**     | LowIncomeCommunityServiceClass -\>fetchData(\$request)  Service Class Function Name |

| **Function**    | create()                                                              |
|-----------------|-----------------------------------------------------------------------|
| **Description** | Returns the form to create a new low income community                 |
| **Parameters**  | null                                                                  |
| **Return**      | layer-info/low-income-communities.create compact('page_title')        |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php   |

| **Function**    | store()                                                             |
|-----------------|---------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data    |
| **Parameters**  | LowIncomeCommunityRequest \$request                                 |
| **Return**      | Success or error message.                                           |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php |
| **Remarks**     | storeData(\$request) Service Class Function Name                    |

| **Function**    | show()                                                              |
|-----------------|---------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of showing data    |
| **Parameters**  | \$id                                                                |
| **Return**      | Success or error message.                                           |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php |
| **Remarks**     | showData(\$request) Service Class Function Name                     |

| **Function**    | edit()                                                                                     |
|-----------------|--------------------------------------------------------------------------------------------|
| **Description** | Returns the edit form page                                                                 |
| **Parameters**  | \$id                                                                                       |
| **Return**      | layer-info.low-income-communities.edit  compact(page_title', 'lic', 'geom', 'lat', 'long’) |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php                        |

| **Function**    | update()                                                                               |
|-----------------|----------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating low income community data |
| **Parameters**  | LowIncomeCommunityRequest (\$request, \$id)                                            |
| **Return**      | Success or error message.                                                              |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php                    |
| **Remarks**     | updateData(\$request, \$id) (service class function)                                   |

| **Function**    | destroy()                                                           |
|-----------------|---------------------------------------------------------------------|
| **Description** | Handles the process of deleting low income community data           |
| **Parameters**  | \$id                                                                |
| **Return**      | Redirection with success/failure message                            |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php |

| **Function**    | export()                                                                                |
|-----------------|-----------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting low income community data |
| **Parameters**  | null                                                                                    |
| **Return**      | CSV file containing low income community data                                           |
| **Source**      | app\\Http\\Controllers\\LayerInfo\\LowIncomeCommunityController.php                     |
| **Remarks**     | LowIncomeCommunityServiceClass -\>exportData();  (service class function)               |

### LowIncomeCommunityServiceClass

Location: app\\Services\\LayerInfo\\LowIncomeCommunityServiceClass.php

The Service Class contains all the business logic. It contains all the functions that are being called in the LowIncomeCommunityController.php

|  **Function**   | fetchData()                                                               |
|-----------------|---------------------------------------------------------------------------|
| **Description** | Handles the process of fetching low income community data for data tables |
| **Parameters**  | \$request                                                                 |
| **Return**      | Returns data of low income community data for datatables                  |
| **Source**      | app\\Services\\LayerInfo\\LowIncomeCommunityServiceClass.php              |

|  **Function**   | storeData()                                                           |
|-----------------|-----------------------------------------------------------------------|
| **Description** | Handles the process of adding new low income community.               |
| **Parameters**  | \$request                                                             |
| **Return**      | Success or error message, stores/updates data to low income community |
| **Source**      | app\\Services\\LayerInfo\\LowIncomeCommunityServiceClass.php          |

|  **Function**   | updateData()                                                 |
|-----------------|--------------------------------------------------------------|
| **Description** | Handles the process of editing low income community.         |
| **Parameters**  | \$request, \$id                                              |
| **Return**      | Success or error message                                     |
| **Source**      | app\\Services\\LayerInfo\\LowIncomeCommunityServiceClass.php |

|  **Function**   | showData()                                                                                  |
|-----------------|---------------------------------------------------------------------------------------------|
| **Description** | Handles the process of showing low income community.                                        |
| **Parameters**  | \$id                                                                                        |
| **Return**      | layer-info/low-income-communities.show' compact('page_title', 'lic', 'geom', 'lat', 'long') |
| **Source**      | app\\Services\\LayerInfo\\LowIncomeCommunityServiceClass.php                                |

| **Function**    | exportData()                                                                            |
|-----------------|-----------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting low income community data |
| **Parameters**  | \$data                                                                                  |
| **Return**      | CSV file containing low income community data                                           |
| **Source**      | app\\Services\\LayerInfo\\LowIncomeCommunityServiceClass.php                            |

**Models**

Location: app\\Models\\ LayerInfo\\ LowIncomeCommunity.php

The models contain the connection between the model and the table defined by \\\$table = ‘layer_info.low_income_communities’ as well as the primary key defined by primaryKey= ‘id’.

Low Income Community Request

Location: app\\Http\\Requests\\LayerInfo\\ LowIncomeCommunityRequest.php

LowIncomeCommunityRequest handles all validation logic as well as error messages to be displayed.

Low Income Community follow CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" section above (2.2) for more information.
