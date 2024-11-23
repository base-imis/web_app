<?php
// Last Modified Date: 16-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024)
namespace App\Http\Controllers\Cwis;

use App\Http\Controllers\Controller;
use App\Models\Cwis\DataSource;
use App\Models\Cwis\cwis_mne;
use App\Exports\MneCsvExport;
use Maatwebsite\Excel\Excel;
use Auth;
use Datatables;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;
use DB; 
use Route;

class CwisMneController extends Controller
{
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $this->middleware('auth');
        // $this->middleware('permission:List CWIS MnE', ['only' => ['index']]);
        // $this->middleware('permission:View CWIS MnE', ['only' => ['show']]);
        // $this->middleware('permission:Add CWIS MnE', ['only' => ['create', 'store']]);
        // $this->middleware('permission:Edit CWIS MnE', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:Export CWIS MnE', ['only' => ['export']]);
    }

    public function index(Request $request)
    {
        $year = $request->year ?? cwis_mne::select("year")
            ->distinct()
            ->orderby('year', 'desc')
            ->pluck('year')
            ->first();
    
        $slugyear = cwis_mne::select(DB::raw('year + 1 as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    
        $pickyear = cwis_mne::select("year")
            ->distinct()
            ->orderby('year', 'desc')
            ->pluck('year');
    
        $data = cwis_mne::where('year', $year)->pluck('data_value', 'indicator_code');
    
        // Check for NaN, nan, or na values
        $hasInvalidValues = false;
        foreach ($data as $value) {
            if (in_array(strtolower($value), ['nan', 'na'])) {
                $hasInvalidValues = true;
                break;
            }
        }
    
        $page_title = "CWIS Monitoring and Evaluation Indicators";
    
        // Flag to show the Add CWIS Data button if no data is available
        $show_add_cwis_button = cwis_mne::count("year") === 0;
    
        return view('cwis/cwis-df-mne.index', compact('pickyear', 'page_title', 'year', 'slugyear', 'show_add_cwis_button', 'data', 'hasInvalidValues'));
    }
    

    public function show(Request $request, $year)
    {

        $pickyear = cwis_mne::select("year")
                    ->distinct()
                    ->orderby('year', 'desc')->pluck('year');

        $page_title = "CWIS Indicators Monitoring and Evaluation";

        $subCategory_titles = DB::Table('cwis.data_cwis as d')
                    ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                    ->where('d.year', '=', $year)
                    ->distinct()->pluck('ds.sub_category_title');

        $param_list = cwis_mne::where('year', '=', $year)
                    ->orderBy('parameter_id')
                    ->groupBy('parameter_id')
                    ->pluck('parameter_id');
        $param_listcount = count($param_list);


        for($i=0; $i<$param_listcount ; $i++)
        {
            $param_titles[$i] = DB::Table('cwis.data_cwis as d')
                        ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                        ->where('d.year', '=', $year)
                        ->where('d.parameter_id', '=', $param_list[$i])->limit(1)
                        ->orderBy('d.source_id')
                        ->pluck('parameter_title');
            $param_details[$i] = DB::Table('cwis.data_cwis as d')
                        ->select('ds.parameter_title', 'd.assmntmtrc_dtpnt', 'd.unit', 'd.co_cf', 'd.data_value')
                        ->selectRaw('d.data_type[1] as data_type, d.data_type[2] as data_type_phldr,  d.data_type[array_length(d.data_type, 1)] as data_type_req')
                        ->leftJoin('cwis.data_source as ds', 'ds.id', '=', 'd.source_id')
                        ->where('d.year', '=', $year)
                        ->where('d.parameter_id', '=', $param_list[$i])
                        ->orderBy('d.source_id')
                        ->get();
        }
        return view('cwis/cwis-df-mne.index', compact('pickyear', 'page_title', 'subCategory_titles', 'param_listcount', 'param_titles', 'param_details'));
    }
    public function cwis($year)
    {

        $result = DB::select(DB::raw('select * from insert_data_into_cwis_table(' . $year . ');'));
        return response()->json($result);
    }
    public function store(Request $request, cwis_mne $cwis_mne)
    {
        // Determine the selected year or use the latest one
        $year = $request->year ?? cwis_mne::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->first();

        // Define the input field names that will be processed
        $fields = [
            'EQ-1',
            'SF-1a',
            'SF-1b',
            'SF-1c',
            'SF-1d',
            'SF-1e',
            'SF-1f',
            'SF-1g',
            'SF-2a',
            'SF-2b',
            'SF-2c',
            'SF-3a',
            'SF-3b',
            'SF-3c',
            'SF-3e',
            'SF-4a',
            'SF-4b',
            'SF-4d',
            'SF-5',
            'SF-6',
            'SF-7',
            'SF-9',
            'SS-1'
        ];

        // Iterate through each field and process the request data
        foreach ($fields as $field) {
            // Check if a record exists for the given year and indicator_code
            $cwis_info = cwis_mne::where('year', $year)
                ->where('indicator_code', $field) // Use 'indicator_code' to identify the field
                ->first();

            $inputValue = $request->input($field) ?? $request->input("{$field}_hidden");

            if ($cwis_info) {
                // Update the existing record with the new value
                $cwis_info->data_value = $inputValue;
                $cwis_info->save();
            } else {
                // Create a new record if it doesn't exist
                cwis_mne::create([
                    'year' => $year,
                    'indicator_code' => $field, // Use 'indicator_code' here
                    'data_value' => $inputValue,
                ]);
            }
        }

        // Redirect with a success message
        return redirect('cwis/cwis/cwis-df-mne/?year=' . $year)
            ->with('success', 'CWIS data updated successfully');
    }

    public function createIndex(Request $request)
    {

        $currentYear = date('Y');
        $newsurveyear = cwis_mne::latest()->selectRaw("year + 1 as newyear")->limit(1)->get();
        $page_title = "Data Framework for Monitoring and Evaluation";

        // Fetch unique indicator codes
        $indicator_list = DataSource::orderBy('indicator_code')
        ->groupBy('indicator_code')
        ->pluck('indicator_code');
        $indicator_count = count($indicator_list);

        $indicators = [];

        for ($i = 0; $i < $indicator_count; $i++) {
            // Fetch details for each indicator code
            $indicators[$i] = DataSource::select('label', 'outcome', 'indicator_code')
            ->where('indicator_code', $indicator_list[$i])
                ->orderBy('id')
                ->get();
        }

        $disabledIndicators = [];

        $year = $request->year;
      
        $cwisResult = $this->cwis($year);
        $data = cwis_mne::where('year', $year)->pluck('data_value', 'indicator_code');
      
        return view('cwis/cwis-df-mne.create', compact(
            'newsurveyear',
            'page_title',
            'indicator_count',
            'indicators',
            'cwisResult',
            'year',
            'data'
        ));
    }

    public function createStore(Request $request, cwis_mne $cwis_mne)
    { 
        $cwis_data_source = DataSource::where('category_id', '=', 7)->orderBy('id');

        if($cwis_data_source){
            foreach($cwis_data_source as $key => $cwis_mne){
                $cwis_mne = new cwis_mne;
                $cwis_mne->sub_category_id = $request->sub_category_id[$key];
                $cwis_mne->parameter_id = $request->parameter_id[$key];
                $cwis_mne->assmntmtrc_dtpnt = $request->assmntmtrc_dtpnt[$key];
                $cwis_mne->unit = $request->unit[$key];
                $cwis_mne->co_cf = $request->co_cf[$key];
                $cwis_mne->data_value = $request->data_value[$key];
                // $cwis_mne->data_type = $request->data_type[$key];
                $cwis_mne->sym_no = $request->sym_no[$key];
                $cwis_mne->year = $request->year[$key];
                $cwis_mne->source_id = $request->source_id[$key];
                $cwis_mne->save();
            }

            return redirect('cwis/cwis/cwis-df-mne')->with('success','CWIS updated successfully');
        }
        return redirect('cwis/cwis/cwis-df-mne')->with('error','Failed to update Data');
    }
    public function exportMneCsv(Request $request)
    {
        $pickyear = cwis_mne::select("year")
        ->distinct()
        ->orderby('year', 'desc')->pluck('year');
        $year = $request->year_select ?? $pickyear[0];
        ob_end_clean();
        ob_start();
        return $this->excel->download(new MneCsvExport($year), 'CWIS M&E '. $year .'.xlsx');
     }

}
