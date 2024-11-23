<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fsm\TreatmentPlantTest;
use App\Models\Fsm\TreatmentPlant;
use App\Http\Requests\Fsm\TreatmentPlantTestRequest;
use App\Services\Fsm\TreatmentPlantTestService;

class TreatmentPlantTestController extends Controller
{
    protected TreatmentPlantTestService $treatmentPlantTest;

    public function __construct(TreatmentPlantTestService $treatmentPlantTest)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Treatment Plant Test', ['only' => ['index']]);
        $this->middleware('permission:View Treatment Plant Test', ['only' => ['show']]);
        $this->middleware('permission:Add Treatment Plant Test', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Treatment Plant Test', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Treatment Plant Test', ['only' => ['destroy']]);
        $this->middleware('permission:Export Treatment Plant Test', ['only' => ['export']]);
        $this->middleware('permission:View Treatment Plant Test History', ['only' => ['history']]);
        $this->treatmentPlantTest = $treatmentPlantTest;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Performance Efficiency Test";

        $tpnames = TreatmentPlantTest::whereNull('deleted_at')
        ->with('treatmentplants:id,name')
        ->get()
        ->pluck('treatmentplants.name', 'treatmentplants.name');
        return view('fsm.treatment-plant-test.index', compact('page_title','tpnames'));

    }

    public function getData(Request $request)
    {
        $data = $request->all();

        return $this->treatmentPlantTest->getAllTreatmentPlants($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Add Performance Efficiency Test";
        $trtName = TreatmentPlant::operational()->pluck('name', 'id')->whereNull('deleted_at');
        $treatmentPlants = null;
        return view('fsm/treatment-plant-test.create', compact('page_title', 'trtName', 'treatmentPlants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TreatmentPlantTestRequest $request)
    {
        return $this->treatmentPlantTest->storeTpt($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $treatmentPlantTest = TreatmentPlantTest::find($id);
        $treatmentPlantName = TreatmentPlantTest::where('fsm.treatmentplant_tests.id', $id)
            ->join('fsm.treatment_plants', 'fsm.treatmentplant_tests.treatment_plant_id', '=', 'fsm.treatment_plants.id')
            ->pluck('fsm.treatment_plants.name');

        if ($treatmentPlantTest) {
            $page_title = "Performance Efficiency Test Details";
            return view('fsm/treatment-plant-test.show', compact('page_title', 'treatmentPlantTest', 'treatmentPlantName'));
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
        $treatmentPlantTest = TreatmentPlantTest::find($id);
        $trtName = TreatmentPlant::pluck('name', 'id');
        if ($treatmentPlantTest) {
            $page_title = "Edit Performance Efficiency Test";
            return view('fsm/treatment-plant-test.edit', compact('page_title', 'treatmentPlantTest', 'trtName'));
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
    public function update(TreatmentPlantTestRequest $request, $id)
    {
        return $this->treatmentPlantTest->updateTpt($request, $id);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $info = TreatmentPlantTest::find($id);
        if ($info) {
            $info->delete();
            return redirect('fsm/treatment-plant-test')->with('success', 'Performance Efficiency Test deleted successfully');
        } else {
            return redirect('fsm/treatment-plant-test')->with('error', 'Failed to delete Performance Efficiency Test');
        }
    }
    public function history($id)
    {
        $treatmentPlant = TreatmentPlantTest::find($id);
        if ($treatmentPlant) {
            $page_title = "Performance Efficiency Test History";
            return view('fsm/treatment-plant-test.history', compact('page_title', 'treatmentPlant'));
        } else {
            abort(404);
        }
    }
    public function export(Request $request)
    {
        $data = $request->all();

        return $this->treatmentPlantTest->exportData($data);
    }
}
