<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fsm\ServiceProvider;
use App\Models\Fsm\TreatmentPlant;
use App\Models\Fsm\HelpDesk;




class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $serviceProvider = ServiceProvider::create([
            'name' => 'Hari Desludging Services',
            'ward' => '1',
            'email' => 'sp1@gmail.com',
            'contact_person'=> 'Hari Krishna Maharjan',
            'contact_no' => '9845784578',
        ]);
        $serviceProvider = ServiceProvider::create([
            'name' => 'Shyam Sundar Desludging Services',
            'ward' => '2',
            'email' => 'sp2@gmail.com',
            'contact_person'=> 'Shyam Sundar',
            'contact_no' => '98648754187',
        ]);
        $treatmentPlant = TreatmentPlant::create([
            'name' => 'FSTP - Pateleban',
            'address' => 'Pateleban',
            'capacity' => '1500',
            'description' => '',
            'caretaker_name'=> 'Shyam Krishna Maharjan',
            'contact_number' => '9845784578',
            'geom' => '0101000020E6100000D95D13329D5855402FE0385545A13B40',
            'ward'=>'2'
        ]);
        $treatmentPlant = TreatmentPlant::create([
            'name' => 'WWTP - Balkumari',
            'address' => 'Balkumari',
            'capacity' => '1700',
            'description' => '',
            'caretaker_name'=> 'Haream Humagain',
            'contact_number' => '9845784578',
            'geom' => '0101000020E61000009A99999999993B403333333333535540',
            'ward'=>'3'
        ]);
        $helpDesk = HelpDesk::create([
            'name' => 'Help Desk',
            'contact_no' => '9845784578',
            'email' => 'helpDesk@gmail.com'
        ]);
       
      

    }
}
