<?php

namespace Database\Seeders\Fsm;

use App\Models\Fsm\EmployeeInfo;
use Illuminate\Database\Seeder;

class EmployeeInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        EmployeeInfo::factory(5)->create();
    }
}
