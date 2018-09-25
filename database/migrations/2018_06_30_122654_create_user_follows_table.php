<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFollowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!env('IS_TOOL')) {
            Schema::create('user_follows', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users');
                $table->integer('org_id')->unsigned()->nullable();
                $table->foreign('org_id')->references('id')->on('organisations');
                $table->integer('group_id')->unsigned()->nullable();
                $table->foreign('group_id')->references('id')->on('organisations');
                $table->integer('data_set_id')->unsigned()->nullable();
                $table->foreign('data_set_id')->references('id')->on('data_sets');
                $table->integer('category_id')->unsigned()->nullable();
                $table->foreign('category_id')->references('id')->on('categories');
                $table->integer('tag_id')->unsigned()->nullable();
                $table->foreign('tag_id')->references('id')->on('categories');
                $table->integer('follow_user_id')->unsigned()->nullable();
                $table->foreign('follow_user_id')->references('id')->on('users');
                $table->boolean('news');
                $table->unique(['user_id', 'org_id', 'group_id', 'data_set_id', 'category_id', 'tag_id', 'follow_user_id', 'news'], 'user_follows_unique');
            });

            DB::unprepared("
                CREATE TRIGGER check_user_follows_insert BEFORE INSERT ON user_follows
                FOR EACH ROW
                BEGIN
                    ". $this->preventUserFollowsQuery() ."
                END;
                CREATE TRIGGER check_user_follows_update BEFORE UPDATE ON user_follows
                FOR EACH ROW
                BEGIN
                    ". $this->preventUserFollowsQuery() ."
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
        Schema::dropIfExists('user_follows');

        DB::unprepared("DROP TRIGGER IF EXISTS check_user_follows_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS check_user_follows_update");
    }

    private function preventUserFollowsQuery()
    {
        return "
            SET @count = 0;

            IF (NEW.category_id IS NOT NULL) THEN
                SET @count = @count + 1;
            END IF;

            IF (NEW.tag_id IS NOT NULL) THEN
                SET @count = @count + 1;
            END IF;

            IF (NEW.data_set_id IS NOT NULL) THEN
                SET @count = @count + 1;
            END IF;

            IF (NEW.org_id IS NOT NULL) THEN
                SET @count = @count + 1;
            END IF;

            IF (NEW.group_id IS NOT NULL) THEN
                SET @count = @count + 1;
            END IF;

            IF (NEW.follow_user_id IS NOT NULL) THEN
                SET @count = @count + 1;
            END IF;

            IF (
                NEW.news = 1 AND (@count > 0)
                OR NEW.news = 0 AND (@count != 1)
            ) THEN
                SIGNAL SQLSTATE '45000' SET message_text = 'Custom trigger check failed!';
            END IF;
        ";
    }
}
