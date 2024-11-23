Version: V1.0.0

# Public Health ISS

## Water Samples

### Tables

Water Samples is under Public Health module and uses the following table:

-   water_samples

The corresponding tables have their respective models that are named in Pascal Case in singular form. Hotspots model is located at app\\Models\\PublicHealth\\.

### Views

All views used by this module is stored in resources\\views\\public-health\\water-samples

-   water-samples.index: lists hotspots records.

-   water-samples.create: opens form and calls partial-form for form contents

-   water-samples.partial-form: creates form content

-   water-samples.edit: opens form and calls partial-form for form contents

-   water-samples.show: displays all attributes of particular record

### WaterSamplesController

app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(WaterSamplesService)                            |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php      |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      | public-health/water-samples.index  compact('page_title')                     |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php            |

| **Function**    | create()                                                          |
|-----------------|-------------------------------------------------------------------|
| **Description** | Returns the form to create new water samples data                 |
| **Parameters**  | null                                                              |
| **Return**      | public-health/water-samples.create  compact('page_title')         |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php |

| **Function**    | store()                                                                                  |
|-----------------|------------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data                         |
| **Parameters**  | WaterSamplesRequest \$request                                                            |
| **Return**      | Success or error message.                                                                |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php                        |
| **Remarks**     | WaterSamplesService -\> storeOrUpdate (\$id = null, \$data)  Service Class Function Name |

| **Function**    | show()                                                             |
|-----------------|--------------------------------------------------------------------|
| **Description** | Returns the page displaying individual water samples data          |
| **Parameters**  | \$id                                                               |
| **Return**      | \\Illuminate\\Http\\Response                                       |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php  |
| **Remarks**     | waterSamplesService -\>show(\$id) (service class function)         |

| **Function**    | edit()                                                                |
|-----------------|-----------------------------------------------------------------------|
| **Description** | Returns the edit form page displaying water samples data              |
| **Parameters**  | \$id                                                                  |
| **Return**      | public-health/water-samples.edit compact('page_title', ‘waterSamples) |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php     |

| **Function**    | update()                                                                        |
|-----------------|---------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating water samples data |
| **Parameters**  | WaterSamplesRequest (\$request, \$id)                                           |
| **Return**      | Success or error message.                                                       |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php               |
| **Remarks**     | waterSamplesService-\>storeOrUpdate(\$id,\$data) (service class function)       |

| **Function**    | destroy()                                                          |
|-----------------|--------------------------------------------------------------------|
| **Description** | Handles the process of deleting water samples data                 |
| **Parameters**  | \$id                                                               |
| **Return**      | Redirection with success/failure message                           |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php  |

| **Function**    | export()                                                                         |
|-----------------|----------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting water samples data |
| **Parameters**  | Request \$request                                                                |
| **Return**      | CSV file containing water samples data                                           |
| **Source**      | app\\Http\\Controllers\\ PublicHealth\\WaterSamplesController.php                |
| **Remarks**     | waterSamplesService-\>download(\$data);  (service class function)                |

### WaterSamplesService

Location: app\\Services\\PublicHealth\\WaterSamplesService.php

The Service Class contains all the business logic. It contains all the functions that are being called in the WaterSamplesController.

|  **Function**   | storeOrUpdate()                                                                                          |
|-----------------|----------------------------------------------------------------------------------------------------------|
| **Description** | Handles the process of adding/updating new water samples.                                                |
| **Parameters**  | \$id, \$data                                                                                             |
| **Return**      | Success or error message, stores/updates data to water samples                                           |
| **Source**      | app\\Services\\ PublicHealth\\WaterSamplesService.php                                                    |
| **Logic**       | if \$id is null store new records to database if \$id has some value edit the record of \$id to database |

|  **Function**   | download()                                                 |
|-----------------|------------------------------------------------------------|
| **Description** | Handles the process of exporting water samples information |
| **Parameters**  | \$data                                                     |
| **Return**      | Returns CSV                                                |
| **Source**      | app\\Services\\ PublicHealth\\WaterSamplesService.php      |

