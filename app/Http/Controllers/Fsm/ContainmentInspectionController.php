<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\ContainmentInspection;
use Illuminate\Http\Request;

class ContainmentInspectionController extends Controller
{
    /**
     * Show page (your blade view with filters + DataTable)
     */
    public function index()
    {
        $page_title = 'Containment Inspections';
        return view('fsm.containment-inspection.index', compact('page_title'));
    }

    /**
     * DataTables endpoint: /fsm/containment-inspection/data
     */
    public function data(Request $request)
    {
        $draw   = (int) $request->get('draw', 1);
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        $ebpsId = trim((string) $request->get('ebps_id', ''));
        $compliance = $request->get('compliance_status', ''); // "true" | "false" | ""

        $baseQuery = ContainmentInspection::query();

        // Filter: EBPS ID (text search)
        if ($ebpsId !== '') {
            $baseQuery->where('ebps_id', 'ILIKE', '%' . $ebpsId . '%'); // Postgres
        }

        // Filter: compliance_status (boolean)
        if ($compliance !== '') {
            $baseQuery->where('compliance_status', filter_var($compliance, FILTER_VALIDATE_BOOLEAN));
        }

        $recordsTotal = ContainmentInspection::count();
        $recordsFiltered = (clone $baseQuery)->count();

        // Ordering (based on DataTables column index)
        $orderColumnIndex = (int) ($request->input('order.0.column', 0));
        $orderDir = $request->input('order.0.dir', 'desc');

        $columnMap = [
            0 => 'ebps_id',
            1 => 'compliance_status',
        ];
        $orderColumn = $columnMap[$orderColumnIndex] ?? 'ebps_id';

        $rows = $baseQuery
            ->orderBy($orderColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'ebps_id' => $row->ebps_id,
                'compliance_status' => $row->compliance_status ? 'Compliant' : 'Not Compliant',
                'action' => view('fsm.containment-inspection.partials.action', [
                    'ebps_id' => $row->ebps_id
                ])->render(),
            ];
        })->toArray();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Optional: View one record by ebps_id
     */
    public function show(string $ebps_id)
    {
        $info = \App\Models\Fsm\ContainmentInspection::findOrFail($ebps_id);
        $page_title = "Containment Inspection - {$ebps_id}";

        return view('fsm.containment-inspection.show', compact('page_title', 'info'));
    }

    /**
     * Optional: Edit page
     */
    public function edit(string $ebps_id)
    {
        $page_title = "Edit Containment Inspection - {$ebps_id}";
        $inspection = ContainmentInspection::findOrFail($ebps_id);

        return view('fsm.containment-inspection.edit', compact('page_title', 'inspection'));
    }
}
