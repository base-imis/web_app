<?php

namespace Database\Seeders;

use DB;
use App\Models\Cwis\DataSource;
use Illuminate\Database\Seeder;

class CwisDataSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datasources =  array(
            [1, 'equity', 'EQ-1', '% of LIC population with access to safe individual toilets / % of total population\nwith access  to safe individual toilets'], 
            [2, 'safety', 'SF-1a', 'Population with access to safe individual toilets'], 
            [3, 'safety', 'SF-1b', 'IHHL OSSs that have been desludged'], 
            [4, 'safety', 'SF-1c', 'Collected FS disposed at treatment plant or designated disposal site'], 
            [5, 'safety', 'SF-1d', 'FS treatment capacity as a % of total FS generated from non-sewered connections'], 
            [6, 'safety', 'SF-1e', 'FS treatment capacity as a % of volume disposed at the treatment plant'], 
            [7, 'safety', 'SF-1f', 'WW treatment capacity as a % of total WW generated from sewered connections and \ngreywater and supernatant generated from non-sewered connections'], 
            [8, 'safety', 'SF-1g', 'Effectiveness of FS treatment in meeting prescribed standards for effluent \ndischarge and biosolids disposal'], 
            [9, 'safety', 'SF-2a', 'Low income community (LIC) population with access to safe individual toilets'], 
            [10, 'safety', 'SF-2b', 'LIC OSSs that have been desludged'], 
            [11, 'safety', 'SF-2c', 'FS collected from LIC that is disposed at treatment plant or designated \ndisposal site'], 
            [12, 'safety', 'SF-3a', 'Dependent population (without IHHL) with access to safe shared facilities'], 
            [13, 'safety', 'SF-3b', 'Shared facilities that adhere to principles of universal design'], 
            [14, 'safety', 'SF-3c', 'Shared facility users who are women'], 
            [15, 'safety', 'SF-3e', 'Average distance from HH to shared facility (m)'], 
            [16, 'safety', 'SF-4a', 'PT where FS generated is safely transported to TP or safely disposed \nin situ'], 
            [17, 'safety', 'SF-4b', 'PT that adhere to principles of universal design'], 
            [18, 'safety', 'SF-4d', 'PT users who are women'], 
            [19, 'safety', 'SF-5', 'Educational institutions where FS generated is safely transported to TP \nor safely disposed in situ'], 
            [20, 'safety', 'SF-6', 'Healthcare facilities where FS generated is safely transported to TP or \nsafely disposed in situ'], 
            [21, 'safety', 'SF-7', 'Desludging services completed mechanically or semi-mechanically \n'],
            [22, 'safety', 'SF-9', '% of water contamination compliance (on fecal coliform)']
        );
     
     foreach ($datasources as $datasource) {
    
         $existDataSource =  DB::table('cwis.data_source')
                 ->where('indicator_code', $datasource[2])
                 ->first();
         if(!$existDataSource) {
            DataSource::insert([
             'id' => $datasource[0],
             'outcome' => $datasource[1], 
             'indicator_code' => $datasource[2],
             'label' => $datasource[3], 
         ] );
         }
     }

    //  Sample data for year 2022
    // $Year = 2022;
    // $update_cwisdata = DB::select( DB::raw("SELECT insert_data_into_cwis_table($Year)") );

    }
}