| **Function**    | getAllData()                                             |
|-----------------|----------------------------------------------------------|
| **Description** | Handles the process of fetching user data for html table |
| **Parameters**  | \$data                                                   |
| **Return**      | Returns data of user table for html table                |
| **Source**      | app\\Services\\ PublicHealth\\WaterSamplesService.php    |

### WaterSamplesRequest

Location: app\\Http\\Requests\\PublicHealth\\WaterSamplesRequest.php

WaterSamplesRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

|  **Function**   | authorize()                                                |
|-----------------|------------------------------------------------------------|
| **Description** | Determines if user is authenticated or not                 |
| **Parameters**  |                                                            |
| **Return**      | Returns true                                               |
| **Source**      | app\\Http\\Requests\\PublicHealth\\WaterSamplesRequest.php |

| **Function**    | message()                                                                                                                         |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Message to be displayed in case of validation error                                                                               |
| **Parameters**  |                                                                                                                                   |
| **Return**      | Return validation message                                                                                                         |
| **Source**      | app\\Http\\Requests\\PublicHealth\\WaterSamplesRequest.php                                                                        |
| **Remarks**     | Need to include errors.list.blade that displays the error message in dashboard Format: ‘Field.validation_rule’ =\>”error_message” |

| **Function**    | rules()                                                                           |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | Contains the validation rules                                                     |
| **Parameters**  |                                                                                   |
| **Return**      | Return validation logic to calling place on the basis of request method           |
| **Source**      | app\\Http\\Requests\\PublicHealth\\WaterSamplesRequest.php                        |
| **Remarks**     | Format for validation rule: ‘field_name’=\>’validation_rule1 \| validation_rule2’ |

### Models

The models contain the connection between the model and the table defined by

\$table = ‘public_health. water_samples’ as well as the primary key defined by

primaryKey= ‘id’

## Waterborne Hotspot

### Tables

Waterborne Hotspot is under FSM module and uses the following table:

-   waterborne_hotspots

The corresponding tables have their respective models that are named in Pascal Case in singular form. Hotspots model is located at app\\Models\\Fsm\\.

### Views

All views used by this module is stored in resources\\views\\fsm\\hotspots

-   hotspots.index: lists hotspots records.

-   hotspots.create: opens form and calls partial-form for form contents

-   hotspots.partial-form: creates form content

-   hotspots.edit.blade: opens form and calls partial-form for form contents

-   hotspots.history: lists all past edits of the record

-   hotspots.create: lists hotspots records

-   hotspots.show: displays all attributes of particular record

### HotspotController

app\\Http\\Controllers\\Fsm\\HotspotController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(HotspotServiceClass)                            |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php                     |

| **Function**    | index()                                                                         |
|-----------------|---------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database.    |
| **Parameters**  | null                                                                            |
| **Return**      | fsm/hotspots.index compact('page_title','wards','enumValues','hotspotLocation') |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php                              |

| **Function**    | create()                                                                                          |
|-----------------|---------------------------------------------------------------------------------------------------|
| **Description** | Returns the form to create new waterborne hotspot with dropdown values fetched from the database. |
| **Parameters**  | null                                                                                              |
| **Return**      | fsm/hotspots.create compact('page_title','wards', 'maxDate', 'minDate','diesase', 'enumValues')   |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php                                                |

| **Function**    | store()                                                                 |
|-----------------|-------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data        |
| **Parameters**  | HotspotRequest \$request                                                |
| **Return**      | Success or error message.                                               |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php                      |
| **Remarks**     | hotspotServiceClass-\>storeData(\$request)  Service Class Function Name |

| **Function**    | show()                                                         |
|-----------------|----------------------------------------------------------------|
| **Description** | Returns the page displaying individual waterborne hotspot data |
| **Parameters**  | \$id                                                           |
| **Return**      | \\Illuminate\\Http\\Response                                   |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php             |
| **Remarks**     | hotspotServiceClass-\>showData(\$id) (service class function)  |

| **Function**    | history()                                                |
|-----------------|----------------------------------------------------------|
| **Description** | lists all past edits of the record                       |
| **Parameters**  | \$id                                                     |
| **Return**      | fsm/hotspots.history   compact('page_title', ‘Hotspots’) |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php       |

