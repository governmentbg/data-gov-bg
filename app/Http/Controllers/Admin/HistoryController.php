<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\User;
use App\Module;
use App\DataSet;
use App\Resource;
use App\UserSetting;
use App\Organisation;
use App\UserToOrgRole;
use App\ActionsHistory;
use App\TermsOfUseRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\ActionsHistoryController as ApiHistory;
use App\Http\Controllers\Api\TermsOfUseRequestController as ApiTermsOfUseRequest;

class HistoryController extends AdminController
{
    public function history(Request $request, $type)
    {
        $view = $type;
        $perPage = 50;
        $params = [];
        $history = [];
        $filename = 'actionsHistory.csv';

        $params = [
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        $selectedOrg = $request->offsetGet('org') ?: null;
        $selectedUser = $request->offsetGet('user') ?: null;

        $selectedActions = $request->offsetGet('action') ?: [];
        $actionTypes = ActionsHistory::getTypes();

        $selectedModules = $request->offsetGet('module') ?: [];
        $modules = Module::getModules();

        $selectedIp = $request->offsetGet('ip') ?: null;

        if (!empty($request->offsetGet('period_from'))) {
            $params['criteria']['period_from'] = date_format(
                date_create($request->offsetGet('period_from')),
                'Y-m-d H:i:s'
            );
        } else {
            $params['criteria']['period_from'] = date('Y-m-d', strtotime('-1 months'));
            $request->merge(['period_from' => date('d-m-Y', strtotime('-1 months'))]);
        }

        if (!empty($request->offsetGet('period_to'))) {
            $params['criteria']['period_to'] = date_format(
                date_create($request->offsetGet('period_to') .' 23:59'),
                'Y-m-d H:i:s'
            );
        } else {
            $params['criteria']['period_to'] = date('Y-m-d'). ' 23:59';
            $request->merge(['period_to' => date('d-m-Y')]);
        }

        if (!is_null($selectedOrg)) {
            $params['criteria']['org_ids'] = [$selectedOrg];
            $filterOrg = Organisation::where('id', $selectedOrg)->first();

            if ($view == 'login') {
                $userIds = UserToOrgRole::where('org_id', $selectedOrg)->pluck('user_id')->toArray();
                $params['criteria']['user_id'] = $userIds;
                $params['criteria']['org_ids'] = null;
            } else {
                $dataSetIds = DataSet::where('org_id', $selectedOrg)->pluck('id')->toArray();
                $resourceIds = empty($dataSetIds)
                    ? null
                    : Resource::whereIn('data_set_id', $dataSetIds)->pluck('uri')->toArray();

                $params['criteria']['dataset_ids'] = empty($dataSetIds) ? null : $dataSetIds;
                $params['criteria']['resource_uris'] = empty($resourceIds) ? null : $resourceIds;
            }
        }

        if (!empty($selectedUser)) {
            $params['criteria']['user_id'] = $selectedUser;
            $filterUser = User::where('id', $selectedUser)->first();
        }

        if (!empty($selectedIp)) {
            $params['criteria']['ip_address'] = $selectedIp;
        }

        if ($view == 'login') {
            $filename = 'loginHistory.csv';
            $params['criteria']['actions'] = [ActionsHistory::TYPE_LOGIN];
        }

        if (!empty($selectedActions)) {
            $params['criteria']['actions'] = $selectedActions;
        }

        if (!empty($selectedModules)) {
            $params['criteria']['module'] = $selectedModules;
        }

        $params['api_key'] = Auth::user()->api_key;
        $params['criteria']['order'] = [
            'type'  => $request->offsetGet('order_type'),
            'field' => $request->offsetGet('order_field'),
        ];

        $rq = Request::create('api/listActionHistory', 'POST', $params);
        $api = new ApiHistory($rq);
        $res = $api->listActionHistory($rq)->getData();

        if ($res->success) {
            $history = $res->actions_history;

            $paginationData = $this->getPaginationData(
                $history,
                $res->total_records,
                array_except(app('request')->input(), ['page']),
                $perPage
            );
        }

        if ($request->has('download')) {
            $tempname = tempnam(sys_get_temp_dir(), 'csv_');
            $temp = fopen($tempname, 'w+');
            $path = stream_get_meta_data($temp)['uri'];

            if ($view == 'login') {
                fputcsv($temp, [
                    __('custom.date'),
                    trans_choice(utrans('custom.users'), 1),
                    utrans('custom.information'),
                    __('custom.ip_address'),
                ]);

                foreach($history as $row) {
                    fputcsv($temp, [
                        $row->occurrence,
                        $row->user,
                        $row->action_msg,
                        $row->ip_address,
                    ]);
                }
            } else {
                fputcsv($temp, [
                    __('custom.date'),
                    trans_choice(utrans('custom.users'), 1),
                    __('custom.module'),
                    __('custom.action'),
                    __('custom.object'),
                    __('custom.information'),
                    __('custom.ip_address'),
                ]);

                foreach($history as $row) {
                    fputcsv($temp, [
                        $row->occurrence,
                        $row->user,
                        $row->module,
                        $actionTypes[$row->action],
                        $row->action_object,
                        $row->action_msg,
                        $row->ip_address,
                    ]);
                }
            }

            $headers = ['Content-Type' => 'text/csv'];

            return response()->download($path, $filename, $headers)->deleteFileAfterSend(true);
        }

        return view('admin/history', [
            'class'             => 'user',
            'view'              => $view,
            'actionTypes'       => $actionTypes,
            'history'           => isset($paginationData['items']) ? $paginationData['items'] : null,
            'pagination'        => isset($paginationData['paginate']) ? $paginationData['paginate'] : null,
            'filterOrg'         => isset($filterOrg) && !empty($filterOrg->id) ? $filterOrg : null,
            'filterUser'        => isset($filterUser) && !empty($filterUser->id) ? $filterUser : null,
            'selectedIp'        => $selectedIp,
            'selectedActions'   => $selectedActions,
            'modules'           => $modules,
            'selectedModules'   => $selectedModules,
            'range'             => [
                'from'              => isset($request->period_from) ? $request->period_from : null,
                'to'                => isset($request->period_to) ? $request->period_to : null
            ],
        ]);
    }
}
