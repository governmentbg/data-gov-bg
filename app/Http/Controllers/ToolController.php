<?php

namespace App\Http\Controllers;

use App\Module;
use App\Resource;
use App\DataQuery;
use Carbon\Carbon;
use App\ActionsHistory;
use App\ConnectionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Api\ActionsHistoryController as ApiHistory;

class ToolController extends Controller
{
    const DRIVER_MYSQL = 1;
    const DRIVER_PGSQL = 2;
    const DRIVER_OCI = 3;
    const DRIVER_INFORMIX = 4;
    const DRIVER_SQLSRV = 5;

    const SOURCE_TYPE_DB = 1;
    const SOURCE_TYPE_FILE = 2;

    const FREQ_TYPE_HOUR = 1;
    const FREQ_TYPE_DAY = 2;
    const FREQ_TYPE_WEEK = 3;
    const FREQ_TYPE_MONTH = 4;

    private function getDrivers()
    {
        return [
            self::DRIVER_MYSQL      => 'mysql',
            self::DRIVER_PGSQL      => 'pgsql',
            self::DRIVER_OCI        => 'oci',
            self::DRIVER_INFORMIX   => 'informix',
            self::DRIVER_SQLSRV     => 'sqlsrv',
        ];
    }

    private function getSourceTypes()
    {
        return [
            self::SOURCE_TYPE_DB    => 'dbms',
            self::SOURCE_TYPE_FILE  => 'file',
        ];
    }

    private function getFreqTypes()
    {
        return [
            self::FREQ_TYPE_HOUR    => __('custom.hour'),
            self::FREQ_TYPE_DAY     => __('custom.day'),
            self::FREQ_TYPE_WEEK    => __('custom.week'),
            self::FREQ_TYPE_MONTH   => __('custom.month'),
        ];
    }

