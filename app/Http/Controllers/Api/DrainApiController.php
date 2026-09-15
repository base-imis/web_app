<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UtilityInfo\DrainRequest;
use Illuminate\Http\Request;
use App\Services\UtilityInfo\DrainService;
use Illuminate\Http\JsonResponse;
use App\Models\UtilityInfo\Drain;
use Auth;

/**
 * @OA\Tag(
 *     name="Drains",
 *     description="Drain network endpoints for creating and updating drain records"
 * )
 *
 * @OA\Schema(
 *     schema="Drain",
 *     type="object",
 *     properties={
 *         @OA\Property(property="code", type="string", example="D000001", description="Drain code"),
 *         @OA\Property(property="road_code", type="string", example="R000001", description="Associated road code"),
 *         @OA\Property(property="cover_type", type="string", example="Concrete", description="Cover type"),
 *         @OA\Property(property="surface_type", type="string", example="Open", description="Surface type"),
 *         @OA\Property(property="length", type="number", format="float", example=350.7, description="Drain length in meters"),
 *         @OA\Property(property="size", type="string", example="600mm", description="Drain size"),
 *         @OA\Property(property="treatment_plant_id", type="integer", example=1, description="Treatment plant ID")
 *     }
 * )
 */
class DrainApiController extends Controller
{
    protected DrainService $drainService;

    public function __construct(DrainService $drainService)
    {
        $this->drainService = $drainService;
    }

    /**
     * @OA\Post(
     *     path="/storeDrain",
     *     operationId="storeDrain",
     *     tags={"Drains"},
     *     summary="Create a new drain",
     *     description="Create a new drain record with optional treatment plant association.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="road_code", type="string", example="R000001"),
     *                 @OA\Property(property="cover_type", type="string", example="Concrete"),
     *                 @OA\Property(property="surface_type", type="string", example="Open"),
     *                 @OA\Property(property="length", type="number", format="float", example=350.7),
     *                 @OA\Property(property="size", type="string", example="600mm"),
     *                 @OA\Property(property="treatment_plant_id", type="integer", example=1)
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Drain created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Drain created successfully."),
     *                 @OA\Property(property="data", ref="#/components/schemas/Drain")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(DrainRequest $request): JsonResponse
    {
        $drain = $this->drainService->storeOrUpdate(null, $request->all());

        return response()->json([
            'message' => 'Drain created successfully.',
            'data'    => $drain,
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/updateDrain/{code}",
     *     operationId="updateDrain",
     *     tags={"Drains"},
     *     summary="Update an existing drain",
     *     description="Update drain fields for an existing drain identified by code.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Drain code"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="road_code", type="string", example="R000001"),
     *                 @OA\Property(property="cover_type", type="string", example="Concrete"),
     *                 @OA\Property(property="surface_type", type="string", example="Open"),
     *                 @OA\Property(property="length", type="number", format="float", example=350.7),
     *                 @OA\Property(property="size", type="string", example="600mm"),
     *                 @OA\Property(property="treatment_plant_id", type="integer", example=1)
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Drain updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Drain updated successfully."),
     *                 @OA\Property(property="data", ref="#/components/schemas/Drain")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=404, description="Drain not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
     public function update(DrainRequest $request, string $code): JsonResponse
    {
        $drain = Drain::where('code', $code)->first();

        if (!$drain) {
            return response()->json([
                'message' => 'Drain not found with code: ' . $code,
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
            'cover_type',
            'surface_type',
            'length',
            'size',
            'treatment_plant_id',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $drain->{$field} = $input[$field];
            }
        }

        $drain->user_id = Auth::id();
        $drain->save();

        return response()->json([
            'message' => 'Drain updated successfully.',
            'data'    => $drain->fresh(),
        ]);
    }
}
