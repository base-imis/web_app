<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SwmPaymentInfo\SwmServicePaymentController;
use App\Models\SwmPaymentInfo\SwmPaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="SWM Payments",
 *     description="Import and export solid waste management payment information"
 * )
 *
 * @OA\Schema(
 *     schema="SwmPaymentApiResponse",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="data", type="object", nullable=true),
 *         @OA\Property(property="message", type="string", example="SWM payment created successfully.")
 *     }
 * )
 */
class SwmPaymentApiController extends Controller
{
    protected $swmServicePaymentController;

    public function __construct(SwmServicePaymentController $swmServicePaymentController)
    {
        $this->swmServicePaymentController = $swmServicePaymentController;
    }

    /**
     * @OA\Post(
     *     path="/storeSwmPayment",
     *     operationId="storeSwmPayment",
     *     tags={"SWM Payments"},
     *     summary="Import SWM payment data from CSV",
     *     description="Upload a CSV file containing SWM payment records and import them into the system.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"csvfile"},
     *                 properties={
     *                     @OA\Property(property="csvfile", type="string", format="binary", description="CSV file with SWM payment data")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SWM payments imported successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SwmPaymentApiResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $response = $this->swmServicePaymentController->store($request);

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'SWM payment created successfully.',
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/swmPayments/export",
     *     operationId="exportSwmPayments",
     *     tags={"SWM Payments"},
     *     summary="Export SWM payments",
     *     description="Download the current SWM payment collection data as a CSV file.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="ward", in="query", description="Ward filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="due_year", in="query", description="Due year filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="swm_customer_id", in="query", description="SWM customer ID filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="bin", in="query", description="BIN filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="tax_code", in="query", description="Tax code filter", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="CSV export generated successfully",
     *         @OA\Header(header="Content-Type", @OA\Schema(type="string", example="text/csv")),
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string", example="attachment; filename=Solid Waste Information Support System.csv"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function export()
    {
        $this->swmServicePaymentController->export();

        return response()->json([
            'success' => true,
            'message' => 'SWM payment exported successfully.',
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/swmPayments/exportunmatched",
     *     operationId="exportUnmatchedSwmPayments",
     *     tags={"SWM Payments"},
     *     summary="Export unmatched SWM payment records",
     *     description="Download unmatched SWM payment records as a CSV file.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unmatched records export generated successfully",
     *         @OA\Header(header="Content-Type", @OA\Schema(type="string", example="text/csv")),
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string", example="attachment; filename=Unmatched Records-Solid Waste Information Support System.csv"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function exportunmatched()
    {
        $response = $this->swmServicePaymentController->exportunmatched();

        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Unmatched SWM payment exported successfully.',
        ], 200);
    }

    /**
     * @OA\Patch(
     *     path="/swmPayments/{swm_customer_id}",
     *     operationId="updateSwmPayment",
     *     tags={"SWM Payments"},
     *     summary="Update an SWM payment record",
     *     description="Update the last payment date for an existing SWM payment record.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="swm_customer_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="SWM customer ID of the payment record"
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
     *         description="SWM payment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SwmPaymentApiResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="SWM payment not found")
     * )
     */
    public function update(Request $request, $swm_customer_id)
    {
        $swmPayment = SwmPaymentStatus::where('swm_customer_id', $swm_customer_id)->first();

        if (!$swmPayment) {
            return response()->json([
                'success' => false,
                'message' => 'SWM payment not found.',
            ], 404);
        }

        $swmPayment->last_payment_date = $request->input('last_payment_date', $swmPayment->last_payment_date);
        $swmPayment->save();

        return response()->json([
            'success' => true,
            'swm_customer_id' => $swm_customer_id,
            'message' => 'SWM payment updated successfully.',
        ], 200);
    }
}
