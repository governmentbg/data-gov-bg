<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Signal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\SignalController as ApiSignal;

class SignalController extends AdminController
{
    /**
     * Lists signals
     *
     * @param Request $request
     *
     * @return view with list of signals
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            if (!empty($request->status)) {
                $params['criteria']['status'] = $request->status;
            }

            if (!empty($request->from)) {
                $params['criteria']['date_from'] = date_format(date_create($request->from), 'Y-m-d H:i:s');
            }

            if (!empty($request->to)) {
                $params['criteria']['date_to'] = date_format(date_create($request->to .' 23:59'), 'Y-m-d H:i:s');
            }

            if (!empty($request->q)) {
                $params['criteria']['search'] = $request->q;
            }

            if (!empty($request->order)) {
                $params['criteria']['order']['field'] = $request->order;
                $params['criteria']['order']['type'] = 'asc';
            }

            $rq = Request::create('/api/listSignals', 'POST', $params);
            $api = new ApiSignal($rq);
            $result = $api->listSignals($rq)->getData();
            $statuses = Signal::getStatuses();

            $getParams = !empty($params['criteria']['search'])
                ? array_merge(array_except(app('request')->input(), ['page']), ['q' => $params['criteria']['search']])
                : array_except(app('request')->input(), ['page', 'q']);

            $signals = !empty($result->signals)
                ? $this->getModelUsernames($result->signals)
                : $result->signals;

            $resourceIds = [];
            foreach ($signals as $signal) {
                $resourceIds[] = $signal->resource_id;
            }

            $paginationData = $this->getPaginationData(
                $signals,
                $result->total_records,
                $getParams,
                $perPage
            );

            return view(
                'admin/signalsList',
                [
                    'class'      => 'user',
                    'signals'    => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                    'statuses'   => $statuses,
                    'search'     => !empty($request->q) ? $request->q : null,
                    'range'      => [
                        'from' => !empty($request->from) ? $request->from : null,
                        'to'   => !empty($request->to) ? $request->to : null
                    ],
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit a signal based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $signal = Signal::where('id', $id)->first();
            $statuses = Signal::getStatuses();

            if (!is_null($signal)) {
                $signal->description = $signal->descript;
                $signal = $this->getModelUsernames($signal);

                if ($request->has('edit')) {
                    $rq = Request::create('/api/editSignal', 'POST', [
                        'signal_id' => $id,
                        'data' => [
                            'email'       => $request->offsetGet('email'),
                            'description' => $request->offsetGet('description'),
                            'firstname'   => $request->offsetGet('firstname'),
                            'lastname'    => $request->offsetGet('lastname'),
                            'status'      => !is_null($request->offsetGet('status'))
                                ? $request->offsetGet('status')
                                : Signal::STATUS_NEW,
                        ]
                    ]);

                    $api = new ApiSignal($rq);
                    $result = $api->editSignal($rq)->getData();

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
                'admin/signalEdit',
                ['class' => $class, 'signal' => $signal, 'statuses' => $statuses]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
    * Deletes signal
    *
    * @param Request $request
    * @param integer $id
    *
    * @return view to previous page
    */
   public function delete(Request $request, $id)
   {
        if (Role::isAdmin()) {
            $signal = Signal::where('id', $id)->first();
            if (!is_null($signal)) {
                $rq = Request::create('/api/deleteSignal', 'POST', [
                    'signal_id'  => $id,
                ]);
                $api = new ApiSignal($rq);
                $result = $api->deleteSignal($rq)->getData();

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