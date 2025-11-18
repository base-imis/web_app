<?php

namespace App\Http\Controllers\EducationInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EducationInfo\School;
use Yajra\DataTables\Facades\DataTables;
use App\Services\EducationInfo\EducationService;
use Illuminate\Support\Facades\DB;
use App\Models\BuildingInfo\Building;
use App\Models\LayerInfo\Ward;

class EducationController extends Controller
{
    //
    protected $educationService;

    public function __construct(EducationService $educationService)
    {
        $this->educationService = $educationService;
    }

    public function index()
    {
        $institution_types = [
            'school' => 'School',
            'college' => 'College',
            'university' => 'University',
            'other' => 'Other'
        ];
        $wards = [
            '1' => 'Ward 1',
            '2' => 'Ward 2',
            '3' => 'Ward 3',
            // Add more wards as needed
        ];


        return view('education-info.school.index',compact('institution_types', 'wards'));
    }

    public function dashboard()
    {
    // Count total schools
            $totalSchools = DB::table('educationinfo.schools')->count();

            // Count primary (basic_1_5)
            // Count primary (Basic 1-5 + Basic 6-8)
            $primarySchools = DB::table('educationinfo.schools')
                ->where(function($query) {
                    $query->where('school_type_basic_1_5', 1)
                        ->orWhere('school_type_basic_6_8', 1);
                })
                ->count();

            // Count secondary (Secondary 9-10)
            $secondarySchools = DB::table('educationinfo.schools')
                ->where('school_type_secondary_9_10', 1)
                ->count();

            // Count higher secondary (Secondary 9-12)
            $higherSecondarySchools = DB::table('educationinfo.schools')
                ->where('school_type_secondary_9_12', 1)
                ->count();

            // Count all primary + secondary + higher secondary
            $primarySecondarySchools = DB::table('educationinfo.schools')
                ->where(function($query) {
                    $query->where('school_type_basic_1_5', 1)
                        ->orWhere('school_type_basic_6_8', 1)
                        ->orWhere('school_type_secondary_9_10', 1)
                        ->orWhere('school_type_secondary_9_12', 1);
                })
                ->count();
            $prePrimarySchools = DB::table('educationinfo.schools')
                ->where('school_type_pre_primary', 1)
                ->where('school_type_basic_1_5', 0)
                ->where('school_type_basic_6_8', 0)
                ->where('school_type_secondary_9_10', 0)
                ->where('school_type_secondary_9_12', 0)
                ->count();


            /* shcool containe Pre primary */
            $prePrimaryWithOtherLevels = DB::table('educationinfo.schools')
            ->where('school_type_pre_primary', 1)
            ->where(function($query) {
                $query->where('school_type_basic_1_5', 1)
                    ->orWhere('school_type_basic_6_8', 1)
                    ->orWhere('school_type_secondary_9_10', 1)
                    ->orWhere('school_type_secondary_9_12', 1);
            })
            ->count();


            /* Card */
            $prePrimaryUpTo5 = DB::table('educationinfo.schools')
            ->where('school_type_pre_primary', 1)
            ->where('school_type_basic_1_5', 1)
            ->where('school_type_basic_6_8', 0)
            ->where('school_type_secondary_9_10', 0)
            ->where('school_type_secondary_9_12', 0)
            ->count();

            $prePrimaryUpTo8 = DB::table('educationinfo.schools')
                ->where('school_type_pre_primary', 1)
                ->where('school_type_basic_6_8', 1)
                ->where('school_type_secondary_9_10', 0)
                ->where('school_type_secondary_9_12', 0)
                ->count();

            $prePrimaryUpTo10 = DB::table('educationinfo.schools')
                ->where('school_type_pre_primary', 1)
                ->where('school_type_secondary_9_10', 1)
                ->where('school_type_secondary_9_12', 0)
                ->count();

            $prePrimaryUpTo12 = DB::table('educationinfo.schools')
                ->where('school_type_pre_primary', 1)
                ->where('school_type_secondary_9_12', 1)
                ->count();

            // Total with pre-primary and any other level (not only pre-primary alone)
            $prePrimaryTotal = DB::table('educationinfo.schools')
                ->where('school_type_pre_primary', 1)
                ->where(function($query) {
                    $query->where('school_type_basic_1_5', 1)
                        ->orWhere('school_type_basic_6_8', 1)
                        ->orWhere('school_type_secondary_9_10', 1)
                        ->orWhere('school_type_secondary_9_12', 1);
                })
                ->count();

            $basic1to5 = DB::table('educationinfo.schools')
                ->where('school_type_basic_1_5', 1)
                ->where('school_type_basic_6_8', 1)
                ->where('school_type_secondary_9_10', 1)
                ->where('school_type_secondary_9_12', 1)
                ->count();
            $basic6to8 = DB::table('educationinfo.schools')
                ->where('school_type_basic_6_8', 1)
                ->where('school_type_secondary_9_10', 1)
                ->where('school_type_secondary_9_12', 1)
                ->count();
            $scondary9to10 = DB::table('educationinfo.schools')
                ->where('school_type_secondary_9_10', 1)
                ->where('school_type_secondary_9_12', 1)
                ->count();
            $scondary9to12 = DB::table('educationinfo.schools')
                ->where('school_type_secondary_9_12', 1)
                ->count();


            // Prepare chart counts
            $schoolTypeCount = [
                'labels' => [
                    'Pre Primary',
                    'Basic 1-5',
                    'Basic 6-8',
                    'Secondary 9-10',
                    'Secondary 9-12'
                ],
                'datasets' => [[
                    'label' => 'Number of Schools',
                    'backgroundColor' => '#4e73df',
                    'data' => [
                        DB::table('educationinfo.schools')->where('school_type_pre_primary', 1)->count(),
                        DB::table('educationinfo.schools')->where('school_type_basic_1_5', 1)->count(),
                        DB::table('educationinfo.schools')->where('school_type_basic_6_8', 1)->count(),
                        DB::table('educationinfo.schools')->where('school_type_secondary_9_10', 1)->count(),
                        DB::table('educationinfo.schools')->where('school_type_secondary_9_12', 1)->count(),
                    ]
                ]]
            ];

            return view('dashboard.educationDashboard', compact(
                'schoolTypeCount',
                'totalSchools',
                'primarySchools',
                'primarySecondarySchools',
                'prePrimaryWithOtherLevels',
                'prePrimarySchools',
                'secondarySchools',
                'higherSecondarySchools',
                'prePrimaryUpTo5',
                'prePrimaryUpTo8',
                'prePrimaryUpTo10',
                'prePrimaryUpTo12',
                'prePrimaryTotal',
                'basic1to5',
                'basic6to8',
                'scondary9to10',
                'scondary9to12'
            ));
}


