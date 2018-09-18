<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\RoleRight;
use App\TermsOfUse;
use App\ActionsHistory;
use App\Module;
use App\DataSet;
use App\Resource;
use App\Organisation;

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
            'ordering'       => 'nullable|integer|digits_between:1,3',
        ]);

        $validator->after(function ($validator) {
            if ($validator->errors()->has('description.bg')) {
                $validator->errors()->add('descript.bg', $validator->errors()->first('description.bg'));
            }
        });

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.add_terms_fail'), $validator->errors()->messages());
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::TERMS_OF_USE,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
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

        $logData = [
            'module_name'      => Module::getModuleName(Module::TERMS_OF_USE),
            'action'           => ActionsHistory::TYPE_ADD,
            'action_object'    => $newTerms->id,
            'action_msg'       => 'Added terms of use',
        ];

        Module::add($logData);

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
            'terms_id'        => 'required|numeric|exists:terms_of_use,id|digits_between:1,10',
            'name'            => 'required_with:locale|max:191',
            'name.bg'         => 'required_without:locale|string|max:191',
            'name.*'          => 'max:191',
            'description'     => 'required_with:locale|max:8000',
            'description.bg'  => 'required_without:locale|string|max:8000',
            'locale'          => 'nullable|string|max:5',
            'active'          => 'required|boolean',
            'is_default'      => 'nullable|boolean',
            'ordering'        => 'nullable|numeric|digits_between:1,3',
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
        $rightCheck = RoleRight::checkUserRight(
            Module::TERMS_OF_USE,
            RoleRight::RIGHT_EDIT,
            [],
            [
                'created_by' => $terms->created_by
            ]
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

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

        $logData = [
            'module_name'      => Module::getModuleName(Module::TERMS_OF_USE),
            'action'           => ActionsHistory::TYPE_MOD,
            'action_object'    => $terms->id,
            'action_msg'       => 'Edited terms of use',
        ];

        Module::add($logData);

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
            'terms_id'  => 'required|exists:terms_of_use,id|digits_between:1,10'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.delete_terms_fail'), $validator->errors()->messages());
        }

        if (empty($terms = TermsOfUse::find($post['terms_id']))) {
            return $this->errorResponse(__('custom.delete_terms_fail'));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::TERMS_OF_USE,
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
            DataSet::withTrashed()
                ->where('terms_of_use_id', $post['terms_id'])
                ->update(['terms_of_use_id' => null]);

            $terms->delete();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse(__('custom.delete_terms_fail'));
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::TERMS_OF_USE),
            'action'           => ActionsHistory::TYPE_DEL,
            'action_object'    => $post['terms_id'],
            'action_msg'       => 'Deleted terms of use',
        ];

        Module::add($logData);

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

        $rightCheck = RoleRight::checkUserRight(
            Module::TERMS_OF_USE,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

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

            $query->orderBy('ordering', 'asc');

            $total_records = $query->count();

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $terms = $query->get();
        } else {
            $terms = TermsOfUse::all()->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $total_records = TermsOfUse::all()->count();
        }

        $response = $this->prepareTerms($terms);

        $logData = [
            'module_name'      => Module::getModuleName(Module::TERMS_OF_USE),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed terms of use',
        ];

        Module::add($logData);

        return $this->successResponse(
            array_merge(
                ['total_records' => $total_records],
                $response
            ),
            true
        );
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
            'terms_id'  => 'required|exists:terms_of_use,id|digits_between:1,10',
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
     * Lists the count of the datasets per terms of use
     *
     * @param array criteria - optional
     * @param array criteria[dataset_criteria] - optional
     * @param array criteria[dataset_criteria][user_ids] - optional
     * @param array criteria[dataset_criteria][org_ids] - optional
     * @param array criteria[dataset_criteria][group_ids] - optional
     * @param array criteria[dataset_criteria][category_ids] - optional
     * @param array criteria[dataset_criteria][tag_ids] - optional
     * @param array criteria[dataset_criteria][formats] - optional
     * @param array criteria[dataset_criteria][terms_of_use_ids] - optional
     * @param boolean criteria[dataset_criteria][reported] - optional
     * @param array criteria[dataset_ids] - optional
     * @param string criteria[locale] - optional
     * @param int criteria[records_limit] - optional
     *
     * @return json response
     */
    public function listDataTermsOfUse(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria' => 'nullable|array'
        ]);

        if (!$validator->fails()) {
            $criteria = isset($post['criteria']) ? $post['criteria'] : [];
            $validator = \Validator::make($criteria, [
                'dataset_criteria'  => 'nullable|array',
                'dataset_ids'       => 'nullable|array',
                'dataset_ids.*'     => 'int|exists:data_sets,id|digits_between:1,10',
                'locale'            => 'nullable|string|max:5|exists:locale,locale,active,1',
                'records_limit'     => 'nullable|int|digits_between:1,10|min:1',
            ]);
        }

        if (!$validator->fails()) {
            $dsCriteria = isset($criteria['dataset_criteria']) ? $criteria['dataset_criteria'] : [];
            $validator = \Validator::make($dsCriteria, [
                'user_ids'            => 'nullable|array',
                'user_ids.*'          => 'int|digits_between:1,10|exists:users,id',
                'org_ids'             => 'nullable|array',
                'org_ids.*'           => 'int|digits_between:1,10|exists:organisations,id',
                'group_ids'           => 'nullable|array',
                'group_ids.*'         => 'int|digits_between:1,10|exists:organisations,id,type,'. Organisation::TYPE_GROUP,
                'category_ids'        => 'nullable|array',
                'category_ids.*'      => 'int|digits_between:1,10|exists:categories,id,parent_id,NULL',
                'tag_ids'             => 'nullable|array',
                'tag_ids.*'           => 'int|digits_between:1,10|exists:tags,id',
                'terms_of_use_ids'    => 'nullable|array',
                'terms_of_use_ids.*'  => 'int|digits_between:1,10|exists:terms_of_use,id',
                'formats'             => 'nullable|array|min:1',
                'formats.*'           => 'string|in:'. implode(',', Resource::getFormats()),
                'reported'            => 'nullable|boolean',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $locale = isset($criteria['locale']) ? $criteria['locale'] : \LaravelLocalization::getCurrentLocale();

                $data = TermsOfUse::join('data_sets', 'terms_of_use.id', '=', 'terms_of_use_id');
                $data->select('terms_of_use.id', 'terms_of_use.name', DB::raw('count(distinct data_sets.id, data_sets.terms_of_use_id) as total'));

                $data->where('terms_of_use.active', 1);
                $data->where('data_sets.status', DataSet::STATUS_PUBLISHED);
                $data->where('data_sets.visibility', DataSet::VISIBILITY_PUBLIC);

                if (!empty($dsCriteria['user_ids'])) {
                    $data->whereIn('data_sets.created_by', $dsCriteria['user_ids']);
                }
                if (!empty($dsCriteria['org_ids'])) {
                    $data->whereIn('org_id', $dsCriteria['org_ids']);
                }
                if (!empty($dsCriteria['group_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_groups')->select('data_set_id')->distinct()->whereIn('group_id', $dsCriteria['group_ids'])
                    );
                }
                if (!empty($dsCriteria['category_ids'])) {
                    $data->whereIn('category_id', $dsCriteria['category_ids']);
                }
                if (!empty($dsCriteria['tag_ids'])) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('data_set_tags')->select('data_set_id')->distinct()->whereIn('tag_id', $dsCriteria['tag_ids'])
                    );
                }
                if (!empty($dsCriteria['terms_of_use_ids'])) {
                    $data->whereIn('terms_of_use_id', $dsCriteria['terms_of_use_ids']);
                }
                if (!empty($dsCriteria['formats'])) {
                    $fileFormats = [];
                    foreach ($dsCriteria['formats'] as $format) {
                        $fileFormats[] = Resource::getFormatsCode($format);
                    }
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->whereIn('file_format', $fileFormats)
                    );
                }
                if (isset($dsCriteria['reported']) && $dsCriteria['reported']) {
                    $data->whereIn(
                        'data_sets.id',
                        DB::table('resources')->select('data_set_id')->distinct()->where('is_reported', Resource::REPORTED_TRUE)
                    );
                }

                if (!empty($criteria['dataset_ids'])) {
                    $data->whereIn('data_sets.id', $criteria['dataset_ids']);
                }

                $data->groupBy(['terms_of_use.id', 'terms_of_use.name'])->orderBy('total', 'desc')->orderBy('ordering', 'asc');

                if (!empty($criteria['records_limit'])) {
                    $data->take($criteria['records_limit']);
                }
                $data = $data->get();

                $results = [];
                if (!empty($data)) {
                    foreach ($data as $item) {
                        $results[] = [
                            'id'             => $item->id,
                            'name'           => $item->name,
                            'locale'         => $locale,
                            'datasets_count' => $item->total,
                        ];
                    }
                }

                return $this->successResponse(['terms_of_use' => $results], true);

            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.list_data_terms_of_use_fail'), $validator->errors()->messages());
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
