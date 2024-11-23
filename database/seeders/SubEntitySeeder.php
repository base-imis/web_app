<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fsm\VacutugType;
use App\Models\Fsm\EmployeeInfo;

class SubEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vaccutug = VacutugType::create([
            'name' => 'Hari Cleaning Car 5000',
            'size' => '5000',
            'width' => '3',
            'service_provider_id' => '1',
            'status' => '1'
        ]);
        $vaccutug = VacutugType::create([
            'name' => 'Hari Cleaning Car 10000',
            'size' => '10000',
            'width' => '5',
            'service_provider_id' => '1',
            'status' => '1'
        ]);

        $vaccutug = VacutugType::create([
            'name' => 'Shyam Cleaning Car 10000',
            'size' => '10000',
            'width' => '5',
            'service_provider_id' => '2',
            'status' => '1'
        ]);   
        $vaccutug = VacutugType::create([
            'name' => 'Shyam Cleaning Car 10000',
            'size' => '10000',
            'width' => '5',
            'service_provider_id' => '2',
            'status' => '1'
        ]);

        $employee = EmployeeInfo::create([
            'employee_type' => 'Cleaner/Emptier',
            'service_provider_id' => '1',
            'name' =>'Krishna',
            'address' =>'Imadole',
            'dob' => '1957-02-11',
            'sex' => 'Male',
            'wage' =>'15000'

        ]);
        $employee = EmployeeInfo::create([
            'employee_type' => 'Driver',
            'service_provider_id' => '1',
            'name' =>'Ramita',
            'address' =>'Imadole',
            'dob' => '1957-02-11',
            'sex' => 'Male',
            'wage' =>'15000'

        ]);
           $employee = EmployeeInfo::create([
            'employee_type' => 'Cleaner/Emptier',
            'service_provider_id' => '2',
            'name' =>'Sushmita',
            'address' =>'Imadole',
            'dob' => '1957-02-11',
            'sex' => 'Male',
            'wage' =>'15000'

        ]);
             $employee = EmployeeInfo::create([
            'employee_type' => 'Driver',
            'service_provider_id' => '2',
            'name' =>'Ashmita',
            'address' =>'Imadole',
            'dob' => '1957-02-11',
            'sex' => 'Male',
            'wage' =>'15000'

        ]);
    }
}
