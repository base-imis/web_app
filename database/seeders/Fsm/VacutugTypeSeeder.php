<?php

namespace Database\Seeders\Fsm;

use App\Models\Fsm\VacutugType;
use Illuminate\Database\Seeder;

class VacutugTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        VacutugType::factory(5)->create();
    }
}
