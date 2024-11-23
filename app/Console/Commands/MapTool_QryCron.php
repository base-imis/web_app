<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MapTool_QryCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'buildfunction:maptool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates or replace (Or Delete and create) maptool queries if not exists functions';

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
        // drop, create and call function to get point buffer buildings Query
        DB::unprepared(Config::get('qry_maptool.fnc_getPointBufferBuildings.fnc_drop_getPointBufferBuildings'));
        DB::unprepared(Config::get('qry_maptool.fnc_getPointBufferBuildings.fnc_set_getPointBufferBuildings'));
        // drop, create and call function to get buffer polygon buildings Query
        DB::unprepared(Config::get('qry_maptool.fnc_getbufferpolygonbuildings.fnc_drop_getbufferpolygonbuildings'));
        DB::unprepared(Config::get('qry_maptool.fnc_getbufferpolygonbuildings.fnc_set_getbufferpolygonbuildings'));
        // DB::unprepared(Config::get('qry_maptool.fnc_getbufferpolygonbuildings.call_getbufferpolygonbuildings'));

        
        \Log::info("Functions with MapTool Queries installed / updated successfully!!");

        return Command::SUCCESS;
    }
}
