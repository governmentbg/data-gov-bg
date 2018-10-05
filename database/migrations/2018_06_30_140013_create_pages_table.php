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
        if (!env('IS_TOOL')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedTinyInteger('type');
                $table->integer('section_id')->unsigned()->nullable();
                $table->foreign('section_id')->references('id')->on('sections');
                $table->integer('title')->unsigned();
                $table->integer('abstract')->unsigned()->nullable();
                $table->integer('body')->unsigned()->nullable();
                $table->integer('head_title')->unsigned()->nullable();
                $table->integer('meta_descript')->unsigned()->nullable();
                $table->integer('meta_key_words')->unsigned()->nullable();
                $table->string('forum_link')->nullable();
                $table->string('help_page')->nullable();
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
