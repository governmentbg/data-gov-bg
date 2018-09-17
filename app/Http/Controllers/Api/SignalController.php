<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\RoleRight;
use App\Signal;
use \Validator;
use App\Module;
use App\DataSet;
use App\Resource;
use App\ActionsHistory;
use App\User;

class SignalController extends ApiController
{
    /**
     * Send a signal
     *
     * @param array data - required
     * @param integer data[resource_id] - required
     * @param string data[description] - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param integer data[status] - optional
     *
     * @return json response with signal_id or error message
     */
    public function sendSignal(Request $request)
    {
        $signalData = $request->all();

        $validator = Validator::make($signalData, [
            'data'              => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($signalData['data'], [
                'resource_id'  => 'required|integer|digits_between:1,10|exists:resources,id',
                'description'  => 'required|string|max:8000',
                'firstname'    => 'required|string|max:100',
                'lastname'     => 'required|string|max:100',
                'email'        => 'required|email|max:191',
                'status'       => 'nullable|integer|in:'. implode(',', array_keys(Signal::getStatuses()))
            ]);
        }

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();

                $newSignal = new Signal;

                $newSignal->resource_id = $signalData['data']['resource_id'];
                $newSignal->descript = $signalData['data']['description'];
                $newSignal->firstname = $signalData['data']['firstname'];
                $newSignal->lastname = $signalData['data']['lastname'];
                $newSignal->email = $signalData['data']['email'];

                if (isset($signalData['data']['status'])) {
                    $newSignal->status = $signalData['data']['status'];
                } else {
                    $newSignal->status = Signal::STATUS_NEW;
                }

                $saved = $newSignal->save();

                // mark related resource as reported
                // if ($saved && $newSignal->status == Signal::STATUS_NEW) {
                if ($saved) {
                    $resource = Resource::where('id', $newSignal->resource_id)->first();
                    $resource->is_reported = Resource::REPORTED_TRUE;
                    $saved = $resource->save();
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::SIGNALS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $newSignal->id,
                    'action_msg'       => 'Sent signal',
                ];

                Module::add($logData);

                DB::commit();
            } catch (QueryException $e) {
                $saved = false;

                DB::rollback();

                Log::error($e->getMessage());
            }

            if ($saved) {
                try {
                    if (($user = User::find($resource->created_by)) && !empty($user->email)) {
                        $mailData = [
                            'user'          => $user->firstname ?: $user->username,
                            'resource_name' => $resource->name,
                            'dataset_uri'   => $resource->dataSet->uri,
                            'dataset_name'  => $resource->dataSet->name,
                        ];

                        Mail::send('mail/signalMail', $mailData, function ($m) use ($user) {
                            $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                            $m->to($user->email, $user->firstname);
                            $m->subject(__('custom.signal_subject'));
                        });
                    }
                } catch (\Exception $ex) {
                    Log::error($ex->getMessage());
                }

                return $this->successResponse(['signal_id :' . $newSignal->id]);
            }
        }

