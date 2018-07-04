<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganisationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedTinyInteger('type');
            $table->integer('name_tg_id')->unsigned();
            $table->foreign('name_tg_id')->references('id')->on('translations');
            $table->integer('descript_tg_id')->unsigned();
            $table->foreign('descript_tg_id')->references('id')->on('translations');
            $table->string('logo_file_name')->nullable();
            $table->string('logo_mime_type')->nullable();
            $table->binary('logo_data')->nullable();
            $table->integer('activity_info_tg_id')->unsigned()->nullable();
            $table->foreign('activity_info_tg_id')->references('id')->on('translations');
            $table->integer('contacts_tg_id')->unsigned()->nullable();
            $table->foreign('contacts_tg_id')->references('id')->on('translations');
            $table->integer('parent_org_id')->unsigned()->nullable();
            $table->foreign('parent_org_id')->references('id')->on('organisations');
            $table->boolean('active');
            $table->boolean('approved');
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
        Schema::dropIfExists('organisations');
    }
}
