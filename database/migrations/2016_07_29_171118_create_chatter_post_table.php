<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatterPostTable extends Migration
{
    public function up()
    {
        if (!env('IS_TOOL')) {
            Schema::create('chatter_post', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('chatter_discussion_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->text('body');
                $table->boolean('markdown')->default(0);
                $table->boolean('locked')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('chatter_discussion_id')
                    ->references('id')
                    ->on('chatter_discussion')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('chatter_post');
    }
}
