<?php

namespace Database\Seeders\Fsm;

use App\Models\Fsm\KeyPerformanceIndicator;
use Illuminate\Database\Seeder;
use DB;

class KeyPerformanceIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indicators = array(
            [ 1 , 'Application Response Efficiency' ],
            [ 2 , 'Customer Satisfaction' ],
            [ 3 , 'PPE Compliance' ],
            [ 4 , 'Safe Desludging' ],
            [ 5 , 'Faecal Sludge Collection Ratio (FSCR)' ],
            [ 6 , 'Response Time' ],
            [ 7 , 'Inclusion' ]
        );

        foreach ($indicators as $indicator){
            $existStructureType =  DB::table('fsm.key_performance_indicators')
                 ->where('indicator', $indicator[1])
                 ->first();
         if(!$existStructureType) {
            KeyPerformanceIndicator::insert([
             'id' => $indicator[0],
             'indicator' => $indicator[1]
         ]);
         }
        }
    }
}