        return $this->errorResponse(__('custom.send_signal_fail'), $validator->errors()->messages());
    }

    /**
     * Edit a signal based on input
     *
     * @param integer signal_id - required
     * @param array data - required
     * @param integer data[resource_id] - optional
     * @param string data[description] - optional
     * @param string data[firstname] - optional
     * @param string data[lastname] - optional
     * @param string data[email] - optional
     * @param integer data[status] - optional
     *
     * @return json response with success or error message
     */
    public function editSignal(Request $request)
    {
        $editSignalData = $request->all();

        $validator = Validator::make($editSignalData, [
            'signal_id'         => 'required|integer|exists:signals,id|digits_between:1,10',
            'data'              => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($editSignalData['data'], [
                'resource_id'  => 'sometimes|integer|digits_between:1,10',
                'description'  => 'sometimes|string|max:191',
                'firstname'    => 'sometimes|string|max:100',
                'lastname'     => 'sometimes|string|max:100',
                'email'        => 'sometimes|email|max:191',
                'status'       => 'nullable|integer|in:'. implode(',', array_keys(Signal::getStatuses())),
            ]);
        }

        if (!$validator->fails()) {
            try {
                $signalToEdit = Signal::find($editSignalData['signal_id']);
                $rightCheck = RoleRight::checkUserRight(
                    Module::SIGNALS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $signalToEdit->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                if (isset($editSignalData['data']['resource_id'])) {
                    $signalToEdit->resource_id = $editSignalData['data']['resource_id'];
                }

                if (isset($editSignalData['data']['description'])) {
                    $signalToEdit->descript = $editSignalData['data']['description'];
                }

                if (isset($editSignalData['data']['firstname'])) {
                    $signalToEdit->firstname = $editSignalData['data']['firstname'];
                }

                if (isset($editSignalData['data']['lastname'])) {
                    $signalToEdit->lastname = $editSignalData['data']['lastname'];
                }

                if (isset($editSignalData['data']['email'])) {
                    $signalToEdit->email = $editSignalData['data']['email'];
                }

                if (isset($editSignalData['data']['status'])) {
                    $signalToEdit->status = $editSignalData['data']['status'];
                } else {
                    $signalToEdit->status = Signal::STATUS_NEW;
                }

                $signalToEdit->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::SIGNALS),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $signalToEdit->id,
                    'action_msg'       => 'Edited signal',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_signal_fail'), $validator->errors()->messages());
    }

    /**
     * Delete a signal based on Id
     *
     * @param integer signal_id - required
     *
     * @return json response with success or error
     */
    public function deleteSignal(Request $request)
    {
        $deleteData = $request->all();
        $validator = Validator::make($deleteData, [
            'signal_id' => 'required|integer|exists:signals,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            try {
                $signalToBeDeleted = Signal::find($deleteData['signal_id']);
                $rightCheck = RoleRight::checkUserRight(
                    Module::SIGNALS,
                    RoleRight::RIGHT_ALL,
                    [],
                    [
                        'created_by' => $signalToBeDeleted->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $signalToBeDeleted->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::SIGNALS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $deleteData['signal_id'],
                    'action_msg'       => 'Deleted signal',
                ];

                Module::add($logData);

                return $this->successResponse();

            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_signal_fail'), $validator->errors()->messages());
    }

    /**
     * List and filter signals based on input
     *
     * @param array criteria - required
     * @param integer criteria[signal_id] - optional
     * @param integer criteria[status] - optional
     * @param date criteria[date_from] - optional
     * @param date criteria[date_to] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param string criteria[search] - optional
     * @param integer criteria[records_per_page] - optional
     * @param integer criteria[page_number] - optional
     *
     * @return json response with success or error message
     */
    public function listSignals(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10'
        ]);

        $criteria = isset($data['criteria']) ? $data['criteria'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'signal_id'    => 'nullable|integer|digits_between:1,10',
                'status'       => 'nullable|integer|digits_between:1,3',
                'date_from'    => 'nullable|date',
                'date_to'      => 'nullable|date',
                'order'        => 'nullable|array',
                'search'       => 'nullable|string|max:191',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string',
                'field'  => 'nullable|string',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_signals_fail'), $validator->errors()->messages());
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::SIGNALS,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        $result = [];
        $query = Signal::select();

        if (isset($criteria['search'])) {
            $ids = Signal::search($criteria['search'])->get()->pluck('id');
            $query->whereIn('id', $ids);
        }

        if (isset($criteria['signal_id'])) {
            $query->where('id', $criteria['signal_id']);
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['date_from'])) {
            $query->where('created_at', '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $query->where('created_at', '<=', $criteria['date_to']);
        }

        $total_records = $query->count();
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'desc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'created_at';

        if (!empty($order)) {
            $query->orderBy($order['field'], $order['type']);
        }

        if ($request->has('records_per_page') || $request->has('page_number')) {
            $query->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        $signals = $query->get();

        $resourceIds = $resourceDatasets = $datasets = [];

        foreach ($signals as $signal) {
            $resourceIds[] = $signal->resource_id;
        }

        $resources = \App\Resource::whereIn('id', $resourceIds)->get();

        foreach ($resources as $resource) {
            $resourceDatasets[$resource->id] = $resource->data_set_id;
        }

        $datasetModels = Dataset::whereHas('resource', function ($query) use ($resourceIds) {
            $query->whereIn('id', $resourceIds);
        })->get();

        foreach ($datasetModels as $dataset) {
            $datasets[$dataset->id] = [
                'name'  => $dataset->name,
                'uri'   => $dataset->uri,
            ];
        }

        if (!empty($signals)) {
            foreach ($signals as $singleSignal) {
                $result[] = [
                    'id'            => $singleSignal->id,
                    'resource_id'   => $singleSignal->resource_id,
                    'dataset_name'  => $datasets[$resourceDatasets[$singleSignal->resource_id]]['name'],
                    'dataset_uri'   => $datasets[$resourceDatasets[$singleSignal->resource_id]]['uri'],
                    'description'   => $singleSignal->descript,
                    'firstname'     => $singleSignal->firstname,
                    'lastname'      => $singleSignal->lastname,
                    'email'         => $singleSignal->email,
                    'status'        => $singleSignal->status,
                    'created_at'    => date($singleSignal->created_at),
                    'updated_at'    => date($singleSignal->updated_at),
                    'created_by'    => $singleSignal->created_by,
                    'updated_by'    => $singleSignal->updated_by,
                ];
            }
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::SIGNALS),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed signals',
        ];

        Module::add($logData);
        return $this->successResponse([
            'total_records' => $total_records,
            'signals' => $result,
        ], true);
    }
}