| **Function**    | edit()                                                                                                      |
|-----------------|-------------------------------------------------------------------------------------------------------------|
| **Description** | Returns the edit form page displaying pre-existing individual building data as well                         |
| **Parameters**  | \$id                                                                                                        |
| **Return**      | fsm/hotspots.edit compact('page_title', 'wards', 'Hotspots', 'geom', 'lat', 'long','diesase', 'enumValues') |
| **Source**      | app\\Http\\Controllers\\ Fsm\\HotspotController.php                                                         |

| **Function**    | update()                                                                             |
|-----------------|--------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating waterborne hotspot data |
| **Parameters**  | HotspotRequest \$request, \$id                                                       |
| **Return**      | Success or error message.                                                            |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php                                   |
| **Remarks**     | hotspotServiceClass-\>updateData(\$request, \$id)  (service class function)          |

| **Function**    | destroy()                                                |
|-----------------|----------------------------------------------------------|
| **Description** | Handles the process of deleting waterborne hotspots data |
| **Parameters**  | \$id                                                     |
| **Return**      | Redirection with success/failure message                 |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php       |

| **Function**    | export()                                                                              |
|-----------------|---------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting waterborne hotspot data |
| **Parameters**  | Request \$request                                                                     |
| **Return**      | CSV file containing hotspots data                                                     |
| **Source**      | app\\Http\\Controllers\\Fsm\\HotspotController.php                                    |
| **Remarks**     | HotspotServiceClass-\>download(\$data);  (service class function)                     |

### HotspotServiceClass

Location: app\\Services\\Fsm\\HotspotServiceClass.php

The Service Class contains all the business logic. It contains all the functions that are being called in the HotspotController.

|  **Function**   | storeData()                                       |
|-----------------|---------------------------------------------------|
| **Description** | Handles the process of adding new hotspot data.   |
| **Parameters**  | \$request                                         |
| **Return**      | Success or error message, stores data to hotspots |
| **Source**      | app\\Services\\Fsm\\HotspotServiceClass.php       |

|  **Function**   | updateData()                                       |
|-----------------|----------------------------------------------------|
| **Description** | Handles the process of adding new hotspot data.    |
| **Parameters**  | \$request, \$id                                    |
| **Return**      | Success or error message, updates data to hotspots |
| **Source**      | app\\Services\\Fsm\\HotspotServiceClass.php        |

|  **Function**   | download()                                                      |
|-----------------|-----------------------------------------------------------------|
| **Description** | Handles the process of exporting building and owner information |
| **Parameters**  |                                                                 |
| **Return**      | Returns CSV                                                     |
| **Source**      | app\\Services\\Fsm\\HotspotServiceClass.php                     |

| **Function**    | fetchData()                                                             |
|-----------------|-------------------------------------------------------------------------|
| **Description** | Handles the process of fetching building and owner data for data tables |
| **Parameters**  | \$request                                                               |
| **Return**      | Returns data of building and owner table for datatables                 |
| **Source**      | app\\Services\\Fsm\\HotspotServiceClass.php                             |

### HotspotRequest

Location: app\\Http\\Requests\\Fsm\\HotspotRequest.php)

HotspotRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

|  **Function**   | authorize()                                  |
|-----------------|----------------------------------------------|
| **Description** | Determines if user is authenticated or not   |
| **Parameters**  |                                              |
| **Return**      | Returns true                                 |
| **Source**      | app\\Http\\Requests\\Fsm\\HotspotRequest.php |

| **Function**    | message()                                                                                                                         |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Message to be displayed in case of validation error                                                                               |
| **Parameters**  |                                                                                                                                   |
| **Return**      | Return validation message                                                                                                         |
| **Source**      | app\\Http\\Requests\\Fsm\\HotspotRequest.php                                                                                      |
| **Remarks**     | Need to include errors.list.blade that displays the error message in dashboard Format: ‘Field.validation_rule’ =\>”error_message” |

