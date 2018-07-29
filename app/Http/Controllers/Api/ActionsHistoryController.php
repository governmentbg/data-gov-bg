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
     * @param integer criteria[category_id] - optional
     * @param integer criteria[tag_id] - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[group_id] - optional
     * @param string criteria[dataset_uri] - optional
     * @param string criteria[ip_adress] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with history list or error message
     */
    public function listActionHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'criteria'             => 'nullable|array',
            'criteria.period_from' => 'nullable|date',
            'criteria.period_to'   => 'nullable|date',
            'criteria.username'    => 'nullable|string',
            'criteria.user_id'     => 'nullable|integer',
            'criteria.module'      => 'nullable|string',
            'criteria.action'      => 'nullable|integer',
            'criteria.category_id' => 'nullable|integer',
            'criteria.tag_id'      => 'nullable|integer',
            'criteria.org_id'      => 'nullable|integer',
            'criteria.group_id'    => 'nullable|integer',
            'criteria.dataset_uri' => 'nullable|string',
            'criteria.ip_adress'   => 'nullable|string',
            'records_per_page'     => 'nullable|integer',
            'page_number'          => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List action history failure', $validator->errors()->messages());
        }

        $criteria = $request->json('criteria');

        $history = ActionsHistory::select(
            'occurrence',
            'module_name',
            'action',
            'action_object',
            'action_msg',
            'user_id'
        )->with('user:id,username')->orderBy('occurrence', 'desc');

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

        if (isset($criteria['category_id'])) {
            $history->where([
                'action_object'    => $criteria['category_id'],
                'module_name'      => ActionsHistory::MODULE_NAMES[0],
            ]);
        }

        if (isset($criteria['tag_id'])) {
            $history->where([
                'action_object'    => $criteria['tag_id'],
                'module_name'      => ActionsHistory::MODULE_NAMES[1],
            ]);
        }

        if (isset($criteria['org_id'])) {
            $history->where([
                'action_object'    => $criteria['org_id'],
                'module_name'      => ActionsHistory::MODULE_NAMES[2],
            ]);
        }

        if (isset($criteria['group_id'])) {
            $history->where([
                'action_object'    => $criteria['group_id'],
                'module_name'      => ActionsHistory::MODULE_NAMES[3],
            ]);
        }

        if (isset($criteria['dataset_uri'])) {
            $dataSet = DataSet::whereUri($criteria['dataset_uri'])->get();

            if (!$dataSet->isEmpty()) {
                $history->where([
                    'action_object'    => $dataSet->org_id,
                    'module_name'      => ActionsHistory::MODULE_NAMES[2],
                ]);
            }
        }

        $recordsPerPage = $request->json('records_per_page');
        $count = $history->count();

        $history->forPage($request->json('page_number'), $this->getRecordsPerPage($recordsPerPage));

        $results = [];
        $history = $history->get();

        if (!empty($history)) {
            foreach ($history as $key => $record) {
                $results[] = [
                    'user'          => $record->user->username,
                    'occurrence'    => $record->occurrence,
                    'module'        => $record->module_name,
                    'action'        => $record->action,
                    'action_object' => $record->action_object,
                    'action_msg'    => $record->action_msg,
                ];
            }
        }

        return $this->successResponse([
            'total_records'     => $count,
            'actions_history'   => $results,
        ], true);
    }
}
