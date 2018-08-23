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
        $data = $request->get('data', []);
        //validate request data
        $validator = \validator::make($data, [
            'name'           => 'required_with:locale|max:191',
            'name.bg'        => 'required_without:locale|string|max:191',
            'name.*'         => 'max:191',
            'description'    => 'required_with:locale|max:8000',
            'description.bg' => 'required_without:locale|string|max:8000',
            'locale'         => 'nullable|string|max:5',
            'active'         => 'required|boolean',
            'is_default'     => 'nullable|boolean',
            'ordering'       => 'nullable|integer|max:3',
        ]);

        $validator->after(function ($validator) {
            if ($validator->errors()->has('description.bg')) {
                $validator->errors()->add('descript.bg', $validator->errors()->first('description.bg'));
            }
        });

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.add_terms_fail'), $validator->errors()->messages());
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
        $newTerms->name = $data['name'];
        $newTerms->descript = $data['description'];
        unset($data['locale'], $data['name'], $data['description']);
        $newTerms->fill($data);

        try {
            $newTerms->save();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.add_terms_fail'));
        }

        return $this->successResponse(['id' => $newTerms->id], true);
    }

    /**
     * API function for editing terms of use
     * Route::post('/editTermsOfUse', 'Api\TermsOfUseController@editTermsOfUse');
     *
     * @param Request $request - JSON containing api_key (string), id of terms of use record to be edited, data (object) containing updated terms of use data
     * @return JsonResponse - JSON containing: On success - Status 200 / On fail - Status 500 error message
     */
    public function editTermsOfUse(Request $request)
    {
        $post = $request->all();
        $data = $request->data;

        if (isset($post['terms_id'])) {
            $data['terms_id'] = $post['terms_id'];
        }

        // set default values to optional fields
        if (!isset($data['is_default'])) {
            $data['is_default'] = 0;
        }

        if (!isset($data['ordering'])) {
            $data['ordering'] = 1;
        }

        //validate request data
        $validator = \validator::make($data, [
            'terms_id'        => 'required|numeric|exists:terms_of_use,id|max:10',
            'name'            => 'required_with:locale|max:191',
            'name.bg'         => 'required_without:locale|string|max:191',
            'name.*'          => 'max:191',
            'description'     => 'required_with:locale|max:8000',
            'description.bg'  => 'required_without:locale|string|max:8000',
            'locale'          => 'nullable|string|max:5',
            'active'          => 'required|boolean',
            'is_default'      => 'nullable|boolean',
            'ordering'        => 'nullable|numeric|max:3',
        ]);

        $validator->after(function ($validator) {
            if ($validator->errors()->has('description.bg')) {
                $validator->errors()->add('descript.bg', $validator->errors()->first('description.bg'));
            }
        });

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.edit_terms_fail'), $validator->errors()->messages());
        }

        $terms = TermsOfUse::find($post['terms_id']);
        $terms->name = $data['name'];
        $terms->descript = $data['description'];
        unset($data['locale'], $data['name'], $data['description'], $data['terms_id']);
        $terms->fill($data);

        try {
            $terms->save();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.edit_terms_fail'));
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
            'terms_id'  => 'required|exists:terms_of_use,id|max:10'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.delete_terms_fail'), $validator->errors()->messages());
        }

        if (empty($terms = TermsOfUse::find($post['terms_id']))) {
            return $this->errorResponse(__('custom.delete_terms_fail'));
        }

        try {
            $terms->delete();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.delete_terms_fail'));
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
            $validator = \Validator::make($post, [
                'active'    => 'boolean',
                'locale'    => 'string|max:5',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(__('custom.list_terms_fail'), $validator->errors()->messages());
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

        return $this->successResponse($response, true);
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
            'terms_id'  => 'required|exists:terms_of_use,id|max:10',
            'locale'    => 'string|max:5',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.get_terms_fail'), $validator->errors()->messages());
        }

        if (empty($terms = TermsOfUse::find($post['terms_id']))) {
            return $this->errorResponse(__('custom.get_terms_fail'));
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
            $results['terms_of_use'][] = [
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
