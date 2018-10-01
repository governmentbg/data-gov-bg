<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;
use App\TermsOfUseRequest;
use App\RoleRight;
use App\Module;
use App\ActionsHistory;

class TermsOfUseRequestController extends ApiController
{
    /**
     * API function for adding new terms of use requests
     * Route::post('/sendTermsOfUseRequest', 'Api\TermsOfUseRequestController@sendTermsOfUseRequest');
     *
     * @param string api_key - optional
     * @param array data - required
     * @param string data[description] - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param int data[status] - optional
     *
     * @return JsonResponse - JSON containing: On success - Status 200, ID of new terms of use request record / On fail - Status 500 error message
     */
    public function sendTermsOfUseRequest(Request $request)
    {
        $data = $request->get('data', []);
        //validate request data
        $validator = \validator::make($data, [
            'description'   => 'required|string|max:8000',
            'firstname'     => 'required|string|max:100',
            'lastname'      => 'required|string|max:100',
            'email'         => 'required|email|string|max:191',
            'status'        => 'integer|in:'. implode(',', array_keys(TermsOfUseRequest::getStatuses())),
        ]);

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::TERMS_OF_USE_REQUESTS,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            // set default values to optional fields
            if (!isset($data['status'])) {
                $data['status'] = TermsOfUseRequest::STATUS_NEW;
            }

            //prepare model data
            $newTerms = new TermsOfUseRequest;
            $newTerms->descript = $data['description'];
            unset($data['description']);
            $newTerms->fill($data);

            try {
                $newTerms->save();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }

            $logData = [
                'module_name'      => Module::getModuleName(Module::TERMS_OF_USE_REQUESTS),
                'action'           => ActionsHistory::TYPE_ADD,
                'action_object'    => $newTerms->id,
                'action_msg'       => 'Sent terms of use request',
            ];

            Module::add($logData);

            return $this->successResponse(['id' => $newTerms->id], true);
        }

