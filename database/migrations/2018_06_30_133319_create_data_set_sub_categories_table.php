<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSetSubCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_set_sub_categories', function (Blueprint $table) {
            $table->integer('data_set_id')->unsigned();
            $table->foreign('data_set_id')->references('id')->on('data_sets');
            $table->integer('sub_cat_id')->unsigned();
            $table->foreign('sub_cat_id')->references('id')->on('categories');
            $table->primary(['data_set_id', 'sub_cat_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_set_sub_categories');
    }
}
