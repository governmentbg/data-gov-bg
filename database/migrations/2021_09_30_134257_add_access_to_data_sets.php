<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccessToDataSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      if (!config('app.IS_TOOL')) {
        Schema::table('data_sets', function (Blueprint $table) {
          $table->unsignedTinyInteger('access')->default('1');
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
        Schema::table('data_sets', function (Blueprint $table) {
          $table->dropColumn('access');
        });
      }
    }
}
