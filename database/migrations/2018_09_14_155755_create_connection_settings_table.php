<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectionSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('IS_TOOL')) {
            Schema::create('connection_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('connection_name');
                $table->tinyInteger('source_type');
                $table->tinyInteger('source_file_type')->nullable();
                $table->string('source_file_path')->nullable();
                $table->tinyInteger('source_db_type')->nullable();
                $table->string('source_db_host')->nullable();
                $table->string('source_db_name')->nullable();
                $table->string('source_db_user')->nullable();
                $table->string('source_db_pass')->nullable();
                $table->string('notification_email')->nullable();
                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('connection_settings');
    }
}
