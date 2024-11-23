<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\WaterSource;
use DB;

class WaterSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [1, 'Jar Water'],
            [2, 'Rainwater'],
            [3, 'Spring/River/Canal'],
            [4, 'Others'],
            [5, 'Private Tanker water'],
            [6, 'Tube well'],
            [7, 'Deep boring'],
            [8, 'Dug well'],
            [9, 'Stone spout/Pond'],
            [10, 'Municipal/Public water supply']

        );

     foreach ($types as $type) {

         $existWaterSource =  DB::table('building_info.water_sources')
                 ->where('source', $type[1])
                 ->first();
         if(!$existWaterSource) {
            WaterSource::create([
             'id' => $type[0],
             'source' => $type[1],
         ]);
         }
     }
    }
}



