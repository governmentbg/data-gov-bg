<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            Schema::create('help_pages', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->integer('section_id')->unsigned()->nullable();
                $table->foreign('section_id')->references('id')->on('help_sections');
                $table->boolean('active');
                $table->integer('title')->unsigned();
                $table->integer('body')->unsigned();
                $table->string('keywords')->nullable();
                $table->integer('ordering')->default(1);
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
        Schema::dropIfExists('help_pages');
    }
}
