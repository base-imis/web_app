Version: V1.0.0

# Utility IMS

## Utility Dashboard

The dashboard utilizes the following tools to deliver data and insights:

-   Charts: Created with Chart.js for visual representation of trends.
-   Cards: Built with Bootstrap, HTML, and CSS.
-   Icons: SVG and Font Awesome.

### Data Retrieval

-   Controller: The UtilityDashboardController (located at app\\Http\\Controllers\\UtilityInfo) initiates data fetching.
-   Service Class: The UtilityDashboardService (located at app\\Services\\UtilityInfo) has been called by controller to perform necessary operations.

Charts

-   Data Fetching: Laravel Eloquent & Raw SQL queries are used to fetch data for charts.

### Views

**Layout:** The core dashboard structure is defined in the resources\\views\\dashboard\\utilityDashboard.blade.php file. This file acts as the overall layout and likely includes placeholders for the various components.

Charts are fetched from resources\\views\\dashboard\\charts.

## Road Network

### Tables

Road Network is under Utility IMS module and uses the following table:

roads: stores with information related to road infrastructure.

The corresponding tables have their respective models that are named in Pascal Case in singular form. Roadline model is located at app\\Models\\UtilityInfo\\.

### Views

All views used by this module is stored in resources\\views\\utility-info\\road-lines

road-lines.index: lists help desks records.

road-lines.create: opens form and calls partial-form for form contents

road-lines.partial-form: creates form content

road-lines.edit.blade: opens form and calls partial-form for form contents

road-lines.history: lists all past edits of the record

### Models

The models contain the connection between the model and the table defined by \$table = ‘utility_info.roads’ as well as the primary key defined by primaryKey= ‘code’

Roadline Model

Location: app\\Models\\UtilityInfo\\Roadline.php

There are multiple relationships defined in the Roadline Model. They are:

buildings: belongsToMany relationship (1 to n relationship)

### Roadline Request

Location: app\\Http\\Requests\\UtilityInfo\\RoadLineRequest.php

RoadLineRequest handles all validation. It handles validation logic as well as error messages to be displayed.

Roadline follows CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" Section **2 - Technical Information/ Basic CRUD** for more information.

However, new roads can be added to the map itself using **Add Road** Tool. The details are included in the maps’ **Add Road** section under **Map Toolbar**.

## Sewer Network

### Tables

Sewer Network is under Utility IMS module and uses the following table:

sewers: manages data associated with sewer systems.

The corresponding tables have their respective models that are named in Pascal Case in singular form. SewerLine model is located at app\\Models\\UtilityInfo\\.

### Views

All views used by this module is stored in resources\\views\\utility-info\\sewer-lines

sewer-lines.index: lists help desks records.

sewer-lines.create: opens form and calls partial-form for form contents

sewer-lines.partial-form: creates form content

sewer-lines.edit.blade: opens form and calls partial-form for form contents

sewer-lines.history: lists all past edits of the record

### Models

The models contain the connection between the model and the table defined by \$table = ‘utility_info.sewers’ as well as the primary key defined by primaryKey= ‘code’

SewerLine Model

Location: app\\Models\\UtilityInfo\\SewerLine.php

There are multiple relationships defined in the SewerLine Model. They are:

buildings: belongsToMany relationship (1 to n relationship)

### SewerLine Request

Location: app\\Http\\Requests\\UtilityInfo\\SewerLineRequest.php

SewerLineRequest handles all validation. It handles validation logic as well as error messages to be displayed.

SewerLine follows CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" section **2 - Technical Information/ Basic CRUD** for more information.

## Water Supply Network

### Tables

Water Supply Network is under Utility IMS module and uses the following table:

water_supplys: contains details about water supply networks

The corresponding tables have their respective models that are named in Pascal Case in singular form. WaterSupplys model is located at app\\Models\\UtilityInfo\\.

### Views

All views used by this module is stored in resources\\views\\utility-info\\sewer-lines C:\\wamp\\www\\insol\\imis-revamp2.0\\resources\\views\\utility-info\\water-supplys.

sewer-lines.index: lists help desks records.

sewer-lines.create: opens form and calls partial-form for form contents

sewer-lines.partial-form: creates form content

sewer-lines.edit.blade: opens form and calls partial-form for form contents

sewer-lines.history: lists all past edits of the record

### Models

The models contain the connection between the model and the table defined by \$table = ‘utility_info.water_supplys’ as well as the primary key defined by primaryKey= ‘code’

WaterSupplys Model

Location: \\app\\Models\\UtilityInfo\\WaterSupplys.php

There are multiple relationships defined in the WaterSupplys Model. They are:

buildings: belongsToMany relationship (1 to n relationship)

### WaterSupplys Request

Location: app\\Http\\Requests\\UtilityInfo\\WaterSupplysRequest.php

WaterSupplysRequest handles all validation. It handles validation logic as well as error messages to be displayed.

WaterSupplys follows CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" Section **2 - Technical Information/ Basic CRUD** for more information.

## Drain Network

### Tables

Sewer Network is under Utility IMS module and uses the following table:

drains: stores information regarding drainage systems

The corresponding tables have their respective models that are named in Pascal Case in singular form. SewerLine model is located at app\\Models\\UtilityInfo\\.

### Views

All views used by this module is stored in resources\\views\\utility-info\\sewer-lines

sewer-lines.index: lists help desks records.

sewer-lines.create: opens form and calls partial-form for form contents

sewer-lines.partial-form: creates form content

sewer-lines.edit.blade: opens form and calls partial-form for form contents

sewer-lines.history: lists all past edits of the record

### Models

The models contain the connection between the model and the table defined by \$table = ‘utility_info.drains’ as well as the primary key defined by primaryKey= ‘code’

Drain Model

Location: app\\Models\\UtilityInfo\\Drain.php

There are multiple relationships defined in the Drain Model. They are:

buildings: belongsToMany relationship (1 to n relationship)

### Drain Request

Location: app\\Http\\Requests\\UtilityInfo\\DrainRequest.php

DrainRequest handles all validation. It handles validation logic as well as error messages to be displayed.

Drain follows CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" section **2 - Technical Information/ Basic CRUD** for more information.
