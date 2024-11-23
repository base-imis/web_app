<?php
namespace App\Http\Controllers\Cwis;

use App\Http\Controllers\Controller;
use App\Models\Cwis\DataJmp;
use App\Models\Cwis\DataSource;
use App\Exports\JmpCsvExport;
use Maatwebsite\Excel\Excel;
use Auth;
use Datatables;
use Illuminate\Http\Request;
use App\Http\Requests;
use Laracasts\Flash\Flash;
use DB;

class CwisJmpController extends Controller
{
    private $excel;
    public function __construct(Excel $excel)
    {
      
        $this->excel = $excel;
        $this->middleware('auth');
        $this->middleware('permission:List CWIS JMP', ['only' => ['index']]);
        $this->middleware('permission:View CWIS JMP', ['only' => ['show']]);
        $this->middleware('permission:Add CWIS JMP', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit CWIS JMP', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Export CWIS JMP to Excel', ['only' => ['export']]);
    }

    public function index(Request $request)
    {
        $year = DataJmp::select("year")
                    ->distinct()
                    ->orderby('year', 'desc')
                    ->pluck('year')->firstOrFail();

        $pickyear = DataJmp::select("year")
                    ->distinct()
                    ->orderby('year', 'desc')->pluck('year');

        $page_title = "Data Framework for JMP";

        $subCategory_titles = DB::Table('cwis.data_jmp as d')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', $year)
                    ->distinct()->pluck('ds.sub_category_title');
   
        $param_list = DataJmp::where('year', '=', $year)
                    ->orderBy('parameter_id')
                    ->groupBy('parameter_id')
                    ->pluck('parameter_id');
        $param_listcount = count($param_list);


        for($i=0; $i<$param_listcount ; $i++)
        {
            $param_titles[$i] = DB::Table('cwis.data_jmp as d')
                        ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                        ->where('d.year', '=', $year)
                        ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                        ->orderBy('d.source_id')
                        ->pluck('parameter_title');
            $param_details[$i] = DB::Table('cwis.data_jmp as d')
                        ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.data_value')
                        ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr, d.data_type[array_length(d.data_type, 1)] as data_type_req')
                        ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                        ->where('d.year', '=', $year)
                        ->where('d.parameter_id', '=', $param_list[$i])
                        ->orderBy('d.source_id')
                        ->get();
        }
        return view('cwis/cwis-df-jmp.index', compact('pickyear', 'page_title', 'subCategory_titles', 'param_listcount', 'param_titles', 'param_details'));
    }
    public function createIndex(Request $request)
    {
        $newsurveyear = DataJmp::latest()->selectRaw("year + 1  as newyear")->limit(1)->get();

        $page_title = "Data Framework for JMP";

        $subCategory_titles = DataSource::where('category_id', '=', 4)
                    ->distinct()->pluck('sub_category_title');

        $param_list = DataSource::where('category_id', '=', 4)
                    ->orderBy('parameter_id')
                    ->groupBy('parameter_id')
                    ->pluck('parameter_id');
        $param_listcount = count($param_list);


        for($i=0; $i<$param_listcount ; $i++)
        {   
            $param_titles[$i] = DataSource::where('category_id', '=', 4)
                        ->where('parameter_id', '=', $param_list[$i])->limit(1)
                        ->orderBy('id')
                        ->pluck('parameter_title');
            $param_details[$i] = DataSource::select('parameter_title', 'assmntmtrc_dtpnt', 'unit', 'co_cf')
                        ->selectRaw('data_type[1] as data_type, data_type[2] as data_type_phldr,  data_type[array_length(data_type, 1)] as data_type_req')
                        ->where('category_id', '=', 4)
                        ->where('parameter_id', '=', $param_list[$i])
                        ->orderBy('id')
                        ->get();
        }
        return view('cwis/cwis-df-jmp.create', compact('newsurveyear', 'page_title', 'subCategory_titles', 'param_listcount', 'param_titles', 'param_details'));
    }

    public function createStore(Request $request, cwis_jmp $cwis_jmp)
    {
        $cwis_data_source = DataSource::where('category_id', '=', 4)->orderBy('id')->get();

        if($cwis_data_source){ 
            foreach($cwis_data_source as $key => $cwis_jmp){
                $cwis_jmp = new DataJmp;
                $cwis_jmp->sub_category_id = $request->sub_category_id[$key];
                $cwis_jmp->parameter_id = $request->parameter_id[$key];
                $cwis_jmp->assmntmtrc_dtpnt = $request->assmntmtrc_dtpnt[$key];
                $cwis_jmp->unit = $request->unit[$key];
                $cwis_jmp->co_cf = $request->co_cf[$key];
                $cwis_jmp->data_value = $request->data_value[$key];
                // $cwis_jmp->data_type = $request->data_type[$key];
                $cwis_jmp->sym_no = $request->sym_no[$key];
                $cwis_jmp->year = $request->year[$key];
                $cwis_jmp->source_id = $request->source_id[$key];
                $cwis_jmp->save();
            }

            return redirect('cwis-df-jmp')->with('success','CWIS updated successfully');
        }
        return redirect('cwis-df-jmp')->with('error','Failed to update Data');
    }
    public function show(Request $request, $year)
    {
        
        $pickyear = DataJmp::select("year")
                    ->distinct()
                    ->orderby('year', 'desc')->pluck('year');

        $page_title = "Data Framework for JMP";

        $subCategory_titles = DB::Table('cwis.data_jmp as d')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', $year)
                    ->distinct()->pluck('ds.sub_category_title');
   
        $param_list = DataJmp::where('year', '=', $year)
                    ->orderBy('parameter_id')
                    ->groupBy('parameter_id')
                    ->pluck('parameter_id');
        $param_listcount = count($param_list);


        for($i=0; $i<$param_listcount ; $i++)
        {
            $param_titles[$i] = DB::Table('cwis.data_jmp as d')
                        ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                        ->where('d.year', '=', $year)
                        ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                        ->orderBy('d.source_id')
                        ->pluck('parameter_title');
            $param_details[$i] = DB::Table('cwis.data_jmp as d')
                        ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.data_value')
                        ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr, d.data_type[array_length(d.data_type, 1)] as data_type_req')
                        ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                        ->where('d.year', '=', $year)
                        ->where('d.parameter_id', '=', $param_list[$i])
                        ->orderBy('d.source_id')
                        ->get();
        }
        return view('cwis/cwis-df-jmp.index', compact('pickyear', 'page_title', 'subCategory_titles', 'param_listcount', 'param_titles', 'param_details'));
    }

    public function store(Request $request, cwis_jmp $cwis_jmp)
    {
        $cwis_infos = DataJmp::where('year', '=', 2021)->orderBy('source_id')->get();

        if($cwis_infos){ 
            foreach($cwis_infos as $key => $cwis_info){
                $cwis_info->data_value = $request->data_value[$key];
                $cwis_info->save();
            }

            return redirect('cwis-df-jmp')->with('success','CWIS updated successfully');
        }
        return redirect('cwis-df-jmp')->with('error','Failed to update Data');
    }
    public function exportJmpCsv(Request $request)
    {
        $year = $request->year_select;
        ob_end_clean(); 
        ob_start(); 
        return $this->excel->download(new JmpCsvExport($year), 'CWIS JMP.xlsx');
     }
    
}
