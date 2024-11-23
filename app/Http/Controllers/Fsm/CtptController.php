<?php
// Last Modified Date: 19-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;
use App\Http\Requests\Fsm\CtptRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fsm\Ctpt;
use DB;
use App\Models\LayerInfo\Ward;
use App\Models\BuildingInfo\Building;
use DataTables;
use App\Services\Fsm\CtptServiceClass;
use App\Models\BuildingInfo\BuildContain;
use App\Enums\CtptStatus;
use App\Enums\CtptStatusOperational;
use App\Models\Fsm\BuildToilet;



class CtptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected CtptServiceClass $ctptServiceClass;

    public function __construct(CtptServiceClass $ctptServiceClass)
    {
        $this->middleware('auth');
        $this->middleware('permission:List CT/PT General Informations', ['only' => ['index']]);
        $this->middleware('permission:View CT/PT General Information', ['only' => ['show']]);
        $this->middleware('permission:Add CT/PT General Information', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit CT/PT General Information', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete CT/PT General Information', ['only' => ['destroy']]);
        $this->middleware('permission:Export CT/PT General Informations', ['only' => ['export']]);
         /**
         * creating a service class instance
         */
        $this->ctptServiceClass = $ctptServiceClass;
    }
    /**
    * Display a listing of the public/community toilets.
    *
    * @return \Illuminate\View\View
    */
    public function index()
    {
       $page_title = 'Public / Community Toilets';
       $ward = Ward::orderBy('ward','asc')->pluck('ward','ward')->all();
       $status = CtptStatus::asSelectArray();
       $operational = CtptStatusOperational::asSelectArray();
       $bin = Ctpt::orderBy('bin')->pluck('bin','bin')->all();
  

       return view("fsm.ct-pt.index", compact('page_title','ward','status','operational','bin'));
    }

    /**
    * Get data related to public/community toilets.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function getData(Request $request)
    {
        return ($this->ctptServiceClass->fetchData($request));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Add Public / Community Toilets";
        $bin = Building::whereHas('containments')->whereNull('deleted_at');
        $ward = Ward::orderBy('ward')->pluck('ward','ward');
        $status = CtptStatus::asSelectArray();
        $operational = CtptStatusOperational::toEnumArray();
        return view('fsm.ct-pt.create', compact('page_title', 'ward', 'bin','status','operational'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CtptRequest $request)
    {
       return $this->ctptServiceClass->storeCtptData($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ctpt = ctpt::find($id);
        if ($ctpt) {
            $page_title = "Public / Community Toilets Details";
            $enumValue = $ctpt->status; 
            $typeValue = CtptStatusOperational::getDescription($enumValue);
            $address = Ctpt::select('building_info.buildings.house_number AS house_address')
            ->join('building_info.buildings', 'building_info.buildings.bin', '=', 'toilets.bin')
            ->where('toilets.bin', $ctpt->bin)
            ->first();  
            switch ($typeValue) {
                case 'Not operational':
                     $operational ='Not Operational';
                     break;
                case 'Operational':
                     $operational = 'Operational';
                     break;
                default:
                $operational = '';
                break;
            }
            $indicative_sign = ($ctpt->indicative_sign === true) ? 'yes' : (($ctpt->indicative_sign === false) ? 'no' : null);
            $fee_collected = ($ctpt->fee_collected === true) ? 'yes' : (($ctpt->fee_collected === false) ? 'no' : null);
            $male_or_female_facility = ($ctpt->male_or_female_facility === true) ? 'yes' : (($ctpt->male_or_female_facility === false) ? 'no' : null);
            $handicap_facility =  ($ctpt->handicap_facility === true) ? 'yes' : (($ctpt->handicap_facility === false) ? 'no' : null);
            $children_facility =  ($ctpt->children_facility === true) ? 'yes' : (($ctpt->children_facility === false) ? 'no' : null);
            $separate_facility_with_universal_design =  ($ctpt->separate_facility_with_universal_design === true) ? 'yes' : (($ctpt->separate_facility_with_universal_design === false) ? 'no' : null);
            $sanitary_supplies_disposal_facility = ($ctpt->sanitary_supplies_disposal_facility === true) ? 'yes' : (($ctpt->sanitary_supplies_disposal_facility === false) ? 'no' : null);
            return view('fsm.ct-pt.show', compact('page_title', 'ctpt','indicative_sign', 'address','fee_collected','sanitary_supplies_disposal_facility',
        'male_or_female_facility', 'handicap_facility', 'children_facility', 'operational', 'separate_facility_with_universal_design'));
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       $ctpt = Ctpt::find($id);
        if ($ctpt) {
            $page_title = "Edit Public / Community Toilets";
            $ward = Ward::orderBy('ward')->pluck('ward','ward');
            $bin = Building::whereHas('containments')->whereNull('deleted_at');
            $status = CtptStatus::asSelectArray();
            $operational = CtptStatusOperational::toEnumArray();
            return view('fsm.ct-pt.edit', compact('page_title','ctpt','ward', 'bin','status','operational'));
        } else {
            abort(404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CtptRequest $request, $id)
    {
        return $this->ctptServiceClass->updateCtptData($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $info = Ctpt::find($id);
        if ($info) {
            $check = BuildToilet::where('toilet_id', $id)->exists(); 
            if ($check) {
                return redirect('fsm/ctpt')->with('error', 'Failed to delete as building is connected to toilet');
            } else {
                $info->delete();
                return redirect('fsm/ctpt')->with('success', 'Public / Community Toilets Deleted successfully');
            }
        } else {
            return redirect('fsm/ctpt')->with('error', 'Failed to delete info');
        }
    }

    public function history($id)
    {
        $ctpt = Ctpt::find($id);
        if ($ctpt) {
            $page_title = "Public / Community Toilets History";
            return view('fsm.ct-pt.history', compact('page_title', 'ctpt'));
        } else {
            abort(404);
        }
    }

    /**
    * Export data related to public/community toilets.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\JsonResponse
    */
    public function export(Request $request)
    {

        $data = $request->all();

        return $this->ctptServiceClass->exportData($data);
    }
    /**
    * List buildings connected to a specific public/community toilet.
    *
    * @param  int  $id
    * @return \Illuminate\View\View
    */
    public function listBuildings($id)
    {
        $toilet = Ctpt::find($id);
        if ($toilet) {
            $page_title = "Building Connected to Toilet: " . $toilet->id;
            $buildings = $toilet->buildings;
            return view('fsm.ct-pt.listBuilding', compact('page_title', 'toilet', 'buildings'));
        } else {
            abort(404);
        }
    }
    /**
    * Show the form for adding buildings to a specific public/community toilet.
    *
    * @param  int  $id
    * @return \Illuminate\View\View
    */
    public function addBuildings($id)
    {
        $toilet = Ctpt::find($id);
        $page_title = "Add Buildings to Toilet: " . $toilet->id;
        return view('fsm.ct-pt.addBuildings', compact('page_title', 'toilet'));
    }
     /**
     * Get the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAllBuildingData(Request $request)
    {
        $allBuildingData = Building::select('*');

        return Datatables::of($allBuildingData)
            ->filter(function ($query) use ($request) {
                if ($request->bin) {
                    $query->where('bin', $request->bin);
                }
                if ($request->holding_num) {
                    $query->where('taxcd', $request->holding_num);
                }
            })
            ->make(true);
    }

    public function saveBuildings(Request $request, $id)
    {
        $toilet = Ctpt::find($id);

        if ($toilet) {
            $this->validate($request, [
                'bin' => 'required',
            ]);

            $toilet->save();
            $toilet->buildings()->syncWithoutDetaching($request->bin);
            $buildings = $toilet->buildings()->orderBy('bin')->get();
            if(count($buildings) == 1)
            {
            $toilet->buildings()->syncWithoutDetaching([
                $buildings[0]->bin => ['main_building' => '1'],
            ]);
            }
            return redirect('fsm.ct-pt/' . $id . '/buildings')->with('success','Buildings added to this toilet');
        } else {
            return redirect('fsm.ct-pt/' . $id . '/buildings')->with('error','Failed to add buildings');
        }
    }

    public function deleteBuilding($id, $buildingId)
    {
        $toilet = Ctpt::find($id);

        if ($toilet) {
            $toilet->buildings()->detach($buildingId);
            $buildings = $toilet->buildings()->orderBy('bin')->get();
            if(count($buildings) == 1)
            {
            $toilet->buildings()->syncWithoutDetaching([
                $buildings[0]->bin => ['main_building' => '1'],
            ]);
            }
            return redirect('fsm.ct-pt/' . $id . '/buildings')->with('success','Buidling deleted successfully.');
        } else {
            return redirect('fsm.ct-pt/' . $id . '/buildings')->with('error','Failed to delete building');
        }
    }
    /**
    * Get house numbers related to public/community toilets.
    *
    * @return \Illuminate\Http\JsonResponse
    */
    public function getHouseNumbers(){

        return($this->ctptServiceClass->fetchHouseNumber());

    }

}
