<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('section_id')->unsigned()->nullable();
            $table->foreign('section_id')->references('id')->on('sections');
            $table->integer('title')->unsigned();
            $table->foreign('title')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('abstract')->unsigned();
            $table->foreign('abstract')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('body')->unsigned()->nullable();
            $table->foreign('body')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('head_title')->unsigned()->nullable();
            $table->foreign('head_title')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('meta_desctript')->unsigned()->nullable();
            $table->foreign('meta_desctript')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('meta_key_words')->unsigned()->nullable();
            $table->foreign('meta_key_words')->references('id')->on('translations')->onDelete('cascade');
            $table->string('forum_link')->nullable();
            $table->boolean('active');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
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
        Schema::dropIfExists('pages');
    }
}
