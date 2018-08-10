<?php
namespace App\Http\Controllers\Api;

use Uuid;
use App\DataSet;
use App\Category;
use App\DataSetGroup;
use \App\Organisation;
use App\CustomSetting;
use App\UserToOrgRole;
use App\DataSetSubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class DataSetController extends ApiController
{
    /**
     * API function for adding Data Set
     *
     * @param string api_key - required
     * @param integer org_id - optional
     * @param array data - required
     * @param string data[locale] - required
     * @param mixed data[name] - required
     * @param string data[uri] - optional
     * @param mixed data[description] - optional
     * @param array data[tags] - optional
     * @param integer data[category_id] - required
     * @param integer data[terms_of_use_id] - optional
     * @param integer data[visibility] - optional
     * @param string data[source] - optional
     * @param string data[version] - optional
     * @param string data[author_name] - optional
     * @param string data[author_email] - optional
     * @param string data[support_name] - optional
     * @param string data[support_email] - optional
     * @param mixed data[sla] - optional
     *
     * @return json response with id of Data Set or error
     */
    public function addDataSet(Request $request)
    {
        $errors = [];
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'    => 'nullable|int',
            'data'      => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $validator = \Validator::make($post['data'], [
                'locale'                => 'nullable|string|max:5',
                'name'                  => 'required_with:locale',
                'name.*'                => 'required_without:locale|string',
                'uri'                   => 'nullable|string|unique:data_sets,uri',
                'description'           => 'nullable',
                'tags.*'                => 'nullable',
                'category_id'           => 'required|int',
                'terms_of_use_id'       => 'nullable|int',
                'visibility'            => 'nullable|int',
                'source'                => 'nullable|string|max:255',
                'version'               => 'nullable|max:15',
                'author_name'           => 'nullable|string',
                'author_email'          => 'nullable|email',
                'support_name'          => 'nullable|string',
                'support_email'         => 'nullable|email',
                'sla'                   => 'nullable',
                'custom_fields.*.label' => 'nullable',
                'custom_fields.*.value' => 'nullable',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse('Add DataSet Failure', $errors);
        }

        if (!$validator->fails() && !empty($post['data'])) {
            DB::beginTransaction();

            if (empty($post['data']['uri'])) {
                $post['data']['uri'] = Uuid::generate(4)->string;
            }

            if (empty($post['data']['visibility'])) {
                $post['data']['visibility'] = DataSet::VISIBILITY_PRIVATE;
            }

            if (empty($post['data']['version'])) {
                $post['data']['version'] = 1;
            }

            $post['data']['status'] = DataSet::STATUS_DRAFT;

            if (!empty($post['data']['tags']) && !empty(array_filter($post['data']['tags']))) {
                $tags = $post['data']['tags'];
                unset($post['data']['tags']);
            }

            if (!empty($post['data']['custom_fields'])) {
                foreach ($post['data']['custom_fields'] as $fieldSet) {
                    if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                        $customFields[] = [
                            'value' => $fieldSet['value'],
                            'label' => $fieldSet['label'],
                        ];
                    }
                }
                unset($post['data']['custom_fields']);
            }

            if (!empty($post['org_id'])) {
                $post['data']['org_id'] = $post['org_id'];
            }

            $newDataSet = new DataSet;

            try {
                $locale = isset($post['data']['locale']) ? $post['data']['locale'] : null;
                unset($post['data']['locale']);

                $newDataSet->name = $this->trans($locale, $post['data']['name']);

                $newDataSet->descript = !empty($post['data']['descript'])
                    ? $this->trans($locale, $post['data']['descript'])
                    : null;

                $newDataSet->sla = !empty($post['data']['sla'])
                    ? $this->trans($locale, $post['data']['sla'])
                    : null;


                unset($post['data']['sla'], $post['data']['name'], $post['data']['descript']);

                $newDataSet->fill($post['data']);

                $newDataSet->save();

                if ($newDataSet) {
                    if (!empty($tags)) {
                        if (!$this->checkAndCreateTags($newDataSet, $tags, $post['data']['category_id'], $locale)) {
                            DB::rollback();

                            return $this->errorResponse('Add DataSet Failure');
                        }
                    }

                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $newDataSet->id)) {
                            DB::rollback();

                            return $this->errorResponse('Add DataSet Failure');
                        }
                    }

                    DB::commit();

                    return $this->successResponse(['uri' => $newDataSet->uri], true);
                } else {
                    DB::rollback();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Add DataSet Failure', $errors);
    }

    /**
     * API function for editing an existing Data Set
     *
     * @param string api_key - required
     * @param string dataset_uri - required
     * @param array data - required
     * @param string data[locale] - required
     * @param string data[name] - required
     * @param string data[uri] - optional
     * @param string data[description] - optional
     * @param array data[tags] - optional
     * @param integer data[category_id] - required
     * @param integer data[terms_of_use_id] - optional
     * @param integer data[visibility] - optional
     * @param string data[source] - optional
     * @param string data[version] - optional
     * @param string data[author_name] - optional
     * @param string data[author_email] - optional
     * @param string data[support_name] - optional
     * @param string data[support_email] - optional
     * @param string data[sla] - optional
     * @param integer data[status] - optional
     *
     * @return json response with success or error
     */
    public function editDataSet(Request $request)
    {
        $post = $request->all();
        $tags = [];
        $customFields = [];
        $errors = [];

        $validator = \Validator::make($post, [
            'dataset_uri'   => 'required|string',
            'data'          => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $validator = \Validator::make($post['data'], [
                'locale'                   => 'nullable|string|max:5',
                'name'                     => 'required_with:locale',
                'name.*'                   => 'required_without:locale|string',
                'description'              => 'nullable',
                'category_id'              => 'required|int',
                'uri'                      => 'nullable|string|unique:data_sets,uri',
                'tags.*'                   => 'nullable',
                'terms_of_use_id'          => 'nullable|int',
                'visibility'               => 'nullable|int',
                'source'                   => 'nullable|string|max:255',
                'version'                  => 'nullable|string|max:15',
                'author_name'              => 'nullable|string',
                'author_email'             => 'nullable|email',
                'support_name'             => 'nullable|string',
                'support_email'            => 'nullable|email',
                'sla'                      => 'nullable',
                'status'                   => 'nullable|int',
                'custom_fields.*.label'    => 'nullable',
                'custom_fields.*.value'    => 'nullable',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse('Edit dataset failure', $errors);
        } else {
            $dataSet = DataSet::where('uri', $post['dataset_uri'])->first();
            $locale = isset($post['data']['locale']) ? $post['data']['locale'] : null;

            if (!empty($post['data']['tags']) && !empty(array_filter($post['data']['tags']))) {
                $tags = $post['data']['tags'];
            }

            if (!empty($post['data']['custom_fields'])) {
                foreach ($post['data']['custom_fields'] as $fieldSet) {
                    if (!empty(array_filter($fieldSet['value']) || !empty(array_filter($fieldSet['label'])))) {
                        $customFields[] = [
                            'value' => $fieldSet['value'],
                            'label' => $fieldSet['label'],
                        ];
                    }
                }
            }

            try {
                DB::beginTransaction();

                if (!empty($post['data']['name'])) {
                    $dataSet->name = $this->trans($locale, $post['data']['name']);
                }

                if (!empty($post['data']['sla'])) {
                    $dataSet->sla = $this->trans($locale, $post['data']['sla']);
                }

                if (!empty($post['data']['description'])) {
                    $dataSet->descript = $this->trans($locale, $post['data']['description']);
                }

                if (!empty($post['data']['category_id'])) {
                    $dataSet->category_id = $post['data']['category_id'];
                }

                if (!empty($post['data']['uri'])) {
                    $dataSet->uri = $post['data']['uri'];
                }

                if (!empty($post['data']['terms_of_use_id'])) {
                    $dataSet->terms_of_use_id = $post['data']['terms_of_use_id'];
                }

                if (!empty($post['data']['visibility'])) {
                    $dataSet->visibility = $post['data']['visibility'];
                }

                if (!empty($post['data']['source'])) {
                    $dataSet->source = $post['data']['source'];
                }

                if (!empty($post['data']['version'])) {
                    $dataSet->version = $post['data']['version'];
                }

                if (!empty($post['data']['author_name'])) {
                    $dataSet->author_name = $post['data']['author_name'];
                }

                if (!empty($post['data']['author_email'])) {
                    $dataSet->author_email = $post['data']['author_email'];
                }

                if (!empty($post['data']['support_name'])) {
                    $dataSet->support_name = $post['data']['support_name'];
                }

                if (!empty($post['data']['support_email'])) {
                    $dataSet->support_email = $post['data']['support_email'];
                }

                if (!empty($post['data']['status'])) {
                    $dataSet->status = $post['data']['status'];
                }

                $flag = $dataSet->save();

                if ($flag) {
                    if (!empty($customFields)) {
                        if (!$this->checkAndCreateCustomSettings($customFields, $dataSet->id)) {
                            DB::rollback();

                            return $this->errorResponse('Edit dataset failure');
                        }
                    }

                    if (!empty($tags)) {
                        if (!$this->checkAndCreateTags($dataSet, $tags, $post['data']['category_id'], $locale)) {
                            DB::rollback();

                            return $this->errorResponse('Edit dataset failure');
                        }
                    }

                    DB::commit();

                    return $this->successResponse();
                } else {
                    DB::rollback();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Edit dataset failure', $errors);
    }

    /**
     * API function for eleting an existing Data Set
     *
     * @param string api_key - required
     * @param integer dataset_uri - required
     *
     * @return json response with success or error
     */
    public function deleteDataSet(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['dataset_uri' => 'required|string']);

        if ($validator->fails()) {
            return $this->errorResponse('Delete dataset failure', $validator->errors()->messages());
        }

        if (empty($dataset = DataSet::where('uri', $post['dataset_uri'])->first())) {
            return $this->errorResponse('Delete dataset failure');
        }

        try {
            $dataset->delete();
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse('Delete dataset failure');
        }

        try {
            $dataset->deleted_by = \Auth::id();
            $dataset->save();

        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return $this->errorResponse('Delete dataset failure');
        }

        return $this->successResponse();
    }


    /**
     * API function for listing Data Sets
     *
     * @param array criteria - optional
     * @param array criteria[dataset_ids] - optional
     * @param string criteria[locale] - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[group_id] - optional
     * @param integer criteria[tag_id] - optional
     * @param integer criteria[category_id] - optional
     * @param integer criteria[terms_of_use_id] - optional
     * @param string criteria[format] - optional
     * @param integer criteria[reported] - optional
     * @param integer criteria[created_by] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json list with records or error
     */
    public function listDataSets(Request $request)
    {
        $post = $request->all();
        $criteria = !empty($post['criteria']) ? $post['criteria'] : false;
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';

        if ($criteria) {
            $validator = \Validator::make($post, [
                'criteria.dataset_ids'       => 'nullable|array',
                'criteria.locale'            => 'nullable|string|max:5',
                'criteria.org_id'            => 'nullable|int',
                'criteria.group_id'          => 'nullable|int',
                'criteria.category_id'       => 'nullable|int',
                'criteria.tag_id'            => 'nullable|int',
                'criteria.format'            => 'nullable|string',
                'criteria.terms_of_use_id'   => 'nullable|int',
                'criteria.reported'          => 'nullable|int',
                'criteria.created_by'        => 'nullable|int',
                'criteria.order.type'        => 'nullable|string',
                'criteria.order.field'       => 'nullable|string',
                'records_per_page'           => 'nullable|int',
                'page_number'                => 'nullable|int',
            ]);

            if (!$validator->fails()) {
                $data = [];
                $reported = [];

                try {
                    $query = DataSet::select();

                    if (!empty($criteria['dataset_ids'])) {
                        $query->whereIn('id', $criteria['dataset_ids']);
                    }

                    // if user id criteria passed get all his datasets, if not get only published
                    if (!empty($criteria['created_by'])) {
                        $query->where('created_by', $criteria['created_by']);
                    } else {
                        $query->where('status', DataSet::STATUS_PUBLISHED);
                    }

                    if (!empty($criteria['org_id'])) {
                        $query->where('org_id', $criteria['org_id']);
                    }

                    if (!empty($criteria['group_id'])) {
                        $query->whereHas('dataSetGroup', function($q) use($criteria) {
                            $q->where('group_id', $criteria['group_id']);
                        });
                    }

                    if (!empty($criteria['category_id'])) {
                        $query->where('category_id', $criteria['category_id']);
                    }

                    if (!empty($criteria['tag_id'])) {
                        $query->whereHas('datasetsubcategory', function($q) use($criteria) {
                            $q->where('sub_cat_id', $criteria['tag_id']);
                        });
                    }

                    if (!empty($criteria['format'])) {
                        $query->whereHas('resource', function($q) use($criteria) {
                            $q->where('file_format', $criteria['format']);
                        });
                    }

                    if (!empty($criteria['terms_of_use_id'])) {
                        $query->where('terms_of_use_id', $criteria['terms_of_use_id']);
                    }

                    if (!empty($criteria['reported'])) {
                        $query->whereHas('resource', function($q) use($criteria) {
                            $q->where('is_reported', $criteria['reported']);
                        });
                    }

                    if (!empty($order)) {
                        $query->orderBy($order['field'], $order['type']);
                    }

                    $count = $query->count();
                    $query->forPage(
                        $request->offsetGet('page_number'),
                        $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                    );

                    $data = $query->get();


                    foreach ($data as $set) {
                        $set['name'] = $set->name;
                        $set['sla'] = $set->sla;
                        $set['descript'] = $set->descript;
                        $set['reported'] = 0;

                        $hasRes = $set->resource()->count();

                        if ($hasRes) {
                            foreach ($set->resource as $resourse) {
                                if ($resourse->is_reported) {
                                    $set['reported'] = 1;
                                }
                            }
                        }
                    }

                    return $this->successResponse([
                        'datasets'      => $data,
                        'total_records' => $count
                    ], true);
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }

            return $this->errorResponse('Criteria error', $validator->errors()->messages());
        }

        $query = DataSet::where('status', DataSet::STATUS_PUBLISHED);

        if (!empty($order)) {
            $query->orderBy($order['field'], $order['type']);
        }

        if (!empty($pagination)) {
            $query->paginate($pagination, ['*'], 'page', $page);
        }

        $dataSets = $query->get();

        foreach ($dataSets as $set) {
            $set['name'] = $set->name;
            $set['sla'] = $set->sla;
            $set['descript'] = $set->descript;
            $set['followers_count'] = $set->userFollow()->count();
            $set['reported'] = 0;

            $hasRes = $set->resource()->count();

            if ($hasRes) {
                foreach ($set->resource as $resourse) {
                    if ($resourse->is_reported) {
                        $set['reported'] = 1;
                    }
                }
            }
        }

        return $this->successResponse($dataSets);
    }


    /**
     * API function for searching Data Sets by keywords
     *
     * @param array criteria - required
     * @param string criteria[locale] - optional
     * @param integer criteria[keywords] - required
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json list with found records or error
     */
    public function searchDataSet(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'required|array',
            'criteria.locale'       => 'nullable|string|max:5',
            'criteria.keywords'     => 'required|string',
            'criteria.user_id'      => 'nullable|integer',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|int',
            'page_number'           => 'nullable|int',
        ]);

        if (!$validator->fails()) {
            $data = [];
            $criteria = $post['criteria'];
            $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
            $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
            $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : null;
            $page = !empty($post['page_number']) ? $post['page_number'] : null;
            $search = !empty($criteria['keywords']) ? $criteria['keywords'] : null;

            try {

                if (!empty($criteria['user_id'])) {
                    $orgIds = UserToOrgRole::where('user_id', $criteria['user_id'])->get()->pluck('org_id');
                    $ids = DataSet::search($search)->get()->pluck('id');
                    $query = DataSet::whereIn('id', $ids)
                        ->whereIn('org_id', $orgIds);
                } else {
                    $ids = DataSet::search($search)->get()->pluck('id');
                    $query = DataSet::whereIn('id', $ids);

                    $query->orWhereHas('resource', function($q) use ($ids) {
                        $q->whereIn('data_set_id', $ids);
                    });
                }

                $count = $query->count();

                $query->forPage(
                    $request->offsetGet('page_number'),
                    $this->getRecordsPerPage($request->offsetGet('records_per_page'))
                );

                $results = [];

                foreach ($query->get() as $set) {
                    $result['name'] = $set->name;
                    $result['uri'] = $set->uri;
                    $result['sla'] = $set->sla;
                    $result['descript'] = $set->descript;
                    $result['uri'] = $set->uri;
                    $result['created_at'] = (string) $set->created_at;
                    $result['followers_count'] = $set->userFollow()->count();
                    $result['reported'] = 0;
                    $result['created_at'] = isset($set->created_at) ? $set->created_at->toDateTimeString() : null;
                    $result['updated_at'] = isset($set->updated_at) ? $set->updated_at->toDateTimeString() : null;
                    $result['created_by'] = $set->created_by;
                    $result['updated_by'] = $set->updated_by;

                    $hasRes = $set->resource()->count();

                    if ($hasRes) {
                        foreach ($set->resource as $resourse) {
                            if ($resourse->is_reported) {
                                $result['reported'] = 1;
                            }
                        }
                    }

                    $results[] = $result;
                }

                return $this->successResponse([
                    'datasets'      => $results,
                    'total_records' => $count,
                ], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Search dataset failure', $validator->errors()->messages());
    }

    /**
     * API function for viewing information about an existing Data Set
     *
     * @param string api_key - optional
     * @param integer dataset_uri - required
     * @param string locale - optional
     *
     * @return json response with data or error
     */
    public function getDataSetDetails(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'dataset_uri'   => 'required|string',
            'locale'        => 'nullable|string',
        ]);

        if (!$validator->fails()) {
            try {
                $data = DataSet::where('uri', $post['dataset_uri'])
                    ->withCount('userFollow as followers_count')
                    ->first();

                if ($data) {
                    $data['name'] = $data->name;
                    $data['sla'] = $data->sla;
                    $data['descript'] = $data->descript;
                    $data['reported'] = 0;
                    //TODO show category and tags
                    $category = $data->category;
                    $tags = $data->dataSetSubCategory;

                    $hasRes = $data->resource()->count();

                    if ($hasRes) {
                        foreach ($data->resource as $resourse) {
                            if ($resourse->is_reported) {
                                $data['reported'] = 1;
                            }
                        }
                    }

                    unset($data['resource']);

                    return $this->successResponse($data);
                }
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse('Get dataset details failure', $validator->errors()->messages());
    }


    /**
     * API function for adding Data Set to group
     *
     * @param string api_key - required
     * @param integer dataset_uri - required
     * @param integer group_id - required
     *
     * @return json success or error
     */
    public function addDataSetToGroup(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'data_set_uri'  => 'required|string|exists:data_sets,uri,deleted_at,NULL',
            'group_id'      => [
                'required',
                'int',
                Rule::exists('organisations', 'id')->where(function ($query) {
                    $query->where('type', Organisation::TYPE_GROUP);
                }),
            ],
        ]);

        if (!$validator->fails()) {
            try {
                $dataSetId = DataSet::where('uri', $post['data_set_uri'])->first()->id;

                if (
                    DataSetGroup::updateOrCreate(['data_set_id' => $dataSetId],
                        ['group_id' => $post['group_id']
                    ])
                ) {

                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Add dataset group failure', $validator->errors()->messages());
    }


    /**
     * API function for removing Data Set from group
     *
     * @param string api_key - required
     * @param integer data_set_uri - required
     * @param integer group_id - required
     *
     * @return json success or error
     */
    public function removeDataSetFromGroup(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'group_id'      => 'required|int',
            'data_set_uri'  => 'required|string',
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['data_set_uri'])->first();

            if ($dataSet) {
                try {
                    if (DataSetGroup::where([
                            'group_id'      => $post['group_id'],
                            'data_set_id'   => $dataSet->id,
                        ])->delete()
                    ) {

                        return $this->successResponse();
                    }
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Add dataset group failure', $validator->errors()->messages());
    }

    private function checkTag($tag, $setId)
    {
        $existingTags = Category::select()
            ->whereHas('dataSetSubCategory', function($q) use($setId) {
                $q->where('data_set_id', $setId);
            })
            ->get()
            ->loadTranslations();

        foreach ($existingTags as $existing) {
            if ($existing->name == $tag) {
                return $existing->id;
            }
        }

        return false;
    }

    private function deleteExcessTags($main)
    {
        $tags = Category::where('parent_id', $main)->get();

        try {
            foreach ($tags as $tag) {
                $subCats = DataSetSubCategory::where('sub_cat_id', $tag->id)->count();

                if ($subCats < 1) {
                    Category::where('id', $tag->id)->delete();
                }
            }
        } catch (QueryException $ex) {
        }
    }

    /**
     * Function for adding tags to Data Set
     *
     * @param array $allTags - required
     * @param integer $parent - required
     * @param string $locale - required
     * @return result true or false
     */
    private function checkAndCreateTags($dataSet, $tags, $parent, $locale)
    {
        try {
            $tagIds = [];
            $category = Category::where('id', $parent)->first();

            foreach ($tags as $tag) {
                if (is_array($tag)) {
                    foreach ($tag as $lang => $value) {
                        if (!empty($value)) {
                            if (!$old = $this->checkTag($value, $dataSet->id)) {
                                $newTag = new Category;
                                $newTag->name = $this->trans($lang, $value);
                                $newTag->parent_id = $parent;
                                $newTag->active = 1;
                                $newTag->ordering = Category::ORDERING_ASC;

                                $newTag->save();

                                $tagIds[] = $newTag->id;
                            } else {
                                $tagIds[] = $old;
                            }
                        }
                    }
                } else {
                    if (!$old = $this->checkTag($tag, $dataSet->id)) {
                        $newTag = new Category;
                        $newTag->name = $this->trans($locale, $tag);
                        $newTag->parent_id = $parent;
                        $newTag->active = 1;
                        $newTag->ordering = Category::ORDERING_ASC;

                        $newTag->save();

                        $tagIds[] = $newTag->id;
                    } else {
                        $tagIds[] = $old;
                    }
                }
            }

            $dataSet->dataSetSubCategory()->sync($tagIds);
            $this->deleteExcessTags($parent);
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Check and create custom settings for datasets
     *
     * @param array $customFields
     * @param int   $datasetId
     *
     * @return true if successful, false otherwise
     */
    public function checkAndCreateCustomSettings($customFields, $datasetId)
    {
        try {
            if (count($customFields) <= 3) {
                if ($datasetId) {
                    DB::beginTransaction();
                    $deletedRows = CustomSetting::where('org_id', $datasetId)->delete();

                    foreach ($customFields as $field) {
                        if (!empty($field['label']) && !empty($field['value'])) {
                            foreach ($field['label'] as $locale => $label) {
                                if (
                                    (empty($field['label'][$locale]) && !empty($field['value'][$locale]))
                                    || (!empty($field['label'][$locale]) && empty($field['value'][$locale]))
                                ) {
                                    DB::rollback();

                                    return false;
                                }
                            }

                            $saveField = new CustomSetting;
                            $saveField->data_set_id = $datasetId;
                            $saveField->created_by = \Auth::user()->id;
                            $saveField->key = $this->trans($empty, $field['label']);
                            $saveField->value = $this->trans($empty, $field['value']);

                            $saveField->save();
                        } else {
                            DB::rollback();

                            return false;
                        }
                    }
                    DB::commit();

                    return true;
                }
            }
        } catch (QueryException $ex) {
            Log::error($ex->getMessage());

            return false;
        }
    }

    /**
     * Function for getting the number of DataSets a given user has
     *
     * @param string api_key - required
     * @param array criteria - required
     * @param integer id - required
     *
     * @return json result with DataSet count or error
     */
    public function getUsersDataSetCount(Request $request)
    {
        $data = $request->criteria;

        $validator = \Validator::make($data, ['id' => 'required|int']);

        if (!$validator->fails()) {
            $sets = DataSet::where('created_by', $data['id']);

            try {
                $count = $sets->count();

                return $this->successResponse(['count' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get Users DataSet count failure');
    }
}
