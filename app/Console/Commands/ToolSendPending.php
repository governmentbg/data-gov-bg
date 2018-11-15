<?php

namespace App\Console\Commands;

use App\Module;
use App\DataQuery;
use App\ActionsHistory;
use App\ConnectionSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ToolController;

class ToolSendPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tool:sendpending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends pending files and database queries based on update frequencies';

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
        $connections = ConnectionSetting::all();
        $successCount = 0;
        $errorCount = 0;

        foreach ($connections as $connection) {
            $queries = DataQuery::where('connection_id', $connection->id)->get();

            foreach ($queries as $query) {
                if ($this->isReady($query)) {
                    if ($connection->source_type == ToolController::SOURCE_TYPE_DB) {
                        $logData = [
                            'module_name'   => Module::getModuleName(Module::TOOL_DB_QUERY),
                            'action'        => ActionsHistory::TYPE_SEND,
                            'action_object' => $query->id,
                        ];

                        $send = false;

                        try {
                            $username = $connection['source_db_user'];
                            $host = $connection['source_db_host'];
                            $dbName = $connection['source_db_name'];
                            $password = $connection['source_db_pass'];
                            $driver = $connection['source_db_type'];

                            $data = ToolController::fetchData($query->query, $driver, $host, $dbName, $username, $password, true);

                            $result = ToolController::updateResourceData(
                                $query->api_key,
                                $query->resource_key,
                                $data,
                                false,
                                $connection->notification_email,
                                $connection->connection_name,
                                $query->query
                            );
                            $send = true;

                            if (!empty($result['success'])) {
                                $logData['status'] = true;
                                $successCount++;
                                $successMessage = empty($result['message']) ? __('custom.query_send_success') : $result['message'];
                                $logData['action_msg'] = truncate($successMessage, 191);
                            } else {
                                $logData['status'] = false;
                                $errorCount++;
                                $errorDetails = empty($result['errors']) ? '' : ' ('. print_r($result['errors'], true) .')';
                                $errorMessage = $result['error']['message'] . $errorDetails;
                                $logData['action_msg'] = '('. truncate($errorMessage, 187) .')';
                            }
                        } catch (\Exception $e) {
                            $logData['status'] = false;
                            $errorCount++;
                            $logData['action_msg'] = '('. truncate($e->getMessage(), 187) .')';

                            if (!$send) {
                                ToolController::updateResourceData(
                                    $query->api_key,
                                    $query->resource_key,
                                    $data,
                                    false,
                                    $connection->notification_email,
                                    $connection->connection_name,
                                    $query->query
                                );
                            }
                        }
                    } else {
                        $logData = [
                            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
                            'action_object' => $query->id,
                            'action'        => ActionsHistory::TYPE_SEND,
                        ];

                        try {
                            $result = ToolController::updateResourceData(
                                $query->api_key,
                                $query->resource_key,
                                $connection->source_file_path,
                                true,
                                $connection->notification_email,
                                $connection->connection_name
                            );

                            if (!empty($result['success'])) {
                                $logData['status'] = true;
                                $successCount++;
                                $successMessage = empty($result['message']) ? __('custom.query_send_success') : $result['message'];
                                $logData['action_msg'] = truncate($successMessage, 191);
                            } else {
                                $logData['status'] = false;
                                $errorCount++;
                                $errorDetails = empty($result['errors']) ? '' : ' ('. print_r($result['errors'], true) .')';
                                $errorMessage = $result['error']['message'] . $errorDetails;
                                $logData['action_msg'] = '('. truncate($errorMessage, 187) .')';
                            }
                        } catch (\Exception $e) {
                            $logData['status'] = false;
                            $errorCount++;
                            $logData['action_msg'] = '('. truncate($e->getMessage(), 187) .')';
                        }
                    }

                    Module::add($logData);
                }
            }
        }

        $this->info("$successCount successfull resource updates");

        if ($errorCount) {
            $this->error("$errorCount failed resource updates");
        }
    }

    public function isReady($query)
    {
        $historyRecord = ActionsHistory::select('occurrence')
            ->where('action_object', $query->id)
            ->orderBy('occurrence', 'desc')
            ->first();

        $lastDate = empty($historyRecord) ? $query->created_at->format('Y-m-d H:i:s') : $historyRecord->occurrence;

        $offsetNumber = $query->upl_freq;
        $offsetType = null;

        switch ($query->upl_freq_type) {
            case ToolController::FREQ_TYPE_HOUR:
                $offsetType = 'hour';
                break;
            case ToolController::FREQ_TYPE_DAY:
                $offsetType = 'day';
                break;
            case ToolController::FREQ_TYPE_WEEK:
                $offsetType = 'week';
                break;
            case ToolController::FREQ_TYPE_MONTH:
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
