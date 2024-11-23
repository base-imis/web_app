<?php

namespace App\Services;

use App\Http\Controllers\HomeController;
use DB;
use App\Models\LayerInfo\LandUse;
use App\Models\Fsm\ServiceProvider;
use App\Models\LayerInfo\Ward;
use App\Models\BuildingInfo\FunctionalUse;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BuildingInfo\Building;

class DashboardService
{
    //used to count building with Institution
    public function countBuildingsByUse($useName)
    {
        return Building::whereIn('functional_use_id', function ($query) use ($useName) {
            $query->select('id')
                ->from('building_info.functional_uses')
                ->where('name', 'like', '%' . $useName . '%');
        })
            ->whereNull('deleted_at')
            ->count();
    }

    
    public function countBuildingsByUseExact($useName)
    {
        return Building::whereIn('functional_use_id', function ($query) use ($useName) {
            $query->select('id')
                ->from('building_info.functional_uses')
                ->where('name', $useName );
        })
            ->whereNull('deleted_at')
            ->count();
    }
    public function getCostPaidByContainmentOwnerPerward($year)
    {

        $chart = array();
        $where = " WHERE es.deleted_at IS NULL";
        $leftJoin = " AND 1=1";
        if ($year) {
            $leftJoin .= " AND extract(year from a.created_at) = '$year'";
        }

        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND a.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
            $whereRawServiceProvider = " AND 1 = 1";
        }

        $query = "SELECT
             w.ward,
        COALESCE(SUM(es.total_cost), 0) AS total_cost
        FROM
            layer_info.wards w
            LEFT JOIN fsm.applications a ON w.ward = a.ward
            LEFT JOIN fsm.emptyings es ON a.id = es.application_id
            AND es.deleted_at IS NULL
            $leftJoin
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
        $query = "
        SELECT t1.ward, t1.emptying_count, t3.feedback_count,
        t4.application_count, t5.sludgecollection_count
        FROM
        (SELECT w.ward, COUNT(e.id) AS emptying_count
        FROM layer_info.wards w
        LEFT JOIN fsm.applications a ON a.ward = w.ward
        LEFT JOIN fsm.emptyings e ON e.application_id = a.id
        $where AND e.deleted_at IS NULL
        GROUP BY w.ward) AS t1
        JOIN
        (SELECT w.ward, COUNT(f.id) AS feedback_count
        FROM layer_info.wards w
        LEFT JOIN fsm.applications a ON a.ward = w.ward
        LEFT JOIN fsm.feedbacks f ON f.application_id = a.id
        $where AND f.deleted_at IS NULL
        GROUP BY w.ward) AS t3 ON t1.ward = t3.ward
        JOIN
        (SELECT w.ward, COUNT(a.id) AS application_count
        FROM layer_info.wards w
        LEFT JOIN fsm.applications a ON a.ward = w.ward
        $where
        GROUP BY w.ward) AS t4 ON t1.ward = t4.ward
        JOIN
        (SELECT w.ward, COUNT(s.id) AS sludgecollection_count
        FROM layer_info.wards w
        LEFT JOIN fsm.applications a ON a.ward = w.ward
        LEFT JOIN fsm.sludge_collections s ON s.application_id = a.id
        $where AND s.deleted_at IS NULL
        GROUP BY w.ward) AS t5 ON t1.ward = t5.ward
        ORDER BY t1.ward";




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
        $sludgecollecion_dataset['label'] = '"Sludge Collection"';
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
        if ($year) {
            $where .= " AND extract(year from fb.created_at) = '$year'";
        }
        if (Auth::user()->hasRole('Service Provider - Admin') || Auth::user()->hasRole('Service Provider - Help Desk')) {

            $whereRawServiceProvider = " AND fb.service_provider_id = " . Auth::user()->service_provider_id;
        } else {
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


    public function getTaxRevenueChart()
    {
        $query = "SELECT dy.value, dy.name, COUNT(build.bin) as c
                FROM taxpayment_info.due_years dy
                LEFT JOIN (
                    SELECT b.bin, case when b.due_year IS NOT NULL then b.due_year else 99 end
                    as due_year from taxpayment_info.tax_payment_status b
                ) AS build
                ON dy.value = build.due_year
                GROUP BY dy.value, dy.name
                ORDER BY dy.value ASC";

        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->name . '"';
            $values[] = $row->c;
        }

        $background_colors = ['"rgba(56, 118, 29, 0.4)"', '"rgba(106, 255, 0, 0.4)"', '"rgba(182, 215, 168, 0.4)"', '"rgba(247, 255, 0, 0.4)"', '"rgba(255, 105, 0, 0.4)"', '"rgba(255, 0, 0, 0.4)"', '"rgba(186, 191, 187)"'];
        $colors = ['"rgba(56, 118, 29, 0.5)"', '"rgba(106, 255, 0, 0.5)"', '"rgba(182, 215, 168, 0.5)"', '"rgba(247, 255, 0, 0.5)"', '"rgba(255, 105, 0, 0.5)"', '"rgba(255, 0, 0, 0.5)"', '"rgba(186, 191, 187)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'background_colors' => $background_colors
        ];

        return $chart;
    }

