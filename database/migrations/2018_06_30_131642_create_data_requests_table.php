<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::create('data_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('org_id')->unsigned();
                $table->foreign('org_id')->references('id')->on('organisations');
                $table->text('descript');
                $table->string('published_url')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('email');
                $table->text('notes')->nullable();
                $table->unsignedTinyInteger('status');
                $table->timestamps();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->integer('created_by')->unsigned()->nullable();
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
        Schema::dropIfExists('data_requests');
    }
}
