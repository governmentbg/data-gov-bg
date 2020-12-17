<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username');
                $table->string('password');
                $table->string('email');
                $table->string('firstname', 100);
                $table->string('lastname', 100);
                $table->text('add_info')->nullable();
                $table->boolean('is_admin');
                $table->boolean('active');
                $table->boolean('approved');
                $table->string('api_key');
                $table->string('hash_id', 32);
                $table->rememberToken();
                $table->timestamps();
                $table->integer('updated_by')->unsigned()->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->integer('created_by')->unsigned();
                $table->foreign('created_by')->references('id')->on('users');
                $table->integer('deleted_by')->unsigned()->nullable();
                $table->foreign('deleted_by')->references('id')->on('users');
                $table->softDeletes();
            });

            DB::unprepared("
                CREATE TRIGGER check_username_insert BEFORE INSERT ON users
                FOR EACH ROW
                BEGIN
                    ". $this->preventUsernameQuery() ."
                END;
                CREATE TRIGGER check_username_update BEFORE UPDATE ON users
                FOR EACH ROW
                BEGIN
                    ". $this->preventUsernameQuery(true) ."
                END;
            ");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');

        DB::unprepared("DROP TRIGGER IF EXISTS check_username_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS check_username_update");
    }

    private function preventUsernameQuery($update = false)
    {
        $updateCondition = $update ? 'AND OLD.username != NEW.username' : '';

        return "
            SET @countNotDeleted = (
                SELECT COUNT(*) FROM users AS u
                WHERE u.username = NEW.username AND u.deleted_by IS NULL
            );

            IF (@countNotDeleted > 0 ". $updateCondition .") THEN
                SIGNAL SQLSTATE '45000' SET message_text = 'Undeleted matching username username exists!';
            END IF;
        ";
    }
}
