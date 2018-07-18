<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->unsigned()->nullable();
            $table->integer('data_set_id')->unsigned()->nullable();
            $table->integer('resource_id')->unsigned()->nullable();
            $table->foreign('org_id')->references('id')->on('organisations');
            $table->integer('key')->unsigned();
            $table->integer('value')->unsigned();
            $table->timestamps();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')->on('users');
        });

        DB::unprepared("
           CREATE TRIGGER check_custom_settings_insert BEFORE INSERT ON custom_settings
           FOR EACH ROW
           BEGIN
               ". $this->preventInsertQuery() ."
           END;
           CREATE TRIGGER check_custom_settings_update BEFORE UPDATE ON custom_settings
           FOR EACH ROW
           BEGIN
               ". $this->preventUpdateQuery() ."
           END;
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_settings');

        DB::unprepared("DROP TRIGGER IF EXISTS check_custom_settings_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS check_custom_settings_update");
    }

    private function preventInsertQuery()
   {
       return "
           SET @count = 0;

           IF (NEW.org_id IS NOT NULL) THEN
               SET @count = @count + 1;
           END IF;

           IF (NEW.data_set_id IS NOT NULL) THEN
               SET @count = @count + 1;
           END IF;

           IF (NEW.resource_id IS NOT NULL) THEN
               SET @count = @count + 1;
           END IF;

           IF (@count != 1) THEN
               SIGNAL SQLSTATE '45000' SET message_text = 'Custom trigger check failed!';
           END IF;
       ";
   }

   private function preventUpdateQuery()
   {
       return "
           SET @old = NULL;

           IF (OLD.org_id IS NOT NULL) THEN
               SET @old = 'org_id';
           END IF;

           IF (OLD.data_set_id IS NOT NULL) THEN
               SET @old = 'data_set_id';
           END IF;

           IF (OLD.resource_id IS NOT NULL) THEN
               SET @old = 'resource_id';
           END IF;

           SET @count = 0;

           IF (@old = 'org_id' OR NEW.org_id IS NOT NULL) THEN
               SET @count = @count + 1;
           END IF;

           IF (@old = 'data_set_id' OR NEW.data_set_id IS NOT NULL) THEN
               SET @count = @count + 1;
           END IF;

           IF (@old = 'resource_id' OR NEW.resource_id IS NOT NULL) THEN
               SET @count = @count + 1;
           END IF;

           IF (@count != 1) THEN
               SIGNAL SQLSTATE '45000' SET message_text = 'Custom trigger check failed!';
           END IF;
       ";
   }
}
