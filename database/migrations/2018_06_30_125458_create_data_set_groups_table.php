<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSetGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_set_groups', function (Blueprint $table) {
            $table->integer('data_set_id')->unsigned();
            $table->foreign('data_set_id')->references('id')->on('data_sets');
            $table->integer('group_id')->unsigned();
            $table->foreign('group_id')->references('id')->on('organisations');
            $table->primary(['data_set_id', 'group_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_set_groups');
    }
}
