<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;
use App\TermsOfUseRequest;

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
        $data = $request->offsetGet('data');
        //validate request data
        $validator = \validator::make($data, [
            'description'   => 'required|string',
            'firstname'     => 'required|string|max:100',
            'lastname'      => 'required|string|max:100',
            'email'         => 'required|email|string|max:191',
            'status'        => 'integer|min:1'
        ]);

        if (!$validator->fails()) {
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

            return $this->successResponse(['id' => $newTerms->id], true);
        }

        return $this->errorResponse('Send terms of use request failure', $validator->errors()->messages());
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
            'data.description'  => 'required|string',
            'data.firstname'    => 'required|string|max:100',
            'data.lastname'     => 'required|string|max:100',
            'data.email'        => 'required|email|string|max:191',
            'data.status'       => 'integer|min:1'
        ]);

        if (!$validator->fails()) {
            $data = $post['data'];
            $terms = TermsOfUseRequest::find($post['request_id']);
            $terms->descript = $data['description'];
            unset($data['description']);
            $terms->fill($data);

            try {
                $terms->save();

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Edit terms of use request failure', $validator->errors()->messages());
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
            'request_id'  => 'required|exists:terms_of_use_requests,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete terms of use failure');
        }

        if (empty($terms = TermsOfUseRequest::find($post['request_id']))) {
            return $this->errorResponse('Delete terms of use request failure');
        }

        try {
            $terms->delete();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse('Delete terms of use request failure');
        }

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
        $validator = \Validator::make($request->all(), [
            'criteria'              => 'nullable|array',
            'criteria.request_id'   => 'nullable|integer|exists:terms_of_use_requests,id',
            'criteria.status'       => 'nullable|integer',
            'criteria.date_from'    => 'nullable|date',
            'criteria.date_to'      => 'nullable|date',
            'criteria.search'       => 'nullable|string',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string|in:asc,desc',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
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

            return $this->successResponse([
                'total_records'         => $total_records,
                'terms_of_use_requests' => $result
            ], true);
        }
        return $this->errorResponse('List terms of use failure');
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
