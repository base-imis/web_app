<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;

class GridsnWardpl_whenApplicationCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatecount:applications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to update grids & wardpl COUNT when fsm.applications has changes';

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
        
        // to set no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet to grids acc to structure_type
        $query_grids_no_emptying ="UPDATE layer_info.grids SET 
                    no_emptying = ( SELECT count(a.id) 
                                    FROM fsm.applications a
                                    LEFT JOIN building_info.buildings b ON a.bin = b.bin
                                    LEFT JOIN layer_info.grids g 
                                    ON ST_Intersects(ST_Transform(g.geom, 4326), b.geom) 
                                    WHERE g.id = layer_info.grids.id AND b.deleted_at is null)";
                    
        // to set no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet to wardpl acc to structure_type
        $query_wardpl_no_emptying ="UPDATE layer_info.wards SET 
                    no_emptying = ( SELECT count(a.id) 
                                    FROM fsm.applications a
                                    LEFT JOIN building_info.buildings b ON a.bin = b.bin
                                    LEFT JOIN layer_info.wards w 
                                    ON ST_Intersects(ST_Transform(w.geom, 4326), b.geom) 
                                    WHERE w.ward = layer_info.wards.ward AND b.deleted_at is null)";
                   
        $update_grids_no_emptying = DB::statement($query_grids_no_emptying);
        $update_wardpl_no_emptying = DB::statement($query_wardpl_no_emptying);

        
        //check larave.log file to confirm all the queries has run successfully
        if($update_grids_no_emptying){
            \Log::info("no_emptying count updated successfully in grids!");
        }
        if($update_wardpl_no_emptying){
            \Log::info("no_emptying count updated successfully in wardpl!");
        }

        
        return Command::SUCCESS;
    }
}
