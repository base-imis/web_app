<?php
// Last Modified Date: 10-07-2024
//Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024)
namespace App\Http\Controllers\SwmPaymentInfo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\SwmPaymentInfo\SwmPaymentStatus;
use App\Models\LayerInfo\Ward;
use App\Models\SwmPaymentInfo\DueYear;
use DataTables;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\AbstractWriter;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SwmImport;
use Maatwebsite\Excel\HeadingRowImport;
use App\Models\SwmPaymentInfo\SwmPayment;


class SwmServicePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Service Payment Collection', ['only' => ['index']]);
        $this->middleware('permission:Import SWM Service Payment Collection From CSV', ['only' => ['create', 'store']]);
        $this->middleware('permission:Export SWM Service Payment Collection Info', ['only' => ['export', 'exportunmatched']]);

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = "Solid Waste Information Support System";
        $wards = Ward::getInAscOrder();
        $dueYears = DueYear::getInAscOrder();

        return view('swmpayment-info.index', compact('page_title','wards', 'dueYears'));
    }
    /**
     * Prepare data for the DataTable.
     *
     * @param Request $request
     * @return DataTables
     * @throws Exception
     */
    public function getData(Request $request)
    {
        $buildingData =DB::table(DB::raw('(SELECT DISTINCT ON (swm_customer_id) 
                        swm_customer_id, bin, tax_code, ward, building_associated_to, customer_name, customer_contact, due_year 
                    FROM swm_info.swmservice_payment_status
                    ORDER BY swm_customer_id DESC) AS swm'))
                ->leftJoin('swm_info.due_years AS due', 'due.value', '=', 'swm.due_year')
                ->select('swm.*', 'due.name')
                ->whereNull('swm.building_associated_to');


        return DataTables::of($buildingData)
            ->filter(function ($query) use ($request) {
                if ($request->dueyear_select) {
                    $query->where('due.name', $request->dueyear_select);
                }
                if ($request->ward_select) {
                    $query->where('swm.ward', $request->ward_select);
                }
            })
            ->make(true);


    }
    /**
     * Display the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $page_title = "Import Solid Waste Information Support System";
        return view('swmpayment-info.create', compact('page_title'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Validator::extend('file_extension', function ($attribute, $value, $parameters, $validator) {
                if( !in_array( $value->getClientOriginalExtension(), $parameters ) ){
                return false;
            }
            else {
                return true;
            }
        }, 'File must be csv format');
        $this->validate($request,
                ['excelfile' => 'required|file_extension:csv'],
                ['required' => 'The csv file field is required.'],
        );

        if (!$request->hasfile('excelfile')) {

            return redirect('swm-payment/data')->with('error','The csv file is required.');
        }
        if ($request->hasFile('excelfile')) {

                $filename = 'swm-service-payments.' . $request->file('excelfile')->getClientOriginalExtension();
                if (Storage::disk('importswmpayment')->exists('/' . $filename)){
                    Storage::disk('importswmpayment')->delete('/' . $filename);
                    //deletes if already exists
                }
                $stored = $request->file('excelfile')->storeAs('/', $filename, 'importswmpayment');

                if ($stored)
                {
                    $storage = Storage::disk('importswmpayment')->path('/');
                    $location = preg_replace('/\\\\/', '', $storage);

                    $file_selection = Storage::disk('importswmpayment')->listContents();
                    $filename = $file_selection[0]['basename'];

                    //checking csv file has all heading row keys
                    $headings = (new HeadingRowImport)->toArray($location.$filename);
                    $heading_row_errors = array();
                    if (!in_array("swm_customer_id", $headings[0][0])) {
                        $heading_row_errors['swm_customer_id'] = "Heading row : swm_customer_id is required";
                    }
                    if (!in_array("customer_name", $headings[0][0])) {
                        $heading_row_errors['customer_name'] = "Heading row : customer_name is required";
                    }
                    if (!in_array("customer_contact", $headings[0][0])) {
                        $heading_row_errors['customer_contact'] = "Heading row : customer_contact is required";
                    }
                    if (!in_array("last_payment_date", $headings[0][0])) {
                        $heading_row_errors['last_payment_date'] = "Heading row : last_payment_date is required";
                    }
                    if (count($heading_row_errors) > 0) {
                    return back()->withErrors($heading_row_errors);
                    }
                    \DB::statement('TRUNCATE TABLE swm_info.swmservice_payments RESTART IDENTITY');
                    #\DB::statement('ALTER SEQUENCE IF exists swm_info.swmservice_payment_id_seq RESTART WITH 1');
                    $import = new SwmImport();
                    $import->import($location.$filename);

                    $message = 'Successfully Imported SWM Service Payments From Excel.';
                    \DB::statement("select swm_info.fnc_swmpaymentstatus()");

                    \DB::statement('select swm_info.fnc_updonimprt_gridnward_swm()');

                    return redirect('swm-payment')->with('success',$message);

                }
                else{
                    $message = 'Building Tax Payments Not Imported From Excel.';
                }

        }
        flash('Could not import from excel. Try Again');
        return redirect('swm-payment');
    }
    /**
     * Export building tax payment data to a CSV file.
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {

        $ward = $_GET['ward'] ?? null;
        $due_year = $_GET['due_year'] ?? null;

        $columns = ['SWM Customer ID', 'BIN', 'Tax Code', 'Ward', 'Customer Name', 'Customer Contact', 'Due Years'];

        $query = DB::table('swm_info.swmservice_payment_status AS pmt')
                                ->leftjoin('swm_info.due_years AS due', 'due.value', '=', 'pmt.due_year')
                                ->select('pmt.*', 'due.name')
                                ->where('pmt.building_associated_to', null)
                                ->where('pmt.deleted_at', null)
                                ->distinct('pmt.swm_customer_id')
                                ->orderBy('pmt.swm_customer_id', 'ASC');


        if (!empty($ward)) {
            $query->where('pmt.ward', $ward);
        }
        if (!empty($due_year)) {
            $query->where('due.name', $due_year);
        }
        //dd($query);
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Solid Waste Information Support System.csv')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($swmpayments) use ($writer) {
            foreach($swmpayments as $swmpayment) {

                $values = [];
                $values[] = $swmpayment->swm_customer_id;
                $values[] = $swmpayment->bin;
                $values[] = $swmpayment->tax_code;
                $values[] = $swmpayment->ward;
                $values[] = $swmpayment->customer_name;
                $values[] = $swmpayment->customer_contact;
                $values[] = $swmpayment->name;
                $writer->addRow($values);
            }
        });

        $writer->close();
    }
    public function exportunmatched()
    {
        $columns = ['SWM Customer ID', 'Customer Name', 'Customer Contact', 'Last Payment date'];

        $query = DB::table('swm_info.swmservice_payments AS pmt')
                 ->leftjoin('building_info.buildings as b', 'pmt.swm_customer_id', '=', 'b.swm_customer_id')
                 ->select('pmt.*', 'b.swm_customer_id')
                 ->whereNull('b.swm_customer_id')
                 ->orderBy('pmt.swm_customer_id', 'ASC');    
       
        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('Unmatched Records.csv')
            ->addRowWithStyle($columns, $style); //Top row of excel

        $query->chunk(5000, function ($swmpayments) use ($writer) {
            foreach($swmpayments as $swmpayment) {
                $values = [];
                $values[] = $swmpayment->swm_customer_id;
                $values[] = $swmpayment->customer_name;
                $values[] = $swmpayment->customer_contact;
                $values[] = $swmpayment->last_payment_date;
                $writer->addRow($values);
            }
        });

        $writer->close();
    }
}
