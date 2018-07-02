<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrgCustomSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('org_custom_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->unsigned();
            $table->foreign('org_id')->references('id')->on('organisations');
            $table->integer('key_tg_id')->unsigned();
            $table->foreign('key_tg_id')->references('id')->on('translations');
            $table->integer('value_tg_id')->unsigned();
            $table->foreign('value_tg_id')->references('id')->on('translations');
            $table->timestamps();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('org_custom_settings');
    }
}
