<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\DataRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\DataRequestController as ApiDataRequest;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class DataRequestController extends AdminController
{
    /**
     * Lists datarequests
     *
     * @param Request $request
     * @return view with a list of data requests
     */
    public function listDataRequests(Request $request)
    {
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

        $params['criteria']['order']['field'] = 'created_at';
        $params['criteria']['order']['type'] = 'desc';

        if (isset($request->order)) {
            $params['criteria']['order']['field'] = $request->order;
        }

        if (isset($request->order_type)) {
            $params['criteria']['order']['type'] = $request->order_type;
        }

        $req = Request::create('/api/listDataRequests', 'POST', $params);
        $api = new ApiDataRequest($req);
        $result = $api->listDataRequests($req)->getData();
        $getParams = array_except(app('request')->input(), ['page', 'q']);
        $statuses = DataRequest::getDataRequestStatuses();

        $dataRequests = !empty($result->dataRequests)
        ? $this->getModelUsernames($result->dataRequests)
        : $result->dataRequests;

        $paginationData = $this->getPaginationData(
            $dataRequests,
            $result->total_records,
            $getParams,
            $perPage
        );

        return view('/admin/dataRequests', [
            'class'         => 'user',
            'dataRequests'  => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'statuses'      => $statuses,
            'search'     => isset($request->q) ? $request->q : null,
            'range'      => [
                'from' => isset($request->from) ? $request->from : null,
                'to'   => isset($request->to) ? $request->to : null
            ],
        ]);
    }

    /**
     * Edits a data request based on id
     *
     * @param Request $request
     * @param integer $id
     * @return success message or error on fail
     */
    public function editDataRequest(Request $request, $id)
    {
        $class = 'user';
        $dataRequest = DataRequest::where('id', $id)->first();

        if ($dataRequest) {
            $params = [
                'active' => true,
                'approved' => true,
                'org_ids' => [$dataRequest->org_id]
            ];

            $statuses = DataRequest::getDataRequestStatuses();

            $orgRequest = Request::create('/api/listOrganisations', 'POST', ['criteria' => $params]);
            $apiOrganisations = new ApiOrganisation($orgRequest);
            $orgList = $apiOrganisations->listOrganisations($orgRequest)->getData();
            $organisations = !empty($orgList->organisations) ? $orgList->organisations : null;

            if ($request->has('edit')) {
                $dataRequest = Request::create('/api/editDataRequest', 'POST', [
                    'request_id' => $id,
                    'data'       => [
                        'org_id'          => $request->offsetGet('org_id'),
                        'description'     => $request->offsetGet('description'),
                        'published_url'   => $request->offsetGet('published_url') ? $request->offsetGet('published_url') : '',
                        'contact_name'    => $request->offsetGet('contact_name') ? $request->offsetGet('contact_name') : '',
                        'email'           => $request->offsetGet('email'),
                        'notes'           => $request->offsetGet('notes') ? $request->offsetGet('notes') : '',
                        'status'          => $request->offsetGet('status'),
                    ]
                ]);

                $api = new ApiDataRequest($dataRequest);
                $result = $api->editDataRequest($dataRequest)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));

                    return back()->withErrors(isset($result->errors) ? $result->errors : []);
                }
            }
        }

        return view('admin/dataRequestEdit', compact('class', 'dataRequest', 'organisations', 'statuses'));
    }

    /**
     * Deletes a datarequest based on id
     *
     * @param Request $request
     * @param integer $id
     * @return success messsage or failure
     */
    public function deleteDataRequest(Request $request, $id)
    {
        $rq = Request::create('/api/deleteDataRequest', 'POST', [
            'request_id' => $id,
        ]);

        $api = new ApiDataRequest($rq);
        $result = $api->deleteDataRequest($rq)->getData();

        if ($result->success) {
            $request->session()->flash('alert-success', __('custom.delete_success'));

            return redirect('/admin/data-requests/list');
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));

            return redirect('/admin/data-requests/list')->withErrors(isset($result->errors) ? $result->errors : []);
        }
    }
}
