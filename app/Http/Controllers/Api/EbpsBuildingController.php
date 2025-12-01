<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;                 // CHANGED: ensure DB facade is imported
use Carbon\Carbon;
use App\Models\BuildingInfo\Building;             // CHANGED: Eloquent model must point to building_info.buildings
use App\Models\BuildingInfo\UseCategory;          // CHANGED: used to map purpose name -> id

class EbpsBuildingController extends Controller
{
    /**
     * Entry point called by your route:
     * /api/building-info/{ebps_id}/{transaction_type}
     */
    public function storeBuildingInfo($ebps_id, $transaction_type, Request $request)
    {
        try {
            if ($transaction_type === 'ApplicationForVacantLand') {
                return $this->handleVacantLand($ebps_id, $request);  // CHANGED: thin entry, delegates work
            } elseif ($transaction_type === 'SuperStructure') {
                return $this->handleSuperStructure($ebps_id, $request); // TODO
            } elseif ($transaction_type === 'Completion') {
                return $this->getBuildingComplitionData($ebps_id, $request); // TODO
            }

            // Unsupported transaction
            return response()->json([
                'success'      => false,
                'responseCode' => '400',
                'error_code'   => 'INVALID_TRANSACTION_TYPE',
                'message'      => "The transaction type '{$transaction_type}' is not supported."
            ], 400);

        } catch (\Throwable $e) {
            \Log::channel('ebps')->error('Failed in storeBuildingInfo method', [
                'ebps_id'         => $ebps_id,
                'transaction_type'=> $transaction_type,
                'error'           => $e->getMessage(),
                'timestamp'       => Carbon::now()->toDateTimeString()
            ]);

            return response()->json([
                'success'      => false,
                'responseCode' => '500',
                'error_code'   => 'EXCEPTION',
                'message'      => 'Internal Server Error',
                'error_details'=> $e->getMessage(),
            ], 500);
        }
    }

    protected function handleVacantLand(string $buildingPermitId, Request $request)
    {
        $data = $request->all();

        // 1) Minimal validation (only the fields we absolutely require) // NEW
        $validationError = $this->validateRequired($data, ['BldgPrmt_TID', 'TransactionType', 'Ward', 'ToleName']);
        /* dd($validationError); */
        if ($validationError) {
            return $validationError; // returns 422
        }

        // 2) Duplicate guard in FLAT TABLE                                      // CHANGED (lowercase schema.table)
        $duplicate = DB::table('ebps_flat_table.ebps_building_info')
            ->where('bldgprmt_tid', data_get($data, 'BldgPrmt_TID'))
            ->where('transaction_type', data_get($data, 'TransactionType'))
            ->first();

        if ($duplicate) {
            return response()->json([
                'success'      => false,
                'responseCode' => '409',
                'content'      => 'Record already exists',
                'error_code'   => 'DUPLICATE_RECORD',
            ], 409);
        }

        // 3) Insert into FLAT TABLE (for trace/debug)                           // CHANGED
        $this->insertIntoFlat($data);

        // 4) Main transactional upsert: building + owner                        // NEW
        DB::beginTransaction();
        try {
            // 4a) Upsert building (create BIN if needed, map use_category, build geometry)
            $building = $this->upsertBuildingFromEbps($data);                   // NEW

            // 4b) Upsert owner using SAME BIN
            $this->upsertOwnerFromEbps($building->bin, $data);                  // NEW


            $containmentId = $this->createContainmentAndLink($building->bin, $data);
            DB::commit();
        }

        catch (\Throwable $e) {
            DB::rollBack();
            \Log::channel('ebps')->error('TX failed (building+owner)', [
                'error'   => $e->getMessage(),
                'payload' => $data,
            ]);
            throw $e;
        }

        // 5) Success response                                                    // CHANGED
        return response()->json([
            'success'      => true,
            'responseCode' => '200',
            'content'      => 'OK',
            'received_data'=> [
                'BldgPrmt_TID'     => data_get($data, 'BldgPrmt_TID'),
                'ApplicationNumber'=> data_get($data, 'ApplicationNumber'),
                'Ward'             => data_get($data, 'Ward'),
                'TransactionType'  => data_get($data, 'TransactionType'),
                'HouseOwnerNm'     => data_get($data, 'HouseOwnerNm'),
                'timestamp'        => Carbon::now()->toDateTimeString(),
            ],
        ], 200);
    }

