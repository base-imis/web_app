<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TaxPaymentInfo\TaxPaymentController;
use App\Models\TaxPaymentInfo\TaxPaymentStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Tax Payments",
 *     description="Import and export property tax payment information"
 * )
 *
 * @OA\Schema(
 *     schema="TaxPaymentApiResponse",
 *     type="object",
 *     properties={
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="data", type="object", nullable=true),
 *         @OA\Property(property="message", type="string", example="Tax created successfully.")
 *     }
 * )
 */
class TaxPaymentApiController extends Controller
{
    protected $taxPaymentController;

    public function __construct(TaxPaymentController $taxPaymentController)
    {
        $this->taxPaymentController = $taxPaymentController;
    }

    /**
     * @OA\Post(
     *     path="/storeTaxPayment",
     *     operationId="storeTaxPayment",
     *     tags={"Tax Payments"},
     *     summary="Import property tax payment data from CSV",
     *     description="Upload a CSV file containing property tax payment records and import them into the system.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"csvfile"},
     *                 properties={
     *                     @OA\Property(property="csvfile", type="string", format="binary", description="CSV file with tax payment data")
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tax payments imported successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaxPaymentApiResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $response = $this->taxPaymentController->store($request);

        return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Tax created successfully.',
            ], 201);
    }

    /**
     * @OA\Get(
     *     path="/taxPayments/export",
     *     operationId="exportTaxPayments",
     *     tags={"Tax Payments"},
     *     summary="Export property tax payments",
     *     description="Download the current property tax payment collection data as a CSV file.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="ward", in="query", description="Ward filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="due_year", in="query", description="Due year filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="tax_code", in="query", description="Tax code filter", @OA\Schema(type="string")),
     *     @OA\Parameter(name="bin", in="query", description="BIN filter", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="CSV export generated successfully",
     *         @OA\Header(header="Content-Type", @OA\Schema(type="string", example="text/csv")),
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string", example="attachment; filename=Property Tax Collection Information Support System.csv"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function export()
    {
        $this->taxPaymentController->export();

        return response()->json([
                'success' => true,
                'message' => 'Tax exported successfully.',
            ], 200);
    }

    /**
     * @OA\Get(
     *     path="/taxPayments/exportunmatched",
     *     operationId="exportUnmatchedTaxPayments",
     *     tags={"Tax Payments"},
     *     summary="Export unmatched tax payment records",
     *     description="Download unmatched property tax payment records as a CSV file.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Unmatched records export generated successfully",
     *         @OA\Header(header="Content-Type", @OA\Schema(type="string", example="text/csv")),
     *         @OA\Header(header="Content-Disposition", @OA\Schema(type="string", example="attachment; filename=Unmatched Records-Property Tax Collection Information Support System.csv"))
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function exportunmatched()
    {
        $response = $this->taxPaymentController->exportunmatched();

        return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Unmatched tax exported successfully.',
            ], 200);
    }

    /**
     * @OA\Patch(
     *     path="/taxPayments/{tax_code}",
     *     operationId="updateTaxPayment",
     *     tags={"Tax Payments"},
     *     summary="Update a tax payment record",
     *     description="Update the last payment date for an existing tax payment record.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="tax_code",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Tax code of the payment record"
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
     *         description="Tax payment updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/TaxPaymentApiResponse")
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Tax payment not found")
     * )
     */
    public function update(Request $request, $tax_code)
    {
        $taxPayment = TaxPaymentStatus::where('tax_code', $tax_code)->first();

        if (!$taxPayment) {
            return response()->json([
                'success' => false,
                'message' => 'Tax payment not found.',
            ], 404);
        }

        $taxPayment->last_payment_date = $request->input('last_payment_date', $taxPayment->last_payment_date);
        $taxPayment->save();

        return response()->json([
            'success' => true,
            'tax_code' => $tax_code,
            'message' => 'Tax payment updated successfully.',
        ], 200);
    }
}
