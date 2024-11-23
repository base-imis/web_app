Version: V1.0.0

# Solid Waste Information Support System

The Solid Waste Information Support System sub-module maintains information on Solid Waste Management data

## Workflow

Solid Waste data is imported into swmservice\_payment\_status table via CSV file. The file is stored in the server in storage disk path as defined in config\filesystems.php for 'importtax'.

Consecutively, triggers are run which creates materialized view with few steps of operations including create status table, update solid waste building owner information and updates proportion column in tables ‘wards’ and ‘grids’. 

The table swmservice\_payment\_status is created with calculated due\_years based on last\_payment\_date (default value:99) and Match value based on the presence of bin in the system. For buildings with match swm\_customer\_id, customer\_name, customer\_contact are updated and if data is for new bin, then new data is created in building\_info.owners table.

## Files

- Command files: Command files SwmPaymentDataImport.php and SwmPayment FunctionBuild.php includes the commands or queries that are supposed to run when the php command is run and are created in app/Console/Commands.
- Config file: Config file named ‘swmpayment-info.php’ inside the ‘Config’ directory includes all the Sql functions and queries in array form which are called to create commands.
- Import class file: SwmImport file inside imports directory includes model, rules, map etc. functions and class is called inside store() function of controller class SwmServicePaymentController

  Location: app/Imports/SwmImport.php 

## Commands

For the initial launching, run the following command:

|*php artisan buildfunction:swmpayment*|
|----------------------------------|

The above code creates/builds functions and triggers in the database.

To test if the import command is working or not. Use Command:

|*php artisan import: swmpayment*|
|----------------------------------|

