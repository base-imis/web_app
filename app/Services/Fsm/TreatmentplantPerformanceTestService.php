<?php

namespace App\Services\Fsm;

use Illuminate\Support\Collection;
use Illuminate\Session\SessionManager;
use Yajra\DataTables\DataTables;
use App\Models\Fsm\TreatmentPlantPerformanceTest;



class TreatmentplantPerformanceTestService {

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
            $performance_test = TreatmentPlantPerformanceTest::firstOrNew();
        
            $performance_test->tss_standard = $data['tss_standard'] ?? null;
            $performance_test->ecoli_standard = $data['ecoli_standard'] ?? null;
            $performance_test->ph_min = $data['ph_min'] ?? null;
            $performance_test->ph_max = $data['ph_max'] ?? null;
            $performance_test->bod_standard = $data['bod_standard'] ?? null;
        
            $performance_test->save();
        
        
     }
}