| **Function**    | rules()                                                                           |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | Contains the validation rules                                                     |
| **Parameters**  |                                                                                   |
| **Return**      | Return validation logic to calling place on the basis of request method           |
| **Source**      | app\\Http\\Requests\\Fsm\\HotspotRequest.php                                      |
| **Remarks**     | Format for validation rule: ‘field_name’=\>’validation_rule1 \| validation_rule2’ |

| **Function**    | store()                                                                           |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | validation rules that apply to the request.                                       |
| **Parameters**  | null                                                                              |
| **Return**      | Return validation logic to calling place for store                                |
| **Source**      | app\\Http\\Requests\\Fsm\\HotspotRequest.php                                      |
| **Remarks**     | Format for validation rule: ‘field_name’=\>’validation_rule1 \| validation_rule2’ |

| **Function**    | update ()                                                                         |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | validation rules that apply to the request.                                       |
| **Parameters**  | null                                                                              |
| **Return**      | Return validation logic to calling place for update                               |
| **Source**      | app\\Http\\Requests\\Fsm\\HotspotRequest.php                                      |
| **Remarks**     | Format for validation rule: ‘field_name’=\>’validation_rule1 \| validation_rule2’ |

### Models

The models contain the connection between the model and the table defined by

\$table = ‘public_health.waterborne_hotspots’ as well as the primary key defined by

primaryKey= ‘id’

## Waterborne Case Information

### Tables

Yearly waterborne case information is under Public Health module and uses the following table:

yearly_waterborne_cases

The corresponding tables have their respective models that are named in Pascal Case in singular form. YearlyWaterborne model is located at app\\Models\\PublicHealth\\.

### Views

All views used by this module is stored in resources\\views\\public-health\\waterborne

-   waterborne.index: lists waterborne cases records.

-   waterborne.create: opens form and calls partial-form for form contents

-   waterborne.partial-form: creates form content

-   waterborne.edit.blade: opens form and calls partial-form for form contents

-   waterborne.history: lists all past edits of the record

-   waterborne.show: displays all attributes of particular record

### YearlyWaterborneController

app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(WaterborneService)                              |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php   |

| **Function**    | index()                                                                      |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page with dropdown values fetched from database. |
| **Parameters**  | null                                                                         |
| **Return**      | public-health/waterborne.index compact('page_title','years','enumValues')    |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php         |

| **Function**    | getData()                                                                   |
|-----------------|-----------------------------------------------------------------------------|
| **Description** | Fetches data using the WaterborneServiceClass based on the provided request |
| **Parameters**  | Request \$request                                                           |
| **Return**      | The fetched data                                                            |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php        |
| **Remarks**     | fetchData(\$request) Service Class Function Name                            |

| **Function**    | create()                                                                                                     |
|-----------------|--------------------------------------------------------------------------------------------------------------|
| **Description** | Returns the form to create new yearly waterborne case with dropdown values fetched from the database.        |
| **Parameters**  | null                                                                                                         |
| **Return**      | public-health/waterborne.create compact('page_title', 'wards', 'maxDate', 'minDate','diesase', 'enumValues') |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php                                         |

| **Function**    | store(YearlyWaterborneRequest \$request)                               |
|-----------------|------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of storing data       |
| **Parameters**  | YearlyWaterborneRequest \$request                                      |
| **Return**      | Success or error message.                                              |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php   |
| **Remarks**     | storeData (\$request) Service Class Function Name                      |

| **Function**    | show()                                                                 |
|-----------------|------------------------------------------------------------------------|
| **Description** | Returns the page displaying individual yearly waterborne cases data    |
| **Parameters**  | \$id                                                                   |
| **Return**      | Public-health/waterborne.show                                          |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php   |
| **Remarks**     | showData(\$id) Service Class Function Name                             |

| **Function**    | history()                                                              |
|-----------------|------------------------------------------------------------------------|
| **Description** | lists all past edits of the record                                     |
| **Parameters**  | \$id                                                                   |
| **Return**      | public-health/waterborne.history   compact('page_title', ‘Waterborne’) |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php   |

