<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WaterSupplyInfo\WaterSupply;
use App\Models\WaterSupplyInfo\DueYear;

class WaterSupplyInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DueYear::truncate();

        $dueYears =  array(
            [ 1 , 'No Due' , 0 ],
            [ 2 , '1 Year' , 1 ],
            [ 3 , '2 Years' , 2 ],
            [ 4 , '3 Years' , 3 ],
            [ 5 , '4 Years' , 4 ],
            [ 6 , '5 Years+' , 5 ],
            [ 7 , 'No Data' , 99 ]
        );

        foreach ($dueYears as $dueYear){
            DueYear::insert([
                'id' => $dueYear[0],
                'name' => $dueYear[1],
                'value' => $dueYear[2]
            ]);
        }
    }
}
