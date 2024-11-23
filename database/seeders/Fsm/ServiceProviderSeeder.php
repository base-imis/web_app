<?php

namespace Database\Seeders\Fsm;

use Illuminate\Database\Seeder;
use App\Models\Fsm\ServiceProvider;

class ServiceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         ServiceProvider::factory(5)->create();
    }
}
