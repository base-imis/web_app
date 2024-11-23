Version: V1.0.0

# 12. Sewer Connection Iss

## Tables

The Sewer Connection Module uses the following tables:

-   Sewer_connections: stores survey information received from the mobile application

The corresponding tables have their respective models that are named in Pascal Case in singluar form. The sewer connection modules are located at app\\Http\\Models\\SewerConnection\\SewerConnectionController.

### Views

All views used by this module is stored in resources\\views\\sewer-connection

-   index: lists sewer connection records (db: sewer_connection.sewer_connections)
-   mapView: opens modal and displays building and sewer on map.
-   Approve: opens modal and updates building’s sewer and sanitation system id.

### SewerConnectionController

app\\Http\\Controllers\\SewerConnection\\SewerConnectionController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

| **Function**    | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  |                                                                        |
| **Return**      | null                                                                   |

| **Function**    | index()                                                                                                                                                                          |
|-----------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Returns the index.blade.php page  Called when SewerConnection is selected from sidebar.blade.php via  \<a href="{{action('SewerConnection\\SewerConnectionController@index') }}" |
| **Parameters**  | null                                                                                                                                                                             |
| **Return**      | sewer-connection.index compact('page_title')                                                                                                                                     |

| **Function**    | getAllData()                                                                                                                                                    |
|-----------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | Returns the data stored in sewer connection page which has is_enabled as true ,Called when Sewer Connection’s index page is loaded via ajax call for datatable. |
| **Parameters**  | null                                                                                                                                                            |
| **Return**      | sewer-connections.index compact('page_title')                                                                                                                   |

| **Function**    | destroy ()                                    |
|-----------------|-----------------------------------------------|
| **Description** | Soft deletes the sewer connection information |
| **Parameters**  | \$id                                          |
| **Return**      | Success or error message                      |
| **Remarks**     |                                               |

| **Function**    | getGeom()                                                                                                                                   |
|-----------------|---------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | This function retrieves a building's geometry in WKT format based on a given BIN and returns an error message if the building is not found. |
| **Parameters**  | \$bin                                                                                                                                       |
| **Return**      | Building's geometry in WKT format                                                                                                           |

| **Function**    | getsewerGeom()                                                                                                                               |
|-----------------|----------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | This function retrieves a sewer's geometry in WKT format based on a given Sewer code and returns an error message if the sewer is not found. |
| **Parameters**  | \$sewer                                                                                                                                      |
| **Return**      | Sewer's geometry in WKT format                                                                                                               |

| **Function**    | approve()                                                                                                                                                                                  |
|-----------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Description** | This function approves a building by updating its sanitation system and sewer code, deletes any associated BuildContain record, and returns an error message if the building is not found. |
| **Parameters**  | \$bin                                                                                                                                                                                      |
| **Return**      | Returns an error message if the building is not found and success after the success                                                                                                        |
