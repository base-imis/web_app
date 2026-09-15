# IMIS API Technical Documentation

> **Security:** Protected API endpoints require a bearer access token. Authentication and permission-based authorization are enforced through middleware configured on the API routes before requests reach the controller.
>
> **Note:** Endpoint paths and route casing should be verified against the final `routes/api.php` before publication.

## BuildingAPIController

### GET Building Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/building/{bin}` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `application/json` — Required |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Building retrieved successfully."
}
```

### POST Building Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeBuilding` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading files |
| Accept | `application/json` — Required |

### PUT Building Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/updateBuilding/{bin}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading files |
| Accept | `application/json` — Required |

### POST/PUT Parameters

The following parameters apply to both create and update operations unless stated otherwise.

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| owner_name | string | Yes | Owner name |
| owner_nid | string | No | Owner national identification number |
| owner_gender | string/reference | Yes | Owner gender |
| owner_contact | integer/string | Yes | Must be a non-negative integer value |
| main_building | boolean/reference | Yes | Indicates whether this is the main building |
| building_associated_to | BIN reference | Conditional | Required when `main_building` is `0` |
| ward | string/reference | Yes | Building ward |
| road_code | string/reference | Yes | Associated road code |
| house_number | string | No | Nullable; must be unique where validation is applied |
| house_locality | string | No | Building locality |
| tax_code | string/reference | Yes | Municipal tax reference |
| surveyed_date | date | No | Survey date |
| structure_type_id | string/reference | Yes | Structure type |
| construction_year | date | Yes | Must not be later than the current date |
| floor_count | numeric | Yes | Minimum `0.1` |
| functional_use_id | string/reference | Yes | Functional use |
| use_category_id | string/reference | Conditional | Required when `functional_use_id` is provided |
| office_business_name | string | No | Office/business name |
| household_served | integer | Conditional | Required unless `use_category_id` is `34` or `35`; must be non-negative |
| male_population | integer | No | Must be non-negative |
| female_population | integer | No | Must be non-negative |
| other_population | integer | No | Must be non-negative |
| population_served | integer | Conditional | Required unless `use_category_id` is `34` or `35`; must be non-negative |
| diff_abled_male_pop | integer | No | Must not exceed `male_population` |
| diff_abled_female_pop | integer | No | Must not exceed `female_population` |
| diff_abled_others_pop | integer | No | Must not exceed `other_population` |
| low_income_hh | boolean/reference | Yes | Low-income household indicator |
| lic_status | boolean/reference | No | Used for low-income classification |
| lic_id | string/reference | Conditional | Required when `lic_status` is `1` |
| water_source_id | string/reference | Yes | Primary water source |
| water_customer_id | string | No | Water customer identifier |
| watersupply_pipe_code | string/reference | Conditional | Required when `water_source_id` is `1` |
| well_presence_status | boolean/reference | No | Indicates whether a well is present |
| distance_from_well | numeric | No | Distance from well |
| swm_customer_id | string | No | Solid-waste customer identifier |
| toilet_status | boolean/reference | Yes | Must be true/Yes for use categories `34` or `35` |
| toilet_count | integer | Conditional | Required when `toilet_status` is `1`; minimum `1` |
| sanitation_system_id | string/reference | Conditional | Required when `toilet_status` is `1` |
| defecation_place | string/reference | Conditional | Required when `toilet_status` is `0` |
| ctpt_name | string | Conditional | Required when `defecation_place` is `9` |
| household_with_private_toilet | integer | No | Must not exceed `household_served` |
| population_with_private_toilet | integer | No | Must not exceed `population_served` |
| sewer_code | string/reference | Conditional | Required for sewer-linked sanitation conditions |
| drain_code | string/reference | Conditional | Required for drain-linked sanitation conditions |
| type_id | string/reference | Conditional | Required when `sanitation_system_id` is `3` or `4` |
| size | numeric | Conditional | Required when `sanitation_system_id` is `3` or `4`; must be non-negative |
| tank_length | numeric | No | Must be non-negative |
| tank_width | numeric | No | Must be non-negative |
| depth | numeric | No | Must be non-negative |
| pit_depth | numeric | No | Must be non-negative |
| pit_diameter | numeric | No | Must be non-negative |
| pit_shape | string/reference | No | Pit shape |
| build_contain | BIN reference | Conditional | Required when `sanitation_system_id` is `11` |
| desludging_vehicle_accessible | boolean/reference | No | Desludging vehicle accessibility |
| location | string/reference | No | Containment location |
| construction_date | date | No | Containment construction date |
| septic_criteria | string/reference | No | Septic criteria |
| geom | KML file | Conditional on POST / Optional on PUT | On create, required when no alternate KML value is supplied; max 1024 KB where configured |
| house_image | JPEG/JPG image | No | Maximum 5120 KB where configured |
| estimated_area | numeric | No | Optional/derived area |

