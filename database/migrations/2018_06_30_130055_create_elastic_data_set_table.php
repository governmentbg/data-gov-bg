<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElasticDataSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elastic_data_set', function (Blueprint $table) {
            $table->increments('id');
            $table->string('index');
            $table->string('index_type');
            $table->string('doc');
            $table->string('version', 15)->nullable();
            $table->integer('resource_id')->unsigned()->nullable();
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->unique(['resource_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elastic_data_set');
    }
}
