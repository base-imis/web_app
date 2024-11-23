<?php
// Last Modified Date: 09-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024)
namespace App\Http\Controllers\UtilityInfo;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UtilityInfo\Drain;
use App\Models\Fsm\TreatmentPlant;
use App\Http\Requests\UtilityInfo\DrainRequest;
use App\Services\UtilityInfo\DrainService;

class DrainController extends Controller
{
    protected DrainService $drainService;
    public function __construct(DrainService $drainService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Drains', ['only' => ['index']]);
        $this->middleware('permission:View Drain', ['only' => ['show']]);
        $this->middleware('permission:Add Drain', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Drain', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Drain', ['only' => ['destroy']]);
        $this->middleware('permission:Export Drains to Excel', ['only' => ['export']]);
        $this->drainService = $drainService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Drain Network";
        $cover_type = Drain::distinct('cover_type')->pluck('cover_type','cover_type')->all();

        return view('utility-info/drains.index', compact('page_title', 'cover_type'));
    }

    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->drainService->getAllData($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Drain";
        return view('drains.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DrainRequest $request)
    {
        $data = $request->all();
        $this->drainService->storeOrUpdate($id = null,$data);
        return redirect('utilityinfo/drains')->with('success','Drain created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Find the drain by its ID
        $drain = Drain::find($id);
    
        // Check if the drain was found
        if ($drain) {
            // Check if the treatmentPlant relationship is not null
            $treatmentplant = $drain->treatmentPlant ? $drain->treatmentPlant->name : '';
    
            // Format the size and length attributes
            $drain->size = number_format($drain->size, 2);
            $drain->length = number_format($drain->length, 2);
    
            // Set the page title
            $page_title = "Drain Network Details";
    
            // Return the view with the data
            return view('utility-info/drains.show', compact('page_title', 'drain', 'treatmentplant'));
        } else {
            // Abort with a 404 error if the drain was not found
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
        $drain = Drain::find($id);
        if ($drain) {
            $drain->size = number_format($drain->size, 2);
            // Format the length attribute to display only two decimal places
            $drain->length = number_format($drain->length, 2);
            $page_title = "Edit Drain Network";
            $cover_type = Drain::where('cover_type','!=',null)->groupBy('cover_type')->pluck('cover_type','cover_type');
            $surface_type = Drain::where('surface_type','!=',null)->groupBy('surface_type')->pluck('surface_type','surface_type');
            $treatmentPlants = TreatmentPlant::where('status', true)->pluck('name', 'id')->unique();
            return view('utility-info/drains.edit', compact('page_title', 'drain', 'cover_type','surface_type','treatmentPlants'));
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
    public function update(DrainRequest $request, $id)
    {
        $drain = Drain::find($id);
        if ($drain) {
            $data = $request->all();
            $this->drainService->storeOrUpdate($drain->code,$data);
            return redirect('utilityinfo/drains')->with('success',' Drain Network updated successfully');
        } else {
            return redirect('utilityinfo/drains')->with('error','Failed to update drain');
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
        $drain = Drain::find($id);

        if ($drain) {
            if ($drain->buildings()->exists()) {
              
                return redirect('utilityinfo/drains')->with('error','Failed to delete drain, it is associated with buildings data');
            }
            else {
                $drain->delete();
                return redirect('utilityinfo/drains')->with('success','Drain deleted Successfully');
            }

        } else {
            return redirect('utilityinfo/drains')->with('error','Failed to delete Drain');
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
        $drain = Drain::find($id);
        if ($drain) {
            $page_title = "Drain Network History";
            return view('utility-info/drains.history', compact('page_title', 'drain'));
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
        return $this->drainService->download($data);
    }
    public function getDrainNames(){
        $query = Drain::all()->toQuery();
        if (request()->search){
            $query->where('code','ilike','%'.request()->search.'%');
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
        $drains = $query->offset($start_from)
            ->limit($limit)
            ->get();
        $json = [];
        foreach($drains as $drain)
        {
            $json[] = ['id'=>$drain['code'], 'text'=>$drain['code']];
        }

        return response()->json(['results' =>$json, 'pagination' => ['more' => $more] ]);
    }

}
