<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSetTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            Schema::create('data_set_tags', function (Blueprint $table) {
                $table->integer('data_set_id')->unsigned();
                $table->foreign('data_set_id')->references('id')->on('data_sets');
                $table->integer('tag_id')->unsigned();
                $table->foreign('tag_id')->references('id')->on('tags');
                $table->primary(['data_set_id', 'tag_id']);
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
        Schema::dropIfExists('data_set_tags');
    }
}
