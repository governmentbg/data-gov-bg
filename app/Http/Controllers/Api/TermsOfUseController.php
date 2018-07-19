<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;
use App\TermsOfUse;


class TermsOfUseController extends ApiController
{
    /**
     * API function for adding new terms of use
     * Route::post('/addTermsOfUse', 'Api\TermsOfUseController@addTermsOfUse');
     *
     * @param Request $request - JSON containing api_key (string), data (object) containing new Terms of use data
     * @return JsonResponse - JSON containing: On success - Status 200, ID of new terms of use record / On fail - Status 500 error message
     */
    public function addTermsOfUse(Request $request)
    {
        $data = $request->data;
        //validate request data
        $validator = \validator::make($data, [
            'name'          => 'required|string|max:100',
            'description'   => 'required|string',
            'locale'        => 'required|string|max:5',
            'active'        => 'required|boolean',
            'is_default'    => 'boolean',
            'ordering'      => 'numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Add terms of use failure');
        }

        // set default values to optional fields
        if (!isset($data['is_default'])) {
            $data['is_default'] = 0;
        }

        if (!isset($data['ordering'])) {
            $data['ordering'] = 1;
        }


        //prepare model data
        $newTerms = new TermsOfUse;
        $newTerms->name = $this->trans(app()->getLocale(), $data['name']);
        $newTerms->descript = $this->trans(app()->getLocale(), $data['description']);
        unset($data['locale'], $data['name'], $data['description']);
        $newTerms->fill($data);

        try {
            $newTerms->save();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse('Add terms of use failure');
        }

        return $this->successResponse(['id' => $newTerms->id], true);
    }

    /**
     * API function for editing terms of use
     * Route::post('/editTermsOfUse', 'Api\TermsOfUseController@editTermsOfUse');
     *
     * @param Request $request - JSON containing api_key (string), id of edited section, data (object) containing updated terms of use data
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function editTermsOfUse(Request $request)
    {
        $post = $request->all();
        //validate request data
        $validator = \validator::make($post, [
            'terms_id'          => 'required||numeric|exists:terms_of_use,id',
            'data.name'         => 'required|string|max:100',
            'data.description'  => 'required|string',
            'data.locale'       => 'required|string|max:5',
            'data.active'       => 'required|boolean',
            'data.is_default'   => 'boolean',
            'data.ordering'     => 'numeric',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Edit terms of use failure');
        }

        $data = $request->data;
        $terms = TermsOfUse::find($post['terms_id']);
        $terms->name = $this->trans(app()->getLocale(), $data['name'], $terms['name']);
        $terms->descript = $this->trans(app()->getLocale(), $data['description'], $terms['descript']);
        unset($data['locale'], $data['name'], $data['description']);
        $terms->fill($data);

        try {
            $terms->save();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse('Edit terms of use failure');
        }

        return $this->successResponse();
    }

    /**
     * API function for deleting terms of use
     * Route::post('/deleteTermsOfUse', 'Api\TermsOfUseController@deleteTermsOfUse');
     *
     * @param Request $request - JSON containing api_key (string), id of record to be deleted
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function deleteTermsOfUse(Request $request)
    {
        $post = $request->all();
        $validator = \Validator::make($post, [
            'terms_id'  => 'required|exists:terms_of_use,id'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete terms of use failure');
        }

        if (empty($terms = TermsOfUse::find($post['terms_id']))) {
            return $this->errorResponse('Delete terms of use failure');
        }

        try {
            $terms->delete();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse('Delete terms of use failure');
        }

        return $this->successResponse();
    }

    /**
     * API function for listing multiple terms of use records
     * Route::post('/listTermsOfUse', 'Api\TermsOfUseController@listTermsOfUse');
     *
     * @param Request $request - JSON containing api_key (string), criteria (object) containing filtering criteria for terms of use records
     * @return JsonResponse - JSON containing: On success - Status 200 list of terms of use / On fail - Status 500 error message
     */
    public function listTermsOfUse(Request $request)
    {
        $post = $request->criteria;

        if (!empty($post)) {
            $validator = \Validator::make($request->all(), [
                'active'    => 'boolean',
                'locale'    => 'string|max:5',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('List terms of use failure');
            }

            $query = TermsOfUse::select();

            if ($request->filled('criteria.active')) {
                $query->where('active', '=', $request->input('criteria.active'));
            }

            $terms = $query->get();
        } else {
            $terms = TermsOfUse::all();
        }

        $response = $this->prepareTerms($terms);

        return $this->successResponse($response);
    }

    /**
     * API function for detailed view of a terms of use record
     * Route::post('/getTermsOfUseDetails', 'Api\TermsOfUseController@getTermsOfUseDetails');
     *
     * @param Request $request - JSON containing api_key (string), id of requested record
     * @return JsonResponse - JSON containing: On success - Status 200 record details / On fail - Status 500 error message
     */
    public function getTermsOfUseDetails(Request $request)
    {
        $post = $request->all();
        $validator = \Validator::make($post, [
            'terms_id'  => 'required|exists:terms_of_use,id',
            'locale'    => 'string|max:5',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Get terms of use details failure');
        }

        if (empty($terms = TermsOfUse::find($post['terms_id']))) {
            return $this->errorResponse('Get terms of use details failure');
        }

        $terms['name'] = $terms->name;
        $terms['descript'] = $terms->descript;
        return $this->successResponse($terms);
    }

    /**
     * Prepare collection of terms of use records for response
     *
     * @param Collection $terms - list of terms of use records
     * @return array - ready for response records data
     */
    private function prepareTerms($terms)
    {
        $results = [];
        foreach ($terms as $term) {
            $results[] = [
                    'id'            => $term->id,
                    'name'          => $term->name,
                    'description'   => $term->descript,
                    'locale'        => $term->locale,
                    'active'        => $term->active,
                    'is_default'    => $term->is_default,
                    'ordering'      => $term->ordering,
                    'created_at'    => isset($term->created_at) ? $term->created_at->toDateTimeString() : null,
                    'updated_at'    => isset($term->updated_at) ? $term->updated_at->toDateTimeString() : null,
                    'created_by'    => $term->created_by,
                    'updated_by'    => $term->updated_by,
                ];
        }

        return $results;
    }
}