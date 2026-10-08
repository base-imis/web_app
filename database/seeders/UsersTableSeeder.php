<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // $role = Role::create ([
        //     'name' => 'Super Admin'
        // ]);

        $super_user = User::create ([
            'name' => 'Innovative Solution',
            'gender' => '',
            'username' => 'ispl',
            'email' => 'superadmin@gmail.com',
            'password' => bcrypt('$uper@dm!n@2024'),
            'user_type' => '',
        ]);

        $super_user->assignRole('Super Admin');

        $super_user = User::create ([
            'name' => 'Municipality Super Admin',
            'gender' => '',
            'username' => 'super-admin',
            'email' => 'munsuperadmin@gmail.com',
            'password' => bcrypt('Munsuperadmin@2024'),
            'user_type' => 'Municipality',
        ]);

        $super_user->assignRole('Municipality - Super Admin');

        $municipality_executive = User::create ([
            'name' => 'Municipality Executive',
            'gender' => '',
            'username' => 'executive',
            'email' => 'executive@gmail.com',
            'password' => bcrypt('Executive@2024'),
            'user_type' => 'Municipality',
        ]);
        $municipality_executive->assignRole('Municipality - Executive');

        $municipality_admin = User::create ([
            'name' => 'Municipality IT Admin',
            'gender' => '',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('Itadmin@2024'),
            'user_type' => 'Municipality',
        ]);

        $municipality_admin->assignRole('Municipality - IT Admin');

        $buildingpermit = User::create ([
            'name' => 'Building Permit Department',
            'gender' => '',
            'username' => 'building',
            'email' => 'building_permit@gmail.com',
            'password' => bcrypt('Permit@2024'),
            'user_type' => 'Municipality',
        ]);

        $buildingpermit->assignRole('Municipality - Building Permit Department');
        
        $surveyor = User::create ([
            'name' => 'Building Surveyor',
            'gender' => '',
            'username' => 'surveyor',
            'email' => 'surveyor@gmail.com',
            'password' => bcrypt('Surveyor@2024'),
            'user_type' => 'Municipality',
        ]);

        $surveyor->assignRole('Municipality - Building Surveyor');

        $road_dept = User::create ([
            'name' => 'Infrastructure Department',
            'gender' => '',
            'username' => 'instrastructure',
            'email' => 'infrastructure@gmail.com',
            'password' => bcrypt('Structure@2024'),
            'user_type' => 'Municipality',
        ]);

        $road_dept->assignRole('Municipality - Infrastructure Department');

        $tax_dept = User::create ([
            'name' => 'Tax Department',
            'gender' => '',
            'username' => 'tax',
            'email' => 'tax@gmail.com',
            'password' => bcrypt('Tax@2024'),
            'user_type' => 'Municipality',
        ]);

        $tax_dept->assignRole('Municipality - Tax Department');

        $watersupply_dept = User::create ([
            'name' => 'Water Billing Unit',
            'gender' => '',
            'username' => 'waterbilling',
            'email' => 'water_billing@gmail.com',
            'password' => bcrypt('Waterbilling@2024'),
            'user_type' => 'Municipality',
        ]);

        $watersupply_dept->assignRole('Municipality - Water Billing Unit');

        $sanitation_cell = User::create ([
            'name' => 'Sanitation Department',
            'gender' => '',
            'username' => 'sanitation',
            'email' => 'sanitation@gmail.com',
            'password' => bcrypt('Sanitation@2024'),
            'user_type' => 'Municipality',
        ]);

        $sanitation_cell->assignRole('Municipality - Sanitation Department');

        $public_health = User::create ([
            'name' => 'Municipality - Public Health',
            'gender' => '',
            'username' => 'public_health',
            'email' => 'public_health@gmail.com',
            'password' => bcrypt('Public@2024'),
            'user_type' => 'Municipality',
        ]);

        $public_health->assignRole('Municipality - Public Health Department');

        $solid_waste = User::create ([
            'name' => 'Municipality - Solid Waste Management Department',
            'gender' => '',
            'username' => 'solid_waste',
            'email' => 'munsolid@gmail.com',
            'password' => bcrypt('Solid@2024'),
            'user_type' => 'Municipality',
        ]);

        $solid_waste->assignRole('Municipality - Solid Waste Management Department');

        // $help_desk = User::create ([
        //     'name' => 'Municipality - Help Desk',
        //     'gender' => 'Male',
        //     'username' => 'munhelpdsk',
        //     'email' => 'munhelpdsk@gmail.com',
        //     'password' => bcrypt('Munhelp@2024'),
        //     'help_desk_id' => '3',
        //     'user_type' => 'Help Desk',
        // ]);
        // $help_desk->assignRole('Municipality - Help Desk');


        // $service_provider_admin = User::create ([
        //     'name' => 'Service Provider-Admin Clean',
        //     'gender' => 'Female',
        //     'username' => 'spadmin',
        //     'email' => 'cleaningservices@gmail.com',
        //     'password' => bcrypt('$SPadmin@2024'),
        //     'service_provider_id' => '1',
        //     'user_type' => 'Service provider',
        // ]);
        // $service_provider_admin->assignRole('Service Provider - Admin');


        // $emptying_operator = User::create ([
        //     'name' => 'Service Provider-Emptying Operator Clean',
        //     'gender' => 'Male',
        //     'username' => 'cleanoperator',
        //     'email' => 'cleanoperator@gmail.com',
        //     'password' => bcrypt('Cleanoperator@2024'),
        //     'service_provider_id' => '1',
        //     'user_type' => 'Service Provider',
        // ]);
        // $emptying_operator->assignRole('Service Provider - Emptying Operator');


        // $service_provider_admin = User::create ([
        //     'name' => 'Service Provider-Admin Sam',
        //     'gender' => 'Male',
        //     'username' => 'sam',
        //     'email' => 'samservices@gmail.com',
        //     'password' => bcrypt('$Sam@2024'),
        //     'service_provider_id' => '2',
        //     'user_type' => 'Service provider',
        // ]);
        // $service_provider_admin->assignRole('Service Provider - Admin');


        // $emptying_operator = User::create ([
        //     'name' => 'Service Provider-Emptying Operator Sam',
        //     'gender' => 'Female',
        //     'username' => 'samoperators',
        //     'email' => 'samoperators@gmail.com',
        //     'password' => bcrypt('Samoperator@2024'),
        //     'service_provider_id' => '2',
        //     'user_type' => 'Service Provider',
        // ]);
        // $emptying_operator->assignRole('Service Provider - Emptying Operator');


        // $treatment_plant = User::create ([
        //     'name' => 'Treatment Plant-Admin',
        //     'gender' => 'Male',
        //     'username' => 'tpadmin',
        //     'email' => 'fstpmun@gmail.com',
        //     'password' => bcrypt('Fstpmun@2024'),
        //     'treatment_plant_id'=> '1',
        //     'user_type' => 'Treatment Plant',
        // ]);
        // $treatment_plant->assignRole('Treatment Plant - Admin');


        $guest = User::create ([
            'name' => 'Guest',
            'gender' => '',
            'username' => 'guest',
            'email' => 'guest@gmail.com',
            'password' => bcrypt('Guest@2024'),
            'user_type' => 'Guest',
        ]);
        $guest->assignRole('Guest');

      
    }


}