    public function getWaterSupplyPaymentChart()
    {
        $query = "SELECT dy.value, dy.name, COUNT(build.bin) as c
            FROM watersupply_info.due_years dy
            LEFT JOIN (
                SELECT b.bin, case when b.due_year IS NOT NULL then b.due_year else 99 end
                as due_year from watersupply_info.watersupply_payment_status b
            ) AS build
            ON dy.value = build.due_year
            GROUP BY dy.value, dy.name
            ORDER BY dy.value ASC";

        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->name . '"';
            $values[] = $row->c;
        }

        $background_colors = ['"rgba(56, 118, 29, 0.4)"', '"rgba(106, 255, 0, 0.4)"', '"rgba(182, 215, 168, 0.4)"', '"rgba(247, 255, 0, 0.4)"', '"rgba(255, 105, 0, 0.4)"', '"rgba(255, 0, 0, 0.4)"', '"rgba(186, 191, 187)"'];
        $colors = ['"rgba(56, 118, 29, 0.5)"', '"rgba(106, 255, 0, 0.5)"', '"rgba(182, 215, 168, 0.5)"', '"rgba(247, 255, 0, 0.5)"', '"rgba(255, 105, 0, 0.5)"', '"rgba(255, 0, 0, 0.5)"', '"rgba(186, 191, 187)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'background_colors' => $background_colors
        ];

