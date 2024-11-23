<?php

namespace App\Http\Controllers\UtilityInfo;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fsm\TreatmentPlant;
use App\Models\UtilityInfo\SewerLine;
use App\Http\Requests\UtilityInfo\SewerLineRequest;
use App\Services\UtilityInfo\SewerLineService;

class SewerLineController extends Controller
{
    protected SewerLineService $sewerLineService;
    public function __construct(SewerLineService $sewerLineService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Sewers', ['only' => ['index']]);
        $this->middleware('permission:View Sewer', ['only' => ['show']]);
        $this->middleware('permission:Add Sewer', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Sewer', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Sewer', ['only' => ['destroy']]);
        $this->middleware('permission:Export Sewers to Excel', ['only' => ['export']]);
        $this->sewerLineService = $sewerLineService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Sewer Network";
        $location = SewerLine::whereNotNull('location')->distinct('location')->pluck('location','location')->all();
        return view('utility-info/sewer-lines.index', compact('page_title','location'));
    }

    public function getData(Request $request)
    {
        $data = $request->all();
        return $this->sewerLineService->getAllData($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = "Create Sewer Line";
        return view('sewer-lines.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SewerLineRequest $request)
    {
        $data = $request->all();
        $this->sewerLineService->storeOrUpdate($id = null,$data);
        return redirect('utilityinfo/sewerlines')->with('success','Sewer Line created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sewerLine = SewerLine::find($id);
        if ($sewerLine) {
            $treatmentplant = $sewerLine->treatmentPlant->name;
            $sewerLine->diameter = number_format($sewerLine->diameter, 2);
            // Format the length attribute to display only two decimal places
            $sewerLine->length = number_format($sewerLine->length, 2);
            $page_title = "Sewer Network Details";
            return view('utility-info/sewer-lines.show', compact('page_title', 'sewerLine' ,'treatmentplant'));
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
        $sewerLine = SewerLine::find($id);
        if ($sewerLine) {
            $sewerLine->diameter = number_format($sewerLine->diameter, 2);
            // Format the length attribute to display only two decimal places
            $sewerLine->length = number_format($sewerLine->length, 2);
            $treatdrp = TreatmentPlant::where('status', true)->pluck('name', 'id');
            $page_title = "Edit Sewer Network";
            return view('utility-info/sewer-lines.edit', compact('page_title', 'sewerLine','treatdrp'));
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
    public function update(SewerLineRequest $request, $id)
    {
        $sewerLine = SewerLine::find($id);
        if ($sewerLine) {
            $data = $request->all();
            $this->sewerLineService->storeOrUpdate($sewerLine->code,$data);
            return redirect('utilityinfo/sewerlines')->with('success','Sewer Network updated successfully');
        } else {
            return redirect('utilityinfo/sewerlines')->with('error','Failed to update drain');
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
        $sewerLine = SewerLine::find($id);
        if ($sewerLine) {
            if ($sewerLine->buildings()->exists()) {
                return redirect('utilityinfo/sewerlines')->with('error','Failed to delete sewer, it is associated with buildings');
            } else {
            $sewerLine->delete();
            return redirect('utilityinfo/sewerlines')->with('success','Sewer Line deleted successfully');
            }
        } else {
            return redirect('utilityinfo/sewerlines')->with('error','Failed to delete sewer');
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
        $sewerLine = SewerLine::find($id);
        if ($sewerLine) {
            $page_title = "Sewer Network History";
            return view('utility-info/sewer-lines.history', compact('page_title', 'sewerLine'));
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

        return $this->sewerLineService->download($data);
    }

    public function getSewerNames(){
        $query = SewerLine::all()->toQuery();
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
        $sewers = $query->offset($start_from)
            ->limit($limit)
            ->get();
        $json = [];
        foreach($sewers as $sewer)
        {
            $json[] = ['id'=>$sewer['code'], 'text'=>$sewer['code']];
        }

        return response()->json(['results' =>$json, 'pagination' => ['more' => $more] ]);
    }
}
