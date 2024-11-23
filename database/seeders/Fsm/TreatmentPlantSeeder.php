<?php

namespace Database\Seeders\Fsm;

use App\Models\Fsm\TreatmentPlant;
use Illuminate\Database\Seeder;

class TreatmentPlantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TreatmentPlant::factory(5)->create();
    }
}
