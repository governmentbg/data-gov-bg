<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('data_set_id')->unsigned();
            $table->foreign('data_set_id')->references('id')->on('data_sets');
            $table->string('uri')->unique();
            $table->string('version', 15)->unique();
            $table->unsignedTinyInteger('resource_type');
            $table->unsignedTinyInteger('file_format');
            $table->integer('es_id')->unsigned()->nullable();
            $table->foreign('es_id')->references('id')->on('elastic_data_set');
            $table->string('resource_url');
            $table->unsignedTinyInteger('http_rq_type');
            $table->string('authentication');
            $table->binary('post_data');
            $table->text('http_headers');
            $table->integer('name')->unsigned();
            $table->foreign('name')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('descript')->unsigned();
            $table->foreign('descript')->references('id')->on('translations')->onDelete('cascade');
            $table->text('schema_descript');
            $table->string('schema_url');
            $table->boolean('is_reported');
            $table->timestamps();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users');
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
