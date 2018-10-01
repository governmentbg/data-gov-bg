<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatterDiscussionTable extends Migration
{
    public function up()
    {
        if (!env('IS_TOOL')) {
            Schema::create('chatter_discussion', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('chatter_category_id')->unsigned()->default('1');
                $table->string('title');
                $table->integer('user_id')->unsigned();
                $table->boolean('sticky')->default(false);
                $table->integer('views')->unsigned()->default('0');
                $table->boolean('answered')->default(0);
                $table->timestamps();
                $table->timestamp('last_reply_at')->useCurrent();
                $table->string('color', 20)->nullable()->default('#232629');
                $table->string('slug')->unique();
                $table->softDeletes();
                $table->foreign('chatter_category_id')
                    ->references('id')
                    ->on('chatter_categories')
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
        Schema::dropIfExists('chatter_discussion');
    }
}
