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
            'user_type' => 'Municipality - IT Admin',
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

        $surveyor->assignRole('Municipality - Building Surveyor (Ward)');

        $road_dept = User::create ([
            'name' => 'Infrastructure Department',
            'gender' => '',
            'username' => 'instrastructure',
            'email' => 'instrastructure@gmail.com',
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
            'name' => 'Water supply Department',
            'gender' => '',
            'username' => 'watersupply',
            'email' => 'water_supply@gmail.com',
            'password' => bcrypt('Watersupply@2024'),
            'user_type' => 'Municipality',
        ]);

        $watersupply_dept->assignRole('Municipality - Water Supply Department');

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
            'name' => 'Municipality - Solid Waste',
            'gender' => '',
            'username' => 'solid_waste',
            'email' => 'munsolid@gmail.com',
            'password' => bcrypt('Solid@2024'),
            'user_type' => 'Municipality',
        ]);

        $solid_waste->assignRole('Municipality - Solid Waste');

        // $help_desk = User::create ([
        //     'name' => 'Help Desk',
        //     'gender' => '',
        //     'username' => 'help_desk',
        //     'email' => 'help_desk@gmail.com',
        //     'password' => bcrypt('$help_desk$'),
        //     'help_desk_id' => '1',
        //     'user_type' => 'Help Desk',
        // ]);

        // $help_desk->assignRole('Municipality - Help Desk');

        // $service_provider_admin = User::create ([
        //     'name' => 'Clean Desludging Pvt. Ltd',
        //     'gender' => '',
        //     'username' => 'clean_admin',
        //     'email' => 'clean_admin@gmail.com',
        //     'password' => bcrypt('$clean_admin$'),
        //     'service_provider_id' => '1',
        //     'user_type' => 'Service provider',
        // ]);

        // $service_provider_admin->assignRole('Service Provider - Admin');

       

        // $emptying_operator = User::create ([
        //     'name' => 'Clean Desludging Operator',
        //     'gender' => '',
        //     'username' => 'clean_operator',
        //     'email' => 'clean_operator@gmail.com',
        //     'password' => bcrypt('$clean_operator$'),
        //     'service_provider_id' => '1',
        //     'user_type' => 'Service Provider',
        // ]);

        // $emptying_operator->assignRole('Service Provider - Emptying Operator');

        // $service_provider_admin = User::create ([
        //     'name' => 'Sams Cleaning Service Pvt. Ltd',
        //     'gender' => '',
        //     'username' => 'sams',
        //     'email' => 'sams@gmail.com',
        //     'password' => bcrypt('$sams$'),
        //     'service_provider_id' => '2',
        //     'user_type' => 'Service Provider',
        // ]);

        // $service_provider_admin->assignRole('Service Provider - Admin');

       

        // $emptying_operator = User::create ([
        //     'name' => 'Sams Cleaning Service Operator',
        //     'gender' => '',
        //     'username' => 'sams_operator',
        //     'email' => 'sams_operator@gmail.com',
        //     'password' => bcrypt('$sams_operator$'),
        //     'service_provider_id' => '2',
        //     'user_type' => 'Service Provider',
        // ]);

        // $emptying_operator->assignRole('Service Provider - Emptying Operator');

        // $service_provider_admin = User::create ([
        //     'name' => 'One Emptying Service Pvt. Ltd',
        //     'gender' => '',
        //     'username' => 'one_emptying',
        //     'email' => 'one_emptying@gmail.com',
        //     'password' => bcrypt('$one_emptying$'),
        //     'service_provider_id' => '3',
        //     'user_type' => 'Service Provider',
        // ]);

        // $service_provider_admin->assignRole('Service Provider - Admin');

       

        // $emptying_operator = User::create ([
        //     'name' => 'One Emptying Service Operator',
        //     'gender' => '',
        //     'username' => 'one_emptying_operator',
        //     'email' => 'one_emptying_operator@gmail.com',
        //     'password' => bcrypt('$one_emptying_operator$'),
        //     'service_provider_id' => '3',
        //     'user_type' => 'Service Provider',
        // ]);

        // $emptying_operator->assignRole('Service Provider - Emptying Operator');

        // $treatment_plant = User::create ([
        //     'name' => 'Treatment Plant Operator',
        //     'gender' => '',
        //     'username' => 'treatment_plant',
        //     'email' => 'treatment_plant@gmail.com',
        //     'password' => bcrypt('$treatment_plant$'),
        //     'treatment_plant_id'=> '1',
        //     'user_type' => 'Treatment Plant',
        // ]);

        // $treatment_plant->assignRole('Treatment Plant');

        // $treatment_plant = User::create ([
        //     'name' => 'WWWTP Opeartor',
        //     'gender' => '',
        //     'username' => 'wwtp',
        //     'email' => 'wwtp@gmail.com',
        //     'password' => bcrypt('$wwtp$'),
        //     'treatment_plant_id'=> '2',
        //     'user_type' => 'Treatment Plant',
        // ]);

        // $treatment_plant->assignRole('Treatment Plant');

        $external = User::create ([
            'name' => 'External',
            'gender' => '',
            'username' => 'external',
            'email' => 'external@gmail.com',
            'password' => bcrypt('External@2024'),
            'user_type' => 'External',
        ]);

        $external->assignRole('External');

      
    }


}
