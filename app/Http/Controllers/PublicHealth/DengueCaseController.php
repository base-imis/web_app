<?php

namespace App\Http\Controllers\PublicHealth;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PublicHealth\DengueCase;

use App\Http\Requests\PublicHealth\DengueCaseRequest;
use App\Services\PublicHealth\DengueCaseService;


class DengueCaseController extends Controller
{
    protected DengueCaseService $dengueCaseService;
    public function __construct(DengueCaseService $dengueCaseService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Public Health', ['only' => ['index']]);
        $this->middleware('permission:View Roadline', ['only' => ['show']]);
        $this->middleware('permission:Add Roadline', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Roadline', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Roadline', ['only' => ['destroy']]);
        $this->middleware('permission:Import Roadlines to Shape', ['only' => ['importShp', 'importShpStore']]);
        $this->middleware('permission:Export Roadlines to Excel', ['only' => ['export']]);
        $this->dengueCaseService = $dengueCaseService;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Dengue Cases";
        return view('public-health/dengue-cases.index', compact('page_title'));
    }
    
    public function getData(Request $request)
    {
        //$data = $request->all();
        return $this->dengueCaseService->getAllData($request);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Case";
        return view('public-health/dengue-cases.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoadLineRequest $request)
    {
        $data = $request->all();
        $this->roadlineService->storeOrUpdate($id = null,$data);
        return redirect('utilityinfo/roadlines')->with('success','Road created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $roadline = DengueCase::find($id);
        if ($roadline) {
            $page_title = "Road Details";
            return view('utility-info/road-lines.show', compact('page_title', 'roadline'));
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
        $roadline = Roadline::find($id);
        $roadHierarchy = Roadline::where('hierarchy','!=',null)->groupBy('hierarchy')->pluck('hierarchy','hierarchy');
        $roadSurfaceTypes = Roadline::where('surface_type','!=',null)->groupBy('surface_type')->pluck('surface_type','surface_type');
        if ($roadline) {
            $page_title = "Edit Road";
            return view('utility-info/road-lines.edit', compact('page_title', 'roadline','roadHierarchy','roadSurfaceTypes'));
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
    public function update(RoadLineRequest $request, $id)
    {
        $roadline = Roadline::find($id);
        if ($roadline) {
            $data = $request->all();
            $this->roadlineService->storeOrUpdate($roadline->code,$data);
            return redirect('utilityinfo/roadlines')->with('success','Road Network updated successfully');
        } else {
            return redirect('utilityinfo/roadlines')->with('error','Failed to update road');
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
        $roadline = Roadline::find($id);
        if ($roadline) {
            if ($roadline->buildings()->exists() || $roadline->containments()->exists()) {
                return redirect('utilityinfo/roadlines')->with('error','Failed to delete road, it is associated with buildings and containment data');
            } else {
            $roadline->delete();
            return redirect('utilityinfo/roadlines')->with('success','Road deleted successfully');
            }
        } else {
            return redirect('utilityinfo/roadlines')->with('error','Failed to delete road');
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
        $roadline = Roadline::find($id);
        if ($roadline) {
            $page_title = "Road History";
            return view('utility-info/road-lines.history', compact('page_title', 'roadline'));
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
        return $this->roadlineService->download($data);
        
    }

    public function getRoadNames(){
        $query = Roadline::all()->toQuery();
        if (request()->search){
            $query->where('name', 'ilike', '%'.request()->search.'%')
            ->orWhere('code','ilike','%'.request()->search.'%');
        }
        if (request()->house_number){
            $building = Building::where('house_number',request()->house_number)->first();
            $query->where('code','=',$building->road_code);
        }
        if (request()->ward){
            $building = Building::where('ward',request()->ward)->first();
            $query->where('code','=',$building->road_code);
        }
        $total = $query->count();


        $limit = 10;
        if (request()->page) {
            $page  = request()->page;
        }
        else{
            $page=1;
        };
        $start_from = ($page-1) * $limit;

        $total_pages = ceil($total / $limit);
        if($page < $total_pages){
            $more = true;
        }
        else
        {
            $more = false;
        }
        $roads = $query->offset($start_from)
            ->limit($limit)
            ->get();
        $json = [];
        foreach($roads as $road)
        {
            $json[] = ['id'=>$road['code'], 'text'=>$road['code']];
        }

        return response()->json(['results' =>$json, 'pagination' => ['more' => $more] ]);
    }
    

}