#### POST Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Building created successfully."
}
```

#### PUT Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Building updated successfully."
}
```

---

## ContainmentAPIController

### GET Containment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/containment/{bin}` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `application/json` — Required |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Containment retrieved successfully."
}
```

### POST Containment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeContainment` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` |
| Accept | `application/json` — Required |

### PUT Containment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/updateContainment/{id}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` |
| Accept | `application/json` — Required |

### POST/PUT Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| type_id | integer/reference | Yes | Containment type |
| size | numeric | Yes | Must be non-negative |
| pit_diameter | numeric | No | Must be non-negative when provided |
| pit_depth | numeric | No | Must be non-negative when provided |
| depth | numeric | No | Must be non-negative when provided |
| tank_length | numeric | No | Must be non-negative when provided |
| tank_width | numeric | No | Must be non-negative when provided |
| sewer_code | string/reference | Conditional | Required when `type_id` is `1` or `13` |
| drain_code | string/reference | Conditional | Required when `type_id` is `2` or `14` |

#### POST Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Containment created successfully."
}
```

#### PUT Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Containment updated successfully."
}
```

---

## RoadAPIController

### GET Road Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/Road/{code}` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `application/json` — Required |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Road retrieved successfully."
}
```

### POST Road Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeRoad` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### PUT Road Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/updateRoad/{code}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### POST/PUT Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| name | string | Yes | Maximum 255 characters |
| hierarchy | string/reference | No | Nullable |
| right_of_way | numeric | Yes | Must be greater than or equal to `carrying_width` |
| carrying_width | numeric | Yes | Required numeric value |
| surface_type | string/reference | No | Nullable |
| length | numeric | Yes | Required numeric value |
| geom | geometry/KML | No | Optional geometry where supported by the controller |

#### POST Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Road created successfully."
}
```

#### PUT Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Road updated successfully."
}
```

### GET Export Road Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/roads/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download containing road network records (`Roads.csv`).

---

## SewerAPIController

### GET Sewer Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/Sewer/{code}` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `application/json` — Required |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Sewer network retrieved successfully."
}
```

### POST Sewer Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeSewer` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### PUT Sewer Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/updateSewer/{code}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### POST/PUT Parameters

| Parameter | Type | POST | PUT | Notes |
|---|---|---|---|---|
| road_code | string/reference | Yes | No | Required on create |
| location | string | Yes | Yes | Required string |
| length | numeric | Yes | Yes | Required numeric value |
| diameter | numeric | Yes | Yes | Required numeric value |
| treatment_plant_id | string/reference | No | No | Nullable |
| geom | geometry/KML | No | No | Optional geometry where supported by the controller |

#### POST Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Sewer network created successfully."
}
```

#### PUT Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Sewer network updated successfully."
}
```

### GET Export Sewer Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/sewers/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download containing sewer network records.

---

## DrainAPIController

### GET Drain Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/Drain/{code}` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `application/json` — Required |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Drain retrieved successfully."
}
```

### POST Drain Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeDrain` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### PUT Drain Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/updateDrain/{code}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### POST/PUT Parameters

| Parameter | Type | POST | PUT | Notes |
|---|---|---|---|---|
| road_code | string/reference | Yes | No | Required on create |
| surface_type | string/reference | No | No | Nullable |
| cover_type | string/reference | No | No | Nullable |
| size | numeric | Yes | Yes | Required numeric value |
| length | numeric | Yes | Yes | Required numeric value |
| treatment_plant_id | string/reference | No | No | Nullable |
| geom | geometry/KML | No | No | Optional geometry where supported by the controller |

#### POST Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Drain created successfully."
}
```

#### PUT Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Drain updated successfully."
}
```

### GET Export Drain Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/drains/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download containing drain network records.

---

## WaterSupplyAPIController

### GET WaterSupply Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/WaterSupply/{code}` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `application/json` — Required |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Water supply retrieved successfully."
}
```

### POST WaterSupply Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeWaterSupply` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### PUT WaterSupply Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/updateWaterSupply/{code}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` or `multipart/form-data` when uploading geometry |
| Accept | `application/json` — Required |

### POST/PUT Parameters