    public function config(Request $request)
    {
        $class = 'index';
        $hasDb = false;
        $dataQueries = [];
        $foundData = false;
        $post = $request->all();
        $sourceTypes = $this->getSourceTypes();
        $freqTypes = $this->getFreqTypes();

        if (
            empty($request->get('source_type'))
            || $request->get('source_type') == $this->getSourceTypes()[self::SOURCE_TYPE_DB]
        ) {
            if ($request->has('test_conn') || $request->has('save_conn')) {
                $validator = \Validator::make($post, [
                    'connection_name'       => ($request->has('test_conn') ? 'nullable' : 'required') .'|string|max:191',
                    'source_db_user'        => 'required|string|max:191',
                    'source_db_host'        => 'required|string|max:191',
                    'source_db_name'        => 'required|string|max:191',
                    'source_db_pass'        => 'nullable|string|max:191',
                    'notification_email'    => 'nullable|email|max:191',
                    'test_query'            => ($request->has('test_conn') ? 'required' : 'nullable') .'|string|max:8000',
                ]);

                if (!$validator->fails()) {
                    $username = $post['source_db_user'];
                    $host = $post['source_db_host'];
                    $dbName = $post['source_db_name'];
                    $password = $post['source_db_pass'];
                    $query = $post['test_query'];

                    $driver = $this->testConnection($host, $dbName, $username, $password);

                    if ($driver) {
                        if ($request->has('save_conn')) {
                            $logData = [
                                'module_name'   => Module::getModuleName(Module::TOOL_DBMS),
                                'action'        => ActionsHistory::TYPE_ADD,
                                'action_object' => $post['connection_name'],
                                'action_msg'    => 'Listed data request',
                            ];

                            try {
                                $this->saveConnection($driver, $post);

                                $hasDb = true;
                                $logData['status'] = true;

                                session()->flash('alert-success', __('custom.conn_save_success'));
                            } catch (QueryException $e) {
                                $logData['status'] = false;

                                session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                            }

                            Module::add($logData);
                        } else {
                            $logData = [
                                'module_name'      => Module::getModuleName(Module::TOOL_DBMS),
                                'action'           => ActionsHistory::TYPE_SEE,
                                'action_msg'       => 'Listed data request',
                            ];

                            try {
                                $foundData = $this->fetchData($query, $driver, $host, $dbName, $username, $password);

                                $logData['status'] = true;

                                session()->flash('alert-success', __('custom.conn_success'));
                            } catch (\PDOException $e) {
                                $logData['status'] = false;

                                session()->flash('alert-danger', __('custom.conn_error') .' ('. $e->getMessage() .')');
                            }

                            Module::add($logData);
                        }
                    } else {
                        session()->flash('alert-danger', __('custom.conn_error'));
                    }
                }

                if (!session()->has('alert-success')) {
                    return back()->withInput()->withErrors($validator->errors()->messages());
                }
            } else {
                $dbData = ConnectionSetting::where('source_type', self::SOURCE_TYPE_DB)->first();

                if (!empty($dbData)) {
                    $hasDb = true;
                    $post = array_merge($post, [
                        'connection_name'       => $dbData['connection_name'],
                        'source_db_host'        => $dbData['source_db_host'],
                        'source_db_name'        => $dbData['source_db_name'],
                        'source_db_user'        => $dbData['source_db_user'],
                        'source_db_pass'        => $dbData['source_db_pass'],
                        'notification_email'    => $dbData['notification_email'],
                    ]);

                    if ($request->has('save_query')) {
                        $validator = \Validator::make($post, [
                            'name'          => 'required|string|max:191|unique:data_queries,name',
                            'api_key'       => 'required|string|max:191',
                            'resource_key'  => 'required|string|max:191',
                            'query'         => 'required|string|max:191',
                            'upl_freq'      => 'required|int|digits_between:1,4',
                            'upl_freq_type' => 'required|int',
                        ]);

                        if (!$validator->fails()) {
                            $logData = [
                                'module_name'      => Module::getModuleName(Module::TOOL_DBMS),
                                'action'           => ActionsHistory::TYPE_ADD,
                                'action_msg'       => 'Listed data request',
                            ];

                            try {
                                DataQuery::create([
                                    'connection_id' => $dbData['id'],
                                    'name'          => $post['name'],
                                    'api_key'       => $post['api_key'],
                                    'resource_key'  => $post['resource_key'],
                                    'query'         => $post['query'],
                                    'upl_freq'      => $post['upl_freq'],
                                    'upl_freq_type' => $post['upl_freq_type'],
                                ]);

                                unset($post['name'],
                                    $post['api_key'],
                                    $post['resource_key'],
                                    $post['query'],
                                    $post['upl_freq'],
                                    $post['upl_freq_type']
                                );

                                $logData['status'] = true;

                                session()->flash('alert-success', __('custom.conn_save_success'));
                            } catch (QueryException $e) {
                                $logData['status'] = true;

                                session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                            }

                            Module::add($logData);
                        }

                        if (!session()->has('alert-success')) {
                            return back()->withInput()->withErrors($validator->errors()->messages());
                        }
                    }

                    if ($request->has('delete_query')) {
                        $logData = [
                            'module_name'      => Module::getModuleName(Module::TOOL_DBMS),
                            'action'           => ActionsHistory::TYPE_DEL,
                            'action_msg'       => 'Listed data request',
                        ];

                        try {
                            $queryId = array_keys($post['delete_query'])[0];

                            DataQuery::find($queryId)->delete();

                            $logData['status'] = true;

                            session()->flash('alert-success', __('custom.query_delete_success'));
                        } catch (QueryException $e) {
                            $logData['status'] = false;

                            session()->flash('alert-danger', __('custom.query_delete_error') .' ('. $e->getMessage() .')');
                        }

                        Module::add($logData);
                    }

                    if ($request->has('send_query')) {
                        try {
                            $username = $dbData['source_db_user'];
                            $host = $dbData['source_db_host'];
                            $dbName = $dbData['source_db_name'];
                            $password = $dbData['source_db_pass'];
                            $driver = $dbData['source_db_type'];
                            $queryId = array_keys($post['send_query'])[0];
                            $dataQuery = DataQuery::find($queryId);

                            $data = $this->fetchData($dataQuery->query, $driver, $host, $dbName, $username, $password);

                            $response = $this->callApi($data, $dataQuery->api_key, $dataQuery->resource_key);

                            if ($response['success']) {
                                session()->flash('alert-success', __('custom.query_send_success'));
                            } else {
                                session()->flash('alert-danger', __('custom.query_send_error') .' ('. $response['error']['message'] .')');
                            }
                        } catch (\Exception $e) {
                            session()->flash('alert-danger', __('custom.query_send_error') .' ('. $e->getMessage() .')');
                        }
                    }

                    $dataQueries = DataQuery::where('connection_id', $dbData['id'])->get();
                }
            }
        } else {
            $file = $request->file('file');

            if ($request->has('test_file') || $request->has('save_file') || $request->has('send_file')) {
                $validator = \Validator::make($post, [
                    'file'              => 'required|file',
                    'file_conn_name'    => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                    'file_nt_email'     => 'nullable|email|max:191',
                    'file_rs_key'       => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                    'file_api_key'      => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                    'file_upl_freq'     => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                ]);

                if (!$validator->fails()) {
                    if ($request->has('save_file')) {
                        $logData = [
                            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
                            'action'        => ActionsHistory::TYPE_ADD,
                            'action_object' => $post['file_conn_name'],
                            'action_msg'    => 'Listed data request',
                        ];

                        try {
                            $this->saveFile($file, $post);

                            $logData['status'] = true;

                            session()->flash('alert-success', __('custom.conn_save_success'));
                        } catch (QueryException $e) {
                            $logData['status'] = false;

                            session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                        }

                        Module::add($logData);
                    } elseif ($request->has('send_file')) {
                        try {
                            $foundData = $this->fetchFileData($file, $post);

                            // $response = $this->callApi($data, $dataQuery->api_key, $dataQuery->resource_key);

                            session()->flash('alert-success', __('custom.conn_success'));
                        } catch (\PDOException $e) {
                            session()->flash('alert-danger', __('custom.conn_error') .' ('. $e->getMessage() .')');
                        }
                    } else {
                        $logData = [
                            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
                            'action'        => ActionsHistory::TYPE_SEE,
                            'action_object' => $post['file_conn_name'],
                            'action_msg'    => 'Listed data request',
                        ];

                        try {
                            $foundData = $this->fetchFileData($file, $post);

                            $logData['status'] = true;

                            session()->flash('alert-success', __('custom.conn_success'));
                        } catch (\PDOException $e) {
                            $logData['status'] = false;

                            session()->flash('alert-danger', __('custom.conn_error') .' ('. $e->getMessage() .')');
                        }
                    }
                }

                if (!session()->has('alert-success')) {
                    return back()->withInput()->withErrors($validator->errors()->messages());
                }
            } else {
                $dbData = ConnectionSetting::where('source_type', self::SOURCE_TYPE_FILE)->first();

                if (!empty($dbData)) {
                    $dataQuery = DataQuery::where('connection_id', $dbData['id'])->first();

                    $post = array_merge($post, [
                        'file_conn_name'        => $dbData['connection_name'],
                        'file_nt_email'         => $dbData['notification_email'],
                        'file'                  => $dbData['source_file_path'],
                        'file_rs_key'           => $dataQuery['resource_key'],
                        'file_api_key'          => $dataQuery['api_key'],
                        'file_upl_freq'         => $dataQuery['upl_freq'],
                        'file_upl_freq_type'    => $dataQuery['upl_freq_type'],
                    ]);
                }
            }
        }

        return view('tool/config', compact(
            'class',
            'post',
            'foundData',
            'sourceTypes',
            'freqTypes',
            'hasDb',
            'dataQueries'
        ));
    }

