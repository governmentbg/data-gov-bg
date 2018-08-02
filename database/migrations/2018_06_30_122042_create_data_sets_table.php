<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_sets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->unsigned()->nullable();
            $table->foreign('org_id')->references('id')->on('organisations');
            $table->string('uri')->unique();
            $table->integer('name')->unsigned();
            $table->integer('descript')->unsigned()->nullable();
            $table->integer('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('terms_of_use_id')->unsigned()->nullable();
            $table->foreign('terms_of_use_id')->references('id')->on('terms_of_use');
            $table->unsignedTinyInteger('visibility');
            $table->string('source', 255)->nullable();
            $table->string('version', 15);
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('support_name')->nullable();
            $table->string('support_email')->nullable();
            $table->integer('sla')->unsigned()->nullable();
            $table->unsignedTinyInteger('status');
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
        Schema::dropIfExists('data_sets');
    }
}
