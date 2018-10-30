<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::create('images', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('img_file');
                $table->string('mime_type');
                $table->string('comment')->nullable();
                $table->integer('size')->unsigned();
                $table->tinyInteger('active')->default(0);
                $table->smallInteger('width')->unsigned();
                $table->smallInteger('height')->unsigned();
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
        Schema::dropIfExists('images');
    }
}
