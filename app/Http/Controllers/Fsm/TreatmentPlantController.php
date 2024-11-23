<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use App\Models\Fsm\TreatmentPlant;
use App\Http\Requests\Fsm\TreatmentPlantRequest;
use App\Models\BuildingInfo\StructureType;
use App\Models\BuildingInfo\SanitationSystemTechnology;

use Auth;
use Illuminate\Http\Request;
use DB;
use Validator;
use App\Services\Auth\UserService;
use App\Services\Fsm\TreatmentPlantService;
use App\Enums\TreatmentPlantStatus;
use App\Enums\TreatmentPlantType;

class TreatmentPlantController extends Controller
{   protected UserService $userService;
    protected TreatmentPlantService $treatmentPlantService;
    public function __construct(TreatmentPlantService $treatmentPlantService, UserService $userService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Treatment Plants', ['only' => ['index']]);
        $this->middleware('permission:View Treatment Plant', ['only' => ['show']]);
        $this->middleware('permission:Add Treatment Plant', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Treatment Plant', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Treatment Plant', ['only' => ['destroy']]);
        $this->middleware('permission:Export Treatment Plants to Excel', ['only' => ['export']]);
        $this->treatmentPlantService = $treatmentPlantService;
        $this->userService = $userService;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $page_title = "Treatment Plants";
        $tpType = TreatmentPlantType::toEnumArray();
        $status = TreatmentPlantType::asSelectArray();

        $structure_type = StructureType::orderBy('type','asc')->pluck('type','id')->all();
        return view('fsm/treatment-plants.index', compact('page_title', 'status','tpType'));
    }

    public function getData(Request $request)
    {
        $data = $request->all();

        return $this->treatmentPlantService->getAllTreatmentPlants($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Add Treatment Plant";
        $treatmentPlant = null;
        $status = TreatmentPlantStatus::asSelectArray();
        $tpType = TreatmentPlantType::toEnumArray();
        return view('fsm/treatment-plants.create', compact('page_title', 'treatmentPlant', 'status','tpType'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TreatmentPlantRequest $request)
    {
        $data = $request->all();
        $this->treatmentPlantService->storeOrUpdate($id = null,$data);
        $data['treatment_plant_id'] =  TreatmentPlant::where('name',$data['name'])->pluck('id')->first();
        $data['user_type'] = "Treatment Plant";
        $data['roles']= "Treatment Plant";
        $data['gender']= null;
        $data['username']= null;
        if(!is_null($request->create_user))
        {
             $this->userService->storeOrUpdate($id = null,$data);
             $successMessage = 'Treatment plant and user created successfully';
        } else {
             $successMessage = 'Treatment plant created successfully';
        }
       
        return redirect('fsm/treatment-plants')->with('success',$successMessage);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id$
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $treatmentPlant = TreatmentPlant::find($id);
        $status = TreatmentPlantStatus::getDescription($treatmentPlant->status);
        $enumValue = (int)$treatmentPlant->type;
        $typeValue = TreatmentPlantType::getDescription($enumValue);
        switch ($enumValue) {
            case TreatmentPlantType::CentralizedWWTP:
                $type = 'Centralized WWTP';
                break;

            case TreatmentPlantType::DecentralizedWWTP:
                $type = 'Decentralized WWTP';
                break;
                case TreatmentPlantType::CoTreatmentPlant:
                    $type = 'Co-Treatment Plant';
                    break;
            default:
                $type = 'FSTP';
        }
        if ($treatmentPlant) {
            $page_title = "Treatment Plant Details";
            return view('fsm/treatment-plants.show', compact('page_title', 'treatmentPlant', 'status','type'));
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
        $treatmentPlant = TreatmentPlant::find($id);
        $status = TreatmentPlantStatus::asSelectArray();
        $tpType = TreatmentPlantType::toEnumArray();
        if ($treatmentPlant) {
            $page_title = "Edit Treatment Plant";
            return view('fsm/treatment-plants.edit', compact('page_title', 'treatmentPlant', 'status','tpType'));
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
    public function update(TreatmentPlantRequest $request, $id)
    {
        $treatmentPlant = TreatmentPlant::find($id);
        if ($treatmentPlant) {
            $data = $request->all();
            $this->treatmentPlantService->storeOrUpdate($treatmentPlant->id,$data);
            return redirect('fsm/treatment-plants')->with('success','Treatment plant updated successfully');
        } else {
            return redirect('fsm/treatment-plants')->with('error','Failed to update treatment plant');
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
        $treatmentPlant = TreatmentPlant::find($id);

        if ($treatmentPlant) {

                if($treatmentPlant->users()){
                    $treatmentPlant->users()->delete();
                }
                if($treatmentPlant->users()){
                    $treatmentPlant->users()->delete();
                }
                if($treatmentPlant->treatmentplantTests()){
                    $treatmentPlant->treatmentplantTests()->delete();
                }

                $treatmentPlant->delete();

                return redirect('fsm/treatment-plants')->with('success','Treatment plant deleted successfully');

            return redirect('fsm/treatment-plants')->with('error','Failed to delete treatment plant');
        }
    }

    /**
     * Display history of the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function history($id)
    {
        $treatmentPlant = TreatmentPlant::find($id);
        if ($treatmentPlant) {
            $page_title = "Treatment Plant History";
            return view('fsm/treatment-plants.history', compact('page_title', 'treatmentPlant'));
        } else {
            abort(404);
        }
    }
    /**
     * Export a listing of the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $data = $request->all();

        return $this->treatmentPlantService->download($data);

    }
}
