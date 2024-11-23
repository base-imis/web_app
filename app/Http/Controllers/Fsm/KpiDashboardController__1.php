<?php

namespace App\Http\Controllers\Fsm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fsm\KeyPerformanceIndicator;
use Carbon\Carbon;
use App\Models\Fsm\Emptying;
use Illuminate\Support\Facades\Auth;
use App\Models\Fsm\SludgeCollection;
use App\Models\Fsm\Feedback;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\Application;
use App\Models\Fsm\KpiTarget;
use App\Services\Fsm\KpiDashboardService;
use Illuminate\Support\Facades\DB;
use PDF;
use App\Models\Fsm\Quarters;



class KpiDashboardController__1 extends Controller
{

    protected KpiDashboardService $kpiDashboardService;
    public function __construct(KpiDashboardService $kpiDashboardService)
    {
        $this->kpiDashboardService = $kpiDashboardService;
    }


    public function generateReport( $year = null, $serviceprovider = null)
    {
    
        if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Municipality - Sanitation Department')|| Auth::user()->hasRole('External'))
        {
            if($serviceprovider !== "null"){

                $whereRawEmptyingsServiceProvider = "emptyings.service_provider_id = " . $serviceprovider  ; 
                $whereRawApplicationsServiceProvider = "applications.service_provider_id = " . $serviceprovider;
                $whereRawSludgeServiceProvider = "sludge_collections.service_provider_id = " . $serviceprovider ;
                $whereRawFeedbackServiceProvider = "feedbacks.service_provider_id = " . $serviceprovider;  
            }  
            else{
                $whereRawEmptyingsServiceProvider = "1 = 1";
                $whereRawApplicationsServiceProvider = "1 = 1";
                $whereRawSludgeServiceProvider = "1 = 1";
                $whereRawFeedbackServiceProvider = "1 = 1";
            }
        }
        
        if(Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk') ){
            $whereRawEmptyingsServiceProvider = "emptyings.service_provider_id = " .Auth::user()->service_provider_id;
            $whereRawApplicationsServiceProvider = "applications.service_provider_id = " .Auth::user()->service_provider_id;
            $whereRawSludgeServiceProvider = "sludge_collections.service_provider_id = " .Auth::user()->service_provider_id;
            $whereRawFeedbackServiceProvider = "feedbacks.service_provider_id = " .Auth::user()->service_provider_id;

        }
            $keyPerformanceData = []; 
        if ($year !== "null") 
        {
            $quarters = Quarters::where('year', '=', $year)->get();
            $quarterCounts = [];

            foreach ($quarters as $quarter) {

                $applicationCount = Application::whereYear('created_at', $year)
                ->where(function ($query) use ($quarter) {
                    $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
                        ->orWhereDate('created_at', '=', $quarter->starttime)
                        ->orWhereDate('created_at', '=', $quarter->endtime);
                })
                ->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider)->count();

                $noOfEmptying = Emptying::whereYear('created_at','=', $year)->where(function ($query) use ($quarter) {
                            $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
                                ->orWhereDate('created_at', '=', $quarter->starttime)
                                ->orWhereDate('created_at', '=', $quarter->endtime);
                        })->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider)->distinct('application_id')->count('application_id');


                $noOfEmptyingReachedToTreatment = SludgeCollection::whereYear('created_at', '=', $year)->where(function ($query) use ($quarter) {
                            $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
                            ->orWhereDate('created_at', '=', $quarter->starttime)
                            ->orWhereDate('created_at', '=', $quarter->endtime);
                            }) ->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider)->whereNull('deleted_at')->count('application_id');

                $noOfFeedback = Feedback::whereYear('created_at', '=', $year)->where(function ($query) use ($quarter) {
                    $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
                        ->orWhereDate('created_at', '=', $quarter->starttime)
                        ->orWhereDate('created_at', '=', $quarter->endtime);
                    })->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->distinct('application_id')->count('application_id');
            
                $noOfPpeWear =  Feedback::where('wear_ppe', true)
                    ->where(function ($query) use ($quarter) {
                        $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
                            ->orWhereDate('created_at', '=', $quarter->starttime)
                            ->orWhereDate('created_at', '=', $quarter->endtime);
                    })->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->whereYear('created_at', '=', $year)->count();