| **Function**    | edit()                                                                                                    |
|-----------------|-----------------------------------------------------------------------------------------------------------|
| **Description** | Returns the edit form page                                                                                |
| **Parameters**  | \$id                                                                                                      |
| **Return**      | public-health/waterborne.edit compact('page_title', 'wards', 'Waterborne','year','diesase', 'enumValues') |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php                                      |

| **Function**    | update()                                                                    |
|-----------------|-----------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of updating help desk data |
| **Parameters**  | YearlyWaterborneRequest \$request, \$id                                     |
| **Return**      | Success or error message.                                                   |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php        |
| **Remarks**     | storeData(Request \$request) (service class function)                       |

| **Function**    | destroy()                                                              |
|-----------------|------------------------------------------------------------------------|
| **Description** | Handles the process of deleting yearly waterborne cases data           |
| **Parameters**  | \$id                                                                   |
| **Return**      | Redirection with success/failure message                               |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php   |

| **Function**    | exportData()                                                                               |
|-----------------|--------------------------------------------------------------------------------------------|
| **Description** | Calls the service class that handles the process of exporting yearly waterborne cases data |
| **Parameters**  | \$data                                                                                     |
| **Return**      | CSV file containing yearly waterborne cases data                                           |
| **Source**      | app\\Http\\Controllers\\PublicHealth\\YearlyWaterborneController.php                       |

### WaterborneService

Location: app\\Services\\PublicHealth\\WaterborneService.php

The Service Class contains all the business logic. It contains all the functions that are being called in the YearlyWaterborneControllers.php

|  **Function**   | storeData()                                                       |
|-----------------|-------------------------------------------------------------------|
| **Description** | Handles the process of adding new waterborne cases.               |
| **Parameters**  | \$id,\$data                                                       |
| **Return**      | Success or error message, stores/updates data to waterborne cases |
| **Source**      | app\\Services\\PublicHealth\\WaterborneService.php                |

|  **Function**   | download()                                                      |
|-----------------|-----------------------------------------------------------------|
| **Description** | Handles the process of exporting building and owner information |
| **Parameters**  |                                                                 |
| **Return**      | Returns CSV                                                     |
| **Source**      | app\\Services\\PublicHealth\\WaterborneService.php              |

| **Function**    | fetchData()                                                                  |
|-----------------|------------------------------------------------------------------------------|
| **Description** | Handles the process of fetching yearly waterborne cases data for data tables |
| **Parameters**  | \$request                                                                    |
| **Return**      | Returns data of waterborne cases data for datatables                         |
| **Source**      | app\\Services\\PublicHealth\\WaterborneService.php                           |

### YearlyWaterborneRequest

Location: app\\Http\\Requests\\PublicHealth\\YearlyWaterborneRequest.php)

YearlyWaterborneRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

|  **Function**   | authorize()                                                    |
|-----------------|----------------------------------------------------------------|
| **Description** | Determines if user is authenticated or not                     |
| **Parameters**  | null                                                           |
| **Return**      | Returns true                                                   |
| **Source**      | app\\Http\\Requests\\PublicHealth\\YearlyWaterborneRequest.php |

| **Function**    | message()                                                                                                                         |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Message to be displayed in case of validation error                                                                               |
| **Parameters**  |                                                                                                                                   |
| **Return**      | Return validation message                                                                                                         |
| **Source**      | app\\Http\\Requests\\PublicHealth\\YearlyWaterborneRequest.php                                                                    |
| **Remarks**     | Need to include errors.list.blade that displays the error message in dashboard Format: ‘Field.validation_rule’ =\>”error_message” |

| **Function**    | rules()                                                                           |
|-----------------|-----------------------------------------------------------------------------------|
| **Description** | Contains the validation rules                                                     |
| **Parameters**  |                                                                                   |
| **Return**      | Return validation logic to calling place                                          |
| **Source**      | app\\Http\\Requests\\PublicHealth\\YearlyWaterborneRequest.php                    |
| **Remarks**     | Format for validation rule: ‘field_name’=\>’validation_rule1 \| validation_rule2’ |

### Models

The models contain the connection between the model and the table defined by

\$table = ‘public_health.yearly_waterborne_cases’ as well as the primary key defined by

primaryKey= ‘id’
