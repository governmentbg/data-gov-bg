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
        if (!env('IS_TOOL')) {
            Schema::create('resources', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('data_set_id')->unsigned();
                $table->foreign('data_set_id')->references('id')->on('data_sets');
                $table->integer('name')->unsigned();
                $table->integer('descript')->unsigned()->nullable();
                $table->string('uri');
                $table->string('version', 15)->nullable();
                $table->unsignedTinyInteger('resource_type');
                $table->unsignedTinyInteger('file_format')->nullable();
                $table->integer('es_id')->unsigned()->nullable();
                $table->foreign('es_id')->references('id')->on('elastic_data_set');
                $table->string('resource_url')->nullable();
                $table->unsignedTinyInteger('http_rq_type')->nullable();
                $table->string('authentication')->nullable();
                $table->binary('post_data')->nullable();
                $table->text('http_headers')->nullable();
                $table->text('schema_descript')->nullable();
                $table->string('schema_url')->nullable();
                $table->boolean('is_reported')->default();
                $table->timestamps();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');
                $table->integer('deleted_by')->unsigned()->nullable();
                $table->foreign('deleted_by')->references('id')->on('users');
                $table->softDeletes();
                $table->unique(['uri', 'version']);
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
        Schema::dropIfExists('resources');
    }
}
