<?php

namespace App\Services\Fsm;

use App\Http\Controllers\HomeController;
use DB;
use App\Models\LayerInfo\LandUse;
use App\Models\Fsm\ServiceProvider;
use App\Models\LayerInfo\Ward;
use App\Models\BuildingInfo\FunctionalUse;
use Illuminate\Support\Facades\Auth;
use App\Models\Fsm\Emptying;
use App\Models\Fsm\Application;
use App\Models\Fsm\KpiTarget;
use Carbon\Carbon;
use App\Models\Fsm\SludgeCollection;
use App\Models\Fsm\Feedback;
use App\Models\Fsm\Quarters;

class KpiDashboardService
{
    /**
     * Retrieves quarterly charts for (FSCR) from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for FSCR.
     */
    public function getFscrChartsQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to FSCR
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return  $data['indicator'] === 'Faecal Sludge Collection Ratio (FSCR)';
        });

        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement')
        ];
        return $chart;
    }

     /**
     * Retrieves quarterly charts for inclusion from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for inclusion.
     */
    public function getInclusionQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to inclusion
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return  $data['indicator'] === 'Inclusion';
        });

        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement')
        ];
        return $chart;
    }

      /**
     * Retrieves quarterly charts for response time from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for response time.
     */
    public function getResponseTimeQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to response time
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return  $data['indicator'] === 'Response Time';
        });

        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement')
        ];
        return $chart;
    }

    /**
     * Retrieves quarterly charts for application response efficiency from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for application response efficiency.
     */
    public function getApplicationResponseEfficiencyQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to application response efficiency
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return  $data['indicator'] === 'Application Response Efficiency';
        });

        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement')
        ];
        return $chart;
    }
    
    /**
     * Retrieves quarterly charts for safe desludging from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for safe desludging.
     */
    public function getSafeDesludgingQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to safe desludging
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return $data['indicator'] === 'Safe Desludging';
        });
    
        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement')
        ];
        return $chart;
       
    }

     /**
     * Retrieves quarterly charts for PPE Compliance from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for PPE Complaince.
     */
    public function getPpeComplianceQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to PPE complaince
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return $data['indicator'] === 'PPE Compliance';
        });
    
        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement'),
            
        ];
        return $chart;
               
    }

     /**
     * Retrieves quarterly charts for customer Satisfaction  from provided key performance data.
     *
     * @param array $keyPerformanceData The key performance data array.
     * @return array Quarter-wise chart data for customer Satisfaction.
     */
    public function getcustomerSatisfactionQuarter($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related tocustomer Satisfaction
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
            return $data['indicator'] === 'Customer Satisfaction';
        });
    
        $chart = [
            'labels' => array_column($filteredData, 'quartername'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement'),
            
        ];
      
        return $chart;
    }

    /**
     * Retrieves application response efficiency data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getApplicationResponseEfficiency($keyPerformanceData)
    {
        // Filter the key performance data to include only entries related to application response efficiency
      $filteredData = array_filter($keyPerformanceData[0], function ($data) {
         
          return $data['indicator'] === 'Application Response Efficiency';
       });

        $chart = [
            'labels' => array_column($filteredData, 'year'),
            'target_values' => array_column($filteredData, 'target'),
            'achievement_values' => array_column($filteredData, 'achievement')
        ];
        return $chart;
               
    }

     /**
     * Retrieves safe desludging data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getSafeDesludging($keyPerformanceData){

        // Filter the key performance data to include only entries related to safe desludging
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
           
            return $data['indicator'] === 'Safe Desludging';
         });
  
          $chart = [
              'labels' => array_column($filteredData, 'year'),
              'target_values' => array_column($filteredData, 'target'),
              'achievement_values' => array_column($filteredData, 'achievement')
          ];
          return $chart;
               
    }

     /**
     * Retrieves customer satisfaction data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getCustomerSatisfaction($keyPerformanceData){

        // Filter the key performance data to include only entries related to customer Satisfaction
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
           
            return $data['indicator'] === 'Customer Satisfaction';
         });
  
          $chart = [
              'labels' => array_column($filteredData, 'year'),
              'target_values' => array_column($filteredData, 'target'),
              'achievement_values' => array_column($filteredData, 'achievement')
          ];
          return $chart;
               
    }

     /**
     * Retrieves PPE Complaince data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getPpeCompliance($keyPerformanceData){

        // Filter the key performance data to include only entries related to PPE Complaince
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
           
            return $data['indicator'] === 'PPE Compliance';
         });
  
          $chart = [
              'labels' => array_column($filteredData, 'year'),
              'target_values' => array_column($filteredData, 'target'),
              'achievement_values' => array_column($filteredData, 'achievement')
          ];
          return $chart;
    }

     /**
     * Retrieves FSCR data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getFscr($keyPerformanceData){

        // Filter the key performance data to include only entries related to FSCR
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
           
            return $data['indicator'] === 'Faecal Sludge Collection Ratio (FSCR)';
         });
  
          $chart = [
              'labels' => array_column($filteredData, 'year'),
              'target_values' => array_column($filteredData, 'target'),
              'achievement_values' => array_column($filteredData, 'achievement')
          ];
          return $chart;
    }

     /**
     * Retrieves response time data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getResponseTime($keyPerformanceData){

        // Filter the key performance data to include only entries related to response time
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
           
            return $data['indicator'] === 'Response Time';
         });
          $chart = [
              'labels' => array_column($filteredData, 'year'),
              'target_values' => array_column($filteredData, 'target'),
              'achievement_values' => array_column($filteredData, 'achievement')
          ];
          return $chart;
    }

     /**
     * Retrieves inclusion data from provided key performance data.
     *
     * @param array $keyPerformanceData The array containing key performance data.
     * @return array Chart data.
     */
    public function getInclusion($keyPerformanceData){

        // Filter the key performance data to include only entries related to inclusion
        $filteredData = array_filter($keyPerformanceData[0], function ($data) {
           
            return $data['indicator'] === 'Inclusion';
         });
  
          $chart = [
              'labels' => array_column($filteredData, 'year'),
              'target_values' => array_column($filteredData, 'target'),
              'achievement_values' => array_column($filteredData, 'achievement')
          ];
          return $chart;
    }
}
