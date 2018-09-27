<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefreshCacheAndElasticData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $stats = Elasticsearch::indices()->stats();

        if (!empty($stats['indices'])) {
            foreach ($stats['indices'] as $index => $stat) {
                \Elasticsearch::indices()->delete(['index' => $index]);
            }
        }

        Artisan::call('cache:clear');
    }
}
