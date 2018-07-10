<?php
use App\Organisation;
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
            $table->integer('name')->unsigned();
            $table->foreign('name')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('descript')->unsigned();
            $table->foreign('descript')->references('id')->on('translations')->onDelete('cascade');
            $table->string('logo_file_name')->nullable();
            $table->string('logo_mime_type')->nullable();
            $table->binary('logo_data')->nullable();
            $table->integer('activity_info')->unsigned()->nullable();
            $table->foreign('activity_info')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('contacts')->unsigned()->nullable();
            $table->foreign('contacts')->references('id')->on('translations')->onDelete('cascade');
            $table->integer('parent_org_id')->unsigned()->nullable();
            $table->foreign('parent_org_id')->references('id')->on('organisations');
            $table->boolean('active');
            $table->boolean('approved');
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
            CREATE TRIGGER check_organisations_insert BEFORE INSERT ON organisations
            FOR EACH ROW
            BEGIN
                ". $this->preventOrganisationQuery() ."
            END;
            CREATE TRIGGER check_organisations_update BEFORE UPDATE ON organisations
            FOR EACH ROW
            BEGIN
                ". $this->preventOrganisationQuery() ."
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
        Schema::dropIfExists('organisations');

        DB::unprepared("DROP TRIGGER IF EXISTS check_organisations_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS check_organisations_update");
    }

    private function preventOrganisationQuery()
    {
        return "
            IF (
                NEW.type = ". Organisation::TYPE_GROUP ."
                AND NEW.parent_org_id IS NOT NULL
            ) THEN
                SIGNAL SQLSTATE '45000' SET message_text = 'Custom trigger check failed!';
            END IF;
        ";
    }
}
