<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUplFreqFieldsToResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('resources', function (Blueprint $table) {
                $table->tinyInteger('upl_freq')->nullable();
                $table->tinyInteger('upl_freq_type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('resources', function (Blueprint $table) {
                $table->dropColumn('upl_freq');
                $table->dropColumn('upl_freq_type');
            });
        }
    }
}
