<?php
// Last Modified Date: 18-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
namespace App\Services\Fsm;
use DB;
use App\Models\LayerInfo\LandUse;
use App\Models\LayerInfo\Ward;
use App\Models\BuildingInfo\FunctionalUse;
use DateTime;
use Illuminate\Support\Facades\Auth;
use App\Models\Fsm\ServiceProvider;
use Carbon\Carbon;
class FsmDashboardService
{

    public function getCostPaidByContainmentOwnerPerward($year)
    {

        $chart = array();
        $where = " WHERE es.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from a.created_at) = '$year'";
        }

        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND a.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }

        $query = "SELECT
            SUM(es.total_cost) AS total_cost,
            w.ward
        FROM
            layer_info.wards w
            LEFT JOIN fsm.applications a ON w.ward = a.ward
            LEFT JOIN fsm.emptyings es ON a.id = es.application_id
                                       AND es.deleted_at IS NULL

            $where $whereRawServiceProvider 
        GROUP BY
            w.ward
        ORDER BY
            w.ward";
        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->total_cost;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    public function getEmptyingServicePerWardsAssessmentFeedback($year)
    {
        $where = " WHERE a.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from a.created_at) = '$year'";
        }
//        $query = "
//        SELECT t1.ward, t1.emptying_count, t3.feedback_count,
//        t4.application_count, t5.sludgecollection_count
//        FROM
//        (SELECT w.ward, COUNT(e.id) AS emptying_count
//        FROM layer_info.wards w
//        LEFT JOIN fsm.applications a ON a.ward = w.ward
//        LEFT JOIN fsm.emptyings e ON e.application_id = a.id
//        $where AND e.deleted_at IS NULL
//        GROUP BY w.ward) AS t1
//        LEFT JOIN
//        (SELECT w.ward, COUNT(f.id) AS feedback_count
//        FROM layer_info.wards w
//        LEFT JOIN fsm.applications a ON a.ward = w.ward
//        LEFT JOIN fsm.feedbacks f ON f.application_id = a.id
//        $where AND f.deleted_at IS NULL
//        GROUP BY w.ward) AS t3 ON t1.ward = t3.ward
//        LEFT JOIN
//        (SELECT w.ward, COUNT(a.id) AS application_count
//        FROM layer_info.wards w
//        LEFT JOIN fsm.applications a ON a.ward = w.ward
//        $where
//        GROUP BY w.ward) AS t4 ON t1.ward = t4.ward
//        LEFT JOIN
//        (SELECT w.ward, COUNT(s.id) AS sludgecollection_count
//        FROM layer_info.wards w
//        LEFT JOIN fsm.applications a ON a.ward = w.ward
//        LEFT JOIN fsm.sludge_collections s ON s.application_id = a.id
//        $where AND s.deleted_at IS NULL
//        GROUP BY w.ward) AS t5 ON t1.ward = t5.ward
//        ORDER BY t1.ward";

        
        
        $query = "SELECT 
            w.ward, 
            COALESCE(t1.emptying_count, 0) AS emptying_count, 
            COALESCE(t3.feedback_count, 0) AS feedback_count, 
            COALESCE(t4.application_count, 0) AS application_count, 
            COALESCE(t5.sludgecollection_count, 0) AS sludgecollection_count 
        FROM 
            layer_info.wards w 
        LEFT JOIN 
            (SELECT a.ward, COUNT(e.id) AS emptying_count 
             FROM fsm.applications a 
             LEFT JOIN fsm.emptyings e ON e.application_id = a.id 
             $where AND e.deleted_at IS NULL 
             GROUP BY a.ward) AS t1 ON w.ward = t1.ward 
        LEFT JOIN 
            (SELECT a.ward, COUNT(f.id) AS feedback_count 
             FROM fsm.applications a 
             LEFT JOIN fsm.feedbacks f ON f.application_id = a.id 
             $where AND f.deleted_at IS NULL 
             GROUP BY a.ward) AS t3 ON w.ward = t3.ward 
        LEFT JOIN 
            (SELECT a.ward, COUNT(a.id) AS application_count 
             FROM fsm.applications a 
             $where
             GROUP BY a.ward) AS t4 ON w.ward = t4.ward 
        LEFT JOIN 
            (SELECT a.ward, COUNT(s.id) AS sludgecollection_count 
             FROM fsm.applications a 
             LEFT JOIN fsm.sludge_collections s ON s.application_id = a.id 
             $where AND s.deleted_at IS NULL 
             GROUP BY a.ward) AS t5 ON w.ward = t5.ward 
        ORDER BY 
            w.ward";

        $results = DB::select($query);
       

        $labels = [];
        /*$assessment_dataset = [];
        $assessment_dataset['stack'] = '"stack 1"';
        $assessment_dataset['label'] = '"Assessment"';
        // $assessment_dataset['color'] = '"#afafaf"';
        $assessment_dataset['color'] = '"rgba(57, 142, 61, 0.75)"';
        $assessment_dataset['data'] = [];*/
        $application_dataset = [];
        $application_dataset['stack'] = '"stack 3"';
        $application_dataset['label'] = '"Application"';
        $application_dataset['color'] = '"rgba(103,233,188, 0.6)"';
        $application_dataset['data'] = [];
        
        $emptying_dataset = [];
        $emptying_dataset['stack'] = '"stack 1"';
        $emptying_dataset['label'] = '"Emptying"';
        $emptying_dataset['color'] = '"rgba(61,225,115, 0.6)"';
        $emptying_dataset['data'] = [];

        $sludgecollecion_dataset = [];
        $sludgecollecion_dataset['stack'] = '"stack 4"';
        $sludgecollecion_dataset['label'] = '"Sludge Disposed"';
        $sludgecollecion_dataset['color'] = '"rgba(34,201,37, 0.6)"';
        $sludgecollecion_dataset['data'] = [];

        
        $feedback_dataset = [];
        $feedback_dataset['stack'] = '"stack 2"';
        $feedback_dataset['label'] = '"Feedback"';
        $feedback_dataset['color'] = '"rgba(66,155,28, 0.6)"';
        $feedback_dataset['data'] = [];

       

        
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            /*$assessment_dataset['data'][] = $row->assessment_count;*/
            $emptying_dataset['data'][] = $row->emptying_count;
            $feedback_dataset['data'][] = $row->feedback_count;
            $application_dataset['data'][] = $row->application_count;
            $sludgecollecion_dataset['data'][] = $row->sludgecollection_count;
        }

        $datasets = [
            $application_dataset,
            /*$assessment_dataset,*/
            $emptying_dataset,
            $sludgecollecion_dataset,
            $feedback_dataset,
            
        ];

        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );

        return $chart;
    }

    public function getFsmSrvcQltyChart($year)
    {
        $where = " WHERE deleted_at IS NULL";
        if($year) {
            $where .= " AND extract(year from fb.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
            {

                $whereRawServiceProvider = " AND fb.service_provider_id = " .Auth::user()->service_provider_id;


            }
            else{
                $whereRawServiceProvider = " AND 1 = 1";

            }
        $query = "SELECT count(CASE WHEN fsm_service_quality THEN 1 END) as yes,count(CASE WHEN NOT fsm_service_quality THEN 1 END) as no"
            . " FROM fsm.feedbacks fb $where $whereRawServiceProvider";

        $results = DB::select($query);
        $labels = array('"Yes"', '"No"');
        $values = array($results[0]->yes, $results[0]->no);

        $colors = ['"rgba(153, 202, 60, 0.8)"', '"rgba(251, 176, 64, 0.8)"'];
        $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(153, 202, 60, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
        $hoverBackgroundColor = ['"rgba(153, 202, 60, 0.9)"', '"rgba(251, 176, 64, 0.9)"'];
        $hoverBorderColor = ['"rgba(153, 202, 60, 1)"', '"rgba(251, 176, 64, 1)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'borderColor' =>  $borderColor,
              'hoverBackgroundColor' => $hoverBackgroundColor,
              'hoverBorderColor' => $hoverBorderColor


        ];

        return $chart;

    }
    public function getSrvcQltyItoPrcChart($year)
        {
            // $where = " WHERE deleted_at IS NULL";
            // if($year) {
            //     $where .= " AND extract(year from fb.created_at) = '$year'";
            // }
            //  if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
            //     {

            //         $whereRawServiceProvider = " AND fb.service_provider_id = " .Auth::user()->service_provider_id;


            //     }
            //     else{
            //         $whereRawServiceProvider = " AND 1 = 1";

            //     }
            // $query = "SELECT ss.scale_value, ss.scale_name, COUNT(fb.id)"
            //      . " FROM fsm.satisfaction_scales ss LEFT JOIN fsm.feedbacks fb ON ss.scale_value = fb.srvc_qlty_ito_prc"
            //      . " $where $whereRawServiceProvider"
            //      . " GROUP BY ss.scale_value, ss.scale_name"
            //      . " ORDER BY ss.scale_value DESC";


            // $results = DB::select($query);
            // $labels = array();
            // $values = array();

            // foreach ($results as $row) {
            //     $labels[] = '"' . $row->scale_name . '"';
            //     $values[] = $row->count;
            // }

            // // $colors = ['"#afafaf"', '"#4286f4"', '"#00ffff"', '"#3de52d"', '"#9e26f4"'];
            // $colors = ['"rgba(57, 142, 61, 0.2)"', '"rgba(62, 199, 68, 0.2)"', '"rgba(255, 229, 0, 0.2)"', '"rgba(255, 179, 3, 0.2)"', '"rgba(219, 61, 61, 0.2)"'];
            // $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(62, 199, 68, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
            // $hoverBackgroundColor = ['"rgba(57, 142, 61, 0.45)"', '"rgba(62, 199, 68, 0.45)"', '"rgba(255, 229, 0, 0.45)"', '"rgba(255, 179, 3, 0.45)"', '"rgba(219, 61, 61, 0.45)"'];
            // $hoverBorderColor = ['"rgba(57, 142, 61, 1)"', '"rgba(62, 199, 68, 1)"', '"rgba(255, 229, 0, 1)"', '"rgba(255, 179, 3, 1)"', '"rgba(219, 61, 61, 1)"'];

            // $chart = [
            //     'labels' => $labels,
            //     'values' => $values,
            //     'colors' => $colors,
            //     'borderColor' =>  $borderColor,
            //     'hoverBackgroundColor' => $hoverBackgroundColor,
            //     'hoverBorderColor' => $hoverBorderColor
            // ];

            $chart = [
                'labels' => [],
                'values' => [],
                'colors' => [],
                'borderColor' =>  [],
                'hoverBackgroundColor' => [],
                'hoverBorderColor' => [],
            ];

            return $chart;
    }

    public function getSludgeAndTransportationServiceChart($year)
    {
        $where = " WHERE deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from fb.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND fb.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }
        $query = "SELECT ss.scale_value, ss.scale_name, COUNT(fb.id)"
            . " FROM fsm.satisfaction_scales ss LEFT JOIN fsm.feedbacks fb ON ss.scale_value = fb.sldgclln_trptn_srvc_qlty"
            . " $where $whereRawServiceProvider"
            . " GROUP BY ss.scale_value, ss.scale_name"
            . " ORDER BY ss.scale_value DESC";


        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->scale_name . '"';
            $values[] = $row->count;
        }

        // $colors = ['"#afafaf"', '"#4286f4"', '"#00ffff"', '"#3de52d"', '"#9e26f4"'];
        $colors = ['"rgba(57, 142, 61, 0.2)"', '"rgba(62, 199, 68, 0.2)"', '"rgba(255, 229, 0, 0.2)"', '"rgba(255, 179, 3, 0.2)"', '"rgba(219, 61, 61, 0.2)"'];
        $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(62, 199, 68, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
        $hoverBackgroundColor = ['"rgba(57, 142, 61, 0.45)"', '"rgba(62, 199, 68, 0.45)"', '"rgba(255, 229, 0, 0.45)"', '"rgba(255, 179, 3, 0.45)"', '"rgba(219, 61, 61, 0.45)"'];
        $hoverBorderColor = ['"rgba(57, 142, 61, 1)"', '"rgba(62, 199, 68, 1)"', '"rgba(255, 229, 0, 1)"', '"rgba(255, 179, 3, 1)"', '"rgba(219, 61, 61, 1)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'borderColor' =>  $borderColor,
            'hoverBackgroundColor' => $hoverBackgroundColor,
            'hoverBorderColor' => $hoverBorderColor
        ];

        return $chart;
    }

    public function getAccelerationServiceDeliveryChart($year)
    {
        $where = " WHERE deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from fb.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND fb.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }
        $query = "SELECT ss.scale_value, ss.scale_name, COUNT(fb.id)"
            . " FROM fsm.satisfaction_scales ss LEFT JOIN fsm.feedbacks fb ON ss.scale_value = fb.accrln_effcnc_srvc_dlvry"
            . " $where $whereRawServiceProvider"
            . " GROUP BY ss.scale_value, ss.scale_name"
            . " ORDER BY ss.scale_value DESC";


        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->scale_name . '"';
            $values[] = $row->count;
        }

        // $colors = ['"#afafaf"', '"#4286f4"', '"#00ffff"', '"#3de52d"', '"#9e26f4"'];
        $colors = ['"rgba(57, 142, 61, 0.2)"', '"rgba(62, 199, 68, 0.2)"', '"rgba(255, 229, 0, 0.2)"', '"rgba(255, 179, 3, 0.2)"', '"rgba(219, 61, 61, 0.2)"'];
        $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(62, 199, 68, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
        $hoverBackgroundColor = ['"rgba(57, 142, 61, 0.45)"', '"rgba(62, 199, 68, 0.45)"', '"rgba(255, 229, 0, 0.45)"', '"rgba(255, 179, 3, 0.45)"', '"rgba(219, 61, 61, 0.45)"'];
        $hoverBorderColor = ['"rgba(57, 142, 61, 1)"', '"rgba(62, 199, 68, 1)"', '"rgba(255, 229, 0, 1)"', '"rgba(255, 179, 3, 1)"', '"rgba(219, 61, 61, 1)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'borderColor' =>  $borderColor,
            'hoverBackgroundColor' => $hoverBackgroundColor,
            'hoverBorderColor' => $hoverBorderColor
        ];

        return $chart;
    }

    public function getBehaviorOfTheServiceProviderChart($year)
    {
        // $where = " WHERE deleted_at IS NULL";
        // if($year) {
        //     $where .= " AND extract(year from fb.created_at) = '$year'";
        // }
        // if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk'))
        //     {

        //         $whereRawServiceProvider = " AND fb.service_provider_id = " .Auth::user()->service_provider_id;


        //     }
        //     else{
        //         $whereRawServiceProvider = " AND 1 = 1";

        //     }
        // $query = "SELECT ss.scale_value, ss.scale_name, COUNT(fb.id)"
        //      . " FROM fsm.satisfaction_scales ss LEFT JOIN fsm.feedbacks fb ON ss.scale_value = fb.bhvr_srvc_prvdr"
        //      . " $where $whereRawServiceProvider"
        //      . " GROUP BY ss.scale_value, ss.scale_name"
        //      . " ORDER BY ss.scale_value DESC";


        // $results = DB::select($query);
        // $labels = array();
        // $values = array();

        // foreach ($results as $row) {
        //     $labels[] = '"' . $row->scale_name . '"';
        //     $values[] = $row->count;
        // }

        // // $colors = ['"#afafaf"', '"#4286f4"', '"#00ffff"', '"#3de52d"', '"#9e26f4"'];
        // $colors = ['"rgba(57, 142, 61, 0.2)"', '"rgba(62, 199, 68, 0.2)"', '"rgba(255, 229, 0, 0.2)"', '"rgba(255, 179, 3, 0.2)"', '"rgba(219, 61, 61, 0.2)"'];
        // $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(62, 199, 68, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
        // $hoverBackgroundColor = ['"rgba(57, 142, 61, 0.45)"', '"rgba(62, 199, 68, 0.45)"', '"rgba(255, 229, 0, 0.45)"', '"rgba(255, 179, 3, 0.45)"', '"rgba(219, 61, 61, 0.45)"'];
        // $hoverBorderColor = ['"rgba(57, 142, 61, 1)"', '"rgba(62, 199, 68, 1)"', '"rgba(255, 229, 0, 1)"', '"rgba(255, 179, 3, 1)"', '"rgba(219, 61, 61, 1)"'];

        // $chart = [
        //     'labels' => $labels,
        //     'values' => $values,
        //     'colors' => $colors,
        //     'borderColor' =>  $borderColor,
        //     'hoverBackgroundColor' => $hoverBackgroundColor,
        //     'hoverBorderColor' => $hoverBorderColor
        // ];

        // return $chart;
    }

    public function getImpactCreatingPublicAwarenessChart($year)
    {
        $where = " WHERE deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from fb.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND fb.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }
        $query = "SELECT ss.scale_value, ss.scale_name, COUNT(fb.id)"
            . " FROM fsm.satisfaction_scales ss LEFT JOIN fsm.feedbacks fb ON ss.scale_value = fb.imct_crtng_pblc_awrns"
            . " $where $whereRawServiceProvider"
            . " GROUP BY ss.scale_value, ss.scale_name"
            . " ORDER BY ss.scale_value DESC";


        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->scale_name . '"';
            $values[] = $row->count;
        }

        // $colors = ['"#afafaf"', '"#4286f4"', '"#00ffff"', '"#3de52d"', '"#9e26f4"'];
        $colors = ['"rgba(57, 142, 61, 0.2)"', '"rgba(62, 199, 68, 0.2)"', '"rgba(255, 229, 0, 0.2)"', '"rgba(255, 179, 3, 0.2)"', '"rgba(219, 61, 61, 0.2)"'];
        $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(62, 199, 68, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
        $hoverBackgroundColor = ['"rgba(57, 142, 61, 0.45)"', '"rgba(62, 199, 68, 0.45)"', '"rgba(255, 229, 0, 0.45)"', '"rgba(255, 179, 3, 0.45)"', '"rgba(219, 61, 61, 0.45)"'];
        $hoverBorderColor = ['"rgba(57, 142, 61, 1)"', '"rgba(62, 199, 68, 1)"', '"rgba(255, 229, 0, 1)"', '"rgba(255, 179, 3, 1)"', '"rgba(219, 61, 61, 1)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'borderColor' =>  $borderColor,
            'hoverBackgroundColor' => $hoverBackgroundColor,
            'hoverBorderColor' => $hoverBorderColor
        ];

        return $chart;
    }

    public function getppeChart($year)
    {
        $where = " WHERE deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from fb.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND fb.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }
        $query = "SELECT count(CASE WHEN wear_ppe THEN 1 END) as yes,count(CASE WHEN NOT wear_ppe THEN 1 END) as no"
            . " FROM fsm.feedbacks fb $where $whereRawServiceProvider";

        $results = DB::select($query);
        $labels = array('"Yes"', '"No"');
        $values = array($results[0]->yes, $results[0]->no);
        $colors = ['"rgba(153, 202, 60, 0.8)"', '"rgba(251, 176, 64, 0.8)"'];
        $borderColor = ['"rgba(57, 142, 61, 0.65)"', '"rgba(153, 202, 60, 0.8)"', '"rgba(255, 229, 0, 0.8)"', '"rgba(255, 179, 3, 0.8)"', '"rgba(219, 61, 61, 0.65)"'];
        $hoverBackgroundColor = ['"rgba(153, 202, 60, 0.9)"', '"rgba(251, 176, 64, 0.9)"'];
        $hoverBorderColor = ['"rgba(153, 202, 60, 1)"', '"rgba(251, 176, 64, 1)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'borderColor' =>  $borderColor,
            'hoverBackgroundColor' => $hoverBackgroundColor,
            'hoverBorderColor' => $hoverBorderColor
        ];

        return $chart;
    }

    public function getTotalFeedbackPpeWear()
    {
        $where = " WHERE deleted_at IS NULL";

        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND fb.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }
        $query = "SELECT count(CASE WHEN wear_ppe THEN 1 END) AS total_count"
            . " FROM fsm.feedbacks fb $where $whereRawServiceProvider";

        $results = DB::select($query);
        return $results[0]->total_count;
    }

    public function getSewerLengthPerWard()
    {
        $chart = array();
        $query = "SELECT w.ward, round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(sewers.geom,w.geom),32645))/1000) as numeric ),2) as length
        FROM layer_info.wards w, utility_info.sewers sewers
        WHERE sewers.deleted_at IS NULL
        GROUP BY w.ward
        ORDER BY w.ward";

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->length;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    public function getHotspotsPerWard($year = null)
    {
        $chart = array();
        $where = " WHERE h.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from h.date) = '$year'";
        }
        
        $query = "SELECT
                    COUNT(h.id) AS num_of_hotspots,
                    w.ward
                FROM
                    layer_info.wards w
                    LEFT JOIN public_health.waterborne_hotspots h ON w.ward = h.ward
                
                    $where 
                GROUP BY
                    w.ward
                ORDER BY
                    w.ward";

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->num_of_hotspots;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }
    // public function getFsmCampaignsPerWard($year = null)
    // {
    //     $chart = array();
    //     $where = " WHERE fsm.deleted_at IS NULL";
    //     if ($year) {
    //         $where .= " AND extract(year from fsm.start_date) = '$year'";
    //     }
    //     $query = "select count(id) as num_of_fsm_campaigns, w.ward
    //     from fsm.fsm_campaigns fsm
    //     right join layer_info.wards w ON fsm.ward = w.ward $where group by w.ward order by w.ward";

    //     $results = DB::select($query);

    //     $labels = array();
    //     $values = array();
    //     foreach ($results as $row) {
    //         $labels[] = '"' . $row->ward . '"';
    //         $values[] = $row->num_of_fsm_campaigns;
    //     }

    //     $chart = array(
    //         'labels' => $labels,
    //         'values' => $values,
    //     );

    //     return $chart;
    // }

    // public function getFsmCampaignsSupportedBy($year = null)
    // {
    //     $chart = array();
    //     $where = " WHERE fsm.deleted_at IS NULL";
    //     if ($year) {
    //         $where .= " AND extract(year from fsm.start_date) = '$year'";
    //     }
    //     $query = "select count(id) as num_of_fsm_campaigns,supported_by
    //     from fsm.fsm_campaigns fsm $where group by supported_by order by supported_by";

    //     $results = DB::select($query);
    //     $labels = array();
    //     $values = array();
    //     foreach ($results as $row) {
    //         $labels[] = '"' . ($row->supported_by == null ? "Unknown" : $row->supported_by) . '"';
    //         $values[] = $row->num_of_fsm_campaigns;
    //     }
    //     $chart = array(
    //         'labels' => $labels,
    //         'values' => $values,
    //     );

    //     return $chart;
    // }

    public function getBuildingsPerWardChart()
    {
        $chart = array();

        $query = 'SELECT w.ward, COUNT(b.bin) AS count'
            . ' FROM layer_info.wards w'
            . ' LEFT JOIN building_info.buildings b'
            . ' ON b.ward = w.ward'
            . ' WHERE b.deleted_at IS NULL'
            . ' GROUP BY w.ward'
            . ' ORDER BY w.ward';

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->count;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }
    public function getEmptyingRequestsPerStructureTypeChart()
    {

        $chart = array();
        $query = 'SELECT st.type, COUNT(e.id) AS count
        FROM fsm.emptyings e
        LEFT JOIN fsm.applications a
        ON a.id = e.application_id
        LEFT JOIN building_info.buildings b
        ON b.bin = a.bin
        LEFT JOIN building_info.structure_types st
        ON st.id = b.structure_type_id
        WHERE e.deleted_at IS NULL
        GROUP BY st.id
        ORDER BY st.id';

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->type . '"';
            $values[] = $row->count;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }
    
    public function getBuildingUseChart($ward = null)
    {
        
        $query = "SELECT
            functional_use_name,
            building_count
          FROM (
            SELECT
              CASE
                WHEN fu.name = 'Residential' THEN 'Residential'
                WHEN fu.name = 'Mixed (Residential + Other Uses)' THEN 'Mixed (Residential + Other Uses)'
                WHEN fu.name = 'Commercial' THEN 'Commercial'
                ELSE 'Others'
              END AS functional_use_name,
              COUNT(b.bin) AS building_count
            FROM
              building_info.buildings b
              LEFT JOIN building_info.functional_uses fu ON fu.id = b.functional_use_id
            WHERE
              b.deleted_at IS NULL
            GROUP BY
              functional_use_name
          ) AS subquery
          ORDER BY
            CASE
              WHEN functional_use_name = 'Residential' THEN 1
              WHEN functional_use_name = 'Mixed (Residential + Other Uses)' THEN 2
              WHEN functional_use_name = 'Commercial' THEN 3
              ELSE 4
            END";
  
      
        
        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->functional_use_name . '"';
            $values[] = $row->building_count;
        }

        $colors = [
            '"#8ECAE6"',
            '"#219EBC"',
            '"#023047"',
            '"#ffb964"',
        ];
        

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors
        ];

        return $chart;
    
    }

    public function getCompostSalesByTreatmentPlantChart($year = null)
    {
        $where = " WHERE c.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from c.created_at) = '$year'";
        }
        $chart = array();

        $query = "SELECT c.name, sum(s.weight) as sum
        FROM fsm.treatment_plants c
        JOIN fsm.compost_sales s
        ON s.treatment_plant_id = c.id
        $where
        GROUP BY c.id, c.name ORDER BY c.id";
        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->name . '"';
            $values[] = $row->sum;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );
        return $chart;
    }

    public function getSludgeCollectionByTreatmentPlantChart($year = null)
    {
        $where = " WHERE c.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from c.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Treatment Plant')) {
            $treatment_plant_id = " AND s.treatment_plant_id  = " . Auth::user()->treatment_plant_id;
        } else {
            $treatment_plant_id = " AND 1 = 1";
        }
        $chart = array();
        
        $query = "WITH SludgeSums AS (
                    SELECT 
                        EXTRACT(YEAR FROM s.date) AS year,
                        s.treatment_plant_id,
                        COALESCE(SUM(s.volume_of_sludge), 0) AS sum_volume
                    FROM 
                        fsm.sludge_collections s
                    GROUP BY 
                        year, s.treatment_plant_id
                )

                SELECT 
                    TO_CHAR(generate_series.date, 'YYYY') AS year, 
                    c.id AS treatment_plant_id,
                    c.name AS treatment_plant_name, 
                    COALESCE(ss.sum_volume, 0) AS sum_volume
                FROM 
                    fsm.treatment_plants c 
                    CROSS JOIN GENERATE_SERIES(NOW() - INTERVAL '4 years', NOW(), INTERVAL '1 year') generate_series
                    LEFT JOIN SludgeSums ss
                        ON ss.treatment_plant_id = c.id
                        AND ss.year = EXTRACT(YEAR FROM generate_series.date)
                WHERE 
                    c.deleted_at IS NULL 
                ORDER BY 
                    year, c.id;";
        
        $results = DB::select($query);
        return $results;
    }

     public function getEmptyingServiceByTypeYear()
    {

        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND e.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }
        /*$query = "SELECT TO_CHAR(i, 'YYYY') AS year, c.type, COUNT(C.*) AS count
        FROM GENERATE_SERIES(NOW() + '-4 years', NOW(), '1 year') AS i
        LEFT JOIN fsm.emptyings e
        ON TO_CHAR(e.emptied_date, 'YYYY') = TO_CHAR(i, 'YYYY')
        LEFT JOIN " . 'fsm.applications' . " a
        ON e.application_id = a.id
        LEFT JOIN " . 'fsm.containments' . " c
        ON a.containment_id = c.id
        WHERE e.deleted_at IS NULL $whereRawServiceProvider
        GROUP BY year, c.type
        ORDER BY year";*/
        
        $query = "SELECT
                        TO_CHAR(i, 'YYYY') AS year,
                        c.type,
                        COUNT(c.*) AS count
                    FROM
                        GENERATE_SERIES(NOW() - INTERVAL '4 years', NOW(), INTERVAL '1 year') AS i
                    LEFT JOIN
                        fsm.emptyings e ON TO_CHAR(e.emptied_date, 'YYYY') = TO_CHAR(i, 'YYYY')
                    LEFT JOIN
                        fsm.applications a ON e.application_id = a.id
                    LEFT JOIN
                        fsm.containments c ON a.containment_id = c.id
                    WHERE
                        e.deleted_at IS NULL $whereRawServiceProvider
                    GROUP BY
                        year, c.type
                    ORDER BY
                        year, c.type";

        $results = DB::select($query);
        $containment_types = DB::select("SELECT
                containment_type,
                containment_count
              FROM (
                SELECT
                  CASE
                    WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                    WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                    WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                        WHEN type = 'Single Pit' THEN 'Single Pit'
                        WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
                   
                  END AS containment_type,
                  COUNT(c.id) AS containment_count
                FROM
                  fsm.containments c

                WHERE
                  c.deleted_at IS NULL
                  AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
                GROUP BY
                  containment_type
              ) AS subquery
              ORDER BY
                CASE
                  WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
                  WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
                  WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                      WHEN containment_type = 'Single Pit' THEN 4
                      WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
               
                END");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }
        $data = array();
        foreach ($results as $row) {
            $data[$row->year][$row->type] = $row->count;
        }

        $years = array_keys($data);
        $labels = array_map(function ($year) {
            return '"' . $year . '"';
        }, $years);
        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
           
            
        );
        $colorsArr = array_slice($colors, 0, count($results), true);
        $datasets = array();
        $count = 0;
        foreach ($types as $key1 => $value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            foreach ($years as $year) {
                $dataset['data'][] = isset($data[$year][$key1]) ? $data[$year][$key1] : '0';
            }
            $datasets[] = $dataset;
        }

        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );

        return $chart;
    }

    public function getContainmentTypesByLanduse()
    {

        $chart = array();

        $landuses = Landuse::orderBy('class')->pluck('class', 'class')->toArray();
        $containment_types = DB::select("SELECT
                containment_type,
                containment_count
              FROM (
                SELECT
                  CASE
                    WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                    WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                    WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                        WHEN type = 'Single Pit' THEN 'Single Pit'
                        WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
                   
                  END AS containment_type,
                  COUNT(c.id) AS containment_count
                FROM
                  fsm.containments c

                WHERE
                  c.deleted_at IS NULL
                  AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
                GROUP BY
                  containment_type
              ) AS subquery
              ORDER BY
                CASE
                  WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
                  WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
                  WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                      WHEN containment_type = 'Single Pit' THEN 4
                      WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
                 
                END");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }


        $query = "SELECT class, type, count, totalclass, percentage_proportion FROM public.landuse_summaryforchart";
        //from materialized view

        $results = DB::select($query);
        $data = array();
        $values = array();

        foreach ($results as $row) {
            $data[$row->type][$row->class] = intval($row->count);
            $values[$row->type][$row->class] = $row->count;
        }

        $labels = array_map(function ($landuse) {
            return '"' . $landuse . '"';
        }, $landuses);
        // $colors = array('"#B938C7"', '"#528aad"','"#5AA59C"');
        $colors = array(
           '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
           
            
        );
        $colorsArr = array_slice($colors, 0, count($results), true);
        $datasets = array();
        $count = 0;

        foreach ($types as $key1 => $value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            $dataset['value'] = array();

            foreach ($landuses as $key2 => $value2) {
                $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
                $dataset['value'][] = isset($values[$key1][$key2]) ? $values[$key1][$key2] : '0';
            }
            $datasets[] = $dataset;
        }
        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );

        return $chart;
    }

    public function getContainmentTypesByBldgUseResidentials()
    {

        $chart = array();

        $wards = Ward::orderBy('ward')->pluck('ward', 'ward')->toArray();
        $containment_types = DB::select("SELECT
                containment_type,
                containment_count
              FROM (
                SELECT
                  CASE
                    WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                    WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                    WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                        WHEN type = 'Single Pit' THEN 'Single Pit'
                        WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
                
                  END AS containment_type,
                  COUNT(c.id) AS containment_count
                FROM
                  fsm.containments c

                WHERE
                  c.deleted_at IS NULL
                  AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
                GROUP BY
                  containment_type
              ) AS subquery
              ORDER BY
                CASE
                  WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
                  WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
                  WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                      WHEN containment_type = 'Single Pit' THEN 4
                      WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
                
                END");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }

        $query = 'SELECT a.ward, a.type, a.count, b.totalward,
		ROUND(a.count * 100/b.totalward::numeric, 1) as percentage_proportion
                FROM ( select c.type, count(c.*), b.ward
                from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                where b.functional_use_id = 1 AND b.deleted_at IS NULL group by c.type, b.ward
                     ) a
                JOIN ( select count(c.*) as totalward, b.ward
                     from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                where b.functional_use_id = 1 AND b.deleted_at IS NULL group by b.ward
                     ) b ON b.ward = a.ward

               ORDER BY a.ward asc';

        $results = DB::select($query);
       
        $data = array();
        $values = array();

        foreach ($results as $row) {
            $data[$row->type][$row->ward] = $row->count;
            $values[$row->type][$row->ward] = $row->count;
        }


        $labels = array_map(function ($ward) {
            return '"' . $ward . '"';
        }, $wards);

        // $colors = array('"#B938C7"', '"#528aad"','"#5AA59C"');
       $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
            
        );

        $colorsArr = array_slice($colors, 0, count($results), true);
        $datasets = array();
        $count = 0;
        foreach ($types as $key1 => $value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            $dataset['value'] = array();
            foreach ($wards as $key2 => $value2) {
                $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
                $dataset['value'][] = isset($values[$key1][$key2]) ? $values[$key1][$key2] : '0';
            }
            $datasets[] = $dataset;
        }

        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );

        return $chart;
    }

    public function getContainmentTypesByBldgUse()
    {

        $chart = array();

        $bldguses = FunctionalUse::orderBy('name')->pluck('name', 'name')->toArray();
        $containment_types = DB::select("SELECT
                containment_type,
                containment_count
              FROM (
                SELECT
                  CASE
                    WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                    WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                    WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                        WHEN type = 'Single Pit' THEN 'Single Pit'
                        WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
                  
                  END AS containment_type,
                  COUNT(c.id) AS containment_count
                FROM
                  fsm.containments c

                WHERE
                  c.deleted_at IS NULL
                  AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
                GROUP BY
                  containment_type
              ) AS subquery
              ORDER BY
                CASE
                  WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
                  WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
                  WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                      WHEN containment_type = 'Single Pit' THEN 4
                      WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
                 
                END");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }

        $query = 'SELECT a.bldg_name, a.type, a.count, b.total_bldguse,
		ROUND(a.count * 100/b.total_bldguse::numeric, 2) as percentage_proportion
                FROM ( select c.type, count(c.*), bldg.name as bldg_name
                from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                join building_info.functional_uses bldg on bldg.id = b.functional_use_id
                where b.functional_use_id is not null AND b.deleted_at IS NULL group by c.type, b.functional_use_id, bldg.name
                     ) a
                JOIN ( select count(c.*) as total_bldguse, bldg.name as bldg_name
               from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                join building_info.functional_uses bldg on bldg.id = b.functional_use_id
                where b.functional_use_id is not null AND b.deleted_at IS NULL group by b.functional_use_id, bldg.name
                     ) b ON b.bldg_name = a.bldg_name

               ORDER BY a.bldg_name asc';

        $results = DB::select($query);

        $data = array();
        $values = array();
        foreach ($results as $row) {
            $data[$row->type][$row->bldg_name] = $row->count;
            $values[$row->type][$row->bldg_name] = $row->count;
        }
        $labels = array_map(function ($bldguse) {
            return '"' . $bldguse . '"';
        }, $bldguses);
        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
           
            
        );
        $colorsArr = array_slice($colors, 0, count($results), true);
        $datasets = array();
        $count = 0;
        foreach ($types as $key1 => $value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            $dataset['value'] = array();
            foreach ($bldguses as $key2 => $value2) {
                $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
                $dataset['value'][] = isset($values[$key1][$key2]) ? $values[$key1][$key2] : '0';
            }
            $datasets[] = $dataset;
        }

        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );

        return $chart;
    }

    public function getContainmentTypesByStructypes()
    {

        $chart = array();

        $results_obj = DB::select('select st.type as st_type,structure_type_id
                from building_info.buildings b
                LEFT JOIN building_info.structure_types st ON b.structure_type_id =  st.id
                where b.structure_type_id is not null AND b.deleted_at IS NULL
                group by b.structure_type_id, st.type');

        foreach ($results_obj as $value) {
            $structtypes[$value->structure_type_id] = $value->st_type;
        }

        $containment_types = DB::select("SELECT
                containment_type,
                containment_count
              FROM (
                SELECT
                  CASE
                    WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                    WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                    WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                        WHEN type = 'Single Pit' THEN 'Single Pit'
                        WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
                   
                  END AS containment_type,
                  COUNT(c.id) AS containment_count
                FROM
                  fsm.containments c

                WHERE
                  c.deleted_at IS NULL
                  AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
                GROUP BY
                  containment_type
              ) AS subquery
              ORDER BY
                CASE
                  WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
                  WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
                  WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                      WHEN containment_type = 'Single Pit' THEN 4
                      WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
                  
                END");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }

        $query = 'SELECT st.type as st_type, a.structure_type_id, a.type, a.count, b.totalstructure_type,
		ROUND(a.count * 100/b.totalstructure_type::numeric, 2 ) as percentage_proportion
                FROM ( select b.structure_type_id, c.type, count(c.*)
                 from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                where structure_type_id is not null group by c.type, b.structure_type_id
                     ) a
                  JOIN ( select count(c.*) as totalstructure_type, b.structure_type_id
                   from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                where structure_type_id is not null group by b.structure_type_id
                     ) b ON b.structure_type_id = a.structure_type_id
                JOIN building_info.structure_types st ON a.structure_type_id =  st.id
               ORDER BY a.structure_type_id asc';

        $results = DB::select($query);

        $data = array();
        $values = array();
        foreach ($results as $row) {

            $data[$row->type][$row->structure_type_id] = $row->count;
            $values[$row->type][$row->structure_type_id] = $row->count;
        }

        $labels = array_map(function ($structtype) {
            return '"' . $structtype . '"';
        }, $structtypes);

        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
           
            
        );
        $colorsArr = array_slice($colors, 0, count($results), true);
        $datasets = array();
        $count = 0;
        foreach ($types as $key1 => $value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            $dataset['value'] = array();
            foreach ($structtypes as $key2 => $value2) {
                $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
                $dataset['value'][] = isset($values[$key1][$key2]) ? $values[$key1][$key2] : '0';
            }
            $datasets[] = $dataset;
        }


        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );

        return $chart;
    }

    public function getContainmentTypesPerWard()
    {
        $chart = array();

        $wards = Ward::orderBy('ward')->pluck('ward', 'ward')->toArray();
        $containment_types = DB::select("SELECT
                containment_type,
                containment_count
              FROM (
                SELECT
                  CASE
                    WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                    WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                    WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                        WHEN type = 'Single Pit' THEN 'Single Pit'
                        WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
                    
                  END AS containment_type,
                  COUNT(c.id) AS containment_count
                FROM
                  fsm.containments c

                WHERE
                  c.deleted_at IS NULL
                  AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
                GROUP BY
                  containment_type
              ) AS subquery
              ORDER BY
                CASE
                  WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
                  WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
                  WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                      WHEN containment_type = 'Single Pit' THEN 4
                      WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
                 
                END");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }


        $query = "SELECT a.ward, a.type, a.count, b.totalward,
        (a.count * 100/b.totalward::numeric) as percentage_proportion
                FROM ( SELECT b.ward, c.type, count(c.*) as count
                         from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                         WHERE c.deleted_at IS NULL
                        GROUP BY b.ward, type
                     ) a
                JOIN ( SELECT ward
                            , count(b.ward) AS totalward
                         FROM building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                         WHERE c.deleted_at IS NULL
                        GROUP BY b.ward
                     ) b ON b.ward = a.ward

               ORDER BY a.ward asc";
        $results = DB::select($query);

        $data = array();
        $values = array();
        foreach($results as $row) {
            $data[$row->type][$row->ward] =   $row->count;
            $values[$row->type][$row->ward] = $row->count;
        }

        $labels = array_map(function($ward) { return '"' . $ward . '"'; }, $wards);
        // $colors = array('"#B938C7"', '"#528aad"','"#5AA59C"');
        // $colors = array('"#7ac36a"', '"#5a9bd4"','"#faa75b"');
        //$colors = array('"rgba(122, 195, 106, 0.8)"', '"rgba(90, 155, 212, 0.8)"', '"rgba(250, 167, 91, 0.8)"', '"rgba(250, 32, 93, 0.8)"');

        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
           
            
        );
        $colorsArr = array_slice($colors, 0, count($results), true);
        $datasets = array();
        $count = 0;
        foreach($types as $key1=>$value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            $dataset['value'] = array();
            foreach($wards as $key2=>$value2) {
                $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
                $dataset['value'][] = isset($values[$key1][$key2]) ? $values[$key1][$key2] : '0';
            }
            $datasets[] = $dataset;
        }

        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets,
        );

        return $chart;
    }

    public function getContainmentsPerWard()
    {
        $query = "SELECT b.ward, COUNT(*) total,
        SUM(CASE WHEN type = 'Pit' THEN 1 ELSE 0 END) pitcount,
        SUM(CASE WHEN type = 'Septic Tank' THEN 1 ELSE 0 END) septicount
        from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
        join fsm.containments c on bc.containment_id = c.id
        WHERE b.ward <> 0 AND b.deleted_at IS NULL GROUP BY ward ORDER BY b.ward";

        $results = DB::select($query);

        $totalResultsCount = count($results);

        $resultsPerTable = ceil($totalResultsCount / 2);

        $wardNo = array();
        $noOfPit = array();
        $noOfSeptic = array();
        $noOfContainments = array();

        foreach ($results as $row) {
            $wardNo[] = $row->ward;
            $noOfPit[] = $row->pitcount;
            $noOfSeptic[] = $row->septicount;
            $noOfContainments[] = $row->total;
        }

        $containPerWard = [
            'wardNo' => $wardNo,
            'noOfPit' => $noOfPit,
            'noOfSeptic' => $noOfSeptic,
            'noOfContainments' => $noOfContainments,
            'totalResultsCount' => $totalResultsCount,
            'resultsPerTable' => $resultsPerTable
        ];

        return $containPerWard;
    }

    public function getContainTypeChart($ward = null)
    {

        $query = "SELECT
            containment_type,
            containment_count
          FROM (
            SELECT
              CASE
                WHEN type = 'Septic Tank with Soak Away Pit' THEN 'Septic Tank with Soak Away Pit'
                WHEN type = 'Septic Tank without Soak Away Pit' THEN 'Septic Tank without Soak Away Pit'
                WHEN type = 'Cesspool/ Holding Tank' THEN 'Cesspool/ Holding Tank'
                    WHEN type = 'Single Pit' THEN 'Single Pit'
                    WHEN type = 'Double Pit with Soak Away Pit' THEN 'Double Pit with Soak Away Pit'
               
              END AS containment_type,
              COUNT(c.id) AS containment_count
            FROM
              fsm.containments c

            WHERE
              c.deleted_at IS NULL
              AND type IN ('Septic Tank with Soak Away Pit', 'Septic Tank without Soak Away Pit', 'Cesspool/ Holding Tank', 'Single Pit', 'Double Pit with Soak Away Pit')
            GROUP BY
              containment_type
          ) AS subquery
          ORDER BY
            CASE
              WHEN containment_type = 'Septic Tank with Soak Away Pit' THEN 1
              WHEN containment_type = 'Septic Tank without Soak Away Pit' THEN 2
              WHEN containment_type = 'Cesspool/ Holding Tank' THEN 3
                  WHEN containment_type = 'Single Pit' THEN 4
                  WHEN containment_type = 'Double Pit with Soak Away Pit' THEN 5
           
            END";
        
        $results = DB::select($query);

        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->containment_type . '"';
            $values[] = $row->containment_count;
        }

        // $colors = array('"#B938C7"', '"#528aad"','"#5AA59C"');

        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(242, 107, 33, 0.8)"',
        );
        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors
        ];

        return $chart;
    }

    public function getPovertyInSettlementChart($ward = null)
    {
        $query = 'SELECT DISTINCT settpovert, COUNT(*) FROM settarea ' . ($ward ? 'WHERE ward = ' . $ward : '') . ' GROUP BY settpovert';

        $results = DB::select($query);

        $labels = ['"Not Poor"', '"Poor"', '"Very Poor"', '"Extreme Poor"'];
        $values = array();

        foreach ($results as $row) {
            $values[] = $row->count;
        }

        $colors = ['"#fff5f0"', '"#fca487"', '"#eb362a"', '"#67000d"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors
        ];

        return $chart;
    }

    public function getNextEmptyingContainmentsChart($ward = null)
    {
        $query = "SELECT TO_CHAR(i, 'YYYY-MM') AS month, COUNT(c.id) AS count"
            . " FROM GENERATE_SERIES(NOW() + '1 month', NOW() + '12 months', '1 month') AS i"
            . " LEFT JOIN " . 'fsm.containments' . " c"
            . " ON TO_CHAR(i, 'YYYY-MM') = TO_CHAR(c.next_emptying_date, 'YYYY-MM')"
            . " WHERE emptied_status = true AND c.deleted_at IS NULL"
            . " GROUP BY month"
            . " ORDER BY month";
        $results = DB::select($query);

        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->month . '"';
            $values[] = $row->count;
        }

        $chart = [
            'labels' => $labels,
            'values' => $values
        ];

        return $chart;
    }

    public function getcontainmentEmptiedByWard()
    {
        $chart = array();
        $dateTime = new DateTime();
        $startDate = $dateTime->format('Y-m-d');
        $dateTime->modify('+29 days');
        $endDate = $dateTime->format('Y-m-d');

        $query = "SELECT
        w.ward, COUNT(a.id) AS count
        FROM layer_info.wards w
        LEFT JOIN ( building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
        join fsm.containments c on bc.containment_id = c.id )
        ON b.ward = w.ward
        LEFT JOIN fsm.applications a
        ON a.containment_id = c.id
        AND a.id IN(SELECT application_id FROM fsm.emptyings WHERE next_emptying_date >= '$startDate' AND next_emptying_date <= '$endDate')
        WHERE a.deleted_at IS NULL
        GROUP BY w.ward
        ORDER BY w.ward";

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->count;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    public function getproposedEmptyingDateContainmentsChart()
    {
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = "WHERE a.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = "WHERE 1 = 1";
        }
        $query = "SELECT i.week, COUNT(a.id) AS count"
            . " FROM (SELECT CONCAT('Week ', w + 1) AS week, NOW()::DATE + w * 7 + 1 AS date FROM GENERATE_SERIES(0, 3) w) AS i"
            . " LEFT JOIN fsm.applications a"
            . " ON a.proposed_emptying_date BETWEEN i.date AND i.date + 6"
            . " $whereRawServiceProvider"
            . " GROUP BY i.week"
            . " ORDER BY i.week";

        $results = DB::select($query);

        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->week . '"';
            $values[] = $row->count;
        }

        $chart = [
            'labels' => $labels,
            'values' => $values
        ];

        return $chart;
    }

    public function getProposedEmptiedDateContainmentsByWard()
    {
        $chart = array();
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = "WHERE a.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = "WHERE 1 = 1";
        }

        $dateTime = new DateTime();
        $startDate = $dateTime->format('Y-m-d');
        $dateTime->modify('+29 days');
        $endDate = $dateTime->format('Y-m-d');
        $query = "SELECT
        w.ward, COUNT(a.id) AS count
        FROM layer_info.wards w
        LEFT JOIN ( building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id )
        ON b.ward = w.ward
        LEFT JOIN fsm.applications a
        ON a.containment_id = c.id
        AND a.id IN(SELECT id FROM fsm.applications WHERE proposed_emptying_date >= '$startDate' AND proposed_emptying_date <= '$endDate')
        $whereRawServiceProvider AND a.deleted_at IS NULL
        GROUP BY w.ward
        ORDER BY w.ward";

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->count;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    public function getMonthlyAppRequestByoperators($year)
    {
            if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

                $whereRawServiceProvider = "WHERE a.service_provider_id = " . Auth::user()->service_provider_id;
            } else {
                $whereRawServiceProvider = "WHERE 1 = 1";
            }
            $where = " AND a.deleted_at IS NULL";
            if($year) 
            {
                $where .= " AND extract(year from a.created_at) = '$year'";
            }
            $chart = array();
            $types = ServiceProvider::Operational()
            ->whereNull('deleted_at')
            ->orderBy('company_name')
            ->pluck('company_name', 'company_name')
            ->toArray();        
            $label = array(1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec");
            $results = DB::select("SELECT a.month, a.spname, a.count
            FROM (
            SELECT months.month_val AS month, sp.company_name AS spname, count(a.id) AS count
            FROM (SELECT m AS month_val FROM GENERATE_SERIES(1,12) m) AS months
            LEFT JOIN fsm.applications a ON months.month_val = extract(month FROM a.created_at)
                AND a.deleted_at IS NULL   AND a.emptying_status IS TRUE
            LEFT JOIN fsm.service_providers sp ON sp.id = a.service_provider_id
            $whereRawServiceProvider $where
            GROUP BY months.month_val, sp.company_name
            ORDER BY months.month_val ASC
            ) a
            JOIN (
                SELECT months.month_val AS month, count(a.id) AS totalclass
                FROM (SELECT m AS month_val FROM GENERATE_SERIES(1,12) m) AS months
                LEFT JOIN fsm.applications a ON months.month_val = extract(month FROM a.created_at)
                    AND a.deleted_at IS NULL
            $where
                GROUP BY months.month_val
                ORDER BY months.month_val ASC
            ) b ON b.month = a.month
            ORDER BY a.month ASC
            ");
            $data = array();
            foreach($results as $row) 
            {
                $data[$row->spname][$row->month] = $row->count;
            }
            $labels = array_map(function($month) { return '"' . $month . '"'; }, $label);
            $colors = [
                    '"rgba(51, 102, 153, 0.7)"',
                    '"rgba(92, 152, 192, 0.7)"',
                    '"rgba(112, 177, 212, 0.7)"',
                    '"rgba(132, 202, 231, 0.7)"',
                    '"rgba(161, 225, 207, 0.7)"',
                    '"rgba(189, 247, 183, 0.7)"',
                    '"rgba(142, 227, 167, 0.7)"',
                    '"rgba(95, 207, 151, 0.7)"',
                    '"rgba(48, 187, 135, 0.7)"',
                    '"rgba(0, 166, 118, 0.7)"'
                ];
            $colorsArr = array_slice($colors, 0, count($results), true);
            $datasets = array();
            $count = 0;
            $stack_count = 1;
            foreach($types as $key1=>$value1) {
                $dataset = array();
                $dataset['label'] = '"' . $value1 . '"';
                $dataset['color'] = $colors[$count++];
                $dataset['data'] = array();
                foreach($labels as $key2=>$value2) {
                    $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
                }

                $dataset['stack'] = '"stack' . $stack_count++ . '"';
                $datasets[] = $dataset;
            }
            $chart = array(
                'labels' => $labels,
                'datasets' => $datasets
            );

        
        return $chart;
    }

    public function getNumberOfEmptyingbyMonths($year)
    {

        $label = array(0 => "Jan", 1 => "Feb", 2 => "Mar", 3 => "Apr", 4 => "May", 5 => "Jun", 6 => "Jul", 7 => "Aug", 8 => "Sep", 9 => "Oct", 10 => "Nov", 11 => "Dec");
        $where = " WHERE a.deleted_at IS NULL";
        $current='';
        if ($year) {
            $current .= " AND extract(year from a.created_at) = '$year'";
        } else {
            $now = Carbon::now()->year;
            $current .= " AND extract(year from a.created_at) = '$now'";
        }
        $query =  "SELECT 
        COUNT(CASE WHEN ST_Intersects(ST_Transform(w.geom, 4326), b.geom) THEN 1 END) AS low_income_count,
        COUNT(CASE WHEN NOT ST_Intersects(ST_Transform(w.geom, 4326), b.geom) THEN 1 END) AS other_count,
        months.month_val AS month
        FROM 
            (SELECT generate_series(1, 12) AS month_val) AS months
        LEFT JOIN fsm.applications AS a ON months.month_val = extract(month FROM a.created_at) $current
        LEFT JOIN building_info.buildings AS b ON a.bin = b.bin
        LEFT JOIN layer_info.low_income_communities AS w ON true
        $where
        GROUP BY 
            months.month_val
        ORDER BY 
            months.month_val;";
        

        $results = DB::select($query);
        
        $labels = [];
        foreach ($label as $month) {
            $labels[] = '"' . $month . '"';
        }

        $low_income_communities_dataset = [];
        $low_income_communities_dataset['stack'] = '"stack 1"';
        $low_income_communities_dataset['label'] = '"Low Income communities"';
        $low_income_communities_dataset['color'] = '"rgba(54, 162, 235,0.5)"';
        $low_income_communities_dataset['data'] = [];

        $other_dataset = [];
        $other_dataset['stack'] = '"stack 2"';
        $other_dataset['label'] = '"Other communities"';
        $other_dataset['color'] = '"rgba(255,183,3, 0.7)"';
        $other_dataset['data'] = [];

        foreach ($results as $row) {

            $low_income_communities_dataset['data'][] = $row->low_income_count;
            $other_dataset['data'][] = $row->other_count;
        }

        $datasets = [
            $low_income_communities_dataset,
            $other_dataset
        ];

        $chart = array(
            'labels' => $labels,
            'datasets' => $datasets
        );
        return $chart;
    }
}
