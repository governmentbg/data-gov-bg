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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Input;
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

    public function configDbms(Request $request)
    {
        $class = 'index';
        $dataQueries = [];
        $foundData = false;
        $post = $request->all();
        $freqTypes = DataQuery::getFreqTypes();
        $errors = [];

        if (!empty($post['edit_dbms'])) {
            if (is_array($post['edit_dbms'])) {
                $this->clearConnection($request, $post);
                $post['edit_dbms'] = array_keys($post['edit_dbms'])[0];

                $this->clearQuery($request, $post);
            }
        }

        if (!empty($request->has('new_conn'))) {
            $this->clearConnection($request, $post);
        }

        if ($request->has('delete_dbms')) {
            $logData = [
                'module_name'   => Module::getModuleName(Module::TOOL_DB_CONNECTION),
                'action'        => ActionsHistory::TYPE_DEL,
                'action_msg'    => 'Deleted db connection',
            ];

            try {
                $connId = array_keys($post['delete_dbms'])[0];
                $logData['action_object'] = $connId;

                ConnectionSetting::find($connId)->delete();

                $this->clearConnection($request, $post);

                $logData['status'] = true;

                session()->flash('alert-success', __('custom.query_delete_success'));
            } catch (\Exception $e) {
                $logData['status'] = false;

                session()->flash(
                    'alert-danger',
                    __('custom.query_delete_error') .' ('. $e->getMessage() .')'
                );
            }

            Module::add($logData);
        }

        if ($request->has('test_conn') || $request->has('save_conn')) {
            $validator = \Validator::make($post, [
                'connection_name'       => ($request->has('test_conn') ? 'nullable' : 'required') .'|string|max:191'
                    . ($request->has('test_conn') ?
                        '' :
                        '|unique:connection_settings,connection_name,'
                        . (empty($post['edit_dbms']) ? 'NULL' : $post['edit_dbms'])
                        .',id,deleted_at,NULL'
                    ),
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
                            $post['edit_dbms'] = $this->saveDBMS($driver, $post, empty($post['edit_dbms']) ? null : $post['edit_dbms']);

                            session()->flash('alert-success', __('custom.conn_save_success'));
                        } catch (\Exception $e) {
                            session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                        }
                    } else {
                        $logData = [
                            'module_name'      => Module::getModuleName(Module::TOOL_DB_CONNECTION),
                            'action'           => ActionsHistory::TYPE_SEE,
                            'action_msg'       => 'Listed data request',
                            'action_object'    => empty($post['edit_dbms']) ? null : $post['edit_dbms'],
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
                $errors = $validator->errors();
            }
        }

        if (!empty($post['edit_dbms'])) {
            $dbData = ConnectionSetting::find($post['edit_dbms']);
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
                    'name'          => 'required|string|max:191'
                        .'|unique:data_queries,name,'
                        . (empty($post['query_id']) ? 'NULL' : $post['query_id'])
                        .',id,deleted_at,NULL',
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
                        if (empty($post['query_id'])) {
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
                            $query = DataQuery::find($post['query_id']);

                            $query->name = $post['name'];
                            $query->api_key = $post['api_key'];
                            $query->resource_key = $post['resource_key'];
                            $query->query = $post['query'];
                            $query->upl_freq = $post['upl_freq'];
                            $query->upl_freq_type = $post['upl_freq_type'];

                            $query->save();
                        }

                        $this->clearQuery($request, $post);

                        $logData['status'] = true;
                        $logData['action_object'] = $query->id;

                        session()->flash('alert-success', __('custom.conn_save_success'));
                    } catch (\Exception $e) {
                        $logData['status'] = false;

                        session()->flash(
                            'alert-danger',
                            __('custom.conn_save_error') .' ('. $e->getMessage() .')'
                        );
                    }

                    Module::add($logData);
                }

                if (!session()->has('alert-success')) {
                    $errors = $validator->errors();
                }
            }

            if ($request->has('delete_query')) {
                $logData = [
                    'module_name'   => Module::getModuleName(Module::TOOL_DB_QUERY),
                    'action'        => ActionsHistory::TYPE_DEL,
                    'action_msg'    => 'Deleted data request',
                ];

                try {
                    $queryId = array_keys($post['delete_query'])[0];
                    $logData['action_object'] = $queryId;

                    DataQuery::find($queryId)->delete();

                    $this->clearQuery($request, $post);

                    $logData['status'] = true;

                    session()->flash('alert-success', __('custom.query_delete_success'));
                } catch (\Exception $e) {
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
                    'module_name'   => Module::getModuleName(Module::TOOL_DB_QUERY),
                    'action'        => ActionsHistory::TYPE_SEND,
                ];

                $send = false;

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

                    $result = $this->updateResourceData(
                        $dataQuery->api_key,
                        $dataQuery->resource_key,
                        $data,
                        false,
                        $dbData['notification_email'],
                        $dbData['connection_name'],
                        $dataQuery->query
                    );
                    $send = true;

                    if (!empty($result['success'])) {
                        $logData['status'] = true;
                        $successMessage = empty($result['message']) ? __('custom.query_send_success') : $result['message'];
                        $logData['action_msg'] = truncate($successMessage, 191);

                        session()->flash('alert-success', $successMessage);
                    } else {
                        $logData['status'] = false;
                        $errorDetails = empty($result['errors']) ? '' : ' ('. print_r($result['errors'], true) .')';
                        $errorMessage = $result['error']['message'] . $errorDetails;
                        $logData['action_msg'] = '('. truncate($errorMessage, 187) .')';

                        session()->flash('alert-danger', __('custom.query_send_error') .': '. $errorMessage);
                    }
                } catch (\Exception $e) {
                    $logData['status'] = false;
                    $logData['action_msg'] = '('. truncate($e->getMessage(), 187) .')';

                    if (!$send) {
                        $this->updateResourceData(
                            $dataQuery->api_key,
                            $dataQuery->resource_key,
                            null,
                            false,
                            $dbData['notification_email'],
                            $dbData['connection_name'],
                            $dataQuery->query
                        );
                    }

                    session()->flash('alert-danger', __('custom.query_send_error') .' ('. $e->getMessage() .')');
                }

                Module::add($logData);
            }

            if ($request->has('edit_query')) {
                $this->clearQuery($request, $post);
                $post['query_id'] = array_keys($post['edit_query'])[0];
                $dataQuery = DataQuery::find($post['query_id']);
                $post = array_merge($post, $dataQuery->toArray());
            }

            if ($request->has('new_query')) {
                $this->clearQuery($request, $post);
            }

            $dataQueries = DataQuery::where('connection_id', $dbData['id'])->get();
        }

        $dbs = ConnectionSetting::where('source_type', ConnectionSetting::SOURCE_TYPE_DB)->get();

        return view('tool/configDbms', compact(
            'class',
            'post',
            'foundData',
            'freqTypes',
            'dataQueries',
            'dbs',
            'errors'
        ));
    }

    private function clearConnection(&$request, &$post)
    {
        $request->offsetUnset('connection_name');
        $request->offsetUnset('source_db_host');
        $request->offsetUnset('source_db_user');
        $request->offsetUnset('source_db_pass');
        $request->offsetUnset('source_db_name');
        $request->offsetUnset('notification_email');
        $request->offsetUnset('test_query');

        unset(
            $post['connection_name'],
            $post['source_db_host'],
            $post['source_db_user'],
            $post['source_db_pass'],
            $post['source_db_name'],
            $post['notification_email'],
            $post['test_query']
        );
    }

    private function clearFile(&$request, &$post)
    {
        $request->offsetUnset('file_conn_name');
        $request->offsetUnset('file');
        $request->offsetUnset('file_nt_email');
        $request->offsetUnset('file_api_key');
        $request->offsetUnset('file_rs_key');
        $request->offsetUnset('file_upl_freq');
        $request->offsetUnset('file_upl_freq_type');
        $post = [];
    }

    private function clearQuery(&$request, &$post)
    {
        $request->offsetUnset('query_id');
        $request->offsetUnset('name');
        $request->offsetUnset('api_key');
        $request->offsetUnset('resource_key');
        $request->offsetUnset('query');
        $request->offsetUnset('upl_freq');
        $request->offsetUnset('upl_freq_type');

        unset(
            $post['query_id'],
            $post['name'],
            $post['api_key'],
            $post['resource_key'],
            $post['query'],
            $post['upl_freq'],
            $post['upl_freq_type']
        );
    }

    public function configFile(Request $request)
    {
        $data = false;
        $class = 'index';
        $errors = [];
        $post = $request->all();
        $sourceTypes = ConnectionSetting::getSourceTypes();
        $freqTypes = DataQuery::getFreqTypes();
        $file = $request->file('file');
        $versionFormat = '';
        $visNotAvailable = [];
        $originalFormat = '';

        if ($request->has('new')) {
            $this->clearFile($request, $post);
        }

        if ($request->has('test_file') || $request->has('save_file') || $request->has('send_file')) {
            $validator = \Validator::make($post, [
                'file'              => 'required|string',
                'file_conn_name'    => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191'
                . ($request->has('test_file') ?
                    '' :
                    '|unique:connection_settings,connection_name,'
                    . (empty($post['conn_id']) ? 'NULL' : $post['conn_id'])
                    .',id,deleted_at,NULL'
                ),
                'file_nt_email'     => 'nullable|email|max:191',
                'file_rs_key'       => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                'file_api_key'      => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
                'file_upl_freq'     => ($request->has('test_file') ? 'nullable' : 'required') .'|string|max:191',
            ]);

            if (!$validator->fails()) {
                if ($request->has('save_file')) {
                    try {
                        $connId = $this->saveFile($file, $post);

                        session()->flash('alert-success', __('custom.conn_save_success'));
                    } catch (\Exception $e) {
                        session()->flash('alert-danger', __('custom.conn_save_error') .' ('. $e->getMessage() .')');
                    }

                    $post['conn_id'] = $connId;
                } elseif ($request->has('send_file')) {
                    if (!empty($post['conn_id'])) {
                        $dataQueryId = DataQuery::where('connection_id', $post['conn_id'])->first()->id;
                    } else {
                        $dataQueryId = null;
                    }

                    $this->sendFile($post, $dataQueryId);
                } else {
                    if (file_exists(self::DOCKER_FILE_VOLUME . $post['file'])) {
                        $content = file_get_contents(self::DOCKER_FILE_VOLUME . $post['file']);
                        $data = empty($content) ? ' ' : $content;
                        $originalFormat = pathinfo($post['file'], PATHINFO_EXTENSION);
                        $versionFormat = Resource::getFormatsCode($originalFormat);
                        $visNotAvailable = ['odt', 'xsd', 'rtf', 'ods', 'doc', 'docx', 'pdf', 'xls', 'xlsx'];

                        if (in_array(strtolower($originalFormat), $visNotAvailable)) {
                            $data = [];
                        }

                        session()->flash('alert-success', __('custom.conn_success'));
                    } else {
                        session()->flash('alert-danger', __('custom.conn_error'));
                    }
                }
            }

            if (!session()->has('alert-success')) {
                $errors = $validator->errors();
            }
        } else {
            if ($request->has('file_conn_id')) {
                $connId = array_keys($post['file_conn_id'])[0];
                $dbData = ConnectionSetting::find($connId);

                if (!empty($dbData)) {
                    $dataQuery = DataQuery::where('connection_id', $dbData['id'])->first();

                    $this->clearFile($request, $post);

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

            if ($request->has('send_file_query')) {
                $this->sendFile($post);
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
                } catch (\Exception $e) {
                    $logData['status'] = false;

                    session()->flash('alert-danger', __('custom.query_delete_error') .' ('. $e->getMessage() .')');
                }

                Module::add($logData);

                return back()->withInput([
                    'edit'          => false,
                    'source_type'   => ConnectionSetting::getSourceTypes()[ConnectionSetting::SOURCE_TYPE_FILE]
                ]);
            }
        }

        $files = ConnectionSetting::with('dataQueries')->where('source_type', ConnectionSetting::SOURCE_TYPE_FILE)->get();

        return view('tool/configFile', compact(
            'class',
            'post',
            'data',
            'sourceTypes',
            'freqTypes',
            'errors',
            'files',
            'versionFormat'
        ));
    }

    private function sendFile($post, $actionObject = null)
    {
        $logData = [
            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
            'action'        => ActionsHistory::TYPE_SEND,
            'action_object' => $actionObject,
            'status'        => true,
        ];

        try {
            if (empty($post['send_file_query'])) {
                $result = $this->updateResourceData(
                    $post['file_api_key'],
                    $post['file_rs_key'],
                    $post['file'],
                    true,
                    $post['file_nt_email'],
                    $post['file_conn_name']
                );
            } else {
                $queryId = array_keys($post['send_file_query'])[0];
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
            }

            if (!empty($result['success'])) {
                $logData['status'] = true;
                $successMessage = empty($result['message']) ? __('custom.query_send_success') : $result['message'];
                $logData['action_msg'] = truncate($successMessage, 191);

                session()->flash('alert-success', $successMessage);
            } else {
                $logData['status'] = false;
                $errorDetails = empty($result['errors']) ? '' : ' ('. print_r($result['errors'], true) .')';
                $errorMessage = $result['error']['message'] . $errorDetails;
                $logData['action_msg'] = '('. truncate($errorMessage, 187) .')';

                session()->flash('alert-danger', __('custom.query_send_error') .': '. $errorMessage);
            }
        } catch (\Exception $e) {
            $logData['status'] = false;
            $logData['action_msg'] = '('. truncate($e->getMessage(), 187) .')';

            session()->flash('alert-danger', __('custom.query_send_error') .' ('. $e->getMessage() .')');
        }

        Module::add($logData);
    }

    private function saveFile($file, $data)
    {
        $setting = [];
        $status = false;
        $actionObject = '';
        $action = '';

        try {
            if (!empty($data['conn_id'])) {
                $setting = ConnectionSetting::find($data['conn_id']);
            }

            $settingData = [
                'connection_name'       => $data['file_conn_name'],
                'source_type'           => ConnectionSetting::SOURCE_TYPE_FILE,
                'source_file_type'      => Resource::getFormatsCode(pathinfo($data['file'], PATHINFO_EXTENSION)),
                'source_file_path'      => $data['file'],
                'notification_email'    => $data['file_nt_email'],
            ];

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
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        $logData = [
            'module_name'   => Module::getModuleName(Module::TOOL_FILE),
            'action'        => $action,
            'action_object' => $actionObject,
            'action_msg'    =>
                ($action == ActionsHistory::TYPE_ADD ? 'Added file connection' : 'Edited file connection')
                . (!empty($message) ? '('. $message .')' : '')
            ,
            'status'        => $status,
        ];

        Module::add($logData);

        return $setting->id;
    }

    private function saveDBMS($driver, $data, $connId)
    {
        $action = '';
        $status = false;

        $settingData = [
            'connection_name'       => $data['connection_name'],
            'source_type'           => ConnectionSetting::SOURCE_TYPE_DB,
            'source_db_type'        => $driver,
            'source_db_host'        => $data['source_db_host'],
            'source_db_name'        => $data['source_db_name'],
            'source_db_user'        => $data['source_db_user'],
            'source_db_pass'        => $data['source_db_pass'],
            'notification_email'    => $data['notification_email'],
        ];

        try {
            if (empty($connId)) {
                $action = ActionsHistory::TYPE_ADD;
                $setting = ConnectionSetting::create($settingData);

                $id = $setting->id;
            } else {
                $action = ActionsHistory::TYPE_MOD;
                $setting = ConnectionSetting::find($connId);
                $setting->update($settingData);

                $id = $setting->id;
            }

            $status = true;
        } catch (\Exception $ex) {
            $status = false;

            Log::error($ex->getMessage());
        }

        $logData = [
            'module_name'   => Module::getModuleName(Module::TOOL_DB_CONNECTION),
            'action'        => $action,
            'action_object' => $id,
            'action_msg'    => 'Listed data request',
            'status'        => $status,
        ];

        Module::add($logData);

        return $id;
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
        $elasticData = null;
        $extension = '';

        if ($file) {
            $file = $data;
            $query = $file;
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if (!file_exists(self::DOCKER_FILE_VOLUME . $file)) {
                $errorResponse = [
                    'success'   => false,
                    'error'     => [
                        'message'   => sprintf(__('custom.file_missing'), $file),
                    ],
                ];
            } else {
                $content = file_get_contents(self::DOCKER_FILE_VOLUME . $file);

                if (!empty($extension)) {
                    $format = $extension;
                }

                ResourceController::callConversions($apiKey, $extension, $content, $resourceUri);
                $elasticData = Session::get('elasticData.'. $resourceUri);
                Session::forget('elasticData.'. $resourceUri);
            }
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

                $format = 'csv';
            }
        }

        $extension = $extension == '' ? 'csv' : $extension;

        $requestUrl = config('app.TOOL_API_URL') .'updateResourceData';

        $ch = curl_init($requestUrl);

        $params = [
            'api_key'           => $apiKey,
            'resource_uri'      => $resourceUri,
            'data'              => $elasticData,
            'support_email'     => $email,
            'connection_name'   => $name,
            'connection_query'  => $query,
            'extension_format'  => $extension
        ];

        if (!empty($format)) {
            $params['format'] = $format;
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // grab URL and pass it to the browser
        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);

        return empty($errorResponse) ? $response : array_merge($errorResponse, ['errors' => $response['errors']]);
    }

    public function configHistory(Request $request)
    {
        $class = 'index';
        $params = [];
        $post = $request->all();
        $modules = [Module::getModuleName(Module::TOOL_DB_CONNECTION), Module::getModuleName(Module::TOOL_FILE)];
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
                'Y-m-d'
            );
        } elseif ($request->has('search') && empty($request->offsetGet('period_to'))) {
            $range['from'] = date_format($today, 'd-m-Y');
        }

        if (!empty($request->offsetGet('period_to'))) {
            $params['criteria']['period_to'] = date_format(
                date_create($request->offsetGet('period_to') .' '. $hourTo),
                'Y-m-d'
            );
        } elseif ($request->has('search') && empty($request->offsetGet('period_from'))) {
            $range['to'] = date_format($today, 'd-m-Y');
        }

        if (!empty($request->offsetGet('time_from'))) {
            $params['criteria']['period_from_time'] = date_format(
                date_create($request->offsetGet('time_from')),
                'H:i'
            );
        }

        if (!empty($request->offsetGet('time_to'))) {
            $params['criteria']['period_to_time'] = date_format(
                date_create($request->offsetGet('time_to')),
                'H:i'
            );
        }

        if (isset($post['status'])) {
            $params['criteria']['status'] = $post['status'];
        }

        if ($request->has('source_type')) {
            if ($request->offsetGet('source_type') == Module::getModuleName(Module::TOOL_FILE)) {
                $params['criteria']['module'] = Module::getModuleName(Module::TOOL_FILE);
            } else {
                $params['criteria']['module'] = [
                    Module::getModuleName(Module::TOOL_DB_CONNECTION),
                    Module::getModuleName(Module::TOOL_DB_QUERY)
                ];
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
        $getParams = array_except(app('request')->input(), ['page']);
        $paginationData = $this->getPaginationData($res->actions_history, $res->total_records, $getParams, $perPage);
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