        return $this->errorResponse(__('custom.send_terms_request_fail'), $validator->errors()->messages());
    }

    /**
     * API function for editing terms of use requests
     * Route::post('/editTermsOfUseRequest', 'Api\TermsOfUseRequestController@editTermsOfUseRequest');
     *
     * @param string api_key - required
     * @param integer request_id - required
     * @param array data - required
     * @param string data[description] - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param int data[status] - optional
     *
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function editTermsOfUseRequest(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'request_id'        => 'required|numeric|exists:terms_of_use_requests,id',
            'data'              => 'required|array'
        ]);

        if (!$validator->fails()) {
            $validator = \Validator::make($post['data'], [
                'description'  => 'required|string|max:8000',
                'firstname'    => 'required|string|max:100',
                'lastname'     => 'required|string|max:100',
                'email'        => 'required|email|string|max:191',
                'status'       => 'integer|in:'. implode(',', array_keys(TermsOfUseRequest::getStatuses())),
            ]);
        }

        if (!$validator->fails()) {
            $data = $post['data'];
            $terms = TermsOfUseRequest::find($post['request_id']);
            $rightCheck = RoleRight::checkUserRight(
                Module::TERMS_OF_USE_REQUESTS,
                RoleRight::RIGHT_EDIT,
                [],
                [
                    'created_by' => $terms->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $terms->descript = $data['description'];
            unset($data['description']);
            $terms->fill($data);

            try {
                $terms->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::TERMS_OF_USE_REQUESTS),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $terms->id,
                    'action_msg'       => 'Edited terms of use request',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_terms_request_fail'), $validator->errors()->messages());
    }

    /**
     * API function for deleting terms of use requests
     * Route::post('/deleteTermsOfUse', 'Api\TermsOfUseController@deleteTermsOfUse');
     *
     * @param string api_key - required
     * @param integer request_id - required
     *
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function deleteTermsOfUseRequest(Request $request)
    {
        $post = $request->all();
        $validator = \Validator::make($post, [
            'request_id'  => 'required|exists:terms_of_use_requests,id|digits_between:1,10'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.delete_terms_request_fail'), $validator->errors()->messages());
        }

        if (empty($terms = TermsOfUseRequest::find($post['request_id']))) {
            return $this->errorResponse(__('custom.delete_terms_request_fail'));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::TERMS_OF_USE_REQUESTS,
            RoleRight::RIGHT_ALL,
            [],
            [
                'created_by' => $terms->created_by
            ]
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }
        try {
            $terms->delete();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.delete_terms_request_fail'));
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::TERMS_OF_USE_REQUESTS),
            'action'           => ActionsHistory::TYPE_DEL,
            'action_object'    => $post['request_id'],
            'action_msg'       => 'Deleted terms of use request',
        ];

        Module::add($logData);

        return $this->successResponse();
    }

    /**
     * API function for listing multiple terms of use request records
     * Route::post('/listTermsOfUseRequests', 'Api\TermsOfUseRequestController@listTermsOfUseRequests');
     *
     * @param string api_key - required
     * @param int records_per_page - optional
     * @param int page_number - optional
     * @param array criteria - optional
     * @param int criteria[request_id] - optional
     * @param int criteria[status] - optional
     * @param datetime criteria[date_from] - optional
     * @param datetime criteria[date_to] - optional
     * @param string criteria[search] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - required
     * @param string criteria[order][field] - required
     *
     * @return JsonResponse - JSON containing: On success - Status 200 list of terms of use / On fail - Status 500 error message
     */
    public function listTermsOfUseRequests(Request $request)
    {
        $data = $request->all();

        $validator = \Validator::make($data, [
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
        ]);

        $criteria = isset($data['criteria']) ? $data['criteria'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($criteria, [
                'request_id'   => 'nullable|integer|exists:terms_of_use_requests,id|digits_between:1,10',
                'status'       => 'nullable|integer|digits_between:1,3',
                'date_from'    => 'nullable|date',
                'date_to'      => 'nullable|date',
                'search'       => 'nullable|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = \Validator::make($order, [
                'type'   => 'nullable|string|in:asc,desc|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::TERMS_OF_USE_REQUESTS,
                RoleRight::RIGHT_VIEW
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $filterColumn = 'created_at';
            $criteria = $request->offsetGet('criteria');

            if (empty($criteria['search'])) {
                $query = TermsOfUseRequest::select();
            } else {
                $ids = TermsOfUseRequest::search($criteria['search'])->get()->pluck('id');
                $query = TermsOfUseRequest::whereIn('id', $ids);
            }

            if (!empty($criteria['request_id'])) {
                $query->where('id', '=', $criteria['request_id']);
            }

            if (!empty($criteria['status'])) {
                $query->where('status', '=', $criteria['status']);
            }

            if (!empty($criteria['date_from'])) {
                $query->where($filterColumn, '>=', $criteria['date_from']);
            }

            if (!empty($criteria['date_to'])) {
                $query->where($filterColumn, '<=', $criteria['date_to']);
            }

            $total_records = $query->count();

            $columns = [
                'id',
                'description',
                'firstname',
                'lastname',
                'email',
                'status',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ];

            if (isset($order['field'])) {
                if (!in_array($order['field'], $columns)) {
                    return $this->errorResponse(__('custom.invalid_sort_field'));
                }
            }

            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $query->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $result = [];
            $terms = $query->get();

            if (!$terms->isEmpty()) {
                $result = $this->prepareTerms($terms);
            }

            $logData = [
                'module_name'      => Module::getModuleName(Module::TERMS_OF_USE_REQUESTS),
                'action'           => ActionsHistory::TYPE_SEE,
                'action_msg'       => 'Listed terms of use requests',
            ];

            Module::add($logData);

            return $this->successResponse([
                'total_records'         => $total_records,
                'terms_of_use_requests' => $result
            ], true);
        }
        return $this->errorResponse(__('custom.list_terms_request_fail'), $validator->errors()->messages());
    }

    /**
     * Prepare collection of terms of use request records for response
     *
     * @param Collection $terms - list of terms of use records
     * @return array - ready for response records data
     */
    private function prepareTerms($terms)
    {
        foreach ($terms as $term) {
            $results[] = [
                    'id'            => $term->id,
                    'description'   => $term->descript,
                    'firstname'     => $term->firstname,
                    'lastname'      => $term->lastname,
                    'email'         => $term->email,
                    'status'        => $term->status,
                    'created_at'    => isset($term->created_at) ? $term->created_at->toDateTimeString() : null,
                    'updated_at'    => isset($term->updated_at) ? $term->updated_at->toDateTimeString() : null,
                    'created_by'    => $term->created_by,
                    'updated_by'    => $term->updated_by,
                ];
        }

        return $results;
    }
}