        return $chart;
    }

    public function getSolidWastePaymentChart()
    {
        $query = "SELECT dy.value, dy.name, COUNT(build.bin) as c
        FROM swm_info.due_years dy
        LEFT JOIN (
			SELECT b.bin, case when b.due_year IS NOT NULL then b.due_year else 99 end
			 as due_year from swm_info.swmservice_payment_status b
		) AS build
        ON dy.value = build.due_year
        GROUP BY dy.value, dy.name
        ORDER BY dy.value ASC";

        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->name . '"';
            $values[] = $row->c;
        }

        $background_colors = ['"rgba(56, 118, 29, 0.4)"', '"rgba(106, 255, 0, 0.4)"', '"rgba(182, 215, 168, 0.4)"', '"rgba(247, 255, 0, 0.4)"', '"rgba(255, 105, 0, 0.4)"', '"rgba(255, 0, 0, 0.4)"', '"rgba(186, 191, 187)"'];
        $colors = ['"rgba(56, 118, 29, 0.5)"', '"rgba(106, 255, 0, 0.5)"', '"rgba(182, 215, 168, 0.5)"', '"rgba(247, 255, 0, 0.5)"', '"rgba(255, 105, 0, 0.5)"', '"rgba(255, 0, 0, 0.5)"', '"rgba(186, 191, 187)"'];

        $chart = [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'background_colors' => $background_colors
        ];

        return $chart;
    }

    public function getSewerLengthPerWard()
    {
        $chart = array();
        $query = "SELECT w.ward, round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(sewers.geom,w.geom),32645))) as numeric ),2) as length
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
    public function getFsmCampaignsPerWard($year = null)
    {
        $chart = array();
        $where = " WHERE fsm.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from fsm.start_date) = '$year'";
        }
        $query = "select count(id) as num_of_fsm_campaigns, w.ward
        from fsm.fsm_campaigns fsm
        right join layer_info.wards w ON fsm.ward = w.ward $where group by w.ward order by w.ward";

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->ward . '"';
            $values[] = $row->num_of_fsm_campaigns;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    public function getFsmCampaignsSupportedBy($year = null)
    {
        $chart = array();
        $where = " WHERE fsm.deleted_at IS NULL";
        if ($year) {
            $where .= " AND extract(year from fsm.start_date) = '$year'";
        }
        $query = "select count(id) as num_of_fsm_campaigns,supported_by
        from fsm.fsm_campaigns fsm $where group by supported_by order by supported_by";

        $results = DB::select($query);
        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . ($row->supported_by == null ? "Unknown" : $row->supported_by) . '"';
            $values[] = $row->num_of_fsm_campaigns;
        }
        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

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
            'filter_by_year' => true,
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
            WHEN fu.name = 'Mixed (Residential + Commercial)' THEN 'Mixed (Residential + Commercial)'
            WHEN fu.name = 'Commercial' THEN 'Commercial'
            WHEN fu.name = 'Industrial' THEN 'Industrial'
            WHEN fu.name = 'Health' THEN 'Health'
            WHEN fu.name = 'Educational' THEN 'Educational'
            WHEN fu.name ILIKE '%Institution%' THEN 'Institution'
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
          WHEN functional_use_name = 'Mixed (Residential + Commercial)' THEN 2
          WHEN functional_use_name = 'Commercial' THEN 3
          WHEN functional_use_name = 'Industrial' THEN 4
          WHEN functional_use_name = 'Health' THEN 5
          WHEN functional_use_name = 'Educational' THEN 6
          WHEN functional_use_name = 'Institution' THEN 7
          ELSE 8
        END";

        $results = DB::select($query);
        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->functional_use_name . '"';
            $values[] = $row->building_count;
        }

        $colors = [
            '"#87CEFA"',  // Light Sky Blue
            '"#6495ED"',  // Cornflower Blue
            '"#1E90FF"',  // Dodger Blue
            '"#4682B4"',  // Steel Blue
            '"#4169E1"',  // Royal Blue
            '"#0000CD"',  // Medium Blue
            '"#000080"',  // Navy Blue
            '"#00BFFF"' // Deep Sky Blue
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

        // Fetch containment types dynamically
        $containmentTypesQuery = "SELECT
            ct.map_display AS containment_type,
            COUNT(c.id) AS containment_count
                FROM
                    fsm.containments c
                JOIN
                    fsm.containment_types ct ON c.type_id = ct.id
                WHERE
                    c.deleted_at IS NULL
                    AND ct.dashboard_display = true
                GROUP BY
                    ct.map_display
                ORDER BY
                    containment_count DESC";

        $containmentTypesResult = DB::select($containmentTypesQuery);

        $containmentTypes = [];
        foreach ($containmentTypesResult as $ctype) {
            $containmentTypes[$ctype->containment_type] = $ctype->containment_type;
        }

        // Query for data
        $query = "SELECT
        TO_CHAR(i, 'YYYY') AS year,
        ct.map_display AS containment_type,
        COUNT(c.id) AS count
            FROM
                GENERATE_SERIES(NOW() - INTERVAL '4 years', NOW(), INTERVAL '1 year') AS i
            LEFT JOIN
                fsm.emptyings e ON TO_CHAR(e.emptied_date, 'YYYY') = TO_CHAR(i, 'YYYY')
            LEFT JOIN
                fsm.applications a ON e.application_id = a.id
            LEFT JOIN
                fsm.containments c ON a.containment_id = c.id
            LEFT JOIN
                fsm.containment_types ct ON c.type_id = ct.id
            WHERE
                e.deleted_at IS NULL $whereRawServiceProvider
                AND ct.dashboard_display = true
            GROUP BY
                year, containment_type
            ORDER BY
                year, containment_type";

        $results = DB::select($query);

        // Format data for chart
        $data = [];
        foreach ($results as $row) {
            $data[$row->year][$row->containment_type] = $row->count;
        }

        $years = array_keys($data);
        $labels = array_map(function ($year) {
            return '"' . $year . '"';
        }, $years);

        $colors = [
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(247, 202, 24, 0.8)"',
            '"rgba(129, 207, 224,0.8)"',
            '"rgba(228, 241, 254, 1)"',
            '"rgba(200, 247, 197, 1)"',
            '"rgba(68, 108, 179, 0.5)"',
            '"rgba(255, 148, 112, 0.2)"',
            '"rgba(178, 222, 39, 0.8)"',
            '"rgba(77, 175, 124, 1)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
        ];
        $colorsArr = array_slice($colors, 0, count($results), true);

        $datasets = [];
        $count = 0;
        foreach ($containmentTypes as $key1 => $value1) {
            $dataset = [
                'label' => '"' . $value1 . '"',
                'color' => $colors[$count++],
                'data' => [],
            ];
            foreach ($years as $year) {
                $dataset['data'][] = isset($data[$year][$key1]) ? $data[$year][$key1] : '0';
            }
            $datasets[] = $dataset;
        }

        $chart = [
            'labels' => $labels,
            'datasets' => $datasets,
        ];

        return $chart;
    }

    public function getContainmentTypesByLanduse()
    {
        // Define the mapping of detailed types to broader categories
        $containmentTypeMapping = [
            'Permeable/ Unlined Pit' => 'Permeable/ Unlined Pit/Holding Tank',
            'Septic Tank Connected to Drain Network' => 'Septic Tank',
            'Septic Tank Connected to Open Ground' => 'Septic Tank',
            'Septic Tank Connected to Sewere Network' => 'Septic Tank',
            'Septic Tank Connected to Soak Pit' => 'Septic Tank',
            'Septic Tank Connected to Water Body' => 'Septic Tank',
            'Single Pit connected to a Soak Pit' => 'Septic Tank',
            'Septic Tank with Unkown Outlet Connection' => 'Septic Tank',
            'Single Pit connected to Open Ground' => 'Septic Tank',
            'Lined Pit connected to Drain Network' => 'Lined Pit',
            'Lined Pit connected to Water Body' => 'Lined Pit',
            'Lined Pit connected to Open Ground' => 'Lined Pit',
            'Lined Pit with Unkown Outlet Connection' => 'Lined Pit',
            'Lined Pit without Outlet' => 'Lined Pit'
        ];

        // Get landuse data
        $landuses = Landuse::orderBy('class')->pluck('class', 'class')->toArray();

        // Get containment types data from the database
        $containmentTypes = DB::table('fsm.containments as c')
            ->join('fsm.containment_types as ct', 'c.type_id', '=', 'ct.id')
            ->select(DB::raw('ct.map_display AS containment_type, COUNT(c.id) AS containment_count'))
            ->whereNull('c.deleted_at')
            ->where('ct.dashboard_display', true)
            ->groupBy('ct.map_display')
            ->orderBy('containment_count', 'DESC')
            ->get();

        // Aggregate containment counts by broader categories
        $typeCounts = [];
        foreach ($containmentTypes as $type) {
            // Preserve original type for labels
            $originalType = $type->containment_type;

            // Find the mapped broader type
            $mappedType = $containmentTypeMapping[$type->containment_type] ?? $type->containment_type;

            if (!isset($typeCounts[$mappedType])) {
                $typeCounts[$mappedType] = 0;
            }
            $typeCounts[$mappedType] += $type->containment_count;

            // Store the original type data separately
            if (!isset($data[$originalType])) {
                $data[$originalType] = [];
                $values[$originalType] = [];
            }
            $data[$originalType][$originalType] = intval($type->containment_count);
            $values[$originalType][$originalType] = $type->containment_count;
        }

        // Query landuse summary data
        $results = DB::select("SELECT class, type, count, totalclass, percentage_proportion FROM public.landuse_summaryforchart");

        // Organize data for chart
        $data = [];
        $values = [];
        foreach ($results as $row) {
            // Aggregate counts based on broader types
            $mappedType = $containmentTypeMapping[$row->type] ?? $row->type;
            if (!isset($data[$mappedType])) {
                $data[$mappedType] = [];
                $values[$mappedType] = [];
            }
            $data[$mappedType][$row->class] = intval($row->percentage_proportion);
            $values[$mappedType][$row->class] = $row->count;
        }

        // Prepare labels and colors
        $labels = array_map(fn($landuse) => "\"$landuse\"", $landuses);
        $colors = [
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(247, 202, 24, 0.8)"',
            '"rgba(129, 207, 224,0.8)"',
            '"rgba(228, 241, 254, 1)"',
            '"rgba(200, 247, 197, 1)"',
            '"rgba(68, 108, 179, 0.5)"',
            '"rgba(255, 148, 112, 0.2)"',
            '"rgba(178, 222, 39, 0.8)"',
            '"rgba(77, 175, 124, 1)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"'
        ];

        $datasets = [];
        $count = 0;

        // Prepare datasets for chart
        foreach ($typeCounts as $type => $totalCount) {
            $datasets[] = [
                'label' => "\"$type\"",
                'color' => $colors[$count++ % count($colors)],
                'data' => array_map(fn($landuse) => $data[$type][$landuse] ?? '0', $landuses),
                'value' => array_map(fn($landuse) => $values[$type][$landuse] ?? '0', $landuses)
            ];
        }

        // Prepare chart data
        $chart = [
            'labels' => $labels,
            'datasets' => $datasets
        ];

        return $chart;
    }


    public function getContainmentTypesByBldgUseResidentials()
    {

        $chart = array();

        $wards = Ward::orderBy('ward')->pluck('ward', 'ward')->toArray();
        $containment_types = DB::select("SELECT
            ct.map_display AS containment_type,
            COUNT(c.id) AS containment_count
            FROM fsm.containments c
            JOIN fsm.containment_types ct ON c.type_id = ct.id
            WHERE c.deleted_at IS NULL AND ct.dashboard_display = true
            GROUP BY ct.map_display
            ORDER BY containment_count DESC");
        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }

        $query = 'SELECT a.ward, a.type, a.count, b.totalward,
            ROUND(a.count * 100/b.totalward::numeric, 1) as percentage_proportion
                    FROM ( SELECT ct.map_display AS type, count(c.*), b.ward
                    from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                    join fsm.containments c on bc.containment_id = c.id
                    join fsm.containment_types ct on c.type_id = ct.id
                    where b.functional_use_id = 1 AND b.deleted_at IS NULL group by ct.map_display, b.ward
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
            $data[$row->type][$row->ward] = $row->percentage_proportion;
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
            '"rgba(247, 202, 24, 0.8)"',
            '"rgba(129, 207, 224,0.8)"',
            '"rgba(228, 241, 254, 1)"',
            '"rgba(200, 247, 197, 1)"',
            '"rgba(68, 108, 179, 0.5)"',
            '"rgba(255, 148, 112, 0.2)"',
            '"rgba(178, 222, 39, 0.8)"',
            '"rgba(77, 175, 124, 1)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',


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

        // Get building uses
        $bldguses = FunctionalUse::orderBy('name')->pluck('name', 'name')->toArray();

        // Get containment types
        $containment_types = DB::select("SELECT
        ct.map_display AS containment_type,
        COUNT(c.id) AS containment_count
        FROM fsm.containments c
        JOIN fsm.containment_types ct ON c.type_id = ct.id
        WHERE c.deleted_at IS NULL AND ct.dashboard_display = true
        GROUP BY ct.map_display
        ORDER BY containment_count DESC");

        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->containment_type] = $ctype->containment_type;
        }

        // Main query to get the percentage proportion
        $query = 'SELECT a.bldg_name, a.type, a.count, b.total_bldguse,
        ROUND(a.count * 100/b.total_bldguse::numeric, 2) as percentage_proportion
                FROM ( SELECT ct.map_display AS type, count(c.*), bldg.name as bldg_name
                from building_info.buildings b join building_info.build_contains bc on b.bin = bc.bin
                join fsm.containments c on bc.containment_id = c.id
                join building_info.functional_uses bldg on bldg.id = b.functional_use_id
                join fsm.containment_types ct on c.type_id = ct.id
                where b.functional_use_id is not null AND b.deleted_at IS NULL group by ct.map_display, b.functional_use_id, bldg.name
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
        $sum_per_bldg = array();

        // Build data and sum percentages for each building use
        foreach ($results as $row) {
            $data[$row->type][$row->bldg_name] = $row->percentage_proportion;
            $values[$row->type][$row->bldg_name] = $row->count;
            if (!isset($sum_per_bldg[$row->bldg_name])) {
                $sum_per_bldg[$row->bldg_name] = 0;
            }
            $sum_per_bldg[$row->bldg_name] += $row->percentage_proportion;
        }

        // Normalize percentages to ensure they don't exceed 100%
        foreach ($data as $type => $bldg_data) {
            foreach ($bldg_data as $bldg_name => $percentage) {
                if ($sum_per_bldg[$bldg_name] > 100) {
                    $data[$type][$bldg_name] = ($percentage / $sum_per_bldg[$bldg_name]) * 100;
                }
            }
        }

        // Prepare chart data
        $labels = array_map(function ($bldguse) {
            return '"' . $bldguse . '"';
        }, $bldguses);

        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(247, 202, 24, 0.8)"',
            '"rgba(129, 207, 224,0.8)"',
            '"rgba(228, 241, 254, 1)"',
            '"rgba(200, 247, 197, 1)"',
            '"rgba(68, 108, 179, 0.5)"',
            '"rgba(255, 148, 112, 0.2)"',
            '"rgba(178, 222, 39, 0.8)"',
            '"rgba(77, 175, 124, 1)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
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



    // public function getContainmentTypesByStructypes()
    // {
    //     $chart = array();

    //     $results_obj = DB::select('SELECT st.type AS st_type, structure_type_id
    //             FROM building_info.buildings b
    //             LEFT JOIN building_info.structure_types st ON b.structure_type_id = st.id
    //             WHERE b.structure_type_id IS NOT NULL AND b.deleted_at IS NULL
    //             GROUP BY b.structure_type_id, st.type');

    //     foreach ($results_obj as $value) {
    //         $structtypes[$value->structure_type_id] = $value->st_type;
    //     }

    //     $containment_types = DB::select("SELECT
    //         ct.map_display AS containment_type,
    //         COUNT(c.id) AS containment_count
    //         FROM fsm.containments c
    //         JOIN fsm.containment_types ct ON c.type_id = ct.id
    //         WHERE c.deleted_at IS NULL AND ct.dashboard_display = true
    //         GROUP BY ct.map_display
    //         ORDER BY containment_count DESC");

    //     $types = array();
    //     foreach ($containment_types as $ctype) {
    //         $types[$ctype->containment_type] = $ctype->containment_type;
    //     }

    //     $query = 'SELECT st.type AS st_type, a.structure_type_id, a.map_display AS type, a.count, b.totalstructure_type,
    //     ROUND(a.count * 100/b.totalstructure_type::numeric, 2 ) AS percentage_proportion
    //             FROM ( SELECT b.structure_type_id, ct.map_display, COUNT(c.*) AS count
    //                  FROM building_info.buildings b
    //                  JOIN building_info.build_contains bc ON b.bin = bc.bin
    //                  JOIN fsm.containments c ON bc.containment_id = c.id
    //                  JOIN fsm.containment_types ct ON c.type_id = ct.id
    //                  WHERE structure_type_id IS NOT NULL
    //                  GROUP BY ct.map_display, b.structure_type_id
    //             ) a
    //             JOIN ( SELECT COUNT(c.*) AS totalstructure_type, b.structure_type_id
    //                 FROM building_info.buildings b
    //                 JOIN building_info.build_contains bc ON b.bin = bc.bin
    //                 JOIN fsm.containments c ON bc.containment_id = c.id
    //                 WHERE structure_type_id IS NOT NULL
    //                 GROUP BY b.structure_type_id
    //             ) b ON b.structure_type_id = a.structure_type_id
    //             JOIN building_info.structure_types st ON a.structure_type_id = st.id
    //             ORDER BY a.structure_type_id ASC';

    //     $results = DB::select($query);

    //     $data = array();
    //     $values = array();
    //     foreach ($results as $row) {
    //         $data[$row->type][$row->structure_type_id] = $row->totalstructure_type;
    //         $values[$row->type][$row->structure_type_id] = $row->count;
    //     }

    //     $labels = array_map(function ($structtype) {
    //         return '"' . $structtype . '"';
    //     }, $structtypes);

    //     $colors = array(
    //         '"rgba(32, 139, 58, 0.8)"',
    //         '"rgba(153, 202, 60, 0.8)"',
    //         '"rgba(252, 236, 82, 0.8)"',
    //         '"rgba(251, 176, 64, 0.8)"',
    //         '"rgba(247, 142, 49, 0.8)"',
    //         '"rgba(247, 202, 24, 0.8)"',
    //         '"rgba(129, 207, 224,0.8)"',
    //         '"rgba(228, 241, 254, 1)"',
    //         '"rgba(200, 247, 197, 1)"',
    //         '"rgba(68, 108, 179, 0.5)"',
    //         '"rgba(255, 148, 112, 0.2)"',
    //         '"rgba(178, 222, 39, 0.8)"',
    //         '"rgba(77, 175, 124, 1)"',
    //         '"rgba(251, 176, 64, 0.8)"',
    //         '"rgba(247, 142, 49, 0.8)"',
    //     );

    //     $colorsArr = array_slice($colors, 0, count($results), true);
    //     $datasets = array();
    //     $count = 0;
    //     foreach ($types as $key1 => $value1) {
    //         $dataset = array();
    //         $dataset['label'] = '"' . $value1 . '"';
    //         $dataset['color'] = $colors[$count++];
    //         $dataset['data'] = array();
    //         $dataset['value'] = array();
    //         foreach ($structtypes as $key2 => $value2) {
    //             $dataset['data'][] = isset($data[$key1][$key2]) ? $data[$key1][$key2] : '0';
    //             $dataset['value'][] = isset($values[$key1][$key2]) ? $values[$key1][$key2] : '0';
    //         }
    //         $datasets[] = $dataset;
    //     }

    //     $chart = array(
    //         'labels' => $labels,
    //         'datasets' => $datasets
    //     );

    //     return $chart;
    // }



    public function getContainmentTypesPerWard()
    {
        $chart = array();

        // Retrieve wards
        $wards = Ward::orderBy('ward')->pluck('ward', 'ward')->toArray();

        // Retrieve containment types with dashboard_display
        $containment_types = DB::select("SELECT
    ct.map_display AS type,
    COUNT(c.id) AS containment_count
    FROM
        fsm.containments c
    JOIN
        fsm.containment_types ct ON c.type_id = ct.id
    WHERE
        c.deleted_at IS NULL
        AND ct.dashboard_display = true
    GROUP BY
        ct.map_display
    ORDER BY
        containment_count DESC;
    ");

        $types = array();
        foreach ($containment_types as $ctype) {
            $types[$ctype->type] = $ctype->type;
        }

        // Updated query to include the new map_display column
        $query = "SELECT a.ward, a.type, a.count, b.totalward,
    (a.count * 100/b.totalward::numeric) as percentage_proportion
    FROM (
        SELECT b.ward, ct.map_display AS type, count(c.*) as count
        FROM building_info.buildings b
        JOIN building_info.build_contains bc ON b.bin = bc.bin
        JOIN fsm.containments c ON bc.containment_id = c.id
        JOIN fsm.containment_types ct ON c.type_id = ct.id
        WHERE c.deleted_at IS NULL
        GROUP BY b.ward, ct.map_display
    ) a
    JOIN (
        SELECT ward, count(b.ward) AS totalward
        FROM building_info.buildings b
        JOIN building_info.build_contains bc ON b.bin = bc.bin
        JOIN fsm.containments c ON bc.containment_id = c.id
        WHERE c.deleted_at IS NULL
        GROUP BY b.ward
    ) b ON b.ward = a.ward
    ORDER BY a.ward ASC";

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

        // Define colors for chart
        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(247, 202, 24, 0.8)"',
            '"rgba(129, 207, 224,0.8)"',
            '"rgba(228, 241, 254, 1)"',
            '"rgba(200, 247, 197, 1)"',
            '"rgba(68, 108, 179, 0.5)"',
            '"rgba(255, 148, 112, 0.2)"',
            '"rgba(178, 222, 39, 0.8)"',
            '"rgba(77, 175, 124, 1)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
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
            CASE
                WHEN ct.map_display IS NOT NULL THEN ct.map_display
                ELSE ct.type
            END AS containment_type,
            COUNT(c.id) AS containment_count
            FROM
             fsm.containments c
            JOIN
              fsm.containment_types ct ON c.type_id = ct.id
            WHERE
                c.deleted_at IS NULL
            AND (
                ct.dashboard_display = true
                OR (
                    ct.map_display IS NOT NULL
                    AND c.type_id IN (
                        SELECT id FROM fsm.containment_types WHERE map_display = ct.map_display
                    )
                )
            )
            GROUP BY
                containment_type
            ORDER BY
                containment_count DESC;";

        $results = DB::select($query);

        $labels = array();
        $values = array();

        foreach ($results as $row) {
            $labels[] = '"' . $row->containment_type . '"';
            $values[] = $row->containment_count;
        }

        $colors = array(
            '"rgba(32, 139, 58, 0.8)"',
            '"rgba(153, 202, 60, 0.8)"',
            '"rgba(252, 236, 82, 0.8)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
            '"rgba(247, 202, 24, 0.8)"',
            '"rgba(129, 207, 224,0.8)"',
            '"rgba(228, 241, 254, 1)"',
            '"rgba(200, 247, 197, 1)"',
            '"rgba(68, 108, 179, 0.5)"',
            '"rgba(255, 148, 112, 0.2)"',
            '"rgba(178, 222, 39, 0.8)"',
            '"rgba(77, 175, 124, 1)"',
            '"rgba(251, 176, 64, 0.8)"',
            '"rgba(247, 142, 49, 0.8)"',
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

        $where = " WHERE a.deleted_at IS NULL";
        if ($year) {
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
            $where
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
        foreach ($results as $row) {
            $data[$row->spname][$row->month] = $row->count;
        }
        $labels = array_map(function ($month) {
            return '"' . $month . '"';
        }, $label);
        /*$colors = array(
                "rgba(0, 144, 211, 0.7)",
                "rgba(255, 185, 100, 0.7)",
                "rgba(150, 200, 70, 0.7)",
                "rgba(255, 100, 120, 0.7)"

                );*/

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
        foreach ($types as $key1 => $value1) {
            $dataset = array();
            $dataset['label'] = '"' . $value1 . '"';
            $dataset['color'] = $colors[$count++];
            $dataset['data'] = array();
            foreach ($labels as $key2 => $value2) {
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
        $current = '';
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

    public function getRoadLengthPerWardChart()
    {
        $chart = array();
        $query = "SELECT w.ward, round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(roads.geom,w.geom),32645))) as numeric ),2) as length
        FROM layer_info.wards w, utility_info.roads roads
        WHERE roads.deleted_at IS NULL
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

    public function getWaterborneCasesChart($year = null)
    {
        $query = "SELECT TO_CHAR(i, 'YYYY') AS year,
        COALESCE(SUM(w.total_no_of_cases), 0) AS total_no_of_cases
        FROM GENERATE_SERIES(NOW() - INTERVAL '4 years', NOW(), INTERVAL '1 year') AS i
        LEFT JOIN public_health.yearly_waterborne_cases w
            ON w.year = EXTRACT(YEAR FROM i) AND w.deleted_at IS NULL
        GROUP BY TO_CHAR(i, 'YYYY')
        ORDER BY year";

        $results = DB::select($query);

        $labels = array();
        $values = array();
        foreach ($results as $row) {
            $labels[] = '"' . $row->year . '"';
            $values[] = $row->total_no_of_cases;
        }

        $chart = array(
            'labels' => $labels,
            'values' => $values,
        );

        return $chart;
    }

    public function getBuildingSanitationSystem()
    {
        return DB::table('building_info.sanitation_systems as s')
            ->leftJoin('building_info.buildings as b', 's.id', '=', 'b.sanitation_system_id')
            ->select('s.sanitation_system', 's.icon_name', DB::raw('COUNT(b.bin) as bin_count'))
            ->where('s.dashboard_display', true)
            ->where('b.deleted_at', null)
            ->groupBy('s.sanitation_system', 's.id')
            ->orderBy('s.id', 'asc')
            ->get();
    }

    public function getBuildingSanitationSystemOthers()
    {

        $results = DB::table('building_info.sanitation_systems as s')
            ->leftJoin('building_info.buildings as b', function ($join) {
                $join->on('s.id', '=', 'b.sanitation_system_id')
                    ->whereNull('b.deleted_at');
            })
            ->select(DB::raw('COUNT(b.bin) as bin_count, s.sanitation_system as sanitation_system_name'))
            ->where('s.dashboard_display', false)
            ->whereNotIn('s.sanitation_system', ['Shared Septic Tank'])

            ->groupBy('s.sanitation_system', 's.id')
            ->get();

        $sanitation_systems_arr = [];
        foreach ($results as $result) {
            $sanitation_systems_arr[] = $result->sanitation_system_name;
        }
        $sanitation_systems = implode(",\n", $sanitation_systems_arr);

        return ['total' => $results->sum('bin_count'), 'sanitation_system_names' => $sanitation_systems];
    }

    public function getSanitationSystemsChart()
    {

        $chart = array();

        $sanitationSystemDashboardDisplayTrue = DB::table('building_info.sanitation_systems as s')
            ->leftJoin('building_info.buildings as b', 's.id', '=', 'b.sanitation_system_id')
            ->select('s.sanitation_system', DB::raw('COUNT(b.bin) as bin_count'))
            ->where('s.dashboard_display', true)
            ->where('b.deleted_at', null)
            ->groupBy('s.sanitation_system', 's.id')
            ->orderBy('s.id', 'asc')
            ->get();

        $sanitationSystemsOne = [];
        foreach ($sanitationSystemDashboardDisplayTrue as $result) {
            $sanitationSystemsOne[] = ['bin_count' => $result->bin_count,  'sanitation_system_name' => $result->sanitation_system];
        }

        $sanitationSystemDashboardDisplayFalse = DB::table('building_info.sanitation_systems as s')
            ->leftJoin('building_info.buildings as b', function ($join) {
                $join->on('s.id', '=', 'b.sanitation_system_id')
                    ->whereNull('b.deleted_at');
            })
            ->select(DB::raw('COUNT(b.bin) as bin_count, s.sanitation_system as sanitation_system_name'))
            ->where('s.dashboard_display', false)
            ->whereNotIn('s.sanitation_system', ['Shared Septic Tank'])
            ->groupBy('s.sanitation_system', 's.id')
            ->get();

        $sanitationSystemOthers = [[
            'bin_count' => $sanitationSystemDashboardDisplayFalse->sum('bin_count'),
            'sanitation_system_name' => 'Others'
        ]];

        $sanitationSystems = array_merge($sanitationSystemsOne, $sanitationSystemOthers);

        $labels = array();
        $values = array();
        foreach ($sanitationSystems as $row) {
            $labels[] = '"' . trim($this->getSubstringBeforeParenthesis($row['sanitation_system_name'])) . '"'; // Add trim here
            $values[] = $row['bin_count'];
        }


        $chart = array(
            'labels' => $labels,
            'values' => $values,
            'filter_by_year' => true,
        );
        //  dd(  $chart,  $sanitationSystems, $sanitationSystemDashboardDisplayTrue,$sanitationSystemDashboardDisplayFalse);
        return $chart;
    }

    function getSubstringBeforeParenthesis($string)
    {
        $position = strpos($string, '(');
        if ($position !== false) {
            return substr($string, 0, $position);
        }
        return $string; // Return the original string if no '(' found
    }

    public function swmPresencebyWard()
    {
        $results = \DB::select("
        SELECT
            w.ward,
            COUNT(CASE WHEN b.swm_customer_id IS NOT NULL THEN 1 END) AS buildings_with_swm_customer_id,
            COUNT(CASE WHEN b.swm_customer_id IS NULL THEN 1 END) AS buildings_without_swm_customer_id
        FROM
            layer_info.wards w
        LEFT JOIN
            building_info.buildings b ON w.ward = b.ward
        GROUP BY
            w.ward
        ORDER BY
            w.ward
    ");

        return $results;
    }

    public function taxCodePresencebyWard()
    {
        $results = \DB::select("
       SELECT
            w.ward,
            COUNT(CASE WHEN b.tax_code IS NOT NULL THEN 1 END) AS buildings_with_tax_code,
            COUNT(CASE WHEN b.tax_code IS NULL THEN 1 END) AS buildings_without_tax_code
        FROM
            layer_info.wards w
        LEFT JOIN
            building_info.buildings b ON w.ward = b.ward
        GROUP BY
            w.ward
        ORDER BY
            w.ward
    ");

        return $results;
    }


    public function waterSupplyPipeCodePresenceByWard()
    {
        $results = \DB::select("
           SELECT
                w.ward,
                COUNT(CASE WHEN b.watersupply_pipe_code IS NOT NULL THEN 1 END) AS buildings_with_pipe_code,
                COUNT(CASE WHEN b.watersupply_pipe_code IS NULL THEN 1 END) AS buildings_without_pipe_code
            FROM
                layer_info.wards w
            LEFT JOIN
                building_info.buildings b ON w.ward = b.ward
            GROUP BY
                w.ward
            ORDER BY
                w.ward
        ");

        return $results;
    }



    public function treatmentPlantTestResultsByYear()
    {
        $results = \DB::select("
    WITH standards AS (
    SELECT
        bod_standard::numeric AS bod_standard,
        tss_standard::numeric AS tss_standard,
        ecoli_standard::numeric AS ecoli_standard
    FROM
        public.treatment_plant_performance_efficiency_test_settings
    WHERE
        deleted_at IS NULL
    LIMIT 1
)
SELECT
    tp.name AS treatment_plant_name,
    EXTRACT(YEAR FROM tpt.date) AS year,
    COUNT(*) AS total_count,
    COUNT(*) - COALESCE(SUM(CASE
        WHEN tpt.bod <= (SELECT bod_standard FROM standards)
            AND tpt.tss <= (SELECT tss_standard FROM standards)
            AND tpt.ecoli <= (SELECT ecoli_standard FROM standards)
        THEN 1
        ELSE 0
    END)::Numeric, 0) AS StandardMeet,
    COALESCE(SUM(CASE
        WHEN tpt.bod <= (SELECT bod_standard FROM standards)
            AND tpt.tss <= (SELECT tss_standard FROM standards)
            AND tpt.ecoli <= (SELECT ecoli_standard FROM standards)
        THEN 1
        ELSE 0
    END)::Numeric, 0) AS BelowStandard
FROM
    fsm.treatmentplant_tests tpt
JOIN
    fsm.treatment_plants tp ON tpt.treatment_plant_id = tp.id
GROUP BY
    tp.name, year
ORDER BY
    tp.name, year;

    ");


        return $results;
    }
}
