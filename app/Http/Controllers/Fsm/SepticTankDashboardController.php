<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\ContainmentInspection;
use App\Models\Fsm\STMonitoringDashboardCount;
use App\Services\Ebps\EbpsClient;
use Illuminate\Support\Facades\DB;

class SepticTankDashboardController extends Controller
{
    public function __construct(private EbpsClient $ebps)
    {
    }

    public function index()
    {
        $page_title = "Standard Septic Tank Monitoring Dashboard";

       try {
        $Building_data = $this->getBuildingApprovalData();
        $Sanitation_data = $this->getSanitationRequestData();

        $totalRequestCount = count($Sanitation_data);
        $matchingRecords = ContainmentInspection::count();

        // 👇 ADD THIS BLOCK HERE
        $counts = STMonitoringDashboardCount::firstOrNew(['id' => 1]);

        $counts->upto_plinth = (int) ($Building_data['TotalPlinthLevelApproval'] ?? 0);
        $counts->above_plinth = (int) ($Building_data['TotalAbovePlinthLevelApproval'] ?? 0);
        $counts->completion = (int) ($Building_data['TotalCompletionApproval'] ?? 0);
        $counts->inspection_requested = (int) $totalRequestCount;
        $counts->inspection_completed = (int) $matchingRecords;

        $counts->save();

    } catch (\Throwable $e) {
            // EBPS down or response changed — load snapshot values
            $snapshot = STMonitoringDashboardCount::find(1);

            $buildingData = [
                'TotalPlinthLevelApproval'      => (int) optional($snapshot)->upto_plinth,
                'TotalAbovePlinthLevelApproval' => (int) optional($snapshot)->above_plinth,
                'TotalCompletionApproval'       => (int) optional($snapshot)->completion,
            ];

            $totalRequestCount   = (int) optional($snapshot)->inspection_requested;
            $inspectionCompleted = (int) optional($snapshot)->inspection_completed;

            $complianceYesCount = DB::table('fsm.sanitation_inspections')
                ->where('compliance_status', 'Yes')
                ->count();
        }

        // dashboard totals
        $result = (int)$buildingData['TotalPlinthLevelApproval']
            + (int)$buildingData['TotalAbovePlinthLevelApproval']
            + (int)$buildingData['TotalCompletionApproval'];

        $totalInspectionRequestCount = (int)$totalRequestCount + (int)$inspectionCompleted;

        return view("containmentmanagementinfo.index", compact(
            'page_title',
            'buildingData',
            'result',
            'totalRequestCount',
            'totalInspectionRequestCount',
            'inspectionCompleted',
            'complianceYesCount'
        ));
    }

    /**
     * Ward-wise: requests from EBPS sanitation report (counts by WardNo)
     */
    public function requestPerWardChart(): array
    {
        $data = $this->ebps->sanitationRequestReport();

        $counts = [];
        foreach ($data as $row) {
            $ward = $row['WardNo'] ?? null;
            if ($ward === null || $ward === '') continue;

            $ward = (int) $ward;
            $counts[$ward] = ($counts[$ward] ?? 0) + 1;
        }

        ksort($counts);

        return [
            'labels' => array_map('strval', array_keys($counts)),
            'values' => array_values($counts),
        ];
    }

    /**
     * Ward-wise: completed inspections from local DB.
     * Adjust column name if your table uses different ward field.
     */
    public function completedPerWardChart(): array
    {
        // Example assumes ContainmentInspection has a "ward" column.
        // If it is "ward_no" or "ward_number" change it here.
        $rows = ContainmentInspection::query()
            ->selectRaw('ward as ward, COUNT(*) as cnt')
            ->whereNotNull('ward')
            ->groupBy('ward')
            ->orderBy('ward')
            ->get();

        $labels = $rows->pluck('ward')->map(fn ($w) => (string) $w)->toArray();
        $values = $rows->pluck('cnt')->map(fn ($c) => (int) $c)->toArray();

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function saveSnapshot(int $plinth, int $abovePlinth, int $completion, int $requested, int $completed): void
    {
        $snapshot = STMonitoringDashboardCount::find(1) ?? new STMonitoringDashboardCount();
        $snapshot->id = 1;

        $snapshot->upto_plinth          = $plinth;
        $snapshot->above_plinth         = $abovePlinth;
        $snapshot->completion           = $completion;
        $snapshot->inspection_requested = $requested;
        $snapshot->inspection_completed = $completed;

        $snapshot->save();
    }

    /**
     * Ensures keys exist and values are numeric.
     */
    private function normalizeBuildingApprovalData(array $data): array
    {
        return [
            'TotalPlinthLevelApproval'      => (int) ($data['TotalPlinthLevelApproval'] ?? 0),
            'TotalAbovePlinthLevelApproval' => (int) ($data['TotalAbovePlinthLevelApproval'] ?? 0),
            'TotalCompletionApproval'       => (int) ($data['TotalCompletionApproval'] ?? 0),
        ];
    }
}
