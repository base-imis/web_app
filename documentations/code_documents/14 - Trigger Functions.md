Version: V1.0.0

#  Trigger Functions

Triggers in PostgreSQL are database functions that automatically respond to certain events, such as INSERT, UPDATE, or DELETE operations on a table. In IMIS, for updating count values in a summary table, triggers are set up to automatically update the count whenever a relevant operation occurs on the base table. Use of triggers in PostgreSQL provides a mechanism for automatically updating summary tables, ensuring data integrity, efficiency, real-time updates, and scalability in database operations.

## GridsnWardpl_fncntgrCron

Description: Creates function and trigger for grid and wardpl and summarychart

app\\Console\\Commands\\GridsnWardpl_fncntgrcron.php

Command: php artisan buildfunction:updatecount

handle()

The handle function calls the following functions stored in

config\\fnc_n_tgr.php

-   fnc_set_buildings
    -   updates grid and ward summary information based on changes in the building_info.buildings table. It calculates various statistics and updates corresponding fields in the layer_info.grids and layer_info.wards tables.
-   tgr_set_gridsNwardpl_buildings
    -   sets trigger on table building_info.buildings that call the function fnc_set_buildings after insert or delete of each row.
-   fnc_set_containments
    -   updates grid and ward summary information based on changes in the fsm.containments table. It calculates various statistics and updates corresponding fields in the layer_info.grids and layer_info.wards tables.
-   tgr_set_gridsNwardpl_containments
    -   sets trigger on table fsm.containments that call the function fnc_set_containments after insert or delete of each row.
-   fnc_set_roadline
    -   updates the total_rdlen column in the grids and wards table. It calculates the total length of roads intersecting with each grid or ward geometry and rounds the result to two decimal places. The length is calculated in kilometers (km).
-   tgr_set_gridsNwardpl_roadline
    -   sets trigger on table utility_info.roads that call the function fnc_set_roadline after insert or delete of each row.
-   fnc_set_landusesummary
    -   creates or refresh materialized view named landuse_summaryforchart with summary data for various kinds of landuse types.
-   tgr_set_landusesummary
    -   sets trigger on table fsm.containments that call the function fnc_set_landusesummary after insert or delete of each row.
-   fnc_set_builtupperwardsummary
    -   creates or refresh materialized view named builtupperward_summaryforchart with summary data per ward for data where landuse class is Buildup.
-   tgr_set_builtupperwardsummary
    -   sets trigger on table fsm.containments that call the function fnc_set_builtupperwardsummary after insert or delete of each row.

## GridWardpl_whenApplicationCron

Description: Updates the counts of no of emptying in the grids and wards tables when changes occur in the fsm.applications table.

app\\Console\\Commands\\GridsnWardpl_Applicationcron.php

Command: php artisan updatecount:application

handle()method

-   This method is called when the console command is executed. It contains the main logic of the command.
-   The first part of the handle() method consists of two SQL queries (query_grids_no_emptying and query_wardpl_no_emptying) that update the no_emptying count in the grids and wards tables, respectively.
-   These queries use subqueries to count the number of records in the fsm.applications table that intersect with the geometries stored in the building_info.buildings table, and then update the no_emptying column in the grids and wards tables accordingly.
-   The DB::statement() method is used to execute these SQL update queries.
-   After executing each query, it checks if the update was successful and logs a message indicating so in the Laravel log file (laravel.log).

## GridsnWardpl_whenBuildingCron

Description: Updates counts related to building structures in the grids and wards tables when changes occur in the building_info.buildings table.

app\\Console\\Commands\\GridsnWardpl_whenBuildingCron.php

Command: php artisan updatecount:building

handle()method

-   Two SQL queries are constructed to update various counts related to building structures in the grids and wards tables.
-   These queries use subqueries to calculate counts based on the geometry relationships between buildings and grid/ward polygons.
-   Counts are calculated for different types of building structures (no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet), as well as population served and household served counts (no_popsrv, no_hhsrv).
-   The counts are updated in the respective grids and wards records.
-   The SQL queries are executed using Laravel's database query builder (DB::statement()).
-   After executing each query, the command logs a message indicating whether the update was successful or not.

## GridsnWardpl_whenContainmentCron

Description: Update the counts of various types of containments (e.g., pit containments, septic tanks) in both the layer_info.grids and layer_info.wards tables based on changes in the fsm.containments table.

app\\Console\\Commands\\GridsnWardpl_whenContainmentCron.php

Command: php artisan updatecount:containment

handle()method

-   Two SQL queries are constructed to update the counts of different types of containments in the layer_info.grids and layer_info.wards tables, respectively. These queries use subqueries to count the number of records in the fsm.containments table that intersect with the geometries stored in the respective grid or ward geometries.
-   The SQL queries are executed using the DB::statement() method.
-   After executing each query, the command logs a message indicating whether the update was successful or not.

