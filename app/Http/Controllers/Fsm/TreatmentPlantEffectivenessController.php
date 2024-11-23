<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Fsm\TreatmentPlantEffectivenessRequest;
use App\Models\Fsm\TreatmentPlantEffectiveness;
use Yajra\DataTables\DataTables;
use Auth;
use App\Services\Fsm\TreatmentPlantEffectivenessService;
use App\Models\Fsm\TreatmentPlant;
use App\Enums\TreatmentPlantStatus;

class TreatmentPlantEffectivenessController extends Controller
{
    protected TreatmentPlantEffectivenessService $treatmentPlantEffectivenessService;
    public function __construct(TreatmentPlantEffectivenessService $treatmentPlantEffectivenessService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Treatment Plant Effectiveness', ['only' => ['index']]);
        $this->middleware('permission:View Treatment Plant Effectiveness', ['only' => ['show']]);
        $this->middleware('permission:Add Treatment Plant Effectiveness', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Treatment Plant Effectiveness', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Treatment Plant Effectiveness', ['only' => ['destroy']]);
        $this->middleware('permission:Export Treatment Plant Effectiveness', ['only' => ['export']]);
        $this->treatmentPlantEffectivenessService = $treatmentPlantEffectivenessService;
    }


    public function index()
    {
        $page_title = "Treatment Plant Effectiveness";
        $pickYearResults = TreatmentPlantEffectiveness::distinct()->get('year');
        return view('fsm.treatment-plant-effectiveness.index', compact('page_title', 'pickYearResults'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->treatmentPlantEffectivenessService->getAllData($data);

    }
    public function create()
    {
        $page_title = "Add Treatment Plant Effectiveness.";
        if(Auth::user()->treatment_plant_id)
        {
        $treatmentPlants = TreatmentPlant::Operational()->orderBy('id')->where('id',Auth::user()->treatment_plant_id)->pluck('name', 'id');
        }
        else{
        $treatmentPlants = TreatmentPlant::Operational()->orderBy('id')->pluck('name', 'id');
        }
        return view('fsm.treatment-plant-effectiveness.create', compact('page_title', 'treatmentPlants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TreatmentPlantEffectivenessRequest $request)
    {
        $data = $request->all();

        if(TreatmentPlantEffectiveness::where('treatment_plant_id', $request->treatment_plant_id)->where('year', $request->year)->where('deleted_at', null)->exists()){
            return redirect('fsm/treatment-plant-effectiveness')->with('error','The record of treatment plant for the year '.$request->year.'already exists!!');
        }
        else{
        $this->treatmentPlantEffectivenessService->storeOrUpdate($id = null,$data);
        return redirect('fsm/treatment-plant-effectiveness')->with('success','Info created successfully');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $treatmentPlanteffective = TreatmentPlantEffectiveness::find($id);
        $status = TreatmentPlantStatus::getDescription($treatmentPlanteffective->status);
        if ($treatmentPlanteffective) {
            $page_title = "Treatment Plant Effectiveness Details";
            return view('fsm/treatment-plant-effectiveness.show', compact('page_title', 'treatmentPlanteffective', 'status'));
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
        $info = TreatmentPlantEffectiveness::find($id);
        if(Auth::user()->treatment_plant_id)
        {
        $treatmentPlants = TreatmentPlant::Operational()->orderBy('id')->where('id',Auth::user()->treatment_plant_id)->pluck('name', 'id');
        }
        else{
        $treatmentPlants = TreatmentPlant::Operational()->orderBy('id')->pluck('name', 'id');
        }
        if ($info) {
            $page_title = "Edit Treatment Plant Effectiveness";
            return view('fsm.treatment-plant-effectiveness.edit', compact('page_title','info', 'treatmentPlants'));
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
    public function update(TreatmentPlantEffectivenessRequest $request, $id)
    {
        $info = TreatmentPlantEffectiveness::find($id);
        if ($info) {
            $data = $request->all();
            $this->treatmentPlantEffectivenessService->storeOrUpdate($info->id,$data);
            return redirect('fsm/treatment-plant-effectiveness')->with('success','info updated successfully');
        } else {
            return redirect('fsm/treatment-plant-effectiveness')->with('error','Failed to update info');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $info = TreatmentPlantEffectiveness::find($id);
        if ($info) {
            $info->delete();
            return redirect('fsm/treatment-plant-effectiveness')->with('success','info deleted successfully');
            }
        else {
            return redirect('fsm/treatment-plant-effectiveness')->with('error','Failed to delete info');
        }
    }


}
