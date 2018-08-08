<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use \App\ActionsHistory;
use \App\DataSet;
use \App\User;
use \Validator;

class ActionsHistoryController extends ApiController
{
    /**
     * Lists actions based on request input
     *
     * @param array criteria - optional
     * @param date criteria[period_from] - optional
     * @param date criteria[period_to] - optional
     * @param string criteria[username] - optional
     * @param integer criteria[user_id] - optional
     * @param string criteria[module] - optional
     * @param integer criteria[action] - optional
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
        $validator = Validator::make($request->all(), [
            'criteria'               => 'nullable|array',
            'criteria.period_from'   => 'nullable|date',
            'criteria.period_to'     => 'nullable|date',
            'criteria.username'      => 'nullable|string',
            'criteria.user_id'       => 'nullable|integer',
            'criteria.module'        => 'nullable|string',
            'criteria.action'        => 'nullable|integer',
            'criteria.category_ids'  => 'nullable|array',
            'criteria.tag_ids'       => 'nullable|array',
            'criteria.org_ids'       => 'nullable|array',
            'criteria.group_ids'     => 'nullable|array',
            'criteria.user_ids'      => 'nullable|array',
            'criteria.dataset_ids'   => 'nullable|array',
            'criteria.resource_uris' => 'nullable|array',
            'criteria.ip_adress'     => 'nullable|string',
            'records_per_page'       => 'nullable|integer',
            'page_number'            => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List action history failure', $validator->errors()->messages());
        }

        $criteria = $request->criteria;

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
            $history->where('module_name', $criteria['module']);
        }

        if (isset($criteria['ip_address'])) {
            $history->where('ip_address', $criteria['ip_address']);
        }

        if (isset($criteria['action'])) {
            $history->where('action', $criteria['action']);
        }

        $actObjCriteria = [];
        if (isset($criteria['category_ids'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[0]] = $criteria['category_ids'];
        }

        if (isset($criteria['tag_ids'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[1]] = $criteria['tag_ids'];
        }

        if (isset($criteria['org_ids'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[2]] = $criteria['org_ids'];
        }

        if (isset($criteria['group_ids'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[3]] = $criteria['group_ids'];
        }

        if (isset($criteria['user_ids'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[4]] = $criteria['user_ids'];
        }

        if (isset($criteria['dataset_ids'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[5]] = $criteria['dataset_ids'];
        }

        if (isset($criteria['resource_uris'])) {
            $actObjCriteria[ActionsHistory::MODULE_NAMES[6]] = $criteria['resource_uris'];
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
        $modules = ActionsHistory::getModuleNames();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $result[] = ['name' => $module];
            }

            return $this->successResponse(['modules'=>$result], true);
        }

        return $this->errorResponse('Get data failure');
    }
}
