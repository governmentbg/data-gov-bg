<?php

namespace App\Console\Commands;

use App\User;
use App\Module;
use App\Resource;
use App\DataQuery;
use App\ActionsHistory;
use App\ConnectionSetting;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\Api\ResourceController as ApiResource;

class ApiResourceSendPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource:sendpending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends pending api resources based on update frequencies';

    protected $currentTimestamp = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->currentTimestamp = strtotime('now');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $successCount = 0;
        $errorCount = 0;

        $resources = Resource::where('upl_freq_type', '!=', null)->where('upl_freq', '!=', null)->get();
        $requestTypes = Resource::getRequestTypes();
        $id = User::where('username', 'system')->value('id');
        Auth::loginUsingId($id);

        foreach ($resources as $resource) {
            if ($this->isReady($resource)) {
                $data = [
                    'type'               => $resource->resource_type,
                    'resource_url'       => $resource->resource_url,
                    'http_rq_type'       => $requestTypes[$resource->http_rq_type],
                    'http_headers'       => $resource->http_headers,
                    'post_data'          => $resource->post_data,
                    'upl_freq_type'      => $resource->upl_freq_type,
                    'upl_freq'           => $resource->upl_freq,
                    'schema_description' => $resource->schema_descript,
                    'schema_url'         => $resource->schema_url,
                ];

                $response = ResourceController::addMetadata($resource->uri, $data, null, true);

                if (isset($response['success'])) {
                    $reqElastic = Request::create(
                        '/updateResourceData',
                        'POST',
                        [
                            'resource_uri' => $response['uri'],
                            'data'         => $response['data'],
                        ]
                    );

                    $api = new ApiResource($reqElastic);
                    $resultElastic = $api->updateResourceData($reqElastic)->getData();

                    if ($resultElastic->success) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $errorCount++;
                }
            }
        }

        Auth::logout();

        $this->info("$successCount successfull resource updates");

        if ($errorCount) {
            $this->error("$errorCount failed resource updates");
        }
    }

    public function isReady($resource)
    {
        $historyRecord = ActionsHistory::select('occurrence')
            ->where('action_object', $resource->uri)
            ->where('action', ActionsHistory::TYPE_SEND)
            ->orderBy('occurrence', 'desc')
            ->first();

        $lastDate = empty($historyRecord) ? $resource->created_at : $historyRecord->occurrence;

        $offsetNumber = $resource->upl_freq;
        $offsetType = null;

        switch ($resource->upl_freq_type) {
            case DataQuery::FREQ_TYPE_HOUR:
                $offsetType = 'minute';
                break;
            case DataQuery::FREQ_TYPE_DAY:
                $offsetType = 'day';
                break;
            case DataQuery::FREQ_TYPE_WEEK:
                $offsetType = 'week';
                break;
            case DataQuery::FREQ_TYPE_MONTH:
                $offsetType = 'month';
                break;
        }

        $targetTimestamp = strtotime($lastDate .' + '. $offsetNumber .' '. $offsetType);

        if ($this->currentTimestamp >= $targetTimestamp) {
            return true;
        }

        return false;
    }
}
