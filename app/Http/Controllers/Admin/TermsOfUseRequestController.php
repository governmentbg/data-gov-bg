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

    /**
     * Edit a terms of use request based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $termsRequest = TermsOfUseRequest::where('id', $id)->first();
            $statuses = TermsOfUseRequest::getStatuses();

            if (!is_null($termsRequest)) {
                $termsRequest->description = $termsRequest->descript;
                $termsRequest = $this->getModelUsernames($termsRequest);

                if ($request->has('edit')) {
                    $rq = Request::create('/api/editTermsOfUseRequest', 'POST', [
                        'request_id' => $id,
                        'data' => [
                            'email'       => $request->offsetGet('email'),
                            'description' => $request->offsetGet('description'),
                            'firstname'   => $request->offsetGet('firstname'),
                            'lastname'    => $request->offsetGet('lastname'),
                            'status'      => !is_null($request->offsetGet('status'))
                                ? $request->offsetGet('status')
                                : TermsOfUseRequest::STATUS_NEW,
                        ]
                    ]);

                    $api = new ApiTermsOfUseRequest($rq);
                    $result = $api->editTermsOfUseRequest($rq)->getData();

                    if ($result->success) {
                        $request->session()->flash('alert-success', __('custom.edit_success'));

                        return back()->withInput(Input::all());
                    } else {
                        $request->session()->flash('alert-danger', __('custom.edit_error'));

                        return back()->withErrors(isset($result->errors) ? $result->errors : []);
                    }

                }
            } else {
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return view(
                'admin/termsOfUseRequestEdit',
                ['class' => $class, 'termsRequest' => $termsRequest, 'statuses' => $statuses]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
    * Deletes terms of use request
    *
    * @param Request $request
    * @param integer $id
    *
    * @return view to previous page
    */
   public function delete(Request $request, $id)
   {
        if (Role::isAdmin()) {
            $termsRequest = TermsOfUseRequest::where('id', $id)->first();
            if (!is_null($termsRequest)) {
                $rq = Request::create('/api/deleteTermsOfUseRequest', 'POST', [
                    'request_id'  => $id,
                ]);
                $api = new ApiTermsOfUseRequest($rq);
                $result = $api->deleteTermsOfUseRequest($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.delete_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.delete_error'));
                }

                return back();
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));

                return back();
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
