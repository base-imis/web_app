Version: V1.0.0

# Dashboard

The dashboard utilizes the following tools to deliver data and insights:

-   Charts: Created with Chart.js for visual representation of data.
-   Cards: Built with Bootstrap, HTML, and CSS. These cards can house both count boxes and charts.
-   Icons: SVG and Font Awesome.

## Data Retrieval

-   Controller: The HomeController (located at app\\Http\\Controllers\\HomeController.php) initiates data fetching. Clear code comments within the controller aid in understanding this process.

Charts

-   Data Fetching: Raw SQL queries are used to fetch data for charts.
-   Service Class: A dedicated service class (located at app\\Services\\DashboardService.php) houses the logic for these raw queries.

Count Boxes

-   Data Fetching: Laravel Eloquent retrieves data for count boxes.
-   Filtering: Specific count boxes offer year-based filtering. Simply select a year to focus on a particular timeframe and narrow down the displayed data for a more focused analysis.
-   Year-Based Filtering Logic: When you submit a year (e.g., "2024"), the HomeController utilizes an if-else statement to determine the appropriate data to display. This ensures you see either a specific chart for the requested year or a general chart encompassing all data.

## Views

Layout

The core dashboard structure is defined in the resources\\views\\dashboard\\indexAdmin.blade.php file. This file acts as the overall layout and includes various components that has charts and count boxes.

Components

Count boxes and charts are incorporated as separate components within the layout:

Count boxes are located in resources\\views\\dashboard\\countBox.

Charts are located in resources\\views\\dashboard\\charts.
