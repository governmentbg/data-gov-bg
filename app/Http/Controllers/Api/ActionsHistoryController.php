<?php

namespace App\Http\Controllers\Api;

use App\Role;
use \Validator;
use App\Module;
use \App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ApiController;

class ActionsHistoryController extends ApiController
{
    /**
     * Add actions history record
     *
     * @param integer user_id - required
     * @param string module_name - required
     * @param string action - required
     * @param integer action_object - required
     * @param string action_msg - required
     * @param integer ip_address - required
     * @param array user_agent - required
     *
     * @return json response with success or failure
     */
    public function addActionHistory(Request $request)
    {
        $post = $request->all();
        $modules = Role::getModuleNames();
        $actions = ActionsHistory::getTypes();

        $validator = Validator::make($post, [
            'module_name'   => 'required|string|max:191|in:'. implode(',', $modules),
            'action'        => 'required|int|digits_between:1,3|in:'. implode(',', array_flip($actions)),
            'action_object' => 'required|max:191',
            'action_msg'    => 'required|string|max:191',
            'ip_address'    => 'required|string|max:15',
            'user_agent'    => 'required|string|max:191',
        ]);

        if (!$validator->fails()) {
            try {
                $dbData = [
                    'user_id'       => Auth::user()->id,
                    'module_name'   => $post['module_name'],
                    'action'        => $post['action'],
                    'action_object' => $post['action_object'],
                    'action_msg'    => $post['action_msg'],
                    'ip_address'    => $post['ip_address'],
                    'user_agent'    => $post['user_agent'],
                    'occurrence'    => date('Y-m-d H:i:s'),
                ];

                $newRecord = ActionsHistory::create($dbData);

                return $this->successResponse(['id' => $newRecord->id], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_action_fail'), $validator->errors()->messages());
    }

    /**
     * Lists actions based on request input
     *
     * @param array criteria - optional
     * @param date criteria[period_from] - optional
     * @param date criteria[period_to] - optional
     * @param string criteria[username] - optional
     * @param integer criteria[user_id] - optional
     * @param string criteria[module] - optional
     * @param array criteria[actions] - optional
     * @param array criteria[category_ids] - optional
     * @param array criteria[tag_ids] - optional
     * @param array criteria[org_ids] - optional
     * @param array criteria[group_ids] - optional
     * @param array criteria[user_ids] - optional
     * @param array criteria[dataset_ids] - optional
     * @param array criteria[resource_uris] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with history list or error message
     */
    public function listActionHistory(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'criteria'               => 'nullable|array',
            'records_per_page'       => 'nullable|integer',
            'page_number'            => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = Validator::make($criteria, [
                'period_from'     => 'nullable|date',
                'period_to'       => 'nullable|date',
                'username'        => 'nullable|string',
                'user_id'         => 'nullable|integer',
                'module'          => 'nullable',
                'actions'         => 'nullable|array',
                'actions.*'       => 'int|in:'. implode(',', array_keys(ActionsHistory::getTypes())),
                'category_ids'    => 'nullable|array',
                'category_ids.*'  => 'int',
                'tag_ids'         => 'nullable|array',
                'tag_ids.*'       => 'int',
                'org_ids'         => 'nullable|array',
                'org_ids.*'       => 'int',
                'group_ids'       => 'nullable|array',
                'group_ids.*'     => 'int',
                'user_ids'        => 'nullable|array',
                'user_ids.*'      => 'int',
                'dataset_ids'     => 'nullable|array',
                'dataset_ids.*'   => 'int',
                'resource_uris'   => 'nullable|array',
                'resource_uris.*' => 'string',
                'ip_adress'       => 'nullable|string',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_action_fail'), $validator->errors()->messages());
        }

        $history = ActionsHistory::select(
            'id',
            'occurrence',
            'module_name',
            'action',
            'action_object',
            'action_msg',
            'user_id'
        )->with('user:id,username,firstname,lastname')->orderBy('occurrence', 'desc');

        if (isset($criteria['period_from'])) {
            $history->where('occurrence', '>=', $criteria['period_from']);
        }

        if (isset($criteria['period_to'])) {
            $history->where('occurrence', '<=', $criteria['period_to']);
        }

        if (isset($criteria['username'])) {
            $history->whereHas('user', function($q) use ($criteria) {
                $q->where('username', 'like', '%'. $criteria['username'] .'%');
            });
        }

        if (isset($criteria['user_id'])) {
            $history->where('user_id', $criteria['user_id']);
        }

        if (isset($criteria['module'])) {
            if (is_array($criteria['module'])){
                $history->whereIn('module_name', $criteria['module']);
            } else {
                $history->where('module_name', $criteria['module']);
            }
        }

        if (isset($criteria['ip_address'])) {
            $history->where('ip_address', $criteria['ip_address']);
        }

        if (isset($criteria['actions'])) {
            $history->whereIn('action', $criteria['actions']);
        }

        $actObjCriteria = [];
        if (isset($criteria['category_ids'])) {
            $actObjCriteria[Module::getModules()[Module::MAIN_CATEGORIES]] = $criteria['category_ids'];
        }

        if (isset($criteria['tag_ids'])) {
            $actObjCriteria[Module::getModules()[Module::TAGS]] = $criteria['tag_ids'];
        }

        if (isset($criteria['org_ids'])) {
            $actObjCriteria[Module::getModules()[Module::ORGANISATIONS]] = $criteria['org_ids'];
        }

        if (isset($criteria['group_ids'])) {
            $actObjCriteria[Module::getModules()[Module::GROUPS]] = $criteria['group_ids'];
        }

        if (isset($criteria['user_ids'])) {
            $actObjCriteria[Module::getModules()[Module::USERS]] = $criteria['user_ids'];
        }

        if (isset($criteria['dataset_ids'])) {
            $actObjCriteria[Module::getModules()[Module::DATA_SETS]] = $criteria['dataset_ids'];
        }

        if (isset($criteria['resource_uris'])) {
            $actObjCriteria[Module::getModules()[Module::RESOURCES]] = $criteria['resource_uris'];
        }

        if (!empty($actObjCriteria)) {
            $history->where(function ($history) use ($actObjCriteria) {
                $isFirst = true;

                foreach ($actObjCriteria as $moduleName => $actionObjects) {
                    if ($isFirst) {
                        $isFirst = false;
                        $history->whereIn('action_object', $actionObjects)->where('module_name', $moduleName);
                    } else {
                        $history->orWhereIn('action_object', $actionObjects)->where('module_name', $moduleName);
                    }
                }
            });
        }

        $count = $history->count();

        $history->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $results = [];
        $history = $history->get();

        if (!empty($history)) {
            foreach ($history as $key => $record) {
                $results[] = [
                    'id'             => $record->id,
                    'user_id'        => $record->user->id,
                    'user'           => $record->user->username,
                    'user_firstname' => $record->user->firstname,
                    'user_lastname'  => $record->user->lastname,
                    'occurrence'     => $record->occurrence,
                    'module'         => $record->module_name,
                    'action'         => $record->action,
                    'action_object'  => $record->action_object,
                    'action_msg'     => $record->action_msg,
                ];
            }
        }

        return $this->successResponse([
            'total_records'     => $count,
            'actions_history'   => $results,
        ], true);
    }

    /**
     * Lists modules from ActionsHistory model
     *
     * @param Request $request
     * @return json response
     */
    public function listModules(Request $request)
    {
        $modules = Role::getModuleNames();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $result[] = ['name' => $module];
            }

            return $this->successResponse(['modules' => $result], true);
        }

        return $this->errorResponse(__('custom.data_failure'));
    }

    /**
     * List action types
     *
     * @param string locale - optional
     * @param boolean publicOnly - optional
     * @return json list with organisation types or error
     */
    public function listActionTypes(Request $request)
    {
        $results = [];

        $post = $request->all();

        $validator = \Validator::make($post, [
            'locale'     => 'nullable|string|exists:locale,locale,active,1',
            'publicOnly' => 'nullable|bool',
        ]);

        if (!$validator->fails()) {
            try {
                if (isset($post['locale'])) {
                    $locale = $post['locale'];
                } else {
                    $locale = \LaravelLocalization::getCurrentLocale();
                }

                if (isset($post['publicOnly']) && $post['publicOnly']) {
                    $actTypes = ActionsHistory::getPublicTypes();
                } else {
                    $actTypes = ActionsHistory::getTypes();
                }

                foreach ($actTypes as $typeId => $typeName) {
                    $results[] = [
                        'id'     => $typeId,
                        'name'   => __($typeName, [], $locale),
                        'locale' => $locale,
                    ];
                }

                return $this->successResponse(['types' => $results], true);

            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_act_types_fail'), $validator->errors()->messages());
    }
}
