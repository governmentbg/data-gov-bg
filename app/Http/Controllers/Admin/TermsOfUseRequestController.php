<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\UserSetting;
use App\Organisation;
use App\TermsOfUseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\TermsOfUseRequestController as ApiTermsOfUseRequest;

class TermsOfUseRequestController extends AdminController
{
    /**
     * Lists terms of use requests
     *
     * @param Request $request
     *
     * @return view with list of terms of use requests
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            if (isset($request->status)) {
                $params['criteria']['status'] = $request->status;
            }

            if (isset($request->from)) {
                $params['criteria']['date_from'] = date_format(date_create($request->from), 'Y-m-d H:i:s');
            }

            if (isset($request->to)) {
                $params['criteria']['date_to'] = date_format(date_create($request->to .' 23:59'), 'Y-m-d H:i:s');
            }

            if (isset($request->q)) {
                $params['criteria']['search'] = $request->q;
            }

            if (isset($request->order)) {
                $params['criteria']['order']['field'] = $request->order;
                $params['criteria']['order']['type'] = 'asc';
            }

            $rq = Request::create('/api/listTermsOfUseRequest', 'POST', $params);
            $api = new ApiTermsOfUseRequest($rq);
            $result = $api->listTermsOfUseRequests($rq)->getData();
            $statuses = TermsOfUseRequest::getStatuses();

            $getParams = !empty($params['criteria']['search'])
                ? array_merge(array_except(app('request')->input(), ['page']), ['q' => $params['criteria']['search']])
                : array_except(app('request')->input(), ['page', 'q']);

            $termsRequests = !empty($result->terms_of_use_requests)
                ? $this->getModelUsernames($result->terms_of_use_requests)
                : $result->terms_of_use_requests;

            $paginationData = $this->getPaginationData(
                $termsRequests,
                $result->total_records,
                $getParams,
                $perPage
            );

            return view(
                'admin/termsOfUseRequestsList',
                [
                    'class'      => 'user',
                    'terms'      => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                    'statuses'   => $statuses,
                    'search'     => isset($request->q) ? $request->q : null,
                    'range'      => [
                        'from' => isset($request->from) ? $request->from : null,
                        'to'   => isset($request->to) ? $request->to : null
                    ],
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