## GridWardpl_whenRoadlineCron.php

Description: Updates grids and wardpl count when utility_info.roads has changes

app\\Console\\Commands\\GridWardpl_whenRoadlineCron.php

Command : php artisan updatecount:roadline

handle()method

-   The first query updates the total_rdlen column in the layer_info.grids table. It calculates the total length of roads intersecting with each grid geometry and rounds the result to two decimal places. The length is calculated in kilometers (km).
-   The second query updates the total_rdlen column in the layer_info.wards table. Similar to the first query, it calculates the total length of roads intersecting with each ward geometry and rounds the result to two decimal places.
-   The SQL queries are executed using Laravel's DB::statement() method.
-   After executing each query, the command logs a message indicating whether the update was successful or not.

## MapTool_QryCron.php

Description: Creates function for grid and wardpl and summarychart

app\\Console\\Commands\\MapTool_QryCron.php

Command : php artisan buildfunction:maptool

handle()

The handle function calls the following functions stored in

config\\qry_maptool.php

-   fnc_set_getPointBufferBuildings
-   This function calculates various counts related to building types within a specified distance of a given point.
-   It uses PostgreSQL's PL/pgSQL language to define the function logic.
-   The function takes longitude, latitude, and distance parameters and returns a table of counts for different sanitation system types.
-   It filters buildings based on their sanitation system technology and geographic location.
-   The result includes counts for each sanitation system type and an aggregate count for other types.
-   fnc_getBufferPolygonBuildings
-   This function calculates counts of building types within a specified buffer polygon.
-   Like fnc_getPointBufferBuildings, it also uses PL/pgSQL language.
-   It takes a buffer polygon geometry and a distance parameter to define the buffer area.
-   Buildings intersecting with the buffer polygon are filtered based on their sanitation system technology.
-   The result includes counts for each sanitation system type and an aggregate count for other types.

The function returns Command::SUCCESS to indicate that the command execution was successful. This is a standard practice in Laravel console commands.

## TaxPaymentDataImport.php

Description: Import of Tax data from excel/csv file to database

app\\Console\\Commands\\TaxPaymentDataImport.php

Command : php artisan import:tax

handle()

-   The command starts by recording the current time (\$start_time) to calculate the execution time later.
-   It accesses the storage disk named 'importtax' to locate the Excel/CSV files.
-   It retrieves the filename of the first file in the storage directory.
-   It truncates the tax_payments table and restarts its identity sequence to ensure a clean import.
-   It imports the data from the Excel/CSV file into the database using the TaxImport class.
-   It records the end time of the execution and calculates the total execution time.
-   It logs a message indicating the successful import of tax data along with the execution time.
-   It also displays an informational message about the successful import and the execution time.

## TaxPaymentFunctionBuild

Description: Creates Functions to create table and update building owner when new tax data is imported

app\\Console\\Commands\\TaxPaymentFunctionsBuild.php

Command : php artisan buildfunction:tax

handle()

The handle function calls the function stored in

-   fnc_create_taxpaymentstatus
    -   creates a materialized view named tax_payment_status with few steps of operations. The view tax_payment_status is created with calculated due_years based on last_payment_date (default value:99) and Match value based on the presence of bin in the system.
-   fnc_updonimprt_gridnward_tax
    -   updates proportion column in tables ‘wards’ and ‘grids’.

## WaterSupplyDataImport.php

Description: The code imports Water Supply data from an Excel/CSV file into the database by first selecting the file from storage, truncating the watersupply_payments table, resetting its identity column sequence, and then executing the import using a custom import class named WaterSupplyImport.

app\\Console\\Commands\\WaterSupplyDataImport.php

Command : php artisan import:watersupply;

handle()

-   Itrecords the start time of the data import process.
-   It retrieves the file path from the storage disk named 'importwatersupply' and sanitizes it for use.
-   It selects the first file from the list of contents in the 'importwatersupply' disk.
-   It truncates the watersupply_payments table, resets its identity column sequence, and imports data from the selected file using the WaterSupplyImport class.
-   It records the end time of the data import process, logs the import success message along with the execution time, and returns a success status for the command execution.

## WaterSupplyFunctionBuild**

Description: Creates Functions to create table and update building owner when new water supply payment data is imported

app\\Console\\Commands\\WaterSupplyFunctionBuild

Command : php artisan buildfunction:watersupply

handle()

The handle function calls the function stored in

-   fnc_create\_ watersupplystatus
    -   creates a materialized view named watersupply_payment_status with few steps of operations. The view watersupply_payment_status is created with calculated due_years based on last_payment_date (default value:99) and Match value based on the presence of bin in the system.
-   fnc_updonimprt_gridnward\_ watersupply
    -   updates proportion column in tables ‘wards’ and ‘grids’.
