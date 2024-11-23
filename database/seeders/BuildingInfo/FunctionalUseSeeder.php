<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\FunctionalUse;
use DB;

class FunctionalUseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names =  array(
            [1, 'Residential'],
            [2, 'Mixed (Residential, Commercial, Office uses)'],
            [3, 'Educational'],
            [4, 'Health Institution'],
            [5, 'Commercial'],
            [6, 'Industrial'],
            [7, 'Agriculture and Livestock'],
            [8, 'Public Institution'],
            [9, 'Government Institution'],
            [10, 'Recreational Institution'],
            [11, 'Social Institution'],
            [12, 'Cultural and Religious'],
            [13, 'Financial Institution'],
            [14, 'Vacant/Under Construction']
        );

        foreach ($names as $name) {

            $existFunctionalUse =  DB::table('building_info.functional_uses')
                ->where('name', $name[1])
                ->first();
            if (!$existFunctionalUse) {
                FunctionalUse::create([
                    'id' => $name[0],
                    'name' => $name[1],
                ]);
            }
        }
    }
}
