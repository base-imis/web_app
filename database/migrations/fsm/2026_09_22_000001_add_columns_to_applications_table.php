<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE fsm.applications ADD COLUMN IF NOT EXISTS is_anf boolean DEFAULT false');
        DB::statement('ALTER TABLE fsm.applications ADD COLUMN IF NOT EXISTS anf_nearest_locality varchar(255)');
        DB::statement('ALTER TABLE fsm.applications ADD COLUMN IF NOT EXISTS desludging_vehicle_size integer');
        DB::statement('ALTER TABLE fsm.applications ADD COLUMN IF NOT EXISTS trip_count integer');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE fsm.applications DROP COLUMN IF EXISTS trip_count');
        DB::statement('ALTER TABLE fsm.applications DROP COLUMN IF EXISTS desludging_vehicle_size');
        DB::statement('ALTER TABLE fsm.applications DROP COLUMN IF EXISTS anf_nearest_locality');
        DB::statement('ALTER TABLE fsm.applications DROP COLUMN IF EXISTS is_anf');
    }
};
