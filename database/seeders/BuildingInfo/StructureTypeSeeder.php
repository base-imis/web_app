<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\StructureType;
use DB;

class StructureTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [ 1 , 'RCC framed' ],
            [ 2 , 'Load bearing' ],
            [ 3 , 'CGI Sheet' ],
            [ 4 , 'Wooden/Mud' ]

        );
     
     foreach ($types as $type) {
    
         $existStructureType =  DB::table('building_info.structure_types')
                 ->where('type', $type[1])
                 ->first();
         if(!$existStructureType) {
            StructureType::insert([
             'id' => $type[0],
             'type' => $type[1],
         ]);
         }
     }

    }
}
