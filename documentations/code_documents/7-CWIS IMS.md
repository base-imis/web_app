Version: V1.0.0

# CWIS IMS

## CWIS Dashboard

The CWIS dashboard incorporates charts (using Chart.js) and cards (using HTML and CSS) to visualize data. This section delves into the implementation of the doughnut chart.

### Data Retrieval

- Data is fetched from the CwisNewDashboardController (located at app\\Http\\Controllers\\Cwis\\CwisNewDashboardController.php).
- The getall() function retrieves data using the following steps:

1. Queries the cwis_mne table (using the Eloquent ORM).
2. Filters records based on year and indicator_code.
3. Selects specific columns, applying formatting to heading.
4. Stores the retrieved records in the variable.

### Views

- View code resides at resources\\views\\cwis\\cwis-dashboard\\chart-layout\\cwis-dash-layout.blade.php.
- It performs the following actions:

1. Fetches data passed from the controller.
2. Uses a \<div\> with a canvas element to display the chart.
3. Calls the createDoughnutChart() function upon DOM content loading.

### JavaScript Function Breakdown

- The createDoughnutChart() function generates the doughnut chart with these parameters:
- percent: The percentage value to display.
- color: The color of the chart.
- canvasId: The ID of the canvas element.
- containerId: The ID of the container element.
- It performs the following actions:

1. Retrieves canvas and container elements from the DOM.
2. Caps percentValue at 100 if it exceeds 100.
3. Creates a div to display the percentage value.
4. Constructs a Chart.js doughnut chart instance:

- Sets chart type to doughnut.
- Specifies data, background color, 0 border width, 78% cutout, and disabled tooltips.

1. Sets animation duration to 1400 milliseconds.
2. Appends the percentage value div to the container.

Further generation of the formula has been mentioned in “CWIS Generator” below.

## CWIS Generator

### Tables

The CWIS module uses the following table:

- data_source: stores with information related indicators, its data types and more.
- data_cwis: stores yearly information related indicators and its value generated from the system or user input.

The corresponding tables have their respective models that are named in Pascal Case in singular form. The desludging vehicles modules are located at app\\Models\\Cwis\\cwis_mne.php.

### Views

All views used by this module is stored in resources\\views\\cwis\\cwis-df-mne

- cwis-df-mne.create: opens form and calls partial-form for form content.
- cwis-df-mne.index: lists existing records for cwis indicators of each year.
- cwis-df-mne.partial-form: creates form content for generation of new cwis indicator data for new year or edit existing cwis indicator value.

### Models

Location: app\\Models\\Cwis\\cwis_mne.php

### Data Retrieval

- Controller: The CwisMneController (located at app\\Http\\Controllers\\Cwis\\CwisMneController.php) initiates contains all the functions that are being called.
- Exports: App\\Exports\\MneCsvExport handles all the exports process of CWIS data.

CwisMneController follow CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" section above (2.2) for more information. Exceptional classes are as follows:

| **Function**    | cwis()                                                                                                                                                                  |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Description** | Calls the postgresql function insert_data_into_cwis_table(\$year) that handles the insert or generation process of new cwis data                                        |
| **Parameters**  | \$year                                                                                                                                                                  |
| **Return**      | json(\$result)                                                                                                                                                          |
| **Source**      | app\\Http\\Controllers\\Cwis\\CwisMneController.php                                                                                                                     |
| **Remarks**     | The new data is generated for new selected year with the reference from data_source table and the data value are calculated from the indicators definition and formula. |

| **Function**    | exportMneCsv()                                                                                         |
| --------------------- | ------------------------------------------------------------------------------------------------------ |
| **Description** | Filters data for selected year and calls the export class that handles the export process of cwis data |
| **Parameters**  | Request\$request                                                                                       |
| **Return**      | Downloaded File.                                                                                       |
| **Source**      | app\\Http\\Controllers\\Cwis\\CwisMneController.php                                                    |
| **Remarks**     | App\\Exports\\MneCsvExport                                                                             |

## KPI Dashboard

The KPI dashboard incorporates charts (using Chart.js) and cards (using HTML and CSS) to visualize key performance indicator data. This section delves into the implementation of the bar chart.

The generation of these elements occurs only upon setting the respective targets for the indicators within the kpi_targets table, as detailed in Section 7.4.

### Tables

KPI Dashboard is under FSM module and uses the following table:

- kpi_target: retrieves the information of kpi targets.
- service_providers: retrieves information of service providers.
- quarters: retrieves information of quarters for each year.

### Data Retrieval

- Data is fetched from the KpiDashboardController (located at app\\Http\\Controllers\\Fsm\\KpiDashboardController.php).
- A dedicated service class, located at app\\Services\\Fsm\\KpiDashboardService.php, exclusively contains the logic designed specifically for generating charts.
- Laravel Eloquent and raw SQL queries retrieves data for cards.
- Here, each indicator is calculated based on a derived formula, which is further explained as

  - Application Response Efficiency: A ratio of total containments emptied/total application received in the given time period.
  - Customer Satisfaction: A ratio of feedback with the “FSM Service Quality” field marked as “Yes”/total number of submitted feedback.
  - PPE compliance: A ratio of feedback with the “Sanitation workers wore PPE during desludging” field marked as “Yes”/total number of submitted feedback.
