<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE SCHEMA IF NOT EXISTS taxpayment_info");
        Schema::create('taxpayment_info.tax_payments', function (Blueprint $table) {
            $table->id();
            $table->string('tax_id',50);
            $table->string('owner_name',100);
            $table->string('gender',50);
            $table->string('contact',15);
            $table->timestamp('last_payment_date');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tax_payments');
    }
}
