<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\Building;
use Illuminate\Http\Request;
use App\Services\BuildingInfo\BuildingStructureService;
use App\Http\Requests\BuildingInfo\BuildingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\RedirectResponse;
use DB;

/**
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     securityScheme="bearerAuth",
 *     name="Authorization",
 *     description="Bearer token authorization"
 * )
 * 
 * @OA\Tag(
 *     name="Buildings",
 *     description="Building management endpoints - Create, update, and export building records"
 * )
 * 
 * @OA\Schema(
 *     schema="Building",
 *     type="object",
 *     properties={
 *         @OA\Property(property="bin", type="string", example="B000001", description="Building Identification Number"),
 *         @OA\Property(property="tax_code", type="string", example="TAX001", description="Tax code or holding ID"),
 *         @OA\Property(property="ward", type="string", example="Ward 1", description="Ward name or code"),
 *         @OA\Property(property="road_code", type="string", example="RC001", description="Road code"),
 *         @OA\Property(property="house_number", type="string", example="123", description="House number"),
 *         @OA\Property(property="house_locality", type="string", example="Main Street", description="House locality or address"),
 *         @OA\Property(property="structure_type_id", type="integer", example=1, description="Structure type ID"),
 *         @OA\Property(property="surveyed_date", type="string", format="date", example="2024-01-15", description="Date when building was surveyed"),
 *         @OA\Property(property="construction_year", type="string", format="date", example="2020-01-01", description="Year of construction"),
 *         @OA\Property(property="floor_count", type="integer", example=3, description="Number of floors"),
 *         @OA\Property(property="functional_use_id", type="integer", example=1, description="Functional use ID (1=Residential, etc.)"),
 *         @OA\Property(property="use_category_id", type="integer", example=1, description="Use category ID"),
 *         @OA\Property(property="office_business_name", type="string", example="ABC Business", description="Office or business name (if applicable)"),
 *         @OA\Property(property="household_served", type="integer", example=5, description="Number of households served"),
 *         @OA\Property(property="population_served", type="integer", example=20, description="Total population served"),
 *         @OA\Property(property="male_population", type="integer", example=10, description="Male population count"),
 *         @OA\Property(property="female_population", type="integer", example=9, description="Female population count"),
 *         @OA\Property(property="other_population", type="integer", example=1, description="Other population count"),
 *         @OA\Property(property="diff_abled_male_pop", type="integer", example=0, description="Differently abled male population"),
 *         @OA\Property(property="diff_abled_female_pop", type="integer", example=0, description="Differently abled female population"),
 *         @OA\Property(property="diff_abled_others_pop", type="integer", example=0, description="Differently abled other population"),
 *         @OA\Property(property="low_income_hh", type="boolean", example=false, description="Is low income household"),
 *         @OA\Property(property="lic_id", type="integer", example=1, description="Low Income Community ID"),
 *         @OA\Property(property="water_source_id", type="integer", example=1, description="Water source ID"),
 *         @OA\Property(property="water_customer_id", type="string", example="WC001", description="Water supply customer ID"),
 *         @OA\Property(property="watersupply_pipe_code", type="string", example="WP001", description="Water supply pipe line code"),
 *         @OA\Property(property="well_presence_status", type="boolean", example=false, description="Is there a well in premises"),
 *         @OA\Property(property="distance_from_well", type="number", format="float", example=50.5, description="Distance from well in meters"),
 *         @OA\Property(property="toilet_status", type="boolean", example=true, description="Presence of toilet"),
 *         @OA\Property(property="toilet_count", type="integer", example=1, description="Number of toilets"),
 *         @OA\Property(property="household_with_private_toilet", type="integer", example=1, description="Households with private toilet"),
 *         @OA\Property(property="population_with_private_toilet", type="integer", example=5, description="Population with private toilet"),
 *         @OA\Property(property="sanitation_system_id", type="integer", example=1, description="Sanitation system ID"),
 *         @OA\Property(property="sewer_code", type="string", example="SEW001", description="Sewer code"),
 *         @OA\Property(property="drain_code", type="string", example="DR001", description="Drain code"),
 *         @OA\Property(property="swm_customer_id", type="string", example="SWM001", description="Solid Waste Management customer ID"),
 *         @OA\Property(property="desludging_vehicle_accessible", type="boolean", example=true, description="Is building accessible to desludging vehicle"),
 *         @OA\Property(property="estimated_area", type="number", format="float", example=150.5, description="Estimated area of building in square meters"),
 *         @OA\Property(property="building_associated_to", type="string", example="B000001", description="BIN of main building if this is associated building"),
 *         @OA\Property(property="verification_status", type="integer", example=1, description="Verification status (1=verified, 0=unverified)")
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="data", type="object"),
 *         @OA\Property(property="message", type="string", example="Operation successful")
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="ApiError",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=false),
 *         @OA\Property(property="error", type="string", example="Error message"),
 *         @OA\Property(property="data", type="object", nullable=true)
 *     }
 * )
 */
