<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use \App\Signal;
use \Validator;

class SignalsController extends ApiController
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
                'resource_id'  => 'required|integer|digits_between:1,10',
                'description'  => 'required|string|max:8000',
                'firstname'    => 'required|string|max:100',
                'lastname'     => 'required|string|max:100',
                'email'        => 'required|email|max:191',
                'status'       => 'nullable|integer|digits_between:1,10',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $newSignal = new Signal;

                $newSignal->resource_id = $signalData['data']['resource_id'];
                $newSignal->descript = $signalData['data']['description'];
                $newSignal->firstname = $signalData['data']['firstname'];
                $newSignal->lastname = $signalData['data']['lastname'];
                $newSignal->email = $signalData['data']['email'];

                if (isset($signalData['data']['status'])) {
                    $newSignal->status = $signalData['data']['status'];
                } else {
                    $newSignal->status = Signal::TYPE_NEW;
                }

                $newSignal->save();

                return $this->successResponse(['signal_id :' . $newSignal->id]);
            } catch (QueryException $e) {
                Log::error($e->getMessage());
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
                'resource_id'  => 'nullable|integer|digits_between:1,10',
                'description'  => 'nullable|string|max:191',
                'firstname'    => 'nullable|string|max:100',
                'lastname'     => 'nullable|string|max:100',
                'email'        => 'nullable|email|max:191',
                'status'       => 'nullable|integer|digits_between:1,10',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $signalToEdit = Signal::find($editSignalData['signal_id']);

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
                    $signalToEdit->status = Signal::TYPE_NEW;
                }

                $signalToEdit->save();
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

                $signalToBeDeleted->delete();
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
                'status'       => 'nullable|integer|digits_between:1,10',
                'date_from'    => 'nullable|date',
                'date_to'      => 'nullable|date',
                'order'        => 'nullable|array',
                'search'       => 'nullable|string|max:191',
            ]);
        }

        $order = isset($data['criteria']['order']) ? $data['criteria']['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string|digits_between:1,10',
                'field'  => 'nullable|string|digits_between:1,10',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_signals_fail'), $validator->errors()->messages());
        }

        $result = [];
        $criteria = $request->json('criteria');

        $signalList = '';
        $columns = [
            'id',
            'resource_id',
            'descript',
            'firstname',
            'lastname',
            'email',
            'status',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        $signalList = Signal::select($columns);

        if (isset($criteria['signal_id'])) {
            $signalList->where('id', $criteria['signal_id']);
        }

        if (isset($criteria['order'])) {
            if (is_array($criteria['order'])) {
                if (!in_array($criteria['order']['field'], $columns)) {
                    unset($criteria['order']['field']);
                }
            }
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            $signalList->orderBy(
                $criteria['order']['field'],
                $criteria['order']['type'] == 'asc' ? 'asc' : 'desc'
            );
        }

        if (isset($criteria['status'])) {
            $signalList->where('status', $criteria['status']);
        }

        if (isset($criteria['search'])) {
            $search = $criteria['search'];

            $signalList->where('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('descript', 'like', '%' . $search . '%');
        }

        if (isset($criteria['date_from'])) {
            $signalList->where('created_at', '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $signalList->where('created_at', '<=', $criteria['date_to']);
        }

        $total_records = $signalList->count();

        if (isset($request['records_per_page']) || isset($request['page_number'])) {
            $signalList->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        $signalList = $signalList->get();

        if (!empty($signalList)) {
            foreach ($signalList as $singleSignal) {
                $result[] = [
                    'id' => $singleSignal->id,
                    'resource_id'   => $singleSignal->resource_id,
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

        return $this->successResponse([
            'total_records' => $total_records,
            'signals' => $result,
        ], true);
    }
}
