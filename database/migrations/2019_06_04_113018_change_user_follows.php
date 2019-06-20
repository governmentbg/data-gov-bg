<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserFollows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('user_follows', function (Blueprint $table) {
                $table->dropForeign('user_follows_tag_id_foreign');
            });

            Schema::table('user_follows', function (Blueprint $table) {
                $table->foreign('tag_id')->references('id')->on('tags');
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
            Schema::table('user_follows', function (Blueprint $table) {
                $table->dropForeign('user_follows_tag_id_foreign');
            });

            Schema::table('user_follows', function (Blueprint $table) {
                $table->foreign('tag_id')->references('id')->on('categories');
            });
        }
    }
}