| Parameter | Type | POST | PUT | Notes |
|---|---|---|---|---|
| road_code | string/reference | Yes | No | Required on create |
| project_name | string | Yes | Yes | Required string |
| type | string | No | No | Nullable |
| material_type | string | No | No | Nullable |
| diameter | numeric | Yes | Yes | Required numeric value |
| length | numeric | Yes | Yes | Required numeric value |
| geom | geometry/KML | No | No | Optional geometry where supported by the controller |
  
#### POST Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Water supply created successfully."
}
```

#### PUT Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "data": {},
  "message": "Water supply updated successfully."
}
```

### GET Export WaterSupply Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/waterSupplies/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download containing water supply network records.

---

## TaxPaymentApiController

### POST Import Tax Payment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeTaxPayment` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `multipart/form-data` |
| Accept | `application/json` — Required |

#### Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| csvfile | file | Yes | CSV file containing property tax payment records |

#### Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Tax created successfully."
}
```

### PUT Update Tax Payment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/taxPayments/update/{tax_code}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` |
| Accept | `application/json` — Required |

#### Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| last_payment_date | date | No | Last payment date (format: YYYY-MM-DD) |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "tax_code": "TAX12345",
  "message": "Tax payment updated successfully."
}
```

### GET Export Tax Payments

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/taxPayments/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Query Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| ward | string | No | Filter records by ward |
| due_year | string | No | Filter records by due year |
| tax_code | string | No | Filter records by tax code |
| bin | string | No | Filter records by BIN |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download (`Property Tax Collection Information Support System.csv`).

### GET Export Unmatched Tax Payments

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/taxPayments/exportunmatched` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download (`Unmatched Records-Property Tax Collection Information Support System.csv`).

---

## SwmPaymentApiController

### POST Import SWM Payment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeSwmPayment` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `multipart/form-data` |
| Accept | `application/json` — Required |

#### Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| csvfile | file | Yes | CSV file containing solid waste management payment records |

#### Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "SWM payment created successfully."
}
```

### PUT Update SWM Payment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/swmPayments/update/{swm_customer_id}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` |
| Accept | `application/json` — Required |

#### Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| last_payment_date | date | No | Last payment date (format: YYYY-MM-DD) |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "swm_customer_id": "SWM12345",
  "message": "SWM payment updated successfully."
}
```

### GET Export SWM Payments

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/swmPayments/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Query Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| ward | string | No | Filter records by ward |
| due_year | string | No | Filter records by due year |
| swm_customer_id | string | No | Filter records by SWM customer ID |
| bin | string | No | Filter records by BIN |
| tax_code | string | No | Filter records by tax code |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download (`Solid Waste Information Support System.csv`).

### GET Export Unmatched SWM Payments

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/swmPayments/exportunmatched` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download (`Unmatched Records-Solid Waste Information Support System.csv`).

---

## WaterSupplyPaymentApiController

### POST Import Water Supply Payment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/storeWaterSupplyPayment` |
| Method | POST |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `multipart/form-data` |
| Accept | `application/json` — Required |

#### Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| csvfile | file | Yes | CSV file containing water supply payment records |

#### Success Response

**HTTP Status:** `201 Created`

```json
{
  "success": true,
  "data": {},
  "message": "Water supply created successfully."
}
```

### PUT Update Water Supply Payment Data

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/waterSupplyPayments/update/{water_customer_id}` |
| Method | PUT |
| Authorization | `Bearer {access_token}` — Required |
| Content-Type | `application/json` |
| Accept | `application/json` — Required |

#### Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| last_payment_date | date | No | Last payment date (format: YYYY-MM-DD) |

#### Success Response

**HTTP Status:** `200 OK`

```json
{
  "success": true,
  "water_customer_id": "WS12345",
  "message": "Water supply payment updated successfully."
}
```

### GET Export Water Supply Payments

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/waterSupplyPayments/export` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Query Parameters

| Parameter | Type | Required? | Notes |
|---|---|---|---|
| ward | string | No | Filter records by ward |
| due_year | string | No | Filter records by due year |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download (`Water Supply Information Support System.csv`).

### GET Export Unmatched Water Supply Payments

| Field | Value |
|---|---|
| URL | `{Base_URL}/api/waterSupplyPayments/exportunmatched` |
| Method | GET |
| Authorization | `Bearer {access_token}` — Required |
| Accept | `text/csv` / `application/json` |

#### Success Response

**HTTP Status:** `200 OK`

CSV file download (`Unmatched Records-Water Supply Information Support System.csv`).

