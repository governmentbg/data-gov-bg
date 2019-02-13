<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsMigratedToDatasetAndResources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            $migrationUserId = DB::table('users')->where('username', 'migrate_data')->get()->pluck('id');

            $lastMigratedDataset = DB::table('data_sets')
                ->where('data_sets.updated_by', $migrationUserId)
                ->orderBy('updated_at', 'desc')
                ->limit(1)
                ->pluck('updated_at')
                ->first();

            Schema::table('data_sets', function (Blueprint $table) {
                $table->tinyInteger('is_migrated')->nullable();
            });

            DB::statement('UPDATE data_sets SET is_migrated = 1 where updated_at <= "'. $lastMigratedDataset . '";');

            Schema::table('resources', function (Blueprint $table) {
                $table->tinyInteger('is_migrated')->nullable();
            });

            DB::statement('UPDATE resources SET is_migrated = 1 where updated_at <= "'. $lastMigratedDataset . '";');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!config('app.IS_TOOL')) {
            Schema::table('data_sets', function (Blueprint $table) {
                $table->dropColumn('is_migrated');
            });

            Schema::table('resources', function (Blueprint $table) {
                $table->dropColumn('is_migrated');
            });
        }
    }
}
