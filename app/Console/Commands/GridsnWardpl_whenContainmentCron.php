<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;

class GridsnWardpl_whenContainmentCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatecount:containments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to update grids & wardpl COUNT when fsm.containments has changes';

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
        // to set no of containments,no of pit containments, no of septic tank containments to grids
        $query_grids_containtype ="UPDATE layer_info.grids SET 
                no_contain = ( SELECT count(c.id) FROM fsm.containments c, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), c.geom) AND g.id = layer_info.grids.id AND c.deleted_at is null);";
                
        // to set no of containments,no of pit containments, no of septic tank containments to wardpl
        $query_wardpl_containtype ="UPDATE layer_info.wards SET 
                                no_contain = ( SELECT count(c.id) FROM fsm.containments c, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), c.geom) AND w.ward = layer_info.wards.ward AND c.deleted_at is null);";

       
        $updatecount_grids_containtype = DB::statement($query_grids_containtype);
        $updatecount_wardpl_containtype = DB::statement($query_wardpl_containtype);

        //check larave.log file to confirm all the queries has run successfully
        if($updatecount_grids_containtype){
            \Log::info("no of containments,no of pit containments, no of septic tank containments count updated successfully in grids!");
        }
        if($updatecount_wardpl_containtype){
            \Log::info("no of containments,no of pit containments, no of septic tank containments count updated successfully in wards!");
        }
        
        return Command::SUCCESS;
    }
}
