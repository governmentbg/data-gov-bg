<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SectionsDropHelpSection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('sections', function (Blueprint $table) {
                $table->dropColumn('help_section');
            });

            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('help_page');
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
            Schema::table('sections', function (Blueprint $table) {
                $table->string('help_section')->nullable();
            });

            Schema::table('pages', function (Blueprint $table) {
                $table->string('help_page')->nullable();
            });
        }
    }
}
