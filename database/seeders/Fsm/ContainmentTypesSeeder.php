<?php

namespace Database\Seeders\Fsm;

use Illuminate\Database\Seeder;
use App\Models\Fsm\ContainmentType;
use DB;

class ContainmentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [ 1 , 'Septic Tank connected to Sewer Network' , 3 , true , 'Septic Tank' ],
            [ 2 , 'Septic Tank connected to Drain Network' , 3 , true , 'Septic Tank' ],
            [ 3 , 'Septic Tank connected to Soak Pit' , 3 , true , 'Septic Tank' ],
            [ 4 , 'Septic Tank connected to Water Body' , 3 , true , 'Septic Tank' ],
            [ 5 , 'Septic Tank connected to Open Ground' , 3 , true , 'Septic Tank' ],
            [ 6 , 'Septic Tank without Outlet Connection' , 3 , true , 'Septic Tank' ],
            [ 7 , 'Septic Tank with Unknown Outlet Connection' , 3 , true , 'Septic Tank' ],
            [ 8 , 'Double Pit' , 4 , true , 'Double Pit' ],
            [ 9 , 'Permeable/ Unlined Pit/Holding Tank' , 4 , true , 'Permeable/ Unlined Pit/Holding Tank' ],
            [ 10 , 'Lined Pit connected to a Soak Pit' , 4 , true , 'Lined Pit' ],
            [ 11 , 'Lined Pit connected to Water Body' , 4 , true , 'Lined Pit' ],
            [ 12 , 'Lined Pit connected to Open Ground' , 4 , true , 'Lined Pit' ],
            [ 13 , 'Lined Pit connected to Sewer Network' , 4 , true , 'Lined Pit' ],
            [ 14 , 'Lined Pit connected to Drain Network' , 4 , true , 'Lined Pit' ],
            [ 15 , 'Lined Pit without Outlet' , 4 , true , 'Lined Pit' ],
            [ 16 , 'Lined Pit with Unknown Outlet Connection' , 4 , true , 'Lined Pit']

        );
        
     foreach ($types as $type) {
    
         $existStructureType =  DB::table('fsm.containment_types')
                 ->where('type', $type[1])
                 ->first();
         if(!$existStructureType) {
            ContainmentType::insert([
             'id' => $type[0],
             'type' => $type[1],
             'sanitation_system_id' => $type[2],
             'dashboard_display' => $type[3],
             'map_display' => $type[4],
         ]);
         }
     }

    }
}
