<?php

namespace Database\Seeders\Fsm;

use App\Models\Fsm\HelpDesk;
use Illuminate\Database\Seeder;

class HelpDeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HelpDesk::factory(5)->create();
    }
}
