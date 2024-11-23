<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;

class GridsnWardpl_whenBuildingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatecount:buildings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'to update grids & wardpl COUNT when buildings has changes';

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
        $query_grids_structure_type ="UPDATE layer_info.grids SET 
        no_build = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_rcc_framed = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '4' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_load_bearing = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '3' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_wooden_mud = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '7' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_cgi_sheet = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '1' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_popsrv = ( SELECT sum(b.population_served) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), ST_centroid(b.geom)) AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_hhsrv = ( SELECT sum(b.household_served) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), ST_centroid(b.geom)) AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_build_directly_to_sewerage_network = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g 
                                WHERE ST_Intersects(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null AND b.sanitation_system_id = '1'),
                    no_pit_holding_tank = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g 
                                WHERE ST_Intersects(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null AND b.sanitation_system_id = '4'),
                    no_septic_tank = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g 
                                WHERE ST_Intersects(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null AND b.sanitation_system_id = '3')";

        // to set no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet to wardpl acc to structure_type
        $query_wardpl_structure_type ="UPDATE layer_info.wards SET 
                    no_build = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_rcc_framed = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '4' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_load_bearing = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '3' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_wooden_mud = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '7' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_cgi_sheet = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '1' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_popsrv = ( SELECT sum(b.population_served) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), ST_centroid(b.geom)) AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_hhsrv = ( SELECT sum(b.household_served) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), ST_centroid(b.geom)) AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_build_directly_to_sewerage_network = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w 
                                WHERE ST_Intersects(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null AND b.sanitation_system_id = '1'),
                    no_pit_holding_tank = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Intersects(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null AND 
                            b.sanitation_system_id = '4'),
                    no_septic_tank = ( ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w 
                                WHERE ST_Intersects(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null AND b.sanitation_system_id = '3'));";

        $update_grids_structure_type = DB::statement($query_grids_structure_type);
        $update_wardpl_structure_type = DB::statement($query_wardpl_structure_type);

        
        //check larave.log file to confirm all the queries has run successfully
        if($update_grids_structure_type){
            \Log::info("structure_type no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet count updated successfully in grids!");
        }
        if($update_wardpl_structure_type){
            \Log::info("structure_type no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet count updated successfully in wardpl!");
        }

        
        return Command::SUCCESS;
    }
}
