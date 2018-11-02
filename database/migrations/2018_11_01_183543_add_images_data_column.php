<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImagesDataColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            DB::statement("ALTER TABLE images ADD data LONGBLOB DEFAULT NULL");
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
            Schema::table('images', function (Blueprint $table) {
                $table->dropColumn('data');
            });
        }
    }
}
