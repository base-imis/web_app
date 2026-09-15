<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UtilityInfo\RoadLineRequest;
use App\Services\UtilityInfo\RoadlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\UtilityInfo\Roadline;
use Auth;

/**
 * @OA\Tag(
 *     name="Roads",
 *     description="Road network endpoints for creating and updating roadline records"
 * )
 *
 * @OA\Schema(
 *     schema="Roadline",
 *     type="object",
 *     properties={
 *         @OA\Property(property="code", type="string", example="R000001", description="Road code"),
 *         @OA\Property(property="name", type="string", example="Main Road", description="Road name"),
 *         @OA\Property(property="hierarchy", type="string", example="Primary", description="Road hierarchy"),
 *         @OA\Property(property="surface_type", type="string", example="Asphalt", description="Road surface type"),
 *         @OA\Property(property="length", type="number", format="float", example=1200.5, description="Road length in meters"),
 *         @OA\Property(property="right_of_way", type="number", format="float", example=10.5, description="Right of way width in meters"),
 *         @OA\Property(property="carrying_width", type="number", format="float", example=7.2, description="Carrying width in meters"),
 *         @OA\Property(property="geom", type="string", example="LINESTRING(76.7 12.9,76.8 13.0)", description="Road geometry as WKT")
 *     }
 * )
 */
class RoadApiController extends Controller
{
    protected RoadlineService $roadlineService;

    public function __construct(RoadlineService $roadlineService)
    {
        $this->roadlineService = $roadlineService;
    }

    /**
     * @OA\Post(
     *     path="/storeRoad",
     *     operationId="storeRoad",
     *     tags={"Roads"},
     *     summary="Create a new roadline",
     *     description="Create a new roadline record with optional geometry.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="name", type="string", example="Main Road"),
     *                 @OA\Property(property="hierarchy", type="string", example="Primary"),
     *                 @OA\Property(property="surface_type", type="string", example="Asphalt"),
     *                 @OA\Property(property="length", type="number", format="float", example=1200.5),
     *                 @OA\Property(property="right_of_way", type="number", format="float", example=10.5),
     *                 @OA\Property(property="carrying_width", type="number", format="float", example=7.2),
     *                 @OA\Property(property="geom", type="string", example="LINESTRING(76.7 12.9,76.8 13.0)")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Roadline created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Roadline created successfully."),
     *                 @OA\Property(property="data", ref="#/components/schemas/Roadline")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(RoadLineRequest $request): JsonResponse
    {
        $roadline = $this->roadlineService->storeOrUpdate(null, $request->all());

        return response()->json([
            'message' => 'Roadline created successfully.',
            'data'    => $roadline,
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/updateRoad/{code}",
     *     operationId="updateRoad",
     *     tags={"Roads"},
     *     summary="Update an existing roadline",
     *     description="Update the roadline fields for an existing roadline identified by code.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Road code"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="name", type="string", example="Main Road"),
     *                 @OA\Property(property="hierarchy", type="string", example="Primary"),
     *                 @OA\Property(property="surface_type", type="string", example="Asphalt"),
     *                 @OA\Property(property="length", type="number", format="float", example=1200.5),
     *                 @OA\Property(property="right_of_way", type="number", format="float", example=10.5),
     *                 @OA\Property(property="carrying_width", type="number", format="float", example=7.2)
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roadline updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="message", type="string", example="Roadline updated successfully."),
     *                 @OA\Property(property="data", ref="#/components/schemas/Roadline")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=404, description="Roadline not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(RoadLineRequest $request, string $code): JsonResponse
    {
        $roadline = Roadline::where('code', $code)->first();

        if (!$roadline) {
            return response()->json([
                'message' => 'Roadline not found with code: ' . $code,
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
            'name',
            'hierarchy',
            'surface_type',
            'length',
            'right_of_way',
            'carrying_width',
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $roadline->{$field} = $input[$field];
            }
        }

        $roadline->user_id = Auth::id();
        $roadline->save();

        return response()->json([
            'message' => 'Roadline updated successfully.',
            'data'    => $roadline->fresh(),
        ]);
    }
}