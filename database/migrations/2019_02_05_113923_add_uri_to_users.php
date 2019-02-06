<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUriToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('uri')->nullable()->unique();
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
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('uri');
            });
        }
    }
}