class BuildingApiController extends Controller
{
    /**
     * @var BuildingStructureService
     */
    protected $buildingStructureService;

    public function __construct(BuildingStructureService $buildingStructureService)
    {
        $this->buildingStructureService = $buildingStructureService;
    }

    /**
     * @OA\Post(
     *     path="/buildings",
     *     operationId="storeBuilding",
     *     tags={"Buildings"},
     *     summary="Create a new building record",
     *     description="Create a new building record with complete information including location which is uploaded through KML, structure details,owner details, utilities, and sanitation information. Automatically generates a unique Building Identification Number (BIN) .",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Building creation data",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ward", "functional_use_id", "toilet_status"},
     *             properties={
     *                 @OA\Property(property="ward", type="string", example="Ward 1"),
     *                 @OA\Property(property="road_code", type="string", example="RC001"),
     *                 @OA\Property(property="house_number", type="string", example="123"),
     *                 @OA\Property(property="house_locality", type="string", example="Main Street"),
     *                 @OA\Property(property="tax_code", type="string", example="TAX001"),
     *                 @OA\Property(property="structure_type_id", type="integer", example=1),
     *                 @OA\Property(property="surveyed_date", type="string", format="date", example="2024-01-15"),
     *                 @OA\Property(property="construction_year", type="string", format="date", example="2020-01-01"),
     *                 @OA\Property(property="floor_count", type="integer", example=3),
     *                 @OA\Property(property="functional_use_id", type="integer", example=1),
     *                 @OA\Property(property="use_category_id", type="integer", example=1),
     *                 @OA\Property(property="office_business_name", type="string", example="ABC Business"),
     *                 @OA\Property(property="household_served", type="integer", example=5),
     *                 @OA\Property(property="population_served", type="integer", example=20),
     *                 @OA\Property(property="male_population", type="integer", example=10),
     *                 @OA\Property(property="female_population", type="integer", example=9),
     *                 @OA\Property(property="other_population", type="integer", example=1),
     *                 @OA\Property(property="diff_abled_male_pop", type="integer", example=0),
     *                 @OA\Property(property="diff_abled_female_pop", type="integer", example=0),
     *                 @OA\Property(property="diff_abled_others_pop", type="integer", example=0),
     *                 @OA\Property(property="low_income_hh", type="boolean", example=false),
     *                 @OA\Property(property="lic_status", type="boolean", example=false),
     *                 @OA\Property(property="lic_id", type="integer", example=1),
     *                 @OA\Property(property="water_source_id", type="integer", example=1),
     *                 @OA\Property(property="water_customer_id", type="string", example="WC001"),
     *                 @OA\Property(property="watersupply_pipe_code", type="string", example="WP001"),
     *                 @OA\Property(property="well_presence_status", type="boolean", example=false),
     *                 @OA\Property(property="distance_from_well", type="number", format="float", example=50.5),
     *                 @OA\Property(property="toilet_status", type="boolean", example=true),
     *                 @OA\Property(property="toilet_count", type="integer", example=1),
     *                 @OA\Property(property="household_with_private_toilet", type="integer", example=1),
     *                 @OA\Property(property="population_with_private_toilet", type="integer", example=5),
     *                 @OA\Property(property="sanitation_system_id", type="integer", example=1),
     *                 @OA\Property(property="defecation_place", type="string", example="9"),
     *                 @OA\Property(property="sewer_code", type="string", example="SEW001"),
     *                 @OA\Property(property="drain_code", type="string", example="DR001"),
     *                 @OA\Property(property="swm_customer_id", type="string", example="SWM001"),
     *                 @OA\Property(property="desludging_vehicle_accessible", type="boolean", example=true),
     *                 @OA\Property(property="house_image", type="string", format="binary", description="Building image file (JPG, PNG, etc.)"),
     *                 @OA\Property(property="main_building", type="boolean", example=true),
     *                 @OA\Property(property="building_associated_to", type="string", example="B000001"),
     *                 @OA\Property(property="survey_id", type="integer", example=1)
     *             }
     *         ),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 properties={
     *                     @OA\Property(property="ward", type="string"),
     *                     @OA\Property(property="house_image", type="string", format="binary")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Building created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="data", ref="#/components/schemas/Building"),
     *                 @OA\Property(property="message", type="string", example="Building saved successfully with BIN: B000001"),
     *                 @OA\Property(property="bin_generated", type="string", example="B000001")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing bearer token"
     *     )
     * )
     */

