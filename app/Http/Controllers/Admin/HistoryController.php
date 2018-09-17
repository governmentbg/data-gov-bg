<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Module;
use App\UserSetting;
use App\Organisation;
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
        $filename = "actionsHistory.csv";

        $params = [
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        $orgDropCount = $request->offsetGet('orgs_count') ?: Organisation::INIT_FILTER;
        $selectedOrgs = $request->offsetGet('org') ?: [];
        $organisations = $this->getOrgDropdown(null, $orgDropCount);

        $userDropCount = $request->offsetGet('users_count') ?: Organisation::INIT_FILTER;
        $selectedUser = $request->offsetGet('user') ?: '';
        $users = $this->getUserDropdown($userDropCount);

        $selectedActions = $request->offsetGet('action') ?: [];
        $actionTypes = ActionsHistory::getTypes();

        $selectedModules = $request->offsetGet('module') ?: [];
        $modules = Module::getModules();

        $ipDropCount = $request->offsetGet('ips_count') ?: Organisation::INIT_FILTER;
        $selectedIp = $request->offsetGet('ip') ?: '';
        $ips = $this->getIpDropdown($view, $ipDropCount);

        if (!empty($request->offsetGet('period_from'))) {
            $params['criteria']['period_from'] = date_format(date_create($request->offsetGet('period_from')), 'Y-m-d H:i:s');
        }

        if (!empty($request->offsetGet('period_to'))) {
            $params['criteria']['period_to'] = date_format(date_create($request->offsetGet('period_to') .' 23:59'), 'Y-m-d H:i:s');;
        }

        if (!empty($selectedOrgs)) {
            $selectedOrgs = array_unique($selectedOrgs);
            $params['criteria']['org_ids'] = $selectedOrgs;
        }

        if (!empty($selectedUser)) {
            $params['criteria']['user_id'] = $selectedUser;
        }

        if (!empty($selectedIp)) {
            $params['criteria']['ip_address'] = $selectedIp;
        }

        if ($view == 'login') {
            $filename = "loginHistory.csv";
            $params['criteria']['actions'] = [ActionsHistory::TYPE_LOGIN];
        }

        if (!empty($selectedActions)) {
            $params['criteria']['actions'] = $selectedActions;
        }

        if (!empty($selectedModules)) {
            $params['criteria']['module'] = $selectedModules;
        }

        $params['criteria']['order'] = [
            'type'  => $request->offsetGet('order_type'),
            'field' => $request->offsetGet('order_field'),
        ];

        $rq = Request::create('api/listActionHistory', 'POST', $params);
        $api = new ApiHistory($rq);
        $res = $api->listActionHistory($rq)->getData();

        if ($res->success) {
            $history = $res->actions_history;
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

            $headers = array(
                'Content-Type' => 'text/csv',
            );

            return response()->download($path, $filename, $headers)->deleteFileAfterSend(true);
        }

        $paginationData = $this->getPaginationData(
            $history,
            $res->total_records,
            array_except(app('request')->input(), ['page',]),
            $perPage
        );

        return view('admin/history', [
            'class'             => 'user',
            'view'              => $view,
            'actionTypes'       => $actionTypes,
            'history'           => $paginationData['items'],
            'pagination'        => $paginationData['paginate'],
            'orgDropCount'      => count($this->getOrgDropdown()),
            'organisations'     => $organisations,
            'selectedOrgs'      => $selectedOrgs,
            'userDropCount'     => count($this->getUserDropdown()),
            'users'             => $users,
            'selectedUser'      => $selectedUser,
            'ipDropCount'       => count($this->getIpDropdown()),
            'ips'               => $ips,
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
