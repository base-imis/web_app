<?php
namespace App\Http\Controllers\BuildingInfo;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\SanitationSystem;
use App\Http\Requests\BuildingInfo\SanitationSystemTypeRequest;
use App\Services\BuildingInfo\SanitationSystemTypeService;
use Auth;
use Illuminate\Http\Request;
use DB;

class SanitationSystemTypeController extends Controller
{
    protected SanitationSystemTypeService $sanitationSystemTypeService;
    public function __construct(SanitationSystemTypeService $sanitationSystemTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Sanitation System Types', ['only' => ['index']]);
        $this->middleware('permission:View Sanitation System Type', ['only' => ['show']]);
        $this->middleware('permission:Add Sanitation System Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Sanitation System Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Sanitation System Type', ['only' => ['destroy']]);
        $this->sanitationSystemTypeService = $sanitationSystemTypeService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Sanitation System Types";
        return view('building-info/sanitation-system-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->sanitationSystemTypeService->getallData($data);
        
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Sanitation System Type";
        $sanitationSystemType = null;
        return view('building-info/sanitation-system-types.create', compact('page_title', 'sanitationSystemType'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SanitationSystemTypeRequest $request)
    {
        $data = $request->all();
        $this->sanitationSystemTypeService->storeOrUpdate($id = null,$data);    

        return redirect('building-info/sanitation-system-types')->with('success','Sanitation System Type created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        $sanitationSystemType = SanitationSystem::find($id);
        
        if ($sanitationSystemType) {
            $page_title = "Sanitation Sytem Type";
            return view('building-info/sanitation-system-types.show', compact('page_title'));
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
        $sanitationSystemType = SanitationSystem::find($id);
       
        if ($sanitationSystemType) {
            $page_title = "Edit Sanitation System Type";
            return view('building-info/sanitation-system-types.edit', compact('page_title', 'sanitationSystemType'));
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
    public function update(SanitationSystemTypeRequest $request, $id)
    {
        $sanitationSystemType = SanitationSystem::find($id);
        if ($sanitationSystemType) {
            $data = $request->all();
            $this->sanitationSystemTypeService->storeOrUpdate($sanitationSystemType->id,$data);

            return redirect('building-info/sanitation-system-types')->with('success','sanitation system type updated successfully!');
        } else {
            Flash::error('Failed to update sanitation system type');
            return redirect('building-info/sanitation-system-types')->with('error','Failed to update sanitation system type!');
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
        $sanitationSystemType = SanitationSystem::find($id);

        if ($sanitationSystemType) {
              
                $sanitationSystemType->delete();

                return redirect('building-info/sanitation-system-types')->with('success','sanitation system type deleted successfully!');
            //}
        } else {
            return redirect('building-info/sanitation-system-types')->with('error','Failed to delete sanitation system type');
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
        $sanitationSystemType = SanitationSystem::find($id);
        if ($sanitationSystemType) {
            $page_title = "Sanitation Sytem type History";
            return view('building-info/sanitation-system-types.history', compact('page_title', 'sanitationSystemType'));
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
        return $this->sanitationSystemTypeService->download($data);
        
    }
}
