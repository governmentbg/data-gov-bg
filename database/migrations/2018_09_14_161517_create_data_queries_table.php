<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataQueriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('IS_TOOL')) {
            Schema::create('data_queries', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('connection_id')->unsigned();
                $table->string('name')->nullable();
                $table->string('api_key');
                $table->string('resource_key');
                $table->text('query')->nullable();
                $table->integer('upl_freq');
                $table->tinyInteger('upl_freq_type');
                $table->timestamp('last_upl')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('connection_id')->references('id')->on('connection_settings')->onDelete('cascade');
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
        Schema::dropIfExists('data_queries');
    }
}
