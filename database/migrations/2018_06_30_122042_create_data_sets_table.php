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
            $table->foreign('name')->references('id')->on('translations');
            $table->integer('descript')->unsigned();
            $table->foreign('descript')->references('id')->on('translations');
            $table->integer('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('terms_of_use_id')->unsigned()->nullable();
            $table->foreign('terms_of_use_id')->references('id')->on('terms_of_use');
            $table->unsignedTinyInteger('visibility');
            $table->string('version', 15);
            $table->string('author_name');
            $table->string('author_email');
            $table->string('support_name');
            $table->string('support_email');
            $table->integer('sla')->unsigned();
            $table->foreign('sla')->references('id')->on('translations');
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
        Schema::dropIfExists('data_sets');
    }
}