                $noOfFsmServiceQuality = Feedback::whereYear('created_at', '=', $year)->where(function ($query) use ($quarter) {
                    $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])->orWhereDate('created_at', '=', $quarter->starttime)->orWhereDate('updated_at', '=', $quarter->endtime);
                    })->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->where('fsm_service_quality', true)->count();
            
            
                $quarterCounts[$quarter->quarterid] = [
                            'applicationCount' => $applicationCount,
                            'noOfEmptying' => $noOfEmptying,
                            'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
                            'noOfFeedback' => $noOfFeedback,
                            'noOfPpeWear' => $noOfPpeWear,
                            'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
                        ];
                } 
                $query = "SELECT t.target,q.quartername, k.indicator,q.quarterid FROM
                fsm.kpi_targets t LEFT JOIN fsm.quarters q ON t.year = q.year LEFT JOIN fsm.key_performance_indicators k ON t.indicator_id = k.id WHERE t.year = $year AND t.deleted_at IS NULL ORDER BY  t.year, k.indicator; ";

                $kpiResults = DB::select($query);
                $keyPerformanceData = []; 
                foreach ($kpiResults as $result) {
                    $name = $result->quartername;
                    $indicator = $result->indicator;
                    $target = $result->target ?? 0;

                if (isset($quarterCounts[$result->quarterid])) {
                    $commonStructure = [
                        "year" => $year,
                        "quartername" => $name,
                        "indicator_id" => $indicator,
                        "target" => $target,
                        "serviceprovider" => $serviceprovider == "null" ? '-' : $serviceprovider,
                ];

            switch ($indicator) {
                case 'Application Response Efficiency':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptying'] / $quarterCounts[$result->quarterid]['applicationCount']) * 100);
                    break;
                case 'Customer Satisfaction':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfFsmServiceQuality'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
                    break;
                case 'OHS Compliance(PPE)':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0: ceil(($quarterCounts[$result->quarterid]['noOfPpeWear'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
                    break;
                case 'Safe Desludging':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfEmptying'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptyingReachedToTreatment'] / $quarterCounts[$result->quarterid]['noOfEmptying']) * 100);
                    break;
            }
            $keyPerformanceData[] = $commonStructure;
            }
        }
        }

        else {
            $years = KpiTarget::pluck('year')->unique()->sort();
            $yearlyCounts = [];
            foreach ($years as $year) {

                $applicationCount = Application::whereYear('created_at', '=', $year)
                                
                ->whereNull('deleted_at')
                ->whereRaw($whereRawApplicationsServiceProvider)
                ->count();
        
            $noOfEmptying = Emptying::whereYear('created_at','=', $year)
                ->whereNull('deleted_at')
                ->whereRaw($whereRawEmptyingsServiceProvider)
                ->distinct('application_id')
                ->count('application_id');
    
                $noOfEmptyingReachedToTreatment = SludgeCollection::whereYear('created_at', '=', $year)->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider)->whereNull('deleted_at')->count('application_id');
                $noOfFeedback = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->distinct('application_id')->count('application_id');
                $noOfFsmServiceQuality = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider)->where('fsm_service_quality', true)->count();
                $noOfPpeWear =  Feedback::where('wear_ppe', true)->whereRaw($whereRawFeedbackServiceProvider)->whereNull('deleted_at')->whereYear('created_at', '=', $year)->count();
                
                
                $yearlyCounts[$year] = [
                    'applicationCount' => $applicationCount,
                    'noOfEmptying' => $noOfEmptying,
                    'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
                    'noOfFeedback' => $noOfFeedback,
                    'noOfPpeWear' => $noOfPpeWear,
                    'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
                ];

            }
            $kpiResults = DB::table('fsm.key_performance_indicators AS i')
                ->leftJoin('fsm.kpi_targets AS t', 'i.id', '=', 't.indicator_id')
                ->select('i.indicator', 'i.id', 't.target', 't.year');

                $kpiResults = $kpiResults->get(); 

            foreach ($kpiResults as $result) {
                $indicator = $result->indicator;
                $target = $result->target ?? 0;
                $resultYear = $result->year;


                if (isset($yearlyCounts[$resultYear])) {
                    $commonStructure = [
                        "year" => $resultYear,
                        "indicator_id" => $indicator,
                        "target" => $target,
                        "serviceprovider" => $serviceprovider == "null" ? '-' : $serviceprovider,
                    ];
                    switch ($indicator) {
                        case 'Application Response Efficiency':
                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['applicationCount'] == 0 ? 0 : ceil(($yearlyCounts[$resultYear]['noOfEmptying'] / $yearlyCounts[$resultYear]['applicationCount']) * 100);
                            break;
                        case 'Customer Satisfaction':
                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0 : ceil(($yearlyCounts[$resultYear]['noOfFsmServiceQuality'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
                            break;
                        case 'OHS Compliance(PPE)':
                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0: ceil(($yearlyCounts[$resultYear]['noOfPpeWear'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
                            break;
                        case 'Safe Desludging':
                            $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['noOfEmptying'] == 0) ? 0 : ceil(($yearlyCounts[$resultYear]['noOfEmptyingReachedToTreatment'] / $yearlyCounts[$resultYear]['noOfEmptying']) * 100);
                            break;
                        }
                    $keyPerformanceData[] = $commonStructure;
            }

            }
        }   
        return PDF::loadView('fsm.kpi-dashboard.kpiReport',compact('keyPerformanceData'))->download('KPI Report.pdf');
    }
   
    
    public function index()
        {
            
            $page_title = "Key Performance Indicators(KPIs) Dashboard";
            $company_name = ServiceProvider::where('id', Auth::user()->service_provider_id)->value('company_name');
            $serviceProviders = ServiceProvider::Operational()->orderBy('id')->pluck('company_name', 'id');
        
            $years = KpiTarget::pluck('year')->unique()->sort();
                $keyPerformanceIndicators = KeyPerformanceIndicator::all()->pluck('indicator','id');    
                $kpiTargets = [];
            
                    if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Municipality - Sanitation Department') || Auth::user()->hasRole('External'))
                    {
                        $values = $this->getData(request()->service_provider , request()->year);
                    
                        $service_provider = request()->service_provider;
                    }
                    
                    if(Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')){
                        $values = $this->getData(Auth::user()->service_provider_id, request()->year);
                    
                        $service_provider = Auth::user()->service_provider_id;
                    
                    }
                
                    $keyPerformanceData_1 = [];
                    $year = request()->input('year', ''); 

                    $results = DB::table('fsm.key_performance_indicators AS i')
                        ->leftJoin('fsm.kpi_targets AS t', function ($join) use ($year) {
                            $join->on('i.id', '=', 't.indicator_id');
                            if (!empty($year)) {
                                $join->where('t.year', '=', $year);
                            }
                        })
                        ->select('i.indicator', 't.target')
                        ->get();
                        
                        foreach ($results as $result) {
                            $target = $result->target;
                            $indicator = $result->indicator;
                        switch ($indicator){
                        
                            case 'Application Response Efficiency':
                        
                                array_push($keyPerformanceData_1,[
                                    "indicator" => $indicator,
                                    "target" => $target ? $target : 0,
                                    "value" => $values['applicationCount']==0?"0":ceil(($values['noOfEmptying']/$values['applicationCount'])*100),
                                    "icon" => '<i class="fa-solid fa-calendar-check"></i>'
                                ]);
                            
                                break;
                            case 'Safe Desludging':
                            
                                array_push($keyPerformanceData_1,[
                                    "indicator" => $indicator,
                                    "target" =>$target ? $target : 0,
                                    "value" => $values['noOfEmptying']==0?"0":ceil(($values['noOfEmptyingReachedToTreatment']/$values['noOfEmptying'])*100),
                                    "icon" => '<i class="fa-solid fa-house-circle-check"></i>'
                                ]);
                                break;
                            case 'Customer Satisfaction':
                            
                                array_push($keyPerformanceData_1,[
                                    "indicator" => $indicator,
                                    "target" => $target ? $target : 0,
                                    "value" => $values['noOfFeedback']==0?"0":(ceil($values['noOfFsmServiceQuality']/$values['noOfFeedback']) *100),
                                    "icon" => '<i class="fa-solid fa-users"></i>'
                                ]);
                                break;
                            case 'OHS Compliance(PPE)':
                            
                                array_push($keyPerformanceData_1,[
                                    "indicator" => $indicator,
                                    "target" => $target ? $target : 0,
                                    "value" => ($values['noOfFeedback'])==0?"0":ceil(($values['noOfPpeWear']/$values['noOfFeedback'])*100),
                                    "icon" => '<i class="fa-solid fa-user-shield"></i>'
                                ]);
                                break;
                        }}
                        if($year != ''){
                            $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiencyQuarter( $service_provider, $year);
                            $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludgingQuarter( $service_provider, $year);
                            $ppeComplianceCharts = $this->kpiDashboardService->getPpeComplianceQuarter( $service_provider, $year);
                            $customerSatisfactionCharts = $this->kpiDashboardService->getcustomerSatisfactionQuarter( $service_provider, $year);
                        }
                    
                        else{
                            $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiency( $service_provider);
                            $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludging( $service_provider);
                            $customerSatisfactionCharts = $this->kpiDashboardService->getCustomerSatisfaction( $service_provider);
                            $ppeComplianceCharts = $this->kpiDashboardService->getPpeCompliance( $service_provider);
                        }
            return view('fsm/kpi-dashboard.index', compact('page_title', 'years', 'year', 'keyPerformanceData_1','serviceProviders',  'company_name', 
            'applicationResponseEfficiencyCharts', 
            'safeDesludgingCharts',
                            'customerSatisfactionCharts',  'ppeComplianceCharts'
                    ));
            
        }

    public function getData( $service_provider_id , $year)
        {
            if($service_provider_id){

                $whereRawEmptyingsServiceProvider_1 = "emptyings.service_provider_id = " . $service_provider_id  ; 
                $whereRawApplicationsServiceProvider_1 = "applications.service_provider_id = " . $service_provider_id;
                $whereRawFeedbackServiceProvider_1 = "feedbacks.service_provider_id = " . $service_provider_id;
                $whereRawSludgeCollectionServiceProvider_1 = "sludge_collections.service_provider_id = " . $service_provider_id ;
                $whereRawSludgeServiceProvider_1 = "sludge_collections.service_provider_id = " . $service_provider_id ;
            }  
            else{
                $whereRawEmptyingsServiceProvider_1 = "1 = 1";
                $whereRawApplicationsServiceProvider_1 = "1 = 1";
                $whereRawSludgeCollectionServiceProvider_1 = "1 = 1";
                $whereRawFeedbackServiceProvider_1 = "1 = 1";
                $whereRawSludgeServiceProvider_1 = "1 = 1";
            }

            if($year)
                {
                    $applicationCount_1 = Application::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider_1)->count();
                    $noOfEmptying_1 = Emptying::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider_1)->distinct('application_id')->count('application_id');
                    $noOfEmptyingReachedToTreatment_1 = SludgeCollection::whereYear( 'created_at', '=' , $year )->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider_1)->whereNull('deleted_at')->count('application_id');
                    $noOfFeedback_1 = Feedback::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->distinct('application_id')->count('application_id');
                    $noOfPpeWear_1 = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at') ->whereRaw($whereRawFeedbackServiceProvider_1)->where('wear_ppe', '=', 1)->count();
                    $noOfFsmServiceQuality_1 = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at') ->whereRaw($whereRawFeedbackServiceProvider_1)->where('fsm_service_quality', '=', 1)->count();
                }
                else
                {
                    $year = Carbon::now()->year;
                    $applicationCount_1 = Application::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider_1)->count();
                    $noOfEmptying_1 = Emptying::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider_1)->distinct('application_id')->count('application_id');
                    $noOfEmptyingReachedToTreatment_1 = SludgeCollection::whereYear( 'created_at', '=' , $year )->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider_1)->whereNull('deleted_at')->count('application_id');
                    $noOfFeedback_1 = Feedback::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->distinct('application_id')->count('application_id');
                    $noOfPpeWear_1 = Feedback::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at') ->whereRaw($whereRawFeedbackServiceProvider_1)->where('wear_ppe', '=', 1)->count();
                    $noOfFsmServiceQuality_1 = Feedback::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->where('fsm_service_quality', '=', 1)->count();
                }

                $data =[
                        'applicationCount' => $applicationCount_1 ,
                        'noOfEmptying' => $noOfEmptying_1 ,
                        'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment_1,
                        'noOfFeedback' => $noOfFeedback_1,
                        'noOfPpeWear' => $noOfPpeWear_1,
                        'noOfFsmServiceQuality' => $noOfFsmServiceQuality_1 
                ];
                return $data;
        }

    public function store()
        {
            $year = Carbon::now()->year;
            $serviceProviders = ServiceProvider::Operational()->orderBy('id')->pluck('id');
            
            $valuesArray = [];
        
            foreach ($serviceProviders as $service_provider_id) {
                $values = $this->getData($service_provider_id, $year);
                $valuesArray[$service_provider_id] = $values;
            }
            
                $keyPerformanceData_1 = []; // Initialize the $keyPerformanceData_1 array
            
                foreach ($valuesArray as $service_provider_id => $values) {
                    $query = "SELECT t.target, i.id
                            FROM fsm.kpi_targets AS t
                            JOIN fsm.key_performance_indicators AS i ON t.indicator_id = i.id
                            WHERE t.year = $year";
            
                    $results = DB::select($query);
                
                    foreach ($results as $result) {
                    
                        $target = $result->target;
                        $indicator = $result->id;
            
                        switch ($indicator) {
                            case 1:
                                array_push($keyPerformanceData_1, [
                                    "year" => $year,
                                    "indicator_id" => $indicator,
                                    "target" => $target ? $target : 0,
                                    "achievement" => $values['applicationCount'] == 0 ? "0" : ceil(($values['noOfEmptying'] / $values['applicationCount']) * 100),
                                    "service_provider_id" => $service_provider_id
                                ]);
                                break;
                            case 2:
                                array_push($keyPerformanceData_1, [
                                    "year" => $year,
                                    "indicator_id" => $indicator,
                                    "target" =>$target ? $target : 0,
                                    "achievement" => $values['noOfEmptying'] == 0 ? "0" : ceil(($values['noOfEmptyingReachedToTreatment'] / $values['noOfEmptying']) * 100),
                                    "service_provider_id" => $service_provider_id
                                ]);
                                break;
                            case 3:
                                array_push($keyPerformanceData_1, [
                                    "year" => $year,
                                    "indicator_id" => $indicator,
                                    "target" =>$target ? $target : 0,
                                    "achievement" => $values['noOfFeedback'] == 0 ? "0" : (ceil($values['noOfFsmServiceQuality'] / $values['noOfFeedback']) * 100),
                                    "service_provider_id" => $service_provider_id
                                ]);
                                break;
                            case 4:
                                array_push($keyPerformanceData_1, [
                                    "year" => $year,
                                    "indicator_id" => $indicator,
                                    "target" => $target ? $target : 0,
                                    "achievement" => ($values['noOfFeedback']) == 0 ? "0" : ceil(($values['noOfPpeWear'] / $values['noOfFeedback']) * 100),
                                    "service_provider_id" => $service_provider_id
                                ]);
                                break;
                        }
                    }
                }
                foreach ($keyPerformanceData_1 as $data) {
                    $currentYear = Carbon::now()->year;
                
                    $kpi = KpiAchievement::where('year', $currentYear)->where('indicator_id', $data['indicator_id'])
                    ->where('service_provider_id', $data['service_provider_id'])
                    ->first();
                    if ($kpi) {
                        $kpi->target = $data['target'];
                        $kpi->achievement = $data['achievement'];
                        $kpi->save();
                    } else {
                        $kpi = new KpiAchievement();
                        $kpi->year = $data['year'];
                        $kpi->indicator_id = $data['indicator_id'];
                        $kpi->target = $data['target'];
                        $kpi->achievement = $data['achievement'];
                        $kpi->service_provider_id = $data['service_provider_id'];
                        $kpi->save();
                    }
                }
        }
}