<?php

namespace Database\Seeders\Fsm;

use Illuminate\Database\Seeder;

use DB;

class TreatmentPlantEfficiencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('treatment_plant_performance_efficiency_test_settings')->insert([
            'tss_standard' => 60,
            'ecoli_standard' => 1000,
            'ph_min' => 6,
            'ph_max' => 9,
            'bod_standard' => 50,
            'created_at' => now(),
        ]);
    }
}