   /*  public function getData(Request $request){
    // You can adapt this later to use Eloquent

        $query = School::select([
        'custom_school_id',
        'name',
        'ward_no',
        'contact_person_name',
        'contact_person_number',
        'school_type_pre_primary',
        'school_type_basic_1_5',
        'school_type_basic_6_8',
        'school_type_secondary_9_10',
        'school_type_secondary_9_12',
        ]);

        return DataTables::of($query)
            ->addColumn('type', function ($row) {
                // Example: you could build 'type' by checking school_type_* fields
                $types = [];
                if ($row->school_type_pre_primary) $types[] = 'Pre-Primary';
                if ($row->school_type_basic_1_5) $types[] = 'Basic 1-5';
                if ($row->school_type_basic_6_8) $types[] = 'Basic 6-8';
                if ($row->school_type_secondary_9_10) $types[] = 'Secondary 9-10';
                if ($row->school_type_secondary_9_12) $types[] = 'Secondary 9-12';
                return implode(', ', $types) ?: 'N/A';
            })
            ->addColumn('ownership', function($row){
                // Or if you later have an ownership field
                return 'Public'; // hard-coded for now
            })
            ->addColumn('action', function ($row) {
                    $content = \Form::open(['method' => 'DELETE', 'route' => ['education.school.delete', $row->custom_school_id]]);

                    // View
                    $content .= '<a href="' . route('education.school.show', $row->custom_school_id) . '"
                                    class="btn btn-sm btn-info mb-1"
                                    title="View Details">
                                    <i class="fas fa-list"></i>
                                </a> ';

                    // Edit
                    $content .= '<a href="' . route('education.school.edit', $row->custom_school_id) . '"
                                    class="btn btn-sm btn-info mb-1"
                                    title="Edit School">
                                    <i class="fas fa-edit"></i>
                                </a> ';

                    // Map view example (adjust your controller / routes accordingly)
                    $content .= '<a href="#"
                                    class="btn btn-sm btn-info mb-1"
                                    title="View on Map">
                                    <i class="fas fa-map-marker-alt"></i>
                                </a> ';

                    // Delete button
                    $content .= '<button type="submit" class="btn btn-sm btn-danger mb-1"
                                    onclick="return confirm(\'Are you sure you want to delete this school?\')"
                                    title="Delete School">
                                    <i class="fas fa-trash"></i>
                                </button>';

                    $content .= \Form::close();
                    return $content;
                })
                ->rawColumns(['action'])
            ->make(true);
    } */
  public function getData(Request $request)
{

    try {
        $query = School::select([
                'custom_school_id',
                'name',
                'ward_no',
                'contact_person_name',
                'contact_person_number',
                'school_type_pre_primary',
                'school_type_basic_1_5',
                'school_type_basic_6_8',
                'school_type_secondary_9_10',
                'school_type_secondary_9_12',
            ])
            ->when($request->institution_name, function ($q, $v) {
                $q->where('name', 'like', "%{$v}%");
            })
            ->when($request->institution_type, function ($q, $v) {
                // placeholder – add real filter when you have a type column
            })
            ->when($request->ward_number, function ($q, $v) {
                $q->where('ward_no', $v);
            })
            ->when($request->ownership, function ($q, $v) {
                // placeholder – add real filter when you have an ownership column
            });

        return DataTables::of($query)
            ->addColumn('type', function ($row) {
                $types = [];
                if ($row->school_type_pre_primary)    $types[] = 'Pre-Primary';
                if ($row->school_type_basic_1_5)      $types[] = 'Basic 1-5';
                if ($row->school_type_basic_6_8)      $types[] = 'Basic 6-8';
                if ($row->school_type_secondary_9_10) $types[] = 'Secondary 9-10';
                if ($row->school_type_secondary_9_12) $types[] = 'Secondary 9-12';
                return implode(', ', $types) ?: 'N/A';
            })
            ->addColumn('ownership', function ($row) {
                return 'Public'; // placeholder until you add a real column
            })
            ->addColumn('action', function ($row) {
                // IMPORTANT: use custom_school_id, NOT id
                $id = $row->custom_school_id;

                // safety guard – if somehow it's null, don't break the whole table
                if (!$id) {
                    \Log::error('School row missing custom_school_id', ['row' => $row]);
                    return '';
                }

                $showUrl   = route('education.school.show',  ['id' => $id]);
                $editUrl   = route('education.school.edit',  ['id' => $id]);
                $deleteUrl = route('education.school.delete', ['id' => $id]);

                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="'.$showUrl.'"
                           class="btn btn-info show-institution"
                           data-id="'.$id.'"
                           title="View">
                            <i class="fas fa-list"></i>
                        </a>
                        <a href="'.$editUrl.'" class="btn btn-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="'.$deleteUrl.'" method="POST" style="display:inline;"
                              onsubmit="return confirm(\'Delete?\')">
                            '.csrf_field().method_field('DELETE').'
                            <button class="btn btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);

    } catch (\Throwable $e) {
        \Log::error('Education getData failed', [
            'msg'   => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'error' => 'Server error: '.$e->getMessage(),
        ], 500);
    }
}

    public function create()
    {
        $institution_types = [
            'school' => 'School',
            'college' => 'College',
            'university' => 'University',
            'other' => 'Other'
        ];
        $wards = Ward::orderBy('ward')->pluck('ward','ward');
       $usedBins = DB::table('educationinfo.schools')
            ->whereNotNull('bin')
            ->pluck('bin')
            ->toArray();

        $bin = DB::table('building_info.buildings as b')
            ->join('building_info.functional_uses as f', 'b.functional_use_id', '=', 'f.id')
            ->where('f.name', 'Educational') // or 'f.function_type'
            ->whereNotIn('b.bin', $usedBins)
            ->select('b.bin')
            ->distinct()
            ->pluck('b.bin')
            ->toArray();
        /* dd($bins); */
        return view('education-info.school.create', compact('institution_types', 'wards', 'bin'));
    }

    public function store(Request $request){
    return $this->educationService->storeSchoolInformation($request);
    }

    public function edit($id){
        $school = School::findOrFail($id);
        return view('education-info.school.edit', compact('school'));
    }

    public function update(Request $request, $id){
        return $this->educationService->updateSchoolInformation($id, $request);
    }
    public function show($id){
        $school = School::findOrFail($id);
        return view('education-info.school.show', compact('school'));
    }
    public function destroy($id){
        $school = School::findOrFail($id);
        $school->delete();
        return redirect()->route('education.school.index')->with('success', 'School deleted successfully');
    }

}
