<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UtilityInfo\SewerLineRequest;
use App\Models\UtilityInfo\SewerLine;
use App\Services\UtilityInfo\SewerLineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Auth;

/**
 * @OA\Tag(
 *     name="Sewers",
 *     description="Sewer network endpoints for creating and updating sewer records"
 * )
 *
 * @OA\Schema(
 *     schema="Sewer",
 *     type="object",
 *     properties={
 *         @OA\Property(property="code", type="string", example="S000001", description="Sewer code"),
 *         @OA\Property(property="road_code", type="string", example="R000001", description="Associated road code"),
 *         @OA\Property(property="location", type="string", example="Near market", description="Sewer location"),
 *         @OA\Property(property="length", type="number", format="float", example=350.7, description="Sewer length in meters"),
 *         @OA\Property(property="diameter", type="number", format="float", example=300.0, description="Sewer diameter in millimeters"),
 *         @OA\Property(property="treatment_plant_id", type="integer", example=1, description="Treatment plant ID")
 *     }
 * )
 */
class SewerApiController extends Controller
{
    protected SewerLineService $sewerLineService;

    public function __construct(SewerLineService $sewerLineService)
    {
        $this->sewerLineService = $sewerLineService;
    }

    /**
     * @OA\Post(
     *     path="/storeSewer",
     *     operationId="storeSewer",
     *     tags={"Sewers"},
     *     summary="Create a new sewer",
     *     description="Create a new sewer record with optional treatment plant association.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="road_code", type="string", example="R000001"),
     *                 @OA\Property(property="location", type="string", example="Near market"),
     *                 @OA\Property(property="length", type="number", format="float", example=350.7),
     *                 @OA\Property(property="diameter", type="number", format="float", example=300.0),
     *                 @OA\Property(property="treatment_plant_id", type="integer", example=1)
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Sewer created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Sewer created successfully."),
     *                 @OA\Property(property="data", ref="#/components/schemas/Sewer")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(SewerLineRequest $request): JsonResponse
    {
        $sewer = $this->sewerLineService->storeOrUpdate(null, $request->all());

        return response()->json([
            'message' => 'Sewer created successfully.',
            'data'    => $sewer,
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/updateSewer/{code}",
     *     operationId="updateSewer",
     *     tags={"Sewers"},
     *     summary="Update an existing sewer",
     *     description="Update sewer fields for an existing sewer identified by code.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Sewer code"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="road_code", type="string", example="R000001"),
     *                 @OA\Property(property="location", type="string", example="Near market"),
     *                 @OA\Property(property="length", type="number", format="float", example=350.7),
     *                 @OA\Property(property="diameter", type="number", format="float", example=300.0),
     *                 @OA\Property(property="treatment_plant_id", type="integer", example=1)
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Sewer updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Sewer updated successfully."),
     *                 @OA\Property(property="data", ref="#/components/schemas/Sewer")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=404, description="Sewer not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(SewerLineRequest $request, string $code): JsonResponse
    {
        $sewer = SewerLine::where('code', $code)->first();

        if (!$sewer) {
            return response()->json([
                'message' => 'Sewer not found with code: ' . $code,
            ], 404);
        }

        $input = $request->all();

        if (empty($input)) {
            $json = json_decode($request->getContent(), true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                $input = $json;
            }
        }

        if (empty($input)) {
            return response()->json([
                'message' => 'Request body is empty.',
            ], 422);
        }

        $allowedFields = [
            'road_code',
            'location',
            'length',
            'diameter',
            'treatment_plant_id',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $sewer->{$field} = $input[$field];
            }
        }

        $sewer->user_id = Auth::id();
        $sewer->save();

        return response()->json([
            'message' => 'Sewer updated successfully.',
            'data'    => $sewer->fresh(),
        ]);
    }
}