    /* -------------------------- helpers: FLAT TABLE -------------------------- */

    // NEW: inserts the incoming payload into the flat table (lowercase schema/table)
    protected function insertIntoFlat(array $data): void
    {
        DB::table('ebps_flat_table.ebps_building_info')->insert([
            'bldgprmt_tid'     => data_get($data, 'BldgPrmt_TID'),
            'application_number'=> data_get($data, 'ApplicationNumber'),
            'tax_code'         => data_get($data, 'tax_code'),
            'structure_type'   => data_get($data, 'structureType'),
            'ward'             => data_get($data, 'Ward'),
            'transaction_type' => data_get($data, 'TransactionType'),
            'lat_designer'     => data_get($data, 'Designer_Latitude'),
            'long_designer'    => data_get($data, 'Designer_Longitude'),
            'functional_use'   => data_get($data, 'buildingPurposeNm'),
            'owner'            => data_get($data, 'HouseOwnerNm'),
            'floor_count'      => data_get($data, 'NoOfStorey'),
            'location'         => data_get($data, 'ToleName'),
            'created_at'       => Carbon::now(),
            'updated_at'       => Carbon::now(),
        ]);
    }

    /* -------------------------- helpers: BUILDING ---------------------------- */

    // NEW: upserts a row in building_info.buildings using EBPS payload
    protected function upsertBuildingFromEbps(array $data): Building
    {
        $ebpsId  = (string) data_get($data, 'BldgPrmt_TID');
        $ward    = data_get($data, 'Ward');
        $locality= data_get($data, 'ToleName');
        $taxCode = data_get($data, 'tax_code');
        $floors  = data_get($data, 'NoOfStorey');

        // Map "Residential" -> use_category_id (case-insensitive)               // CHANGED
        $useCategoryName = (string) data_get($data, 'buildingPurposeNm');
        $useCategoryId   = $this->resolveUseCategoryIdByName($useCategoryName);  // NEW

        // Convert lon/lat point to a small MultiPolygon (0.5 m buffer)          // CHANGED
        $lon = is_null(data_get($data, 'Designer_Longitude')) ? null : (float) data_get($data, 'Designer_Longitude');
        $lat = is_null(data_get($data, 'Designer_Latitude'))  ? null : (float) data_get($data, 'Designer_Latitude');
        $geomExpr = $this->buildMultiPolygonFromPoint($lon, $lat);               // NEW (returns DB::raw(...) or null)

        // Fetch by ebps_id or create new
        $building = Building::where('ebps_id', $ebpsId)->first();
        if (!$building) {
            $building = new Building();

            // Robust BIN generation                                             // CHANGED
            $maxBIN  = Building::max('bin'); // 'B000123' or null
            $numeric = (int) preg_replace('/\D/', '', $maxBIN ?? '0');
            $building->bin = 'B' . sprintf('%06d', $numeric + 1);
        }

        $building->ebps_id        = $ebpsId;
        $building->ward           = $ward;
        $building->house_locality = $locality;
        $building->tax_code       = $taxCode;
        $building->floor_count    = $floors;

        if ($useCategoryId) {
            $building->use_category_id = $useCategoryId;
        }
        if ($geomExpr) {
            $building->geom = $geomExpr;  // geometry(MultiPolygon,4326) column
        }

        $building->save();

        return $building;
    }

    // NEW: resolve use_category id from its name (case-insensitive)
    protected function resolveUseCategoryIdByName(?string $name): ?int
    {
        if (!$name) return null;

        return UseCategory::query()
            ->whereRaw('LOWER(name) = LOWER(?)', [$name])
            ->value('id');
    }

    // NEW: build a tiny MultiPolygon from lon/lat point; returns DB::raw(...) or null
    protected function buildMultiPolygonFromPoint(?float $lon, ?float $lat)
    {
        if (is_null($lon) || is_null($lat)) {
            return null;
        }

        // Buffer as geography to use meters, cast back to geometry, then ST_Multi to ensure MultiPolygon
        return DB::raw(sprintf(
            "ST_Multi((ST_Buffer(ST_SetSRID(ST_Point(%F,%F),4326)::geography, 0.5))::geometry)",
            $lon, $lat
        ));
    }

