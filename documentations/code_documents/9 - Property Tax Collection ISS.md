Version: V1.0.0

# Property Tax Collection ISS

The Property Tax Collection Information Support System sub-module maintains information on Property Tax Payment data.

## Workflow

Property Tax payment data is imported into tax_payments table via CSV file. The file is stored in the server in storage disk path as defined in config\\filesystems.php for 'importtax'.

Consecutively, triggers are run which creates materialized view with few steps of operations including create status table, update tax building owner information and updates proportion column in tables ‘wards’ and ‘grids’.

The table tax_payment_status is created with calculated due_years based on last_payment_date (default value:99) and Match value based on the presence of bin in the system. For buildings with match tax_code, owner_name, owner_contact are updated and if data is for new bin, then new data is created in building_info.owners table.

## Files

-   Command files: Command files TaxDataImport.php and TaxFunctionBuild.php includes the commands or queries that are supposed to run when the php command is run and are created in app/Console/Commands.
-   Config file: Config file named ‘taxpayment-info.php’ inside the ‘Config’ directory includes all the Sql functions and queries in array form which are called to create commands.
-   Import class file: TaxImport file inside imports directory includes model, rules, map etc. functions and class is called inside store() function of controller class TaxPaymentController

    Location: app/Imports/TaxImport.php

## Commands

For the initial launching, run the following command:

| *php artisan buildfunction:tax* |
|---------------------------------|

The above code creates/builds functions and triggers in the database.

To test if the import command is working or not. Use Command:

| *php artisan import:tax* |
|--------------------------|
