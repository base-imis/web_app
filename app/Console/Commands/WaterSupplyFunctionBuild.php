<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WaterSupplyFunctionBuild extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildfunction:watersupply';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Functions to create table and update count when new watersupply data is imported';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
         // function and triggers to update grids & wardpl when buildings has changes
         DB::unprepared(Config::get('watersupply-info.fnc_create_watersupplystatus'));
         DB::unprepared(Config::get('watersupply-info.fnc_updonimprt_gridnward_watersupply'));

         \Log::info("Functions to create table and update building owner after water supply import successfully!!");
         $this->info('Functions to create table and update building owner after  water supply import successfully!!');
 
         return Command::SUCCESS;
    }
}
