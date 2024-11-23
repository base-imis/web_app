<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;

class GridsnWardpl_whenRoadlineCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatecount:roadlines';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to update grids & wardpl COUNT when utility_info.roads has changes';

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
        // to set total length of roads to grids
        $query_grids_total_rdlen ="UPDATE layer_info.grids 
            SET total_rdlen = ( SELECT round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(r.geom,g.geom),32645))/1000) as numeric ),2) FROM utility_info.roads r, layer_info.grids g
            WHERE g.id = layer_info.grids.id AND r.deleted_at is null);";
        
        //to set total length of roads to wardpl
        $query_wardpl_total_rdlen ="UPDATE layer_info.wards 
            SET total_rdlen = ( SELECT round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(r.geom,w.geom),32645))/1000) as numeric ),2) FROM utility_info.roads r, layer_info.wards w
            WHERE w.ward = layer_info.wards.ward AND r.deleted_at is null);";
        

        $updatecount_grids_total_rdlen = DB::statement($query_grids_total_rdlen);
        $updatecount_wardpl_total_rdlen = DB::statement($query_wardpl_total_rdlen);

        //check larave.log file to confirm all the queries has run successfully
        if($updatecount_grids_total_rdlen){
            \Log::info("total length of roads count updated successfully in grids!");
        }
        if($updatecount_wardpl_total_rdlen){
            \Log::info("total length of roads count updated successfully in grids!");
        }
        
        return Command::SUCCESS;
    }
}
