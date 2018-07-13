<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use \App\Signal;
use \Validator;

class SignalsController extends ApiController
{
    /**
     * Send a signal
     *
     * @param Request $request
     * @return json response
     */
    public function sendSignal(Request $request)
    {
        $signalData = $request->all();

        $validator = Validator::make($signalData, [
            'data' => 'required|array',
            'data.resource_id' => 'required|integer',
            'data.description' => 'required|string',
            'data.firstname' => 'required|string',
            'data.lastname' => 'required|string',
            'data.email' => 'required|email',
            'data.status' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Send signal failure');
        }

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
        try {
            $newSignal->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Send signal failure');
        }
        return $this->successResponse(['signal_id :' . $newSignal->id]);

    }

    /**
     * Edit a signal based on input
     *
     * @param Request $request
     * @return json response
     */
    public function editSignal(Request $request)
    {
        $editSignalData = $request->all();

        $validator = Validator::make($editSignalData, [
            'signal_id' => 'required|integer',
            'data' => 'required|array',
            'data.resource_id' => 'integer',
            'data.description' => 'string',
            'data.firstname' => 'string',
            'data.lastname' => 'string',
            'data.email' => 'email',
            'data.status' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Edit signal failure');
        }

        $signalToEdit = Signal::find($editSignalData['signal_id']);

        if ($signalToEdit) {
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

            try {
                $signalToEdit->save();
            } catch (QueryException $e) {
                return $this->errorResponse('Signal edit failure');
            }
        } else {
            return $this->errorResponse('Signal edit failure');
        }
        return $this->successResponse();
    }

    /**
     * Delete a signal based on Id
     *
     * @param Request $request
     * @return json response
     */
    public function deleteSignal(Request $request)
    {
        $deleteData = $request->all();
        $validator = Validator::make($deleteData, [
            'signal_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete psignalage failure');
        }

        $signalToBeDeleted = Signal::find($deleteData['signal_id']);

        if ($signalToBeDeleted) {
            try {
                $signalToBeDeleted->delete();
            } catch (QueryException $e) {
                return $this->errorResponse('Delete signal failure');
            }
        } else {
            return $this->errorResponse('Delete signal failure');
        }
        return $this->successResponse();
    }

    /**
     * Lists and filters signals based on input
     *
     * @param Request $request
     * @return json response
     */
    public function listSignals(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'criteria' => 'array',
            'criteria.signal_id' => 'integer',
            'criteria.status' => 'integer',
            'criteria.date_from' => 'date',
            'criteria.date_to' => 'date',
            'criteria.order' => 'array',
            'criteria.order.type' => 'string',
            'criteria.order.field' => 'string',
            'criteria.search' => 'string',
            'records_per_page' => 'integer',
            'page_number' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List pages failure');
        }

        $result = [];

        $criteria = $request->json('criteria');

        $signalList = '';
        $signalList = Signal::select(
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
            'updated_by');

        if (is_null($criteria)) {
            $signalList = $signalList;
        }

        if (isset($criteria['signal_id'])) {
            $signalList = $signalList->where('id', $criteria['signal_id']);
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if ($criteria['order']['type'] == 'desc') {
                $signalList = $signalList->orderBy($criteria['order']['field'], 'desc');
            } else {
                if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                    $signalList = $signalList->orderBy($criteria['order']['field'], 'asc');
                }
            }
        }

        if (isset($criteria['status'])) {
            $signalList = $signalList->where('status', $criteria['status']);
        }

        if (isset($criteria['search'])) {
            $search = $criteria['search'];

            $signalList = $signalList->where('firstname', 'like', '%' . $search . '%')
                ->orWhere('lastname', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('descript', 'like', '%' . $search . '%');
        }

        if (isset($criteria['date_from'])) {
            $signalList = $signalList->where('created_at', '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $signalList = $signalList->where('created_at', '<=', $criteria['date_to']);
        }

        if (isset($request['records_per_page']) || isset($request['page_number'])) {
            $signalList = $signalList->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        $signalList = $signalList->get();

        if (!empty($signalList)) {
            $total_records = $signalList->count();
            foreach ($signalList as $singleSignal) {
                $result[] = [
                    'id' => $singleSignal->id,
                    'resource_id' => $singleSignal->resource_id,
                    'description' => $singleSignal->descript,
                    'firstname' => $singleSignal->firstname,
                    'lastname' => $singleSignal->lastname,
                    'email' => $singleSignal->email,
                    'status' => $singleSignal->status,
                    'created_at' => date($singleSignal->created_at),
                    'updated_at' => date($singleSignal->updated_at),
                    'created_by' => $singleSignal->created_by,
                    'updated_by' => $singleSignal->updated_by,
                ];
            }
        }
        return $this->successResponse([
            'total_records' => $total_records,
            'signals' => $result,
        ], true);
    }
}
