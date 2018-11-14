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
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\ResourceController;
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

    const DOCKER_LOCALHOST = 'host.docker.internal';
    const DOCKER_FILE_VOLUME = '/var/files/';

    public static function getDrivers()
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

    public static function getFreqTypes()
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
        $fileQueries = [];
        $foundData = false;
        $post = $request->all();
        $edit = !empty($post['conn_id']);
        $sourceTypes = $this->getSourceTypes();
        $freqTypes = self::getFreqTypes();
        $files = ConnectionSetting::with('dataQueries')->where('source_type', self::SOURCE_TYPE_FILE)->get();

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
                            try {
                                $this->saveConnection($driver, $post);

                                $hasDb = true;

                                session()->flash('alert-success', __('custom.conn_save_success'));
                            } catch (QueryException $e) {
                                session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                            }
                        } else {
                            $logData = [
                                'module_name'      => Module::getModuleName(Module::TOOL_DB_CONNECTION),
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

                if (!session()->has('alert-success') || $request->has('test_conn')) {
                    return back()->withInput()->withErrors($validator->errors()->messages());
                }
            } else {
                $dbData = ConnectionSetting::where('source_type', self::SOURCE_TYPE_DB)->first();

                if (!empty($dbData)) {
                    $hasDb = true;
                    $dbPostData = [
                        'connection_name'       => $dbData['connection_name'],
                        'source_db_host'        => $dbData['source_db_host'],
                        'source_db_name'        => $dbData['source_db_name'],
                        'source_db_user'        => $dbData['source_db_user'],
                        'source_db_pass'        => $dbData['source_db_pass'],
                        'notification_email'    => $dbData['notification_email'],
                    ];

                    $post = array_merge($post, $dbPostData);

                    if ($request->has('save_query')) {
                        $validator = \Validator::make($post, [
                            'name'          => 'required|string|max:191'. (
                                empty($post['id']) ? '|unique:data_queries,name' : ''
                            ),
                            'api_key'       => 'required|string|max:191',
                            'resource_key'  => 'required|string|max:191',
                            'query'         => 'required|string|max:191',
                            'upl_freq'      => 'required|int|digits_between:1,4',
                            'upl_freq_type' => 'required|int',
                        ]);

                        if (!$validator->fails()) {
                            $logData = [
                                'module_name'      => Module::getModuleName(Module::TOOL_DB_QUERY),
                                'action_msg'       => 'Listed data request',
                            ];

                            try {
                                if (empty($post['id'])) {
                                    $logData['action'] = ActionsHistory::TYPE_ADD;
                                    $query = DataQuery::create([
                                        'connection_id' => $dbData['id'],
                                        'name'          => $post['name'],
                                        'api_key'       => $post['api_key'],
                                        'resource_key'  => $post['resource_key'],
                                        'query'         => $post['query'],
                                        'upl_freq'      => $post['upl_freq'],
                                        'upl_freq_type' => $post['upl_freq_type'],
                                    ]);
                                } else {
                                    $logData['action'] = ActionsHistory::TYPE_MOD;
                                    $query = DataQuery::find($post['id']);

                                    $query->name = $post['name'];
                                    $query->api_key = $post['api_key'];
                                    $query->resource_key = $post['resource_key'];
                                    $query->query = $post['query'];
                                    $query->upl_freq = $post['upl_freq'];
                                    $query->upl_freq_type = $post['upl_freq_type'];

                                    $query->save();
                                }

                                unset(
                                    $post['id'],
                                    $post['name'],
                                    $post['api_key'],
                                    $post['resource_key'],
                                    $post['query'],
                                    $post['upl_freq'],
                                    $post['upl_freq_type']
                                );

                                $logData['status'] = true;
                                $logData['action_object'] = $query->id;

                                session()->flash('alert-success', __('custom.conn_save_success'));
                            } catch (QueryException $e) {
                                $logData['status'] = false;

                                session()->flash(
                                    'alert-danger',
                                    __('custom.conn_save_error') .' ('. $e->getMessage() .')'
                                );
                            }

                            Module::add($logData);
                        }

                        if (!session()->has('alert-success')) {
                            return back()->withInput()->withErrors($validator->errors()->messages());
                        }
                    }

                    if ($request->has('delete_query')) {
                        $logData = [
                            'module_name'      => Module::getModuleName(Module::TOOL_DB_QUERY),
                            'action'           => ActionsHistory::TYPE_DEL,
                            'action_msg'       => 'Listed data request',
                        ];

                        try {
                            $queryId = array_keys($post['delete_query'])[0];
                            $logData['action_object'] = $queryId;

                            DataQuery::find($queryId)->delete();

                            $logData['status'] = true;
                            $post = $dbPostData;

                            session()->flash('alert-success', __('custom.query_delete_success'));
                        } catch (QueryException $e) {
                            $logData['status'] = false;

                            session()->flash(
                                'alert-danger',
                                __('custom.query_delete_error') .' ('. $e->getMessage() .')'
                            );
                        }

                        Module::add($logData);
                    }

                    if ($request->has('send_query')) {
                        $logData = [
                            'module_name'      => Module::getModuleName(Module::TOOL_DB_QUERY),
                            'action'           => ActionsHistory::TYPE_SEND,
                            'action_msg'       => 'Send data request',
                        ];

                        try {
                            $username = $dbData['source_db_user'];
                            $host = $dbData['source_db_host'];
                            $dbName = $dbData['source_db_name'];
                            $password = $dbData['source_db_pass'];
                            $driver = $dbData['source_db_type'];
                            $queryId = array_keys($post['send_query'])[0];
                            $logData['action_object'] = $queryId;
                            $dataQuery = DataQuery::find($queryId);

                            $data = $this->fetchData($dataQuery->query, $driver, $host, $dbName, $username, $password, true);

                            $response = $this->updateResourceData(
                                $dataQuery->api_key,
                                $dataQuery->resource_key,
                                $data,
                                false,
                                $dbData['notification_email'],
                                $dbData['connection_name'],
                                $dataQuery->query
                            );

                            if (!empty($response['success'])) {
                                session()->flash(
                                    'alert-success',
                                    empty($response['message']) ? __('custom.query_send_success') : $response['message']
                                );

                                $logData['status'] = true;
                            } else {
                                session()->flash(
                                    'alert-danger',
                                    __('custom.query_send_error') .' ('. $response['error']['message'] .')'
                                );

                                $logData['status'] = false;
                            }
                        } catch (\Exception $e) {
                            $logData['status'] = false;

                            session()->flash('alert-danger', __('custom.query_send_error') .' ('. $e->getMessage() .')');
                        }

                        Module::add($logData);
                    }

                    if ($request->has('edit_query')) {
                        $queryId = array_keys($post['edit_query'])[0];
                        $dataQuery = DataQuery::find($queryId);

                        $post = array_merge($dataQuery->toArray(), $dbPostData);
                    }

                    if ($request->has('new_query')) {
                        $post = $dbPostData;
                    }

                    $dataQueries = DataQuery::where('connection_id', $dbData['id'])->get();
                }
            }
        } else {
            $file = $request->file('file');
            $actionObject = '';

            if (!empty($post['conn_id'])) {
                $actionObject = DataQuery::where('connection_id', $post['conn_id'])->first()->id;
            }

            if ($request->has('test_file') || $request->has('save_file') || $request->has('send_file')) {
                $validator = \Validator::make($post, [
                    'file'              => 'required|string',
                    'file_conn_name'    => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                    'file_nt_email'     => 'nullable|email|max:191',
                    'file_rs_key'       => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                    'file_api_key'      => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                    'file_upl_freq'     => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                ]);

                if (!$validator->fails()) {
                    if ($request->has('save_file')) {
                        try {
                            $actionObject = $this->saveFile($file, $post);

                            session()->flash('alert-success', __('custom.conn_save_success'));
                        } catch (QueryException $e) {
                            session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                        }

                        return back()->withInput(array_merge(Input::all(), [
                            'edit'      => true,
                            'conn_id'   => $actionObject,
                        ]));
                    } elseif ($request->has('send_file')) {
                        $logData = [
                            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
                            'action'        => ActionsHistory::TYPE_SEND,
                            'action_object' => $actionObject,
                            'action_msg'    => 'Sent file connection',
                        ];

                        try {
                            $result = $this->updateResourceData(
                                $post['file_api_key'],
                                $post['file_rs_key'],
                                $post['file'],
                                true,
                                $post['file_nt_email'],
                                $post['file_conn_name']
                            );

                            if (empty($result['success'])) {
                                $logData['status'] = false;

                                session()->flash('alert-danger', __('custom.conn_error') .': '. $result['error']['message']);
                            } else {
                                $logData['status'] = true;

                                session()->flash(
                                    'alert-success',
                                    empty($result['message']) ? __('custom.conn_success') : $result['message']
                                );
                            }
                        } catch (\Exception $e) {
                            $logData['status'] = false;

                            session()->flash('alert-danger', __('custom.conn_error') .' ('. $e->getMessage() .')');
                        }

                        Module::add($logData);
                    } else {
                        if (file_exists(self::DOCKER_FILE_VOLUME . $post['file'])) {
                            session()->flash('alert-success', __('custom.conn_success'));
                        } else {
                            session()->flash('alert-danger', __('custom.conn_error'));
                        }

                        return back()->withInput(array_merge(Input::all(), ['edit' => $edit]));
                    }
                }

                if (!session()->has('alert-success')) {
                    return back()
                        ->withInput(array_merge(Input::all(), ['edit' => $edit]))
                        ->withErrors($validator->errors()->messages());
                }
            } else {
                if ($request->has('file_conn_id')) {
                    $edit = true;
                    $connId = array_keys($post['file_conn_id'])[0];
                    $dbData = ConnectionSetting::find($connId);

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
                            'conn_id'               => $dbData['id'],
                        ]);
                    }
                }

                if ($request->has('send_query')) {
                    $logData = [
                        'module_name'   => Module::getModuleName(Module::TOOL_FILE),
                        'action'        => ActionsHistory::TYPE_SEND,
                        'action_msg'    => 'Send File',
                        'status'        => true,
                    ];

                    try {
                        $queryId = array_keys($post['send_query'])[0];
                        $query = DataQuery::find($queryId);

                        $logData['action_object'] = $queryId;

                        $result = $this->updateResourceData(
                            $query->api_key,
                            $query->resource_key,
                            $query->connection->source_file_path,
                            true,
                            $query->connection->notification_email,
                            $query->connection->connection_name
                        );

                        if (!empty($result['success'])) {
                            $logData['status'] = true;

                            session()->flash(
                                'alert-success',
                                empty($result['message']) ? __('custom.query_send_success') : $result['message']
                            );
                        } else {
                            $logData['status'] = false;

                            session()->flash('alert-danger', __('custom.query_send_error') .': '. $result['error']['message']);
                        }
                    } catch (QueryException $e) {
                        $logData['status'] = false;

                        session()->flash('alert-danger', __('custom.query_send_error') .' ('. $e->getMessage() .')');
                    }

                    Module::add($logData);
                }

                if ($request->has('delete_file')) {
                    $logData = [
                        'module_name'   => Module::getModuleName(Module::TOOL_FILE),
                        'action'        => ActionsHistory::TYPE_DEL,
                        'action_msg'    => 'Deleted file connection',
                    ];

                    try {
                        $queryId = array_keys($post['delete_file'])[0];

                        $logData['action_object'] = DataQuery::where('connection_id', $queryId)->first()->id;

                        ConnectionSetting::find($queryId)->delete();

                        $logData['status'] = true;

                        session()->flash('alert-success', __('custom.query_delete_success'));
                    } catch (QueryException $e) {
                        $logData['status'] = false;

                        session()->flash('alert-danger', __('custom.query_delete_error') .' ('. $e->getMessage() .')');
                    }

                    Module::add($logData);

                    return back()->withInput([
                        'edit'          => false,
                        'source_type'   => $this->getSourceTypes()[self::SOURCE_TYPE_FILE]
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
            'dataQueries',
            'files',
            'edit'
        ));
    }

    private function saveFile($file, $data)
    {
        $setting = [];
        $status = false;
        $actionObject = '';
        $action = '';

        if (!empty($data['conn_id'])) {
            $setting = ConnectionSetting::find($data['conn_id']);
        }

        $settingData = [
            'connection_name'       => $data['file_conn_name'],
            'source_type'           => self::SOURCE_TYPE_FILE,
            'source_file_type'      => Resource::getFormatsCode(pathinfo($data['file'], PATHINFO_EXTENSION)),
            'source_file_path'      => $data['file'],
            'notification_email'    => $data['file_nt_email'],
        ];

        try {
            if (empty($setting)) {
                $action = ActionsHistory::TYPE_ADD;
                $setting = ConnectionSetting::create($settingData);

                $dataQuery = DataQuery::create([
                    'connection_id' => $setting->id,
                    'name'          => $data['file'],
                    'api_key'       => $data['file_api_key'],
                    'resource_key'  => $data['file_rs_key'],
                    'upl_freq'      => $data['file_upl_freq'],
                    'upl_freq_type' => $data['file_upl_freq_type'],
                ]);

                $actionObject = $dataQuery->id;
            } else {
                $action = ActionsHistory::TYPE_MOD;
                $setting->update($settingData);
                $dataQuery = DataQuery::where('connection_id', $setting->id)->first();

                $dataQuery->name = $data['file'];
                $dataQuery->api_key = $data['file_api_key'];
                $dataQuery->resource_key = $data['file_rs_key'];
                $dataQuery->upl_freq = $data['file_upl_freq'];
                $dataQuery->upl_freq_type = $data['file_upl_freq_type'];

                $dataQuery->save();

                $actionObject = $dataQuery->id;
            }

            $status = true;
        } catch (QueryException $e) {
            $status = false;
        }

        $logData = [
            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
            'action'        => $action,
            'action_object' => $actionObject,
            'action_msg'    => $action == ActionsHistory::TYPE_ADD ? 'Added file connection' : 'Edited file connection',
            'status'        => $status,
        ];

        Module::add($logData);

        return $actionObject;
    }

    private function saveConnection($driver, $data)
    {
        $setting = ConnectionSetting::where('source_type', self::SOURCE_TYPE_DB)->first(); //TODO This needs to be changed
        $action = '';
        $status = false;

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

        try {
            if (empty($setting)) {
                $action = ActionsHistory::TYPE_ADD;
                $setting = ConnectionSetting::create($settingData);

                $conId = $setting->id;
            } else {
                $action = ActionsHistory::TYPE_MOD;
                $setting->update($settingData);

                $conId = $setting->id;
            }

            $status = true;
        } catch (\QueryException $e) {
            $status = false;
        }

        $logData = [
            'module_name'   => Module::getModuleName(Module::TOOL_DB_CONNECTION),
            'action'        => $action,
            'action_object' => $conId,
            'action_msg'    => 'Listed data request',
            'status'        => $status,
        ];

        Module::add($logData);
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

    public static function fetchData($query, $driver, $host, $dbName, $username, $password = null, $toJson = false)
    {
        $driver = self::getDrivers()[$driver];

        $connection = self::getConnection($driver, $host, $dbName, $username, $password);

        $stmt = $connection->prepare($query);
        $stmt->execute();

        $result = $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $stmt->fetchAll();

        if ($toJson) {
            return empty($result) ? [] : $result;
        }

        if (!empty($result[0])) {
            $result = array_merge([array_keys($result[0])], $result);
        }

        return empty($result) ? [] : $result;
    }

    public static function getConnection($driver, $host, $dbName, $username, $password)
    {
        if (config('app.IS_DOCKER')) {
            $hostParts = explode(':', $host);

            if (isset($hostParts[1]) && in_array($hostParts[0], ['localhost', '127.0.0.1'])) {
                $host = self::DOCKER_LOCALHOST .':'. $hostParts[1];
            }
        }

        $connection = new \PDO($driver .':host='. $host .';dbname='. $dbName, $username, $password);
        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $connection;
    }

    public static function updateResourceData($apiKey, $resourceUri, $data, $file = false, $email = null, $name = null, $query = null)
    {
        if ($file) {
            $file = $data;
            $query = $file;
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $content = @file_get_contents(self::DOCKER_FILE_VOLUME . $file);

            if (!file_exists()) {
                return [
                    'success'   => false,
                    'error'     => [
                        'message'   => sprintf(__('custom.missing_file'), $file),
                    ],
                ];
            }

            if (!empty($extension)) {
                $metadata['data']['file_format'] = $extension;
            }

            $data = ResourceController::callConversions($apiKey, $extension, $content);
            $elasticData = Session::get('elasticData');
            Session::forget('elasticData');
        } else {
            if (!empty($data)) {
                $elasticData = [array_keys($data[0])];

                foreach ($data as $row) {
                    foreach ($row as &$val) {
                        if (is_null($val)) {
                            $val = '';
                        }
                    }

                    $elasticData[] = array_values($row);
                }
            }
        }

        $requestUrl = config('app.TOOL_API_URL') .'updateResourceData';

        $ch = curl_init($requestUrl);

        $params = [
            'api_key'           => $apiKey,
            'resource_uri'      => $resourceUri,
            'data'              => $elasticData,
            'support_email'     => $email,
            'connection_name'   => $name,
            'connection_query'  => $query,
        ];

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
        $modules = ['DB Connection', 'File'];
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
            if ($request->offsetGet('source_type') == 'File') {
                $params['criteria']['module'] = $request->offsetGet('source_type');
            } else {
                $params['criteria']['module'] = ['DB Connection', 'DB Query'];
            }
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

        foreach ($history as $record) {
            if ($record->module == Module::getModuleName(Module::TOOL_DB_CONNECTION)) {
                $data = ConnectionSetting::where('id', $record->action_object)
                    ->withTrashed()
                    ->first();

                if (!empty($data->connection_name)) {
                    $record->action_object = $data->connection_name;
                }
            } else {
                $dataQuery = DataQuery::where('id', $record->action_object)
                    ->withTrashed()
                    ->first();

                $connectionName = $dataQuery->connection()->withTrashed()->first()->connection_name;

                $record->action_object = $connectionName .' ('. $dataQuery->name .')';
            }
        }

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