    private function saveFile($file, $data)
    {
        $setting = ConnectionSetting::where('source_type', self::SOURCE_TYPE_FILE)->first();

        $settingData = [
            'connection_name'       => $data['file_conn_name'],
            'source_type'           => self::SOURCE_TYPE_FILE,
            'source_file_type'      => Resource::getFormatsCode($file->getClientOriginalExtension()),
            'source_file_path'      => $file->getPathname(),
            'notification_email'    => $data['file_nt_email'],
        ];

        if (empty($setting)) {
            $setting = ConnectionSetting::create($settingData);
        } else {
            $setting->update($settingData);
        }

        DataQuery::updateOrCreate([
            'connection_id' => $setting['id'],
            'api_key'       => $data['file_api_key'],
            'resource_key'  => $data['file_rs_key'],
            'upl_freq'      => $data['file_upl_freq'],
            'upl_freq_type' => $data['file_upl_freq_type'],
        ]);
    }

    private function saveConnection($driver, $data)
    {
        $setting = ConnectionSetting::where('source_type', self::SOURCE_TYPE_DB)->first();

        $settingData = [
            'connection_name'       => $data['connection_name'],
            'source_type'           => self::SOURCE_TYPE_DB,
            'source_db_type'        => $driver,
            'source_db_host'        => $data['source_db_host'],
            'source_db_name'        => $data['source_db_name'],
            'source_db_user'        => $data['source_db_user'],
            'source_db_pass'        => $data['source_db_pass'],
            'notification_email'    => $data['notification_email'],
        ];

        if (empty($setting)) {
            ConnectionSetting::create($settingData);
        } else {
            $setting->update($settingData);
        }
    }

