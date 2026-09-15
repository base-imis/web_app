<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fsm\ContainmentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\BuildingInfo\BuildingStructureService;
use Illuminate\Validation\Rule;

class ContainmentApiController extends Controller
{
    protected BuildingStructureService $buildingStructureService;

    public function __construct(BuildingStructureService $buildingStructureService)
    {
        $this->buildingStructureService = $buildingStructureService;
    }

    public function storeContainment(ContainmentRequest $request, string $flag, string $type): JsonResponse
    {
        try {
            if (!in_array($flag, ['communal', 'shared', 'containment'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid containment flag.',
                ], 422);
            }

            if (!in_array($type, ['create', 'update', 'createContainOnly'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid containment operation type.',
                ], 422);
            }

            $rules = [
                'bin' => [
                    'required',
                    Rule::exists('pgsql.building_info.buildings', 'bin'),
                ],
            ];

            if ($flag === 'communal') {
                $rules['ctpt_name'] = ['required'];
            }

            if ($flag === 'shared') {
                $rules['build_contain'] = [
                    'required',
                    Rule::exists('pgsql.building_info.buildings', 'bin'),
                ];
            }

            if ($flag === 'containment') {
                $rules = array_merge($rules, [
                    'type_id' => ['required', Rule::exists('pgsql.fsm.containment_types', 'id')],
                    'size' => ['required', 'numeric', 'min:0'],
                    'construction_date' => ['nullable', 'date'],
                    'location' => ['nullable', 'string'],
                    'sewer_code' => ['nullable', 'string', Rule::exists('pgsql.utility_info.sewers', 'code')],
                    'drain_code' => ['nullable', 'string', Rule::exists('pgsql.utility_info.drains', 'code')],
                ]);

                if ($type === 'update') {
                    $rules['id'] = ['required', Rule::exists('pgsql.fsm.containments', 'id')];
                }
            }

            $validated = $request->validate($rules);

            $result = $this->buildingStructureService->storeContainmentInfo($flag, $type, $request);

            return response()->json([
                'success' => true,
                'message' => 'Containment information saved successfully.',
                'data' => $result,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (Throwable $e) {
            Log::error('Containment API failed.', [
                'flag' => $flag,
                'type' => $type,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Containment could not be saved.',
            ], 500);
        }
    }
}
