# KML Viewer QA Process

## Purpose

The KML Viewer is a map-index tool that allows an authenticated user to select a KML file and view its geographic features on the map. It validates the uploaded geometries against the municipality boundary and, for polygon features, retrieves related building information.

The supported workflow is file selection through the **KML Viewer** control. Drag-and-drop is not part of the supported user workflow.

## How the KML Viewer Works

1. The user opens the Maps page and clicks **KML Viewer**.
2. The application opens the KML file picker.
3. OpenLayers parses the selected file with `ol.format.KML`.
4. The application rejects empty files and files containing more than 1,000 features.
5. Feature geometries are sent to `POST /maps/check-geometry` for municipality-intersection validation.
6. Intersecting features are displayed and the map zooms to their extent.
7. Polygon features are sent to `POST /maps/get-kml-summary-info`.
8. Related buildings and summary information are displayed.
9. The polygon geometry can be submitted to `POST /maps/get-kml-info-report-csv` for an Excel report.

## QA Test Procedure

### Valid KML file

1. Log in and open the Maps page.
2. Click **KML Viewer**.
3. Select a valid point, line, or polygon KML file.

Expected result: the KML is parsed, validated, displayed on the map, and the map zooms to the valid features.

### Polygon summary

1. Select a polygon KML that intersects the municipality.
2. Wait for processing to complete.

Expected result: related buildings are displayed and summary information appears in the popup.

### KML summary report

1. Select a polygon KML that intersects buildings and containments.
2. Wait for the building layer and summary popup to load.
3. Submit the KML summary report/export form.
4. Confirm that the Excel file downloads.

Expected result:

- The report contains a **Build-Contain List** sheet.
- The sheet contains **Polygon Name**, **BIN**, and **Containment ID**.
- The polygon name matches the uploaded KML feature name, or uses **Unnamed Feature** when no name exists.
- BIN and containment values match the returned building-containment relationships.

### Negative tests

| Test case | Expected result |
|---|---|
| Select a non-KML file | Invalid KML warning |
| Select malformed KML | KML could not be read error |
| Select an empty KML | No features found warning |
| Select more than 1,000 features | Feature-limit warning |
| Select a completely outside KML | No features intersect error |
| Submit without valid polygon geometry | Invalid KML geometry message |
| Summary request fails | Error is displayed and loading stops |

## Implementation Traceability

| Component | Responsibility |
|---|---|
| `resources/views/maps/index.blade.php` | KML file selection, parsing, styling, validation, and map display |
| `MapsController@checkGeometry` | Municipality-intersection validation |
| `MapsController@getKmlSummaryInfo` | Related building and summary lookup |
| `MapsController@getKmlInfoReportCsv` | Excel report download |
| `SummaryInfoMultiSheetKmlExport` | Builds the multi-sheet KML report |
| `BuildContainKmlExport` | Builds the **Build-Contain List** sheet |

