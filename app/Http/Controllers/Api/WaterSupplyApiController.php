<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UtilityInfo\WaterSupplysRequest;
use App\Http\Controllers\WaterSupplyInfo\WaterSupplyController;
use App\Models\WaterSupplyInfo\WaterSupplyStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Water Supply Payments",
 *     description="Import and export water supply payment information"
 * )
 *w
 * @OA\Schema(
 *     schema="WaterSupplyApiResponse",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="data", type="object", nullable=true),
 *         @OA\Property(property="message", type="string", example="Water supply created successfully.")
 *     }
 * )
 */
class WaterSupplyApiController extends Controller
{
    protected $waterSupplyController;

    public function __construct(WaterSupplyController $waterSupplyController)
    {
        $this->waterSupplyController = $waterSupplyController;
    }

    /**
     * @OA\Post(
     *     path="/storeWaterSupplyPayment",
     *     operationId="storeWaterSupplyPayment",
     *     tags={"Water Supply Payments"},
     *     summary="Import water supply payment data from CSV",
     *     description="Upload a CSV file containing water supply payment records and import them into the system.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"csvfile"},
     *                 properties={
     *                     @OA\Property(property="csvfile", type="string", format="binary", description="CSV file with water supply payment data")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Water supply payments imported successfully",
     *         @OA\JsonContent(ref="#/components/schemas/WaterSupplyApiResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(WaterSupplyPaymentApiRequest $request)
    {
        $response = $this->waterSupplyController->store($request);

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Water supply created successfully.',
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/waterSupplyPayments/export",
     *     operationId="exportWaterSupplyPayments",
     *     tags={"Water Supply Payments"},
     *     summary="Export water supply payments",
     *     description="Download the current water supply payment collection data as a CSV file.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="ward", in="query", description="Ward filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="due_year", in="query", description="Due year filter", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="CSV export generated successfully",
     *         @OA\Header(header="Content-Type", @OA\Schema(type="string", example="text/csv")),
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string", example="attachment; filename=Water Supply Information Support System.csv"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function export()
    {
        $this->waterSupplyController->export();

        return response()->json([
            'success' => true,
            'message' => 'Water supply exported successfully.',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/waterSupplyPayments/exportunmatched",
     *     operationId="exportUnmatchedWaterSupplyPayments",
     *     tags={"Water Supply Payments"},
     *     summary="Export unmatched water supply payment records",
     *     description="Download unmatched water supply payment records as a CSV file.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unmatched records export generated successfully",
     *         @OA\Header(header="Content-Type", @OA\Schema(type="string", example="text/csv")),
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string", example="attachment; filename=Unmatched Records-Water Supply Information Support System.csv"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function exportunmatched()
    {
        $response = $this->waterSupplyController->exportunmatched();

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Unmatched water supply exported successfully.',
        ], 200);
    }

    /**
     * @OA\Patch(
     *     path="/waterSupplyPayments/{water_customer_id}",
     *     operationId="updateWaterSupplyPayment",
     *     tags={"Water Supply Payments"},
     *     summary="Update a water supply payment record",
     *     description="Update the last payment date for an existing water supply payment record.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="water_customer_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Water customer ID of the payment record"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             properties={
     *                 @OA\Property(property="last_payment_date", type="string", format="date", example="2024-01-15")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Water supply payment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/WaterSupplyApiResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Water supply payment not found")
     * )
     */
    public function update(WaterSupplyPaymentApiRequest $request, $water_customer_id)
    {
        $waterSupplyPayment = WaterSupplyStatus::where('water_customer_id', $water_customer_id)->first();

        if (!$waterSupplyPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Water supply payment not found.',
            ], 404);
        }

        $waterSupplyPayment->last_payment_date = $request->input('last_payment_date', $waterSupplyPayment->last_payment_date);
        $waterSupplyPayment->save();

        return response()->json([
            'success' => true,
            'water_customer_id' => $water_customer_id,
            'message' => 'Water supply payment updated successfully.',
        ], 200);
    }

    public function storeWaterSupply(Request $request): JsonResponse
    {
        return $this->store($request);
    }
}
