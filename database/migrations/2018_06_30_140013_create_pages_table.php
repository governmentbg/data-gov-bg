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
            $table->integer('section_id')->unsigned();
            $table->foreign('section_id')->references('id')->on('sections');
            $table->integer('title_t_id')->unsigned();
            $table->foreign('title_t_id')->references('id')->on('translations');
            $table->integer('abstract_t_id')->unsigned();
            $table->foreign('abstract_t_id')->references('id')->on('translations');
            $table->integer('body_t_id')->unsigned();
            $table->foreign('body_t_id')->references('id')->on('translations');
            $table->integer('head_title_t_id')->unsigned();
            $table->foreign('head_title_t_id')->references('id')->on('translations');
            $table->integer('meta_desctript_t_id')->unsigned();
            $table->foreign('meta_desctript_t_id')->references('id')->on('translations');
            $table->integer('meta_key_words_t_id')->unsigned();
            $table->foreign('meta_key_words_t_id')->references('id')->on('translations');
            $table->string('forum_link');
            $table->boolean('active');
            $table->date('valid_from');
            $table->date('valid_to');
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