    /* ---------------------------- helpers: OWNER ----------------------------- */

    // NEW: upsert owner in building_info.owners using same BIN as building
    protected function upsertOwnerFromEbps(string $bin, array $data): void
    {
        $ownerName   = data_get($data, 'HouseOwnerNm');
        $ownerGender = data_get($data, 'gender');
        $ownerPhone  = data_get($data, 'contact_no');
        $nid         = data_get($data, 'citizenshipNum');

        // If there’s an owners model, use it; otherwise, raw table insert/update
        // Here we use raw table to avoid guessing your Eloquent model name.
        $now = Carbon::now();

        // Try update-first; if not exists, insert
        $exists = DB::table('building_info.owners')                         // NOTE: ensure this table exists
            ->where('bin', $bin)
            ->whereNull('deleted_at')                                      // NOTE: if soft-deletes
            ->exists();

        if ($exists) {
            DB::table('building_info.owners')->where('bin', $bin)->update([ // CHANGED
                'owner_name'   => $ownerName,
                'owner_gender' => $ownerGender,
                'owner_contact'=> $ownerPhone,
                'nid'          => $nid,
                'updated_at'   => $now,
            ]);
        } else {
            DB::table('building_info.owners')->insert([                    // NEW
                'bin'          => $bin,
                'owner_name'   => $ownerName,
                'owner_gender' => $ownerGender,
                'owner_contact'=> $ownerPhone,
                'nid'          => $nid,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }
    }


    private function mapContainmentLocation(?string $location): ?string
    {
        if (!$location) {
            return null;
        }

        $location = trim(strtolower($location));

        // Any value containing "inside"
        if (str_contains($location, 'inside')) {
            return 'Inside the house';
        }

        // Any value containing "outside"
        if (str_contains($location, 'outside')) {
            return 'Outside the house';
        }

        // Default fallback (optional)
        return null;
    }

    private function createContainmentAndLink(string $bin, array $data): ?string
    {
        // Skip if no septic fields present
        if (
            is_null(data_get($data, 'SepticTankLength')) &&
            is_null(data_get($data, 'SepticTankWidth')) &&
            is_null(data_get($data, 'SepticTankDepth')) &&
            is_null(data_get($data, 'SepticTankLocation'))
        ) {
            return null;
        }

        // Generate next containment id
        $containmentId = $this->nextContainmentId();
        $mappedLocation = $this->mapContainmentLocation(data_get($data, 'SepticTankLocation'));

        // Insert into fsm.containments
        DB::table('fsm.containments')->insert([
            'id'          => $containmentId,
            'location'    => $mappedLocation,
            'tank_length' => data_get($data, 'SepticTankLength'),
            'tank_width'  => data_get($data, 'SepticTankWidth'),
            'depth'       => data_get($data, 'SepticTankDepth'),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Insert into pivot table
        DB::table('building_info.build_contains')->insert([
            'bin'            => $bin,
            'containment_id' => $containmentId,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return $containmentId;
    }
    private function nextContainmentId(): string
    {
        $next = DB::table(DB::raw('fsm.containments'))
            ->selectRaw("COALESCE(MAX(REGEXP_REPLACE(id,'\\D','','g')::int),0) + 1 AS n")
            ->value('n');

        return 'C' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }


    /* ------------------------------ misc utils ------------------------------ */

    // NEW: tiny validator for required keys; returns JSON 422 or null
    protected function validateRequired(array $data, array $requiredKeys){
        $missing = [];
        foreach ($requiredKeys as $key) {
            if (data_get($data, $key) === null || data_get($data, $key) === '') {
                $missing[] = $key;
            }
        }
        if (!empty($missing)) {
            return response()->json([
                'success'      => false,
                'responseCode' => '422',
                'error_code'   => 'VALIDATION_ERROR',
                'error_details'=> 'Missing required fields: ' . implode(', ', $missing)
            ], 422);
        }
        return null;
    }

    /* -------------------- placeholders (keep your originals) -------------------- */

    // TODO: keep your existing implementations or add later
    protected function getSuperStructureData($ebps_id, Request $request) {
        return response()->json(['success'=>false,'message'=>'Not implemented'],501);
    }



    protected function getStoryAdditionData(){
        return response()->json(['success'=>false,'message'=>'Not implemented'],501);
    }

    protected function getBuildingAbhilehikaranData(){
        return response()->json(['success'=>false,'message'=>'Not implemented'],501);
    }



    // Super Structuture.
    protected function handleSuperStructure(string $buildingPermitId, Request $request)
    {
        try {
            $data = $request->all();

            // Add buildingPermitId into data if needed
            $data['Bldgprmt_TID'] = $buildingPermitId;

            $this->insertSSdataInFlatTable($data);

            return response()->json([
                'success' => true,
                'message' => 'Super Structure data saved successfully'
            ], 200);

        } catch (\Throwable $e) {
            \Log::channel('ebps')->error('Failed inserting SS flat table data', [
                'ebps_id' => $buildingPermitId,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to insert Super Structure data',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    protected function insertSSdataInFlatTable(array $data)
    {
        DB::table('ebps_flat_table.ebps_building_info')->insert([
            'bldgprmt_tid'        => $data['Bldgprmt_TID'] ?? null,
            'transaction_type'    => $data['TransactionType_Nm'] ?? null,
            'road_code'           => $data['RodeCode'] ?? null,
            'application_number'  => $data['ApplicationNumber'] ?? null,
            'sewer_code'          => $data['SewerCode'] ?? null,

            // septic outlet info
            'outlet_pipe_connect_designer'   => $data['OutletPipeConectionDesigner'] ?? null,
            'outlet_pipe_connect_sanitation' => $data['OutletPipeConnectSanitaion'] ?? null,

            // septic dimensions (numeric(10,2))
            'length'             => isset($data['SepticTankLength']) ? (float) $data['SepticTankLength'] : null,
            'width'              => isset($data['SepticTankWidth']) ? (float) $data['SepticTankWidth'] : null,
            'depth'              => isset($data['SepticTankDepth']) ? (float) $data['SepticTankDepth'] : null,

            // date (Postgres will accept null or proper date)
            'construction_date'  => $data['Construction_Date'] ?? null,

            // functional use
            'functional_use'     => $data['buildingPurposeNm'] ?? null,

            // designer location (numeric(10,6) in DB)
            'lat_designer'       => isset($data['Designer_Latitude'])
                                    ? (float) trim($data['Designer_Latitude'])
                                    : null,
            'long_designer'      => isset($data['Designer_Longitude'])
                                    ? (float) trim($data['Designer_Longitude'])
                                    : null,

            // footprints (jsonb) – Laravel will send as text; Postgres will cast if it's valid JSON
            'ward_building_footprint'     => $data['Field_footprint'] ?? null,
            'designer_building_footprint' => $data['Designer_footprint'] ?? null,

            // owner/contact
            'owner'        => $data['HouseOwnerNm'] ?? null,
            'gender'       => isset($data['gender']) ? trim($data['gender']) : null,
            'contact_no'   => $data['contact_no'] ?? null,
            'floor_count'  => isset($data['NoOfStorey']) ? (int) $data['NoOfStorey'] : null,

            // septic design flags (booleans)
            'issepticsealed'       => $data['IsSepticSealed'] ?? null,
            'issepticcompartments' => $data['IsSepticCompartments'] ?? null,
            'isseptictankdepth'    => $data['IsSepticTankDepth'] ?? null,
            'islengretdesign'      => $data['IsLenGretDesign'] ?? null,
            'iswidgretdesign'      => $data['IsWidGretDesign'] ?? null,
            'islensepticdouble'    => $data['IsLenSepticDouble'] ?? null,
            'ischamberlength'      => $data['IsChamberLength'] ?? null,
            'ispipeinoutletlevel'  => $data['IsPipeInOutletLevel'] ?? null,
            'ispartwalldepth'      => $data['IsPartWallDepth'] ?? null,
            'isinnoutletsection'   => $data['IsInNOutLetSection'] ?? null,
            'isseptictanksealed'   => $data['IsSepticTankSealed'] ?? null,
            'isinletnoutletsection'=> $data['IsInletNOutletSection'] ?? null,

            // inspection date
            'inspecteddate'        => $data['inspectedDate'] ?? null,

            // timestamps
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }



     protected function getBuildingComplitionData($ebps_id, Request $request) {
        try {
            $data = $request->all();

            // Add buildingPermitId into data if needed
            $data['Bldgprmt_TID'] = $ebps_id;

            $this->insertCompletionDataInFlatTable($data);

            return response()->json([
                'success' => true,
                'message' => 'Completion data saved successfully'
            ], 200);

        } catch (\Throwable $e) {
            \Log::channel('ebps')->error('Failed inserting Completion flat table data', [
                'ebps_id' => $ebps_id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to insert Completion data',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    protected function insertCompletionDataInFlatTable(array $data){
    // ---------- 1) EBPS ID & image saving ----------
        $ebpsId = $data['Bldgprmt_TID'] ?? $data['Bldgprmt_tid'] ?? null;

        $image1 = null;
        $image2 = null;
        $image3 = null;

        if ($ebpsId) {
            $folder = 'ebps_photos/' . $ebpsId;

            // Always create folder (even if no valid images)
            Storage::disk('public')->makeDirectory($folder);

            $photos = $data['Photos'] ?? [];

            foreach ($photos as $index => $photo) {
                if ($index > 2) {
                    break; // only up to 3
                }

                $slot    = $index + 1; // 1,2,3
                $base64  = $photo['Base64Image'] ?? null;
                $docFile = $photo['DocImgFile'] ?? null;

                // extension from DocImgFile or default jpg
                $ext = pathinfo($docFile ?? '', PATHINFO_EXTENSION);
                if (!$ext) {
                    $ext = 'jpg';
                }

                $fileName = 'image_' . $slot . '.' . $ext;
                $path     = $folder . '/' . $fileName;

                if ($base64) {
                    try {
                        if (strpos($base64, 'base64,') !== false) {
                            $base64 = explode('base64,', $base64)[1];
                        }

                        $decoded = base64_decode($base64);
                        if ($decoded !== false) {
                            Storage::disk('public')->put($path, $decoded);

                            if ($slot === 1) {
                                $image1 = $path;
                            } elseif ($slot === 2) {
                                $image2 = $path;
                            } elseif ($slot === 3) {
                                $image3 = $path;
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::channel('ebps')->error('Error saving completion image', [
                            'ebps_id' => $ebpsId,
                            'slot'    => $slot,
                            'error'   => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        // ---------- 2) Insert only fields that exist in completion payload ----------
        DB::table('ebps_flat_table.ebps_building_info')->insert([
            // IDs / meta
            'bldgprmt_tid'       => $ebpsId,
            'transaction_type'   => $data['TransactionType_Nm'] ?? null,
            'application_number' => $data['ApplicationNumber'] ?? null,

            // codes
            'road_code'          => $data['RodeCode'] ?? null,
            'sewer_code'         => $data['SewerCode'] ?? null,

            // use & construction
            'functional_use'     => $data['buildingPurposeNm'] ?? null,
            'construction_date'  => $data['Construction_Date'] ?? null,

            // septic dimensions (they’re in payload, even if null)
            'length'             => isset($data['SepticTankLength']) ? (float) $data['SepticTankLength'] : null,
            'width'              => isset($data['SepticTankWidth'])  ? (float) $data['SepticTankWidth']  : null,
            'depth'              => isset($data['SepticTankDepth'])  ? (float) $data['SepticTankDepth']  : null,

            // outlet sanitation
            'outlet_pipe_connect_sanitation' => $data['OutletPipeConnectSanitaion'] ?? null,

            // owner & contact
            'owner'       => $data['HouseOwnerNm'] ?? null,
            'gender'      => isset($data['gender']) ? trim($data['gender']) : null,
            'contact_no'  => $data['contact_no'] ?? null,
            'floor_count' => isset($data['NoOfStorey']) ? (int) $data['NoOfStorey'] : null,

            // location
            'location'    => $data['Location'] ?? null,

            // footprints
            'ward_building_footprint'     => $data['Field_footprint'] ?? null,
            'designer_building_footprint' => $data['Designer_footprint'] ?? null,

            // images
            'image_1'     => $image1,
            'image_2'     => $image2,
            'image_3'     => $image3,

            // timestamps
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
    ]);
}




}
