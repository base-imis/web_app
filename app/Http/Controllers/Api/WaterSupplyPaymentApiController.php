<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaterSupplyInfo\WaterSupplyStatus;
use App\Http\Controllers\WaterSupplyInfo\WaterSupplyController;
use Illuminate\Http\Request;

class WaterSupplyPaymentApiController extends Controller
{
    protected $waterSupplyController;
    public function __construct(WaterSupplyController $waterSupplyController)
    {
        $this->waterSupplyController = $waterSupplyController;
    }

    public function store(Request $request)
    {
        $waterSupplyPayment = $this->waterSupplyController->store($request);

        return response()->json([
            'message' => 'Water supply payment created successfully.',
            'data' => $waterSupplyPayment,
        ], 201);
    }

    public function update(Request $request, $water_customer_id)
    {
        $waterSupplyPayment = WaterSupplyStatus::where('water_customer_id', $water_customer_id)->first();
        $waterSupplyPayment->last_payment_date = $request->input('last_payment_date', $waterSupplyPayment->last_payment_date);
        $waterSupplyPayment->save();

        return response()->json([
            'message' => 'Water supply payment updated successfully.',
            'data' => $waterSupplyPayment,
        ], 200);
    }
}