- Safe Desludging: A ratio of the total number of desludging recorded in treatment plant/ total number of containments emptied. (Assumption: 1 desludge is 1 containment)
- Inclusion: delivered services in LICs and Slums / total delivered services.
- Response Time: Average time between application and delivered service.
- Faecal Sludge Collection Ratio: Total amount of faecal sludge emptied from containments/ Total estimated sludge to be emptied in accessible areas.
- The filtering system allows for selecting a specific year and service provider. Upon choosing a year, cards are generated based on the selected year, and charts are generated by further breaking it down into four quarters: Q1, Q2, Q3, and Q4. Detailed information for these quarters can be obtained from the quarters table.
- Clear code comments within the controller aid in understanding this process.

### Views

**Layout:** The core dashboard structure is defined in the resources\\views\\fsm\\kpi-dashboard\\index.blade.php file. This file acts as the overall layout and includes placeholders for the various components. It fetches data passed from the controller.

**Components:** Cards and charts are incorporated as separate components within the layout:

- Cards are located in resources\\views\\layouts\\dashborad\\key-performance-indicator-card.
- Charts are located in resources\\views\\fsm\\kpi-dashboard\\KpiCharts.

## KPI Target

### Tables

KPI Target is under CWIS IMS module and uses the following table:

kpi_targets: store the primary information of the KPI targets

key_performance_indicators: retrieves information of key performance indicators

The corresponding tables have their respective models that are named in Pascal Case in singular form. KpiTarget model is located at app\\Models\\Fsm\\.

### Views

All views used by this module is stored in resources\\views\\fsm\\kpi-target

kpi-target.index: lists KPI targets records.

kpi-target.create: opens form and calls partial-form for form contents

kpi-target.partial-form: creates form content

kpi-target.edit.blade: opens form and calls partial-form for form contents

kpi-target.history: lists all past edits of the record

kpi-target.show: displays all attributes of particular record

### Models

The models contain the connection between the model and the table defined by

*\$table = ‘fsm.kpi_targets’* as well as the primary key defined by primaryKey= ‘id’

### KpiTarget Model

Location: app\\Models\\Fsm\\kpiTarget.php

KpiTarget follows CRUD operations, which usually have the same pattern. You can refer to the "Basic CRUD" Section **2 - Technical Information/ Basic CRUD** for more information.

### KpiTargetRequest

Location: app\\Http\\Requests\\Fsm\\KpiTargetRequest.php)

KpiTargetRequest handles all validation login. It handles validation logic as well as error messages to be displayed.

## CWIS Setting

### Tables

CWIS Setting is under Settings module and uses the following table:

public.site_settings

The corresponding tables have their respective models that are named in Pascal Case in singular form. CwisSetting model is located at app\\Models\\Fsm\\.

The CWIS setting sets the values for variables that are required for the CWIS Dashboard.

### Views

All views used by this module is stored in resources\\views\\fsm\\cwis-setting

cwis-setting.index: opens cwis setting form.

### CwisSettingController

app\\Http\\Controllers\\Fsm\\CwisSettingController.php

The controller’s main function is to provide the connection between the calling route and its subsequent function written in the Service Class.

The basic classes of the controller are:

|  **Function**   | \__construct()                                                         |
|-----------------|------------------------------------------------------------------------|
| **Description** | Initializes authentication, permissions and the service class instance |
| **Parameters**  | Service class instance(CwisSettingService)                             |
| **Return**      | null                                                                   |
| **Source**      | app\\Http\\Controllers\\Fsm\\CwisSettingController.php                 |

| **Function**    | index()                                                 |
|-----------------|---------------------------------------------------------|
| **Description** | Returns the index.blade.php page                        |
| **Parameters**  | null                                                    |
| **Return**      | fsm/cwis-setting.index compact('page_title', 'data')    |
| **Source**      | app\\Http\\Controllers\\Fsm\\CwisSettingController.php  |

| **Function**    | store()                                                         |
|-----------------|-----------------------------------------------------------------|
| **Description** | Store or update cwis setting data                               |
| **Parameters**  | Request \$request                                               |
| **Return**      | Redirection with success/failure message                        |
| **Source**      | app\\Http\\Controllers\\Fsm\\CwisSettingController.php          |
| **Remarks**     | cwissetting-\>storeOrUpdate(\$data) Service Class Function Name |

### CwisSettingService

Location: app\\Services\\Fsm\\CwisSettingService.php

The Service Class contains all the business logic. It contains all the functions that are being called in the CwisSettingController.php

|  **Function**   | storeOrUpdate()                                                |
|-----------------|----------------------------------------------------------------|
| **Description** | Handles the process of edditing cwis settings.                 |
| **Parameters**  | \$data                                                         |
| **Return**      | Success or error message, stores/updates data to cwis settings |
| **Source**      | app\\Services\\Fsm\\CwisSettingService.php                     |
