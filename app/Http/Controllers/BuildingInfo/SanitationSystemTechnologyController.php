<?php
namespace App\Http\Controllers\BuildingInfo;

use App\Http\Controllers\Controller;
use App\Models\BuildingInfo\SanitationSystemTechnology;
use App\Models\BuildingInfo\SanitationSystem;
use App\Http\Requests\BuildingInfo\SanitationSystemTechnologyRequest;
use App\Services\BuildingInfo\SanitationSystemTechnologyService;
use Auth;
use Illuminate\Http\Request;
use DB;

class SanitationSystemTechnologyController extends Controller
{
    protected SanitationSystemTechnologyService $sanitationSystemTechnologyService;
    public function __construct(SanitationSystemTechnologyService $sanitationSystemTechnologyService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Sanitation System Technology', ['only' => ['index']]);
        $this->middleware('permission:View System Technology', ['only' => ['show']]);
        $this->middleware('permission:Add System Technology', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit System Technology', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete System Technology', ['only' => ['destroy']]);
        $this->sanitationSystemTechnologyService = $sanitationSystemTechnologyService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Sanitation System Technologies";
        $sanitationSystemTypes = sanitationSystem::orderBy('id','asc')->pluck('type','id')->all();
        return view('building-info/sanitation-system-technologies.index', compact('page_title', 'sanitationSystemTypes'));
    }

    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->sanitationSystemTechnologyService->getallData($data);
        
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Sanitation System Technology";
        $sanitationSystemtechnology = null;
        $sanitationSystemTypes = sanitationSystem::orderBy('id','asc')->pluck('type','id')->all();
        return view('building-info/sanitation-system-technologies.create', compact('page_title','sanitationSystemtechnology', 'sanitationSystemTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SanitationSystemTechnologyRequest $request)
    {
        $data = $request->all();
        $this->sanitationSystemTechnologyService->storeOrUpdate($id = null,$data);    

        return redirect('building-info/sanitation-system-technologies')->with('success','Sanitation System Technology created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        $sanitationSystemTechnology = SanitationSystemTechnology::find($id);
        
        if ($sanitationSystemTechnology) {
            $page_title = "Sanitation Sytem Type";
            return view('building-info/sanitation-system-technologies.show', compact('page_title', 'sanitationSystemTechnology'));
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
        $sanitationSystemTechnology = SanitationSystemTechnology::find($id);
        $sanitationSystemTypes = sanitationSystem::orderBy('id','asc')->pluck('type','id')->all();
        if ($sanitationSystemTechnology) {
            $page_title = "Edit Sanitation System Technology";
            return view('building-info/sanitation-system-technologies.edit', compact('page_title', 'sanitationSystemTechnology', 'sanitationSystemTypes'));
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
    public function update(SanitationSystemTechnologyRequest $request, $id)
    {
        $sanitationSystemTechnology = SanitationSystemTechnology::find($id);
        if ($sanitationSystemTechnology) {
            $data = $request->all();
            $this->sanitationSystemTechnologyService->storeOrUpdate($sanitationSystemTechnology->id,$data);

            return redirect('building-info/sanitation-system-technologies')->with('success','sanitation system technology updated successfully!');
        } else {
            Flash::error('Failed to update sanitation system technology');
            return redirect('building-info/sanitation-system-technologies')->with('error','Failed to update sanitation system technology!');
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
        $sanitationSystemTechnology = SanitationSystemTechnology::find($id);

        if ($sanitationSystemTechnology) {
              
                $sanitationSystemTechnology->delete();

                return redirect('building-info/sanitation-system-technologies')->with('success','sanitation system technology deleted successfully!');
            //}
        } else {
            return redirect('building-info/sanitation-system-technologies')->with('error','Failed to delete sanitation system technology');
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
        $sanitationSystemTechnology = SanitationSystemTechnology::find($id);
        if ($sanitationSystemTechnology) {
            $page_title = "Sanitation Sytem Technology History";
            return view('building-info/sanitation-system-technologies.history', compact('page_title', 'sanitationSystemTechnology'));
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
        return $this->sanitationSystemTechnologyService->download($data);
        
    }
}
