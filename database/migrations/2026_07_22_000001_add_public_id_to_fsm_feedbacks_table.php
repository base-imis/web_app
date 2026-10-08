<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddPublicIdToFsmFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fsm.feedbacks', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->unique();
        });

        DB::table('fsm.feedbacks')
            ->whereNull('public_id')
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($feedbacks) {
                foreach ($feedbacks as $feedback) {
                    DB::table('fsm.feedbacks')
                        ->where('id', $feedback->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fsm.feedbacks', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
}
