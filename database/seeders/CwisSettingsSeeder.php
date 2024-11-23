<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Fsm\CwisSetting;
use DB;

class CwisSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [ 1 , "average_water_consumption_lpcd" , 150 , "cwis_setting" ],
            [ 2 , "waste_water_conversion_factor" , 80 , "cwis_setting" ],
            [ 3 , "greywater_conversion_factor_connected_to_sewer" , 60 , "cwis_setting" ],
            [ 4 , "greywater_conversion_factor_not_connected_to_sewer" , 100 , "cwis_setting" ]
        );
        
     foreach ($types as $type) {
    
         $existCwisSettings =  DB::table('public.site_settings')
                 ->where('name', $type[1])
                 ->first();
         if(!$existCwisSettings) {
            CwisSetting::insert([
             'id' => $type[0],
             'name' => $type[1],
             'value' => $type[2],
             'category' => $type[3]
         ]);
         }
     }

    }
}
