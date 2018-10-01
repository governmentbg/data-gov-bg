<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            Schema::create('help_sections', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('name')->unsigned();
                $table->integer('parent_id')->unsigned()->nullable();
                $table->foreign('parent_id')->references('id')->on('help_sections');
                $table->boolean('active');
                $table->tinyInteger('ordering')->default(1);
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
        Schema::dropIfExists('help_sections');
    }
}