    private function testConnection($host, $dbName, $username, $password)
    {
        foreach ($this->getDrivers() as $id => $driver) {
            if ($this->checkConnection($driver, $host, $dbName, $username, $password)) {
                return $id;
            } else {
                continue;
            }
        }

        return false;
    }

    private function checkConnection($driver, $host, $dbName, $username, $password = null)
    {
        try {
            $connection = $this->getConnection($driver, $host, $dbName, $username, $password);

            return true;
        } catch(\PDOException $e) {}

        return false;
    }

    private function fetchData($query, $driver, $host, $dbName, $username, $password = null)
    {
        $driver = $this->getDrivers()[$driver];

        $connection = $this->getConnection($driver, $host, $dbName, $username, $password);

        $stmt = $connection->prepare($query);
        $stmt->execute();

        $result = $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        if (!empty($result[0])) {
            $result = array_merge([array_keys($result[0])], $result);
        }

        return empty($result) ? [] : $result;
    }

    private function fetchFileData($file, $post)
    {
        return empty($result) ? [] : $result;
    }

    private function getConnection($driver, $host, $dbName, $username, $password)
    {
        $connection = new \PDO($driver .':host='. $host .';dbname='. $dbName, $username, $password);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $connection;
    }

    private function callApi($data, $apiKey, $resourceUri)
    {
        $requestUrl = env('TOOL_API_URL') . 'addResourceData';
        $ch = curl_init($requestUrl);

        $params = ['api_key' => $apiKey, 'resource_uri' => $resourceUri, 'data' => $data];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // grab URL and pass it to the browser
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);

        return $response;
    }

    public function configHistory(Request $request)
    {
        $class = 'index';
        $params = [];
        $post = $request->all();
        $modules = Module::getToolModules();
        $actionTypes = ActionsHistory::getTypes();
        $connectionTypes = $this->getDrivers();
        $today = Carbon::now();

        $range = [
            'from'  => isset($request->period_from) ? $request->period_from : null,
            'to'    => isset($request->period_to) ? $request->period_to : null
        ];

        $time = [
            'from'  => isset($request->time_from) ? $request->time_from : null,
            'to'    => isset($request->time_to) ? $request->time_to : null
        ];

        $hourFrom = $request->offsetGet('time_from') ?: '';
        $hourTo = $request->offsetGet('time_to') ?: '23:59';

        if (!empty($request->offsetGet('period_from'))) {
            $params['criteria']['period_from'] = date_format(
                date_create($request->offsetGet('period_from') .' '. $hourFrom),
                'Y-m-d H:i:s'
            );
        } else if (!empty($request->offsetGet('time_from'))) {
            $params['criteria']['period_from'] = date_format(
                date_create($today->toDateString() .' '. $request->offsetGet('time_from')),
                'Y-m-d H:i:s'
            );
        }

        if (!empty($request->offsetGet('period_to'))) {
            $params['criteria']['period_to'] = date_format(
                date_create($request->offsetGet('period_to') .' '. $hourTo),
                'Y-m-d H:i:s'
            );
        } else if (!empty($request->offsetGet('time_to'))) {
            $params['criteria']['period_to'] = date_format(
                date_create($today->toDateString() .' '. $request->offsetGet('time_to')),
                'Y-m-d H:i:s'
            );
        }

        if (isset($post['status'])) {
            $params['criteria']['status'] = $post['status'];
        }

        if (!empty($request->offsetGet('source_type'))) {
            $params['criteria']['module'] = $request->offsetGet('source_type');
        }

        if (!empty($request->offsetGet('db_type'))) {
            $params['criteria']['source_db_type'] = $request->offsetGet('db_type');
        }

        if (!empty($request->offsetGet('q'))) {
            $params['criteria']['query_name'] = $request->offsetGet('q');
        }

        $perPage = 8;
        $params = array_merge($params, [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ]);

        $rq = Request::create('api/listActionHistory', 'POST', $params);
        $api = new ApiHistory($rq);
        $res = $api->listActionHistory($rq)->getData();
        $res->actions_history = isset($res->actions_history) ? $res->actions_history : [];
        $paginationData = $this->getPaginationData($res->actions_history, $res->total_records, [], $perPage);
        $pagination = !empty($paginationData['paginate']) ? $paginationData['paginate'] : [];

        $history = $res->success ? $res->actions_history : [];

        return view('tool/history', compact(
            'class',
            'modules',
            'range',
            'history',
            'actionTypes',
            'post',
            'connectionTypes',
            'pagination',
            'time'
        ));
    }
}
