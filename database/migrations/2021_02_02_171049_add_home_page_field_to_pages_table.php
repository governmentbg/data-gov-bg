<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHomePageFieldToPagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    if (!config('app.IS_TOOL')) {
      Schema::table('pages', function (Blueprint $table) {
        $table->boolean('home_page');
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
      Schema::table('pages', function (Blueprint $table) {
        $table->dropColumn('home_page');
      });
    }
  }
}
