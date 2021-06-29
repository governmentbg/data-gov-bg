<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPagesTableHomePageField extends Migration
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
        $table->boolean('home_page')->default('0')->change();
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
    Schema::table('pages', function (Blueprint $table) {
      //
    });
  }
}
