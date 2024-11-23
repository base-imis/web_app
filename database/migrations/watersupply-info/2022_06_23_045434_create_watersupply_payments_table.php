<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWatersupplyPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE SCHEMA IF NOT EXISTS watersupply_info");
        Schema::create('watersupply_info.watersupply_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tax_id',50);
            $table->string('owner_name',100);
            $table->string('gender',50)->nullable();
            $table->string('contact_no',15)->nullable();
            $table->timestamp('last_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('watersupply_payments');
    }
}
