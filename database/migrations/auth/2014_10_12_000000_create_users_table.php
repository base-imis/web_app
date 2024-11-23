<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE SCHEMA IF NOT EXISTS auth");
        DB::statement("CREATE EXTENSION IF NOT EXISTS postgis");
        Schema::create('auth.users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedInteger('treatment_plant_id')->nullable();
            $table->unsignedInteger('help_desk_id')->nullable();
            $table->unsignedInteger('service_provider_id')->nullable();
            $table->unsignedInteger('transfer_station_id')->nullable();
            $table->unsignedInteger('landfill_site_id')->nullable();
            $table->string('user_type');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->foreign('help_desk_id')->references('id')->on('fsm.help_desks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth.users');
    }
}
