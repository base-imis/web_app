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
use Illuminate\Support\Collection;




class KpiDashboardController_copy extends Controller
{

    protected KpiDashboardService $kpiDashboardService;
    public function __construct(KpiDashboardService $kpiDashboardService)
    {
        $this->kpiDashboardService = $kpiDashboardService;
    }

    public static function fscr($year)
    {
        // Calculate population per household ratio
        $populationPerHouseholdRatio = DB::table('building_info.buildings')
            ->selectRaw('SUM(population_served) / SUM(household_served) as population_per_household_ratio')
            ->value('population_per_household_ratio');

        // Count buildings with sanitation system technology excluded
        $countSanitationSystemIncluded = DB::table('building_info.buildings')
        ->whereRaw("construction_year <= $year")
        ->whereIn('sanitation_system_technology_id', [2, 3, 4])
        ->count();
    
        $countSanitationSystem_ptIncluded = DB::table('building_info.buildings')
        ->whereRaw("construction_year <= $year")
            ->whereIn('sanitation_system_technology_id', [3, 9])
            ->count();
      
        // Calculate safety tank usage
        $sum = $countSanitationSystemIncluded * $populationPerHouseholdRatio * 0.26  + $countSanitationSystem_ptIncluded * $populationPerHouseholdRatio * 0.26;
       
        return $sum;
    }