    public function store(BuildingRequest $request): JsonResponse
    {
        try {
            if (!$request->hasFile('geom')) {
                return response()->json([
                    'success' => false,
                    'message' => 'geom file is required.',
                    'errors' => [
                        'geom' => ['The geom field is required. Upload a KML file.'],
                    ],
                ], 422);
            }

            $this->buildingStructureService->storeBuildingData($request);

            $bin = $request->bin ?? $request->input('bin');

            if (!$bin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Building could not be created. The legacy service did not provide a BIN.',
                ], 500);
            }

            $building = Building::where('bin', $bin)->first();

            if (!$building) {
                Log::error('Building creation failed after BIN was generated.', [
                    'bin_generated' => $bin,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Building creation failed after BIN was generated.',
                    'bin_generated' => $bin,
                    'debug' => [
                        'possible_reason' => 'The legacy service generated a BIN but failed before saving or committing the building.',
                    ],
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Building created successfully.',
                'data' => $building,
                'bin_generated' => $building->bin,
            ], 201);

        } catch (Throwable $e) {
            Log::error('Building API create failed.', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Building could not be created.',
                'debug' => [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);

        }
    }

     /**
         * @OA\Put(
         *     path="/buildings/{bin}",
         *     operationId="updateBuilding",
         *     tags={"Buildings"},
         *     summary="Update a building record",
         *     description="Update an existing building record. This API uses the existing building service bridge, so geom/KML file upload may be required for certain updates.",
         *     security={{"bearerAuth": {}}},
         *
         *     @OA\Parameter(
         *         name="bin",
         *         in="path",
         *         required=true,
         *         description="Building Identification Number (BIN)",
         *         @OA\Schema(type="string", example="B000001")
         *     ),
         *
         *     @OA\RequestBody(
         *         required=false,
         *         description="Building update data. Send as multipart/form-data when uploading files (geom/house_image).",
         *         @OA\MediaType(
         *             mediaType="multipart/form-data",
         *             @OA\Schema(
         *                 type="object",
         *                 properties={
         *                     @OA\Property(property="ward", type="string"),
         *                     @OA\Property(property="road_code", type="string"),
         *                     @OA\Property(property="house_number", type="string"),
         *                     @OA\Property(property="house_locality", type="string"),
         *                     @OA\Property(property="tax_code", type="string"),
         *                     @OA\Property(property="structure_type_id", type="integer"),
         *                     @OA\Property(property="toilet_status", type="boolean"),
         *                     @OA\Property(property="geom", type="string", format="binary", description="KML/geom file"),
         *                     @OA\Property(property="house_image", type="string", format="binary", description="Building image file")
         *                 }
         *             )
         *         )
         *     ),
         *
         *     @OA\Response(
         *         response=200,
         *         description="Building updated successfully",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(property="data", ref="#/components/schemas/Building"),
         *             @OA\Property(property="message", type="string", example="Building Information updated successfully.")
         *         )
         *     ),
         *
         *     @OA\Response(
         *         response=422,
         *         description="Validation error or missing data",
         *         @OA\JsonContent(ref="#/components/schemas/ApiError")
         *     ),
         *
         *     @OA\Response(
         *         response=500,
         *         description="Server error",
         *         @OA\JsonContent(ref="#/components/schemas/ApiError")
         *     ),
         *
         *     @OA\Response(
         *         response=401,
         *         description="Unauthorized - Invalid or missing bearer token"
         *     )
         * )
         */
   public function update(BuildingRequest $request, string $bin): JsonResponse
{
    try {
        $existingBuilding = Building::find($bin);

        if (!$existingBuilding) {
            return response()->json([
                'success' => false,
                'message' => 'Building not found.',
                'data' => null,
            ], 404);
        }

        if (empty($request->all()) && empty($request->allFiles())) {
            return response()->json([
                'success' => false,
                'message' => 'Empty request body. Please provide at least one field to update.',
                'data' => null,
            ], 422);
        }

        $serviceResponse = $this->buildingStructureService->updateBuildingData($request, $bin);

        $building = Building::find($bin);

        if (!$building) {
            return response()->json([
                'success' => false,
                'message' => 'Building could not be updated.',
                'data' => null,
            ], 500);
        }

        $flashError = session('error');

        if ($flashError) {
            return response()->json([
                'success' => false,
                'message' => $flashError,
                'data' => $building,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Building Information updated successfully.',
            'data' => $building,
        ], 200);

    } catch (Throwable $e) {
        Log::error('Building API update failed.', [
            'bin' => $bin,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Building could not be updated.',
            'data' => Building::find($bin),
        ], 500);
    }
}

    /**
     * @OA\Get(
     *     path="/buildings/export/csv",
     *     operationId="exportBuildings",
     *     tags={"Buildings"},
     *     summary="Export building records as CSV",
     *     description="Export filtered building records as a downloadable CSV file. Supports multiple filter parameters for advanced searching.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="bin",
     *         in="query",
     *         required=false,
     *         description="Filter by Building Identification Number (partial match)",
     *         @OA\Schema(type="string", example="B000001")
     *     ),
     *     @OA\Parameter(
     *         name="house_number",
     *         in="query",
     *         required=false,
     *         description="Filter by house number (partial match)",
     *         @OA\Schema(type="string", example="123")
     *     ),
     *     @OA\Parameter(
     *         name="structype",
     *         in="query",
     *         required=false,
     *         description="Filter by structure type ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="ward",
     *         in="query",
     *         required=false,
     *         description="Filter by ward code",
     *         @OA\Schema(type="string", example="Ward1")
     *     ),
     *     @OA\Parameter(
     *         name="functional_use_id",
     *         in="query",
     *         required=false,
     *         description="Filter by functional use ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="roadcd",
     *         in="query",
     *         required=false,
     *         description="Filter by road code",
     *         @OA\Schema(type="string", example="RC001")
     *     ),
     *     @OA\Parameter(
     *         name="ownername",
     *         in="query",
     *         required=false,
     *         description="Filter by owner name (partial match)",
     *         @OA\Schema(type="string", example="John")
     *     ),
     *     @OA\Parameter(
     *         name="toilet",
     *         in="query",
     *         required=false,
     *         description="Filter by toilet status (1=Yes, 0=No)",
     *         @OA\Schema(type="integer", enum={0, 1}, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="toiletconn",
     *         in="query",
     *         required=false,
     *         description="Filter by toilet connection type",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="watersourc",
     *         in="query",
     *         required=false,
     *         description="Filter by water source ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="well_prese",
     *         in="query",
     *         required=false,
     *         description="Filter by well presence status (1=Yes, 0=No)",
     *         @OA\Schema(type="integer", enum={0, 1}, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sanitation_system_id",
     *         in="query",
     *         required=false,
     *         description="Filter by sanitation system ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="floor_count",
     *         in="query",
     *         required=false,
     *         description="Filter by floor count (prefix match)",
     *         @OA\Schema(type="integer", example=3)
     *     ),
     *     @OA\Parameter(
     *         name="use_category_select",
     *         in="query",
     *         required=false,
     *         description="Filter by use category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         required=false,
     *         description="Filter construction year from date",
     *         @OA\Schema(type="string", format="date", example="2020-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         required=false,
     *         description="Filter construction year to date",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CSV file download started successfully",
     *         @OA\Header(
     *             header="Content-Type",
     *             description="File content type",
     *             @OA\Schema(type="string", example="text/csv")
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Attachment header with filename",
     *             @OA\Schema(type="string", example="attachment; filename=Buildings.csv")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid filter parameters",
     *         @OA\JsonContent(ref="#/components/schemas/ApiError")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Invalid or missing bearer token"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function fetchExport()
    {
        try {
            ob_start();

            $this->buildingStructureService->fetchExport();

            $csvContent = ob_get_clean();

            if (empty($csvContent)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Export could not be generated.',
                ], 500);
            }

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="Buildings.csv"',
            ]);

        } catch (\Throwable $e) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            Log::error('Building export API failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Building export could not be generated.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
