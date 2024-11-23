<?php

namespace App\Services\Fsm;

use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Yajra\DataTables\DataTables;
use App\Models\Fsm\TreatmentPlantPerformanceTest;
use App\Models\Fsm\CwisSetting;

class CwisSettingService
{

    protected $session;
    protected $instance;

    /**
     * Constructs a new LandfillSite object.
     *
     *
     */
    public function __construct()
    {
        /*Session code
        ....
         here*/
    }


    /**
     * Store or update a newly created resource in storage.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */


    public function storeOrUpdate($data)
    {
        $performance_test = CwisSetting::where('category', 'cwis_setting')->get();
        $updates = [
            "average_water_consumption_lpcd" => $data['average_water_consumption_lpcd'],
            "waste_water_conversion_factor" => $data['waste_water_conversion_factor'],
            "greywater_conversion_factor_connected_to_sewer" => $data['greywater_conversion_factor_connected_to_sewer'],
            "greywater_conversion_factor_not_connected_to_sewer" => $data['greywater_conversion_factor_not_connected_to_sewer']
        ];
        foreach ($updates as $key => $value) {
            $setting = $performance_test->where('name', $key)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
        }
    }
}