    public function data($select_year= null, $service_provider_id= null)
    {
    
        
        $years = KpiTarget::pluck('year')->unique()->sort()->toArray(); 
       
        $keyPerformanceData = [];
        $cards_kpi =[];
        if(Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
            {
                
                $service_provider_id = Auth::user()->service_provider_id;
                    
            }
        if(!empty($service_provider_id))
        {
            $whereServiceProviderID = "a.service_provider_id = " . $service_provider_id;
        }
        else
        {
            $whereServiceProviderID = "1 = 1";

        }
 
        $base_query = DB::table('fsm.applications as a')
            ->leftJoin('fsm.emptyings as e', 'a.id', '=', 'e.application_id')
            ->leftJoin('fsm.sludge_collections as s', 'a.id', '=', 's.application_id')
            ->leftJoin('fsm.feedbacks as f', 'a.id', '=', 'f.application_id')
            ->leftJoin('fsm.service_providers as sp', 'a.service_provider_id', '=', 'sp.id')
            ->select(
                'a.application_date',
                'e.emptied_date',
                'sp.id as service_provider_id',
                'a.id as application_id',
                'e.id as emptying_id',
                's.id as sludge_collection_id',
                'f.wear_ppe',
                'f.fsm_service_quality as quality')
            
            ->whereRaw($whereServiceProviderID)
            ->whereNull('a.deleted_at');

        
        if($select_year !== null && $select_year !== "null")
        {
           
            //For Cards
            $query2 = clone $base_query;
            $query1 = clone $base_query;
            $query3 = clone $base_query;

            $applicationCount = $query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('a.id');
            $noOfEmptying= $query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('e.application_id');
            $noOfEmptyingReachedToTreatment=$query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('s.application_id');
            $noOfFeedback=$query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->count('f.application_id');
            $noOfPpeWear=$query2->where(DB::raw('extract(year from a.application_date)'), $select_year)->where('f.wear_ppe',true)->count('f.application_id');
            $noOfFsmServiceQuality=$query1->where(DB::raw('extract(year from a.application_date)'), $select_year)->where('f.fsm_service_quality',true)->count('f.application_id'); 

            $response = DB::select(DB::raw(" SELECT  EXTRACT(YEAR FROM a.application_date) AS year, AVG(AGE(e.emptied_date, a.application_date)) AS time FROM fsm.applications AS a JOIN fsm.emptyings AS e ON a.id = e.application_id WHERE      EXTRACT     (YEAR FROM a.application_date) = $select_year and $whereServiceProviderID
            GROUP BY year "));
            
            $inclusion = DB::select(DB::raw("SELECT
                        SUM(application_count) AS total_application_count
                    FROM
                        (SELECT
                            COUNT(emptyings.application_id) AS application_count
                        FROM
                            building_info.buildings AS buildings
                        JOIN
                            layer_info.low_income_communities AS communities
                        ON
                            ST_Within(buildings.geom, communities.geom)
                        LEFT JOIN
                            fsm.emptyings AS emptyings
                        ON
                            emptyings.application_id IN (
                                SELECT applications.id
                                FROM fsm.applications AS applications
                                WHERE buildings.house_number = applications.house_number and EXTRACT(year FROM applications.application_date) = $select_year
                            )
                        GROUP BY
                            buildings.geom) AS subquery;
                    "));
        
                $inclusionValue = $inclusion[0]->total_application_count;

                $fscr = $this->fscr($select_year);
                $sludgeCount = DB::select(DB::raw("
                            SELECT
                                SUM(volume_of_sludge) AS sCount
                            FROM
                                fsm.emptyings
                            LEFT JOIN
                                fsm.applications ON applications.id = emptyings.application_id
                            WHERE
                                EXTRACT(YEAR FROM applications.application_date) = $select_year
                        "));


                $kpiResults = DB::table('fsm.key_performance_indicators AS i')->leftJoin('fsm.kpi_targets AS t', 'i.id', '=', 't.indicator_id')->select('i.indicator', 'i.id', 't.target', 't.year')->where('t.year', '=', $select_year)->whereNull('t.deleted_at')->orderBy('i.indicator')->get();
        
                foreach ($kpiResults as $result) {
                    $indicator = $result->indicator;
                    $target = $result->target ?? 0;
                
                    $commonStructure = [
                        "indicator" => $indicator,
                        "target" => $target,
                    ];
            
                switch ($indicator) {
                case 'Application Response Efficiency':
                    $commonStructure["icon"] = '<i class="fa-solid fa-calendar-check"></i>';
                    $commonStructure["achievement"] = $applicationCount == 0 ? 0 : ceil(( $noOfEmptying / $applicationCount) * 100);
                    break;
                case 'Customer Satisfaction':
                    $commonStructure["icon"] = '<i class="fa-solid fa-users"></i>';
                    $commonStructure["achievement"] = $noOfFeedback == 0 ? 0 : ceil(( $noOfFsmServiceQuality/ $noOfFeedback) * 100);
                    break;
                case 'PPE Compliance':
                   $commonStructure["icon"] = '<i class="fa-solid fa-user-shield"></i>';
                    $commonStructure["achievement"] = $noOfFeedback == 0 ? 0: ceil(($noOfPpeWear / $noOfFeedback) * 100);
                    break;
                case 'Safe Desludging':
                   $commonStructure["icon"] ='<i class="fa-solid fa-house-circle-check"></i>'; 
                    $commonStructure["achievement"] = ( $noOfEmptying == 0) ? 0 : ceil(($noOfEmptyingReachedToTreatment /  $noOfEmptying) * 100);
                    break;
                case 'Inclusion':
                    $commonStructure["icon"] = '<i class="fa-solid fa-landmark"></i>'; 
                    $commonStructure["achievement"] = ($applicationCount == 0) ? 0 : ceil(($inclusionValue / $applicationCount) * 100);
                    break;
                case 'Faecal Sludge Collection Ratio (FSCR)':
                    $commonStructure["icon"] = '<i class="fa-solid fa-truck"></i>'; 
                    if (!empty($sludgeCount)) {
                        $commonStructure["achievement"] = ceil((($sludgeCount[0]->scount)/ $fscr)* 100);
                    } else {
                        $commonStructure["achievement"] = 0;
                    }
                    break;    
                case 'Response Time':
                    $commonStructure["icon"] = '<i class="fa-solid fa-clock"></i>'; 
                    if (!empty($response)) {
            
                        $time = Carbon::parse($response[0]->time);
                        $total_hours = $time->diffInHours();
                        
                        $commonStructure["achievement"] = $total_hours ? $total_hours : 0;
                    } else {
                        $commonStructure["achievement"] = 0;
                    }
                            break;    
                    }    
                    
                $cards_kpi[] = $commonStructure;
                }
         
                //For Quarters
                
                $quarterCounts = []; 
                $quarters = Quarters::where('year', '=', $select_year)->get();
                foreach($quarters as $quarter)
                {
        
                
                $base_query2 = DB::table('fsm.applications as a')
                    ->leftJoin('fsm.emptyings as e', 'a.id', '=', 'e.application_id')
                    ->leftJoin('fsm.sludge_collections as s', 'a.id', '=', 's.application_id')
                    ->leftJoin('fsm.feedbacks as f', 'a.id', '=', 'f.application_id')
                    ->select(
                        'a.application_date',
                        'e.emptied_date',
                        'a.id as application_id',
                        'e.id as emptying_id',
                        's.id as sludge_collection_id',
                        'f.wear_ppe',
                        'f.fsm_service_quality as quality',
                      
                    )
                    ->whereRaw($whereServiceProviderID)
                    ->whereYear('application_date', '=', $select_year)
                    ->whereBetween('application_date', [$quarter->starttime, $quarter->endtime])
                    ->orWhereDate('application_date', '=', $quarter->starttime)
                    ->orWhereDate('application_date', '=', $quarter->endtime)
                    ->whereNull('a.deleted_at');
                
    
   
                $base_query1 = DB::table('fsm.applications as a')
                ->leftJoin('fsm.emptyings as e', 'a.id', '=', 'e.application_id')
                ->leftJoin('fsm.sludge_collections as s', 'a.id', '=', 's.application_id')
                ->leftJoin('fsm.feedbacks as f', 'a.id', '=', 'f.application_id')
                ->select('a.application_date', 'e.emptied_date', 'a.id as application_id', 'e.id as emptying_id', 's.id as sludge_collection_id', 'f.wear_ppe', 'f.fsm_service_quality as quality')->whereRaw($whereServiceProviderID)->whereYear('application_date','=', $select_year)->whereBetween('application_date', [$quarter->starttime, $quarter->endtime])
                        ->orWhereDate('application_date', '=', $quarter->starttime)
                        ->orWhereDate('application_date', '=', $quarter->endtime)
                ->whereNull('a.deleted_at');
    

                $time = DB::select(DB::raw(" 
                        SELECT AVG(AGE(e.emptied_date, a.application_date)) AS time
                        FROM fsm.applications AS a
                        JOIN fsm.emptyings AS e ON a.id = e.application_id
                        WHERE EXTRACT(YEAR FROM a.application_date) = $select_year
                        AND (
                            (a.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                            OR
                            (DATE(a.application_date) = '$quarter->starttime')
                            OR
                            (DATE(a.application_date) = '$quarter->endtime')
                        )
                        AND $whereServiceProviderID
                    "));

   
                $response = $time[0]->time;

                $inclusion = DB::select(DB::raw("SELECT
                        SUM(application_count) AS total_application_count
                    FROM
                        (SELECT
                            COUNT(emptyings.application_id) AS application_count
                        FROM
                            building_info.buildings AS buildings
                        JOIN
                            layer_info.low_income_communities AS communities
                        ON
                            ST_Within(buildings.geom, communities.geom)
                        LEFT JOIN
                            fsm.emptyings AS emptyings
                        ON
                            emptyings.application_id IN (
                                SELECT applications.id
                                FROM fsm.applications AS applications
                                WHERE buildings.house_number = applications.house_number and EXTRACT(year FROM applications.application_date) = $select_year   AND (
                            (applications.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                            OR
                            (DATE(applications.application_date) = '$quarter->starttime')
                            OR
                            (DATE(applications.application_date) = '$quarter->endtime')
                        )
                            )
                        GROUP BY
                            buildings.geom) AS subquery;
                    "));

                $inclusionValue = $inclusion[0]->total_application_count;


                /* ---------------------------------------------------  For FSCR ---------------------------------------------------------------------------------------------- */
                //  // Calculate population per household ratio
                //     $populationPerHouseholdRatio = DB::table('building_info.buildings')
                //     ->selectRaw('SUM(population_served) / SUM(household_served) as population_per_household_ratio')
                //     ->value('population_per_household_ratio');

                // // Count buildings with sanitation system technology excluded
                // $countSanitationSystemIncluded = DB::table('building_info.buildings')
                // ->whereRaw("construction_year <= $select_year")
                
                // ->whereIn('sanitation_system_technology_id', [2, 3, 4])
                // ->count();

                // $countSanitationSystem_ptIncluded = DB::table('building_info.buildings')
                // ->whereRaw("construction_year <= $select_year")
                //     ->whereIn('sanitation_system_technology_id', [3, 9])
                //     ->count();
            
                // // Calculate safety tank usage
                // $sum = $countSanitationSystemIncluded * $populationPerHouseholdRatio * 0.26  + $countSanitationSystem_ptIncluded * $populationPerHouseholdRatio * 0.26;
                // $fscr = $this->fscr($select_year);

         
                // $sludgeCount = DB::select(DB::raw("
                //             SELECT
                //                 SUM(volume_of_sludge) AS sCount
                //             FROM
                //                 fsm.emptyings
                //             LEFT JOIN
                //                 fsm.applications ON applications.id = emptyings.application_id
                //             WHERE
                //                 EXTRACT(YEAR FROM applications.application_date) = $select_year AND (
                //                     (applications.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                //                     OR
                //                     (DATE(applications.application_date) = '$quarter->starttime')
                //                     OR
                //                     (DATE(applications.application_date) = '$quarter->endtime')
                //                 )
                //         "));
                //     $sludgeCount = $sludgeCount[0]['scount'];

                /* ------------------------------------------------------------------------------------------------------------------------------------------------------------- */
                $inclusion = DB::select(DB::raw("SELECT
                SUM(application_count) AS total_application_count
                FROM
                (SELECT
                    COUNT(emptyings.application_id) AS application_count
                FROM
                    building_info.buildings AS buildings
                JOIN
                    layer_info.low_income_communities AS communities
                ON
                    ST_Within(buildings.geom, communities.geom)
                LEFT JOIN
                    fsm.emptyings AS emptyings
                ON
                    emptyings.application_id IN (
                        SELECT applications.id
                        FROM fsm.applications AS applications
                        WHERE buildings.house_number = applications.house_number and EXTRACT(year FROM applications.application_date) = $select_year AND (
                            (applications.application_date BETWEEN '$quarter->starttime' AND '$quarter->endtime')
                            OR
                            (DATE(applications.application_date) = '$quarter->starttime')
                            OR
                            (DATE(applications.application_date) = '$quarter->endtime')
                        )
                    ) 
                GROUP BY
                    buildings.geom) AS subquery;
                "));
                $inclusion= $inclusion[0]->total_application_count;

                $applicationCount = $base_query2->count('a.id');
                $noOfEmptying= $base_query2->count('e.application_id');
                $noOfEmptyingReachedToTreatment= $base_query2->count('s.application_id');
                $noOfFeedback= $base_query2->count('f.application_id');
                $noOfPpeWear= $base_query2->where('f.wear_ppe',true)
                                    ->count('f.application_id');
                $noOfFsmServiceQuality=$base_query1->where('f.fsm_service_quality',true)
                                    ->count('f.application_id'); 
                       
                $quarterCounts[$quarter->quarterid] = [
                    'response' => $response,
                    'applicationCount' => $applicationCount,
                    'noOfEmptying' => $noOfEmptying,
                    'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
                    'noOfFeedback' => $noOfFeedback,
                    'noOfPpeWear' => $noOfPpeWear,
                    'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
                    'inclusion' => $inclusion
                ];
                
            }

         
                $query = "SELECT t.target,q.quartername, k.indicator,q.quarterid FROM
                fsm.kpi_targets t LEFT JOIN fsm.quarters q ON t.year = q.year LEFT JOIN fsm.key_performance_indicators k ON t.indicator_id = k.id WHERE t.year = $select_year AND t.deleted_at IS NULL ORDER BY  t.year, k.indicator; ";
        

                $kpiResults = DB::select($query);
            
                $keyPerformanceData = [];
                foreach ($kpiResults as $result) {
                    $name = $result->quartername;
                    $indicator = $result->indicator;
                    $target = $result->target ?? 0;
        
                
                    $commonStructure = [
                        "year" => $select_year,
                        "quartername" => $name,
                        "indicator" => $indicator,
                        "target" => $target,
                        "serviceprovider" => $service_provider_id,
                ];
    
                switch ($indicator) {
                case 'Application Response Efficiency':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptying'] / $quarterCounts[$result->quarterid]['applicationCount']) * 100);
                    break;
                case 'Customer Satisfaction':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfFsmServiceQuality'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
                    break;
                case 'PPE Compliance':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0: ceil(($quarterCounts[$result->quarterid]['noOfPpeWear'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
                    break;
                case 'Safe Desludging':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfEmptying'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptyingReachedToTreatment'] / $quarterCounts[$result->quarterid]['noOfEmptying']) * 100);
                    break;
                case 'Safe Desludging':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['inclusion'] / $quarterCounts[$result->quarterid]['applicationCount']) * 100);
                    break;
                case 'Inclusion':
                    $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($inclusion /$quarterCounts[$result->quarterid]['applicationCount']) * 100);
                    break;
                case 'Faecal Sludge Collection Ratio (FSCR)':
                    $commonStructure["achievement"] = [];
                    break;    

                case 'Response Time':
             
                    $time = Carbon::parse($quarterCounts[$result->quarterid]['response']);
                    $total_hours = $time->diffInHours();
                    
                    $commonStructure["achievement"] = $total_hours?$total_hours :0;
                    
                    break;    
                    
            }
            $keyPerformanceData[] = $commonStructure;
       
            }
           
 
        }
        else
        {
            
            $yearlyCounts = []; 
            $kpiResultsByYear = [];
                        
            foreach($years as $year)
            {
                $base_query2 = clone $base_query;
                $base_query1 = clone $base_query;
                $applicationCount = $base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('a.id');
                $noOfEmptying= $base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('e.application_id');
                $noOfEmptyingReachedToTreatment=$base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('s.application_id');
                $noOfFeedback=$base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->count('f.application_id');
                $noOfPpeWear=$base_query2->where(DB::raw('extract(year from a.application_date)'), $year)->where('f.wear_ppe',true)->count('f.application_id');
                $noOfFsmServiceQuality=$base_query1->where(DB::raw('extract(year from a.application_date)'), $year)->where('f.fsm_service_quality',true)->count('f.application_id'); 
                $response = DB::select(DB::raw(" SELECT  EXTRACT(YEAR FROM a.application_date) AS year, AVG(AGE(e.emptied_date, a.application_date)) AS time FROM fsm.applications AS a JOIN fsm.emptyings AS e ON a.id = e.application_id WHERE      EXTRACT     (YEAR FROM a.application_date) = $year and $whereServiceProviderID
                        GROUP BY year "));
               
                $inclusion = DB::select(DB::raw("SELECT SUM(application_count) AS total_application_count
                    FROM (SELECT COUNT(emptyings.application_id) AS application_count FROM building_info.buildings AS buildings JOIN layer_info.low_income_communities AS communities
                    ON ST_Within(buildings.geom, communities.geom) LEFT JOIN
                    fsm.emptyings AS emptyings  ON emptyings.application_id IN ( SELECT applications.id FROM fsm.applications AS applications
                        WHERE buildings.house_number = applications.house_number and EXTRACT(year FROM applications.application_date) = $year
                    ) GROUP BY buildings.geom) AS subquery; "));

                $inclusionValue = $inclusion[0]->total_application_count;
                $fscr = $this->fscr($year);
                
                $sludgeCount = DB::select(DB::raw(" SELECT SUM(volume_of_sludge) AS sCount  FROM fsm.emptyings LEFT JOIN fsm.applications ON applications.id = emptyings.application_id
                    WHERE EXTRACT(YEAR FROM applications.application_date) = $year"));
                $sludgeCount = $sludgeCount[0]->scount;

                $yearlyCounts[$year] = [
                    'applicationCount' => $applicationCount,
                    'noOfEmptying' => $noOfEmptying,
                    'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
                    'noOfFeedback' => $noOfFeedback,
                    'noOfPpeWear' => $noOfPpeWear,
                    'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
                    'inclusionValue' => $inclusionValue,
                    'fscr'=> $fscr,
                    'response'=>$response,
                    'sludgeCount' =>$sludgeCount
                ];
        
            }
          
            $kpiResults = DB::table('fsm.key_performance_indicators AS i')
                ->leftJoin('fsm.kpi_targets AS t', 'i.id', '=', 't.indicator_id')
                ->select('i.indicator', 'i.id', 't.target', 't.year')->get(); 

            foreach ($kpiResults as $result) {
                $indicator = $result->indicator;
                $target = $result->target ?? 0;
                $resultYear = $result->year;


                if (isset($yearlyCounts[$resultYear])) {
                    $commonStructure = [
                        "year" => $resultYear,
                        "indicator" => $indicator,
                        "target" => $target,
                        "serviceprovider" => $service_provider_id,
                    ];
                    
                    switch ($indicator) {
                        case 'Application Response Efficiency':
                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['applicationCount'] == 0 ? 0 : ceil(($yearlyCounts[$resultYear]['noOfEmptying'] / $yearlyCounts[$resultYear]['applicationCount']) * 100);
                            break;
                        case 'Customer Satisfaction':
                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0 : ceil(($yearlyCounts[$resultYear]['noOfFsmServiceQuality'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
                            break;
                        case 'PPE Compliance':
                            $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0: ceil(($yearlyCounts[$resultYear]['noOfPpeWear'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
                            break;
                        case 'Safe Desludging':
                            $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['noOfEmptying'] == 0) ? 0 : ceil(($yearlyCounts[$resultYear]['noOfEmptyingReachedToTreatment'] / $yearlyCounts[$resultYear]['noOfEmptying']) * 100);
                            break;
                        case 'Inclusion':
                            $commonStructure["icon"] = '<i class="fa-solid fa-house-circle-check"></i>'; 
                            $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['applicationCount'] == 0) ? 0 : ceil(($yearlyCounts[$resultYear]['inclusionValue'] / $yearlyCounts[$resultYear]['applicationCount']) * 100);
                            break;
                        case 'Faecal Sludge Collection Ratio (FSCR)':
                            $commonStructure["icon"] = '<i class="fa-solid fa-house-circle-check"></i>'; 
                            if (!empty($sludgeCount)) {
                                $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['fscr'] == 0)? 0 : ceil(($yearlyCounts[$resultYear]['sludgeCount']/ $yearlyCounts[$resultYear]['fscr'])* 100);
                            } else {
                                $commonStructure["achievement"] = 0;
                            }
                                break;    
                        case 'Response Time':
                            $commonStructure["icon"] = '<i class="fa-solid fa-house-circle-check"></i>'; 

                            if (!empty($yearlyCounts[$resultYear]['response'])) {
                                // Extract the response from the array
                                $response = $yearlyCounts[$resultYear]['response'];
                                $firstResponse = $response[0];
                                
                                $time = Carbon::parse($firstResponse->time);
                                
                                $total_hours = $time->diffInHours();
                                $commonStructure["achievement"] = $total_hours;
                            } else {
                                $commonStructure["achievement"] = 0;
                            }
                            
                                break;    
                        }   
                    $keyPerformanceData[] = $commonStructure;
            }

            }
       
        }
        

        return [ $keyPerformanceData,  $cards_kpi];

    }


    public function index($select_year= null, $service_provider_id= null)
    {
        $page_title = "Key Performance Indicators (KPIs) Dashboard";
        $years = KpiTarget::pluck('year')->unique()->sort();
        $year = request()->input('year', '');
        
        $company_name = ServiceProvider::where('id', Auth::user()->service_provider_id)->value('company_name');
        $serviceProviders = ServiceProvider::Operational()->orderBy('id')->pluck('company_name', 'id');
        $keyPerformanceData = $this->data(request()->year, request()->service_provider );
   
        //For charts
        if($year != ''){
            $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiencyQuarter( $keyPerformanceData);
            $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludgingQuarter( $keyPerformanceData);
            $ppeComplianceCharts = $this->kpiDashboardService->getPpeComplianceQuarter( $keyPerformanceData);
            $customerSatisfactionCharts = $this->kpiDashboardService->getcustomerSatisfactionQuarter( $keyPerformanceData);
            $fscrCharts = '';
            $responseTimeCharts = $this->kpiDashboardService->getResponseTimeQuarter( $keyPerformanceData);
            $inclusionCharts = $this->kpiDashboardService->getInclusionQuarter( $keyPerformanceData);
        }

        else{
            $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiency( $keyPerformanceData);
            $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludging( $keyPerformanceData);
            $customerSatisfactionCharts = $this->kpiDashboardService->getCustomerSatisfaction( $keyPerformanceData);
            $ppeComplianceCharts = $this->kpiDashboardService->getPpeCompliance( $keyPerformanceData);
            $fscrCharts = $this->kpiDashboardService->getFscr( $keyPerformanceData);
            $responseTimeCharts = $this->kpiDashboardService->getResponseTime( $keyPerformanceData);
           

            $inclusionCharts = $this->kpiDashboardService->getInclusion( $keyPerformanceData);

        }
        return view('fsm/kpi-dashboard.index', compact('page_title','keyPerformanceData','years', 'year','serviceProviders', 'company_name',
            'applicationResponseEfficiencyCharts',
        'safeDesludgingCharts','customerSatisfactionCharts', 'ppeComplianceCharts', 'fscrCharts', 'responseTimeCharts', 'inclusionCharts' ));

    }

    public function generateReport($select_year, $service_provider_id)
    {
        if($select_year == "null"){
            $select_year = null;
        }
        if($service_provider_id == "null"){
            $service_provider_id = null;
        }
      
       $keyPerformanceData= $this->data($select_year , $service_provider_id );
      
       $keyPerformanceData = $keyPerformanceData[0];

       $distinctYears = [];
       foreach ($keyPerformanceData as $data) {
            if (isset($data['year'])) {
                $distinctYears[$data['year']] = true;
            }
        }
        $distinctYears = array_keys($distinctYears);

        $distinctKpi = [];
        foreach ($keyPerformanceData as $data) {
            if (isset($data['indicator'])) {
                $distinctKpi[$data['indicator']] = true;
            }
        }
        $distinctKpi = array_keys($distinctKpi);
      
        return PDF::loadView('fsm.kpi-dashboard.kpiReport',compact('keyPerformanceData', 'distinctYears','distinctKpi'))->download('KPI Report.pdf');
    }
   
    
    // public function __index()
    // {
            
    //         $page_title = "Key Performance Indicators(KPIs) Dashboard";
    //         $company_name = ServiceProvider::where('id', Auth::user()->service_provider_id)->value('company_name');
    //         $serviceProviders = ServiceProvider::Operational()->orderBy('id')->pluck('company_name', 'id');
    //         $years = KpiTarget::pluck('year')->unique()->sort();
    //         $keyPerformanceIndicators = KeyPerformanceIndicator::all()->pluck('indicator','id');    
    //         $kpiTargets = [];
            
    //                 if (Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Municipality - Sanitation Department') || Auth::user()->hasRole('External'))
    //                 {
    //                     $keyPerformanceData = $this->getData(request()->service_provider , request()->year);
                    
    //                     $service_provider = request()->service_provider;
    //                 }
                    
    //                 if(Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
    //                 {
    //                     $keyPerformanceData = $this->getData(Auth::user()->service_provider_id, request()->year);
                    
    //                     $service_provider = Auth::user()->service_provider_id;
                    
    //                 }
    //             $year = request()->input('year', ''); 


    //                     if($year != ''){
    //                         $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiencyQuarter( $keyPerformanceData);
    //                         $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludgingQuarter( $keyPerformanceData);
    //                         $ppeComplianceCharts = $this->kpiDashboardService->getPpeComplianceQuarter( $keyPerformanceData);
    //                         $customerSatisfactionCharts = $this->kpiDashboardService->getcustomerSatisfactionQuarter( $keyPerformanceData);
    //                     }
                    
    //                     else{
    //                         $applicationResponseEfficiencyCharts = $this->kpiDashboardService->getApplicationResponseEfficiency( $service_provider);
    //                         $safeDesludgingCharts = $this->kpiDashboardService->getSafeDesludging( $service_provider);
    //                         $customerSatisfactionCharts = $this->kpiDashboardService->getCustomerSatisfaction( $service_provider);
    //                         $ppeComplianceCharts = $this->kpiDashboardService->getPpeCompliance( $service_provider);
    //                     }
    //         return view('fsm/kpi-dashboard.index', compact('page_title', 'years','serviceProviders', 'year',  'company_name', 
    //         'applicationResponseEfficiencyCharts', 'keyPerformanceData',
    //         'safeDesludgingCharts',
    //                         'customerSatisfactionCharts',  'ppeComplianceCharts'
    //                 ));
            
    // }

    // public function getData( $service_provider_id , $year)
    // {
    //         if($service_provider_id)
    //         {
    //             $whereRawEmptyingsServiceProvider_1 = "emptyings.service_provider_id = " . $service_provider_id  ; 
    //             $whereRawApplicationsServiceProvider_1 = "applications.service_provider_id = " . $service_provider_id;
    //             $whereRawFeedbackServiceProvider_1 = "feedbacks.service_provider_id = " . $service_provider_id;
    //             $whereRawSludgeCollectionServiceProvider_1 = "sludge_collections.service_provider_id = " . $service_provider_id ;
    //             $whereRawSludgeServiceProvider_1 = "sludge_collections.service_provider_id = " . $service_provider_id ;
    //         }  
    //         else
    //         {
    //             $whereRawEmptyingsServiceProvider_1 = "1 = 1";
    //             $whereRawApplicationsServiceProvider_1 = "1 = 1";
    //             $whereRawSludgeCollectionServiceProvider_1 = "1 = 1";
    //             $whereRawFeedbackServiceProvider_1 = "1 = 1";
    //             $whereRawSludgeServiceProvider_1 = "1 = 1";
    //         }

    //         $keyPerformanceData = [];
    //         if($year)
    //             {
                    
    //                 $applicationCount_1 = Application::whereYear( 'app', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider_1)->count();
    //                 $noOfEmptying_1 = Emptying::whereYear( 'app', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider_1)->distinct('application_id')->count('application_id');
    //                 $noOfEmptyingReachedToTreatment_1 = SludgeCollection::whereYear( 'created_at', '=' , $year )->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider_1)->whereNull('deleted_at')->count('application_id');
    //                 $noOfFeedback_1 = Feedback::whereYear( 'created_at', '=' , $year )->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->distinct('application_id')->count('application_id');
    //                 $noOfPpeWear_1 = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at') ->whereRaw($whereRawFeedbackServiceProvider_1)->where('wear_ppe', '=', 1)->count();
    //                 $noOfFsmServiceQuality_1 = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at') ->whereRaw($whereRawFeedbackServiceProvider_1)->where('fsm_service_quality', '=', 1)->count();

    //                 $keyPerformanceData_1 = []; // Initialize the $keyPerformanceData_1 array
    //                 $query = "SELECT t.target, i.indicator
    //                         FROM fsm.kpi_targets AS t
    //                         JOIN fsm.key_performance_indicators AS i ON t.indicator_id = i.id
    //                         WHERE t.year = $year";
            
    //             $results = DB::select($query);
    //             foreach ($results as $result) {
                  
    //                     switch ($result['indicator']) {
    //                         case 1:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" =>$result['indicator'],
    //                                 "target" => $result['target'] ? $result['target'] : 0,
    //                                 "achievement" => $values['applicationCount'] == 0 ? "0" : ceil(($values['noOfEmptying'] / $values['applicationCount']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                         case 2:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" =>$result['indicator'],
    //                                 "target" => $result['target'] ? $result['target'] : 0,
    //                                 "achievement" => $values['noOfEmptying'] == 0 ? "0" : ceil(($values['noOfEmptyingReachedToTreatment'] / $values['noOfEmptying']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                         case 3:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" =>$result['indicator'],
    //                                 "target" => $result['target'] ? $result['target'] : 0,
    //                                 "achievement" => $values['noOfFeedback'] == 0 ? "0" : (ceil($values['noOfFsmServiceQuality'] / $values['noOfFeedback']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                         case 4:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" =>$result['indicator'],
    //                                 "target" => $result['target'] ? $result['target'] : 0,
    //                                 "achievement" => ($values['noOfFeedback']) == 0 ? "0" : ceil(($values['noOfPpeWear'] / $values['noOfFeedback']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                     }
                
    //                 }
    //                 //For quarter 
    //                 $quarters = Quarters::where('year', '=', $year)->get();
    //                 $quarterCounts = [];
        
    //                 foreach ($quarters as $quarter) {
        
    //                     $applicationCount = Application::whereYear('application_date', $year)
    //                     ->where(function ($query) use ($quarter) {
    //                         $query->whereBetween('application_date', [$quarter->starttime, $quarter->endtime])
    //                             ->orWhereDate('application_date', '=', $quarter->starttime)
    //                             ->orWhereDate('application_date', '=', $quarter->endtime);
    //                     })
    //                     ->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider_1)->count();
        
    //                     $noOfEmptying = Emptying::whereYear('created_at','=', $year)->where(function ($query) use ($quarter) {
    //                                 $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
    //                                     ->orWhereDate('created_at', '=', $quarter->starttime)
    //                                     ->orWhereDate('created_at', '=', $quarter->endtime);
    //                             })->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider_1)->distinct('application_id')->count('application_id');
        
    //                     $noOfEmptyingReachedToTreatment = SludgeCollection::whereYear('created_at', '=', $year)->where(function ($query) use ($quarter) {
    //                                 $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
    //                                 ->orWhereDate('created_at', '=', $quarter->starttime)
    //                                 ->orWhereDate('created_at', '=', $quarter->endtime);
    //                                 })->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider_1)->whereNull('deleted_at')->count('application_id');
        
    //                     $noOfFeedback = Feedback::whereYear('created_at', '=', $year)->where(function ($query) use ($quarter) {
    //                         $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
    //                             ->orWhereDate('created_at', '=', $quarter->starttime)
    //                             ->orWhereDate('created_at', '=', $quarter->endtime);
    //                         })->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->distinct('application_id')->count('application_id');
                    
    //                     $noOfPpeWear =  Feedback::where('wear_ppe', true)
    //                         ->where(function ($query) use ($quarter) {
    //                             $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])
    //                                 ->orWhereDate('created_at', '=', $quarter->starttime)
    //                                 ->orWhereDate('created_at', '=', $quarter->endtime);
    //                         })->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->whereYear('created_at', '=', $year)->count();
        
    //                     $noOfFsmServiceQuality = Feedback::whereYear('created_at', '=', $year)->where(function ($query) use ($quarter) {
    //                         $query->whereBetween('created_at', [$quarter->starttime, $quarter->endtime])->orWhereDate('created_at', '=', $quarter->starttime)->orWhereDate('created_at', '=', $quarter->endtime);
    //                         })->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->where('fsm_service_quality', true)->count();
                    
    //                     $quarterCounts[$quarter->quarterid] = [
    //                                 'applicationCount' => $applicationCount,
    //                                 'noOfEmptying' => $noOfEmptying,
    //                                 'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
    //                                 'noOfFeedback' => $noOfFeedback,
    //                                 'noOfPpeWear' => $noOfPpeWear,
    //                                 'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
    //                             ];
    //                     } 

    //                     $query = "SELECT t.target,q.quartername, k.indicator,q.quarterid FROM
    //                     fsm.kpi_targets t LEFT JOIN fsm.quarters q ON t.year = q.year LEFT JOIN fsm.key_performance_indicators k ON t.indicator_id = k.id WHERE t.year = $year AND t.deleted_at IS NULL ORDER BY  t.year, k.indicator; ";
        
    //                     $kpiResults = DB::select($query);
    //                     $keyPerformanceData = []; 
    //                     foreach ($kpiResults as $result) {
    //                         $name = $result->quartername;
    //                         $indicator = $result->indicator;
    //                         $target = $result->target ?? 0;
        
    //                     if (isset($quarterCounts[$result->quarterid])) {
    //                         $commonStructure = [
    //                             "year" => $year,
    //                             "quartername" => $name,
    //                             "indicator_id" => $indicator,
    //                             "target" => $target,
    //                             "serviceprovider" => $service_provider_id == "null" ? '-' : $service_provider_id,
    //                     ];
        
    //                 switch ($indicator) {
    //                     case 'Application Response Efficiency':
    //                         $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['applicationCount'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptying'] / $quarterCounts[$result->quarterid]['applicationCount']) * 100);
    //                         break;
    //                     case 'Customer Satisfaction':
    //                         $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfFsmServiceQuality'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
    //                         break;
    //                     case 'OHS Compliance(PPE)':
    //                         $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfFeedback'] == 0) ? 0: ceil(($quarterCounts[$result->quarterid]['noOfPpeWear'] / $quarterCounts[$result->quarterid]['noOfFeedback']) * 100);
    //                         break;
    //                     case 'Safe Desludging':
    //                         $commonStructure["achievement"] = ($quarterCounts[$result->quarterid]['noOfEmptying'] == 0) ? 0 : ceil(($quarterCounts[$result->quarterid]['noOfEmptyingReachedToTreatment'] / $quarterCounts[$result->quarterid]['noOfEmptying']) * 100);
    //                         break;
    //                 }
    //                 $keyPerformanceData[] = $commonStructure;
    //                 }
    //             }

    //             }
    //             else
    //             {
    //                 $years = KpiTarget::pluck('year', 'year')->unique()->sort();
    //                 $yearlyCounts = [];
    //                 foreach ($years as $year) {
        
    //                     $applicationCount = Application::whereYear(DB::raw('application_date'), '=', $year)->whereNull('deleted_at')->whereRaw($whereRawApplicationsServiceProvider_1)->count();
    //                     $noOfEmptying = Emptying::whereYear('created_at','=', $year)->whereNull('deleted_at')->whereRaw($whereRawEmptyingsServiceProvider_1)->distinct('application_id')->count('application_id');
    //                     $noOfEmptyingReachedToTreatment = SludgeCollection::whereYear('created_at', '=', $year)->distinct('application_id')->whereRaw($whereRawSludgeServiceProvider_1)->whereNull('deleted_at')->count('application_id');
    //                     $noOfFeedback = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->distinct('application_id')->count('application_id');
    //                     $noOfFsmServiceQuality = Feedback::whereYear('created_at', '=', $year)->whereNull('deleted_at')->whereRaw($whereRawFeedbackServiceProvider_1)->where('fsm_service_quality', true)->count();
    //                     $noOfPpeWear =  Feedback::where('wear_ppe', true)->whereRaw($whereRawFeedbackServiceProvider_1)->whereNull('deleted_at')->whereYear('created_at', '=', $year)->count();
                        
    //                     $yearlyCounts[$year] = [
    //                         'applicationCount' => $applicationCount,
    //                         'noOfEmptying' => $noOfEmptying,
    //                         'noOfEmptyingReachedToTreatment' => $noOfEmptyingReachedToTreatment,
    //                         'noOfFeedback' => $noOfFeedback,
    //                         'noOfPpeWear' => $noOfPpeWear,
    //                         'noOfFsmServiceQuality' => $noOfFsmServiceQuality,
    //                     ];
        
    //                 }

    //                 $kpiResults = DB::table('fsm.key_performance_indicators AS i')
    //                 ->leftJoin('fsm.kpi_targets AS t', 'i.id', '=', 't.indicator_id')
    //                 ->select('i.indicator', 'i.id', 't.target', 't.year');
    
    //                 $kpiResults = $kpiResults->get(); 
    //                 foreach ($kpiResults as $result) {
    //                     $indicator = $result->indicator;
    //                     $target = $result->target ?? 0;
    //                     $resultYear = $result->year;
        
        
    //                     if (isset($yearlyCounts[$resultYear])) {
    //                         $commonStructure = [
    //                             "year" => $resultYear,
    //                             "indicator_id" => $indicator,
    //                             "target" => $target,
    //                             "serviceprovider" => $service_provider_id == "null" ? '-' : $service_provider_id,
    //                         ];
    //                         switch ($indicator) {
    //                             case 'Application Response Efficiency':
    //                                 $commonStructure["achievement"] = $yearlyCounts[$resultYear]['applicationCount'] == 0 ? 0 : floor(($yearlyCounts[$resultYear]['noOfEmptying'] / $yearlyCounts[$resultYear]['applicationCount']) * 100);
    //                                 break;
    //                             case 'Customer Satisfaction':
    //                                 $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0 : floor(($yearlyCounts[$resultYear]['noOfFsmServiceQuality'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
    //                                 break;
    //                             case 'OHS Compliance(PPE)':
    //                                 $commonStructure["achievement"] = $yearlyCounts[$resultYear]['noOfFeedback'] == 0 ? 0: floor(($yearlyCounts[$resultYear]['noOfPpeWear'] / $yearlyCounts[$resultYear]['noOfFeedback']) * 100);
    //                                 break;
    //                             case 'Safe Desludging':
    //                                 $commonStructure["achievement"] = ($yearlyCounts[$resultYear]['noOfEmptying'] == 0) ? 0 : floor(($yearlyCounts[$resultYear]['noOfEmptyingReachedToTreatment'] / $yearlyCounts[$resultYear]['noOfEmptying']) * 100);
    //                                 break;
    //                             }
    //                         $keyPerformanceData[] = $commonStructure;
    //                 }
    //                 }
    //             }
    //             return $keyPerformanceData;
    // }

    // public function _store()
    // {
    //         $year = Carbon::now()->year;
    //         $service_provider_ids = ServiceProvider::Operational()->orderBy('id')->pluck('id');
            
    //         $valuesArray = [];
        
    //         foreach ($serviceProviders as $service_provider_id) {
    //             $values = $this->getData($service_provider_id, $year);
    //             $valuesArray[$service_provider_id] = $values;
    //         }
            
    //             $keyPerformanceData_1 = []; // Initialize the $keyPerformanceData_1 array
            
    //             foreach ($valuesArray as $service_provider_id => $values) {
    //                 $query = "SELECT t.target, i.id
    //                         FROM fsm.kpi_targets AS t
    //                         JOIN fsm.key_performance_indicators AS i ON t.indicator_id = i.id
    //                         WHERE t.year = $year";
            
    //                 $results = DB::select($query);
                
    //                 foreach ($results as $result) {
                    
    //                     $target = $result->target;
    //                     $indicator = $result->id;
            
    //                     switch ($indicator) {
    //                         case 1:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" => $indicator,
    //                                 "target" => $target ? $target : 0,
    //                                 "achievement" => $values['applicationCount'] == 0 ? "0" : ceil(($values['noOfEmptying'] / $values['applicationCount']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                         case 2:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" => $indicator,
    //                                 "target" =>$target ? $target : 0,
    //                                 "achievement" => $values['noOfEmptying'] == 0 ? "0" : ceil(($values['noOfEmptyingReachedToTreatment'] / $values['noOfEmptying']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                         case 3:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" => $indicator,
    //                                 "target" =>$target ? $target : 0,
    //                                 "achievement" => $values['noOfFeedback'] == 0 ? "0" : (ceil($values['noOfFsmServiceQuality'] / $values['noOfFeedback']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                         case 4:
    //                             array_push($keyPerformanceData_1, [
    //                                 "year" => $year,
    //                                 "indicator_id" => $indicator,
    //                                 "target" => $target ? $target : 0,
    //                                 "achievement" => ($values['noOfFeedback']) == 0 ? "0" : ceil(($values['noOfPpeWear'] / $values['noOfFeedback']) * 100),
    //                                 "service_provider_id" => $service_provider_id
    //                             ]);
    //                             break;
    //                     }
    //                 }
    //             }
    //             foreach ($keyPerformanceData_1 as $data) {
    //                 $currentYear = Carbon::now()->year;
                
    //                 $kpi = KpiAchievement::where('year', $currentYear)->where('indicator_id', $data['indicator_id'])
    //                 ->where('service_provider_id', $data['service_provider_id'])
    //                 ->first();
    //                 if ($kpi) {
    //                     $kpi->target = $data['target'];
    //                     $kpi->achievement = $data['achievement'];
    //                     $kpi->save();
    //                 } else {
    //                     $kpi = new KpiAchievement();
    //                     $kpi->year = $data['year'];
    //                     $kpi->indicator_id = $data['indicator_id'];
    //                     $kpi->target = $data['target'];
    //                     $kpi->achievement = $data['achievement'];
    //                     $kpi->service_provider_id = $data['service_provider_id'];
    //                     $kpi->save();
    //                 }
    //             }
    // }
}