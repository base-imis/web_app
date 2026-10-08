<?php

namespace App\Http\Controllers\Cwis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class CwisGeneratorDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List CWIS');
    }

    private function filterData(Request $request): array
    {
        $currentYear = now()->year;
        $years = collect(range($currentYear, $currentYear - 5));
        $serviceOutcomes = ['equity' => __('Equity'), 'safety' => __('Safety'), 'sustainability' => __('Sustainability')];
        $filters = $request->validate([
            'year' => ['nullable', 'integer', Rule::in($years->all())],
            'service_outcome' => ['nullable', Rule::in(array_keys($serviceOutcomes))],
        ]);
        $year = $filters['year'] ?? $currentYear;

        return compact('years', 'serviceOutcomes', 'filters', 'year');
    }

    private function policyInputKey(string $code): string
    {
        return str_replace(['-', '.'], '_', $code);
    }

    public function index(Request $request)
    {
        return view('cwis.generator-data.index', $this->filterData($request) + [
            'page_title' => __('CWIS Generator Data'),
        ]);
    }

    public function getData(Request $request)
    {
        $data = $this->filterData($request);
        // Mock rows only; the Yajra endpoint does not read or change indicator records.
        $rows = collect($data['serviceOutcomes'])->filter(function ($label, $outcome) use ($data) {
            return empty($data['filters']['service_outcome']) || $data['filters']['service_outcome'] === $outcome;
        })->map(function ($label) use ($data) {
            return ['year' => $data['year'], 'service_outcome' => $label];
        })->values();

        return DataTables::of($rows)
            ->addColumn('action', function ($row) {
                return view('cwis.generator-data.actions', compact('row'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create(Request $request)
    {
        $sections = [
            [
                'title' => __('Equity of Subsidies (EQ-3)'),
                'fields' => [
                    ['nss', 'total_subsidies_amount_nss', 'EQ-3.NSS', 'Total Subsidies Amount NSS', 'Total subsidies amount paid to non-sewered sanitation during the reporting year.', '0.01'],
                    ['ss', 'total_subsidies_amount_ss', 'EQ-3.SS', 'Total Subsidies Amount SS', 'Total subsidies amount paid to sewered sanitation during the reporting year, in the same currency as NSS.', '0.01'],
                ],
            ],
            [
                'title' => __('Gender Equity in Sanitation Workforce (EQ-4 / EQ-4a / EQ-5)'),
                'fields' => [
                    ['w4', 'women_employee_count', 'EQ-4.W', 'Women Employees', 'Women in eligible sanitation decision-making bodies, including full-time and contract staff; excluding NGOs and community organizations.', '1'],
                    ['t4', 'total_employee_count', 'EQ-4.T', 'Total Employees', 'All employees in the same eligible bodies.', '1'],
                    ['w4a', 'women_leadership_count', 'EQ-4a.W', 'Women in Leadership', 'Women who are functional or managerial heads.', '1'],
                    ['t4a', 'total_leadership_count', 'EQ-4a.T', 'Total Leadership Positions', 'All people in leadership positions; cannot exceed total employees.', '1'],
                    ['sw', 'average_salary_of_women_in_sanitation', 'EQ-5.W', 'Avg Annual Salary - Women', 'Average annualized pay of eligible women.', '0.01'],
                    ['sm', 'average_salary_of_men_in_sanitation', 'EQ-5.M', 'Avg Annual Salary - Men', 'Average annualized pay of eligible men, on the same basis as women.', '0.01'],
                ],
            ],
        ];
        $policies = [
            ['EQ-6.1', 'Training / Certification Required', '', 'Training requirement document'],
            ['EQ-6.1a', 'Training Covers Labor Rights & Recourse', 'EQ-6.1', 'Training content covering both topics'],
            ['EQ-6.1b', 'Training Covers Safety, Health Risks & SOP', 'EQ-6.1', 'Curriculum covering all three topics'],
            ['EQ-6.2', 'Formal Legal Recourse for All Workers', '', 'Evidence that the channel also covers informal workers'],
            ['EQ-6.3', 'Right to Unionize / Union Registered', '', 'Union registration document'],
            ['EQ-6.3a', 'Worker Union Is Operational', 'EQ-6.3', 'Meeting minutes or similar evidence'],
            ['EQ-6.3b', 'City Supports Running the Union', 'EQ-6.3', 'Evidence of city support'],
            ['EQ-6.4', 'All Sanitation Workers Are Covered by Social Security', '', 'Document verifying social-security coverage of all sanitation workers in the city'],
            ['EQ-6.5', 'All Sanitation Workers Are Covered by Health Insurance', '', 'Document verifying health-insurance coverage of all sanitation workers in the city'],
        ];

        return view('cwis.generator-data.create', $this->filterData($request) + [
            'page_title' => __('Add CWIS Data'),
            'sections' => $sections,
            'policies' => $policies,
        ]);
    }

    public function store(Request $request)
    {
        $currentYear = now()->year;
        $years = collect(range($currentYear, $currentYear - 5))->all();
        $policyCodes = [
            'EQ-6.1',
            'EQ-6.1a',
            'EQ-6.1b',
            'EQ-6.2',
            'EQ-6.3',
            'EQ-6.3a',
            'EQ-6.3b',
            'EQ-6.4',
            'EQ-6.5',
        ];

        $validated = $request->validate([
            'year' => ['required', 'integer', Rule::in($years)],
            'save_mode' => ['required', Rule::in(['draft', 'submitted'])],
            'inputs.total_subsidies_amount_nss' => ['nullable', 'numeric', 'min:0'],
            'inputs.total_subsidies_amount_ss' => ['nullable', 'numeric', 'min:0'],
            'inputs.women_employee_count' => ['nullable', 'integer', 'min:0'],
            'inputs.total_employee_count' => ['nullable', 'integer', 'min:0'],
            'inputs.women_leadership_count' => ['nullable', 'integer', 'min:0'],
            'inputs.total_leadership_count' => ['nullable', 'integer', 'min:0'],
            'inputs.average_salary_of_women_in_sanitation' => ['nullable', 'numeric', 'min:0'],
            'inputs.average_salary_of_men_in_sanitation' => ['nullable', 'numeric', 'min:0'],
            'policies' => ['nullable', 'array'],
            'evidence' => ['nullable', 'array'],
            'evidence.*' => ['nullable', 'file', 'max:10240'],
        ]);

        $inputs = $validated['inputs'] ?? [];
        $policies = $request->input('policies', []);
        $files = $request->file('evidence', []);
        $userId = auth()->id();
        $now = now();

        $storedPaths = [];
        try {
        $submissionId = DB::transaction(function () use ($validated, $inputs, $policies, $files, $policyCodes, $userId, $now, &$storedPaths) {
            $submissionId = DB::table('cwis.equity_submissions')->insertGetId([
                'reporting_year' => $validated['year'],
                'status' => $validated['save_mode'],
                'submitted_by' => $validated['save_mode'] === 'submitted' ? $userId : null,
                'submitted_at' => $validated['save_mode'] === 'submitted' ? $now : null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('cwis.equity_subsidies')->insert([
                'submission_id' => $submissionId,
                'total_subsidies_amount_nss' => $inputs['total_subsidies_amount_nss'] ?? null,
                'total_subsidies_amount_ss' => $inputs['total_subsidies_amount_ss'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('cwis.sanitation_personnel_snapshot')->insert([
                'submission_id' => $submissionId,
                'women_employee_count' => $inputs['women_employee_count'] ?? null,
                'total_employee_count' => $inputs['total_employee_count'] ?? null,
                'women_leadership_count' => $inputs['women_leadership_count'] ?? null,
                'total_leadership_count' => $inputs['total_leadership_count'] ?? null,
                'average_salary_of_women_in_sanitation' => $inputs['average_salary_of_women_in_sanitation'] ?? null,
                'average_salary_of_men_in_sanitation' => $inputs['average_salary_of_men_in_sanitation'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $policyData = [
                'submission_id' => $submissionId,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $policyColumns = [
                'EQ-6.1' => ['training_certification_required', 'training_requirement_document'],
                'EQ-6.1a' => ['covers_labor_rights_and_recourse', 'labor_rights_document'],
                'EQ-6.1b' => ['covers_safety_health_and_sop', 'safety_health_sop_document'],
                'EQ-6.2' => ['legal_recourse_available_to_all', 'legal_recourse_document'],
                'EQ-6.3' => ['worker_union_exists', 'worker_union_registration_document'],
                'EQ-6.3a' => ['worker_union_operational', 'worker_union_operation_document'],
                'EQ-6.3b' => ['city_supports_worker_union', 'worker_union_support_document'],
                'EQ-6.4' => ['all_workers_social_security_covered', 'social_security_coverage_document'],
                'EQ-6.5' => ['all_workers_health_insurance_covered', 'health_insurance_coverage_document'],
            ];

            foreach ($policyCodes as $code) {
                [$valueColumn, $documentColumn] = $policyColumns[$code];
                $inputKey = $this->policyInputKey($code);
                $value = $policies[$inputKey] ?? null;
                $policyData[$valueColumn] = $value === 'Yes' ? true : ($value === 'No' ? false : null);
                $policyData[$documentColumn . '_id'] = null;
                if ($value === 'Yes' && isset($files[$inputKey])) {
                    $file = $files[$inputKey];
                    $path = $file->store('cwis/equity-documents', 'public');
                    if ($path === false) {
                        throw new \RuntimeException('Unable to store CWIS evidence document.');
                    }
                    $storedPaths[] = $path;
                    $policyData[$documentColumn . '_id'] = DB::table('cwis.equity_documents')->insertGetId([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'created_by' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            DB::table('cwis.sanitation_worker_policy')->insert($policyData);

            return $submissionId;
        });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($storedPaths);
            throw $exception;
        }

        return redirect()
            ->action('Cwis\CwisGeneratorDataController@create', ['year' => $validated['year']])
            ->with('success', __(
                $validated['save_mode'] === 'submitted'
                    ? 'CWIS Equity data submitted for approval. Submission ID: :id'
                    : 'CWIS Equity data saved as draft. Submission ID: :id',
                ['id' => $submissionId]
            ));
    }
}
