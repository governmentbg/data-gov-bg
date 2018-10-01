<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
<<<<<<< HEAD
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('name')->unsigned();
            $table->integer('descript')->unsigned();
            $table->string('file_name');
            $table->string('mime_type');
            $table->timestamps();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users');
        });
=======
        if (!env('IS_TOOL')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('name')->unsigned();
                $table->integer('descript')->unsigned();
                $table->string('file_name');
                $table->string('mime_type');
                $table->string('forum_link')->nullable();
                $table->timestamps();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');
            });

            DB::statement("ALTER TABLE documents ADD data LONGBLOB NOT NULL");
        }
>>>>>>> 3566345b2a2f036b0f7e9c087e8abc4f93dda7eb
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
