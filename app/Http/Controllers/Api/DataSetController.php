<?php
namespace App\Http\Controllers\Api;

Use Uuid;
use App\DataSet;
use App\Category;
use App\DataSetGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class DataSetController extends ApiController
{
    /**
     * API function for adding Data Set
     *
     * @param Request $request - POST request
     * @return json $response - response with status and id of Data Set if successfull
     */
    public function addDataSet(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'org_id'                        => 'nullable|integer',
            'data'                          => 'nullable|required',
            'data.locale'                   => 'required|string|max:5',
            'data.name'                     => 'required|string',
            'data.uri'                      => 'nullable|string',
            'data.descript'                 => 'nullable|string',
            'data.tags.*'                   => 'nullable|string',
            'data.category_id'              => 'required|integer',
            'data.terms_of_use_id'          => 'nullable|integer',
            'data.visibility'               => 'nullable|integer',
            'data.version'                  => 'nullable|string',
            'data.author_name'              => 'nullable|string',
            'data.author_email'             => 'nullable|email',
            'data.support_name'             => 'nullable|string',
            'data.support_email'            => 'nullable|email',
            'data.sla'                      => 'nullable|string',
        ]);

        if(!$validator->fails() && !empty($post['data'])) {
            DB::beginTransaction();

            if (empty($post['data']['uri'])) {
                $post['data']['uri'] = Uuid::generate(4)->string;
            }

            $post['data']['status'] = DataSet::STATUS_DRAFT;
            unset($post['data']['locale']);

            if (!empty($post['data']['tags'])) {
                $tags = $post['data']['tags'];
                unset($post['data']['tags']);
            }

            if (!empty($post['org_id'])) {
                $post['data']['org_id'] = $post['org_id'];
            }

            try {
                $newDataSet = DataSet::create($post['data']);

                if ($newDataSet) {
                    if (!empty($tags)) {
                        if (!$this->checkAndCreateTags($tags, $post['data']['category_id'])) {
                            DB::rollback();

                            return $this->errorResponse('Add DataSet Failure');
                        }
                    }

                    DB::commit();
                    return $this->successResponse(['uri' => $newDataSet->uri], true);
                } else {
                    DB::rollback();

                    return $this->errorResponse('Add DataSet Failure');
                }
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Add DataSet Failure');
    }

    /**
     * API function for editing Data Sets
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function editDataSet(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'dataset_uri'           => 'required|string',
            'data.locale'           => 'required|string|max:5',
            'data.name'             => 'nullable|string',
            'data.descript'         => 'nullable|string',
            'data.category_id'      => 'nullable|required|integer',
            'data.uri'              => 'nullable|string',
            'data.tags.*'           => 'nullable|string',
            'data.terms_of_use_id'  => 'nullable|integer',
            'data.visibility'       => 'nullable|integer',
            'data.version'          => 'nullable|string',
            'data.author_name'      => 'nullable|string',
            'data.author_email'     => 'nullable|email',
            'data.support_name'     => 'nullable|string',
            'data.support_email'    => 'nullable|email',
            'data.sla'              => 'nullable|string',
            'data.status'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['dataset_uri'])->first();
            unset($post['data']['locale']);

            if (!empty($post['data']['tags'])) {
                $tags = $post['data']['tags'];
                unset($post['data']['tags']);
            }

            if ($dataSet) {
                DB::beginTransaction();

                if (!empty($tags)) {
                    if (!$this->checkAndCreateTags($tags, $post['data']['category_id'])) {
                        DB::rollback();

                        return $this->errorResponse('Edit dataset failure');
                    }
                }

                try {
                    if ($dataSet->update($post['data'])) {
                        DB::commit();

                        return $this->successResponse();
                    } else {
                        DB::rollback();
                    }
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Edit dataset failure');
    }

    /**
     * API function for deleting a Data Set
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function deleteDataSet(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['dataset_uri' => 'required|string']);

        if (!$validator->fails()) {
            try {
                if (DataSet::where('uri', $post['dataset_uri'])->delete()) {
                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Delete dataset failure');
    }


    /**
     * API function for listing all Data Sets
     *
     * @param Request $request - POST request
     * @return json $response - response with status and list of Data Sets from selected criteria
     */
    public function listDataSets(Request $request)
    {
        $post = $request->all();
        $criteria = !empty($post['criteria']) ? $post['criteria'] : false;
        $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : 15;
        $page = !empty($post['page_number']) ? $post['page_number'] : 1;
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';

        if ($criteria) {
            $validator = \Validator::make($post, [
                'criteria.locale'            => 'nullable|string|max:5',
                'criteria.org_id'            => 'nullable|integer',
                'criteria.group_id'          => 'nullable|integer',
                'criteria.category_id'       => 'nullable|integer',
                'criteria.tag_id'            => 'nullable|integer',
                'criteria.format'            => 'nullable|string',
                'criteria.terms_of_use_id'   => 'nullable|integer',
                'criteria.reported'          => 'nullable|integer',
                'criteria.order.type'        => 'nullable|string',
                'criteria.order.field'       => 'nullable|string',
                'records_per_page'           => 'nullable|integer',
                'page_number'                => 'nullable|integer',
            ]);

            if (!$validator->fails()) {
                $data = [];
                $reported = [];

                try {
                    $query = DataSet::where('status', DataSet::STATUS_DRAFT);

                    if (!empty($criteria['org_id'])) {
                        $query->where('org_id', $criteria['org_id']);
                    }

                    if (!empty($criteria['group_id'])) {
                        $query->where('group_id', $criteria['group_id']);
                    }

                    if (!empty($criteria['category_id'])) {
                        $query->where('category_id', $criteria['category_id']);
                    }

                    if (!empty($criteria['tag_id'])) {
                        $query->whereHas('category', function($q) use($criteria) {
                            $q->where('id', $criteria['tag_id']);
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

                    if (!empty($pagination) && !empty($page)) {
                        $query->paginate($pagination, ['*'], 'page', $page);
                    }

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
                    ], true);
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }

            return $this->errorResponse('Criteria error');
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
     * API function for searching for a Data Set from keyword
     *
     * @param Request $request - POST request
     * @return json $response - response with status and list of Data Sets if successfull
     */
    public function searchDataSet(Request $request)
    {
        $post = $request->all();
        $criteria = isset($post['criteria']) ? $post['criteria'] : false;

        if (!empty($criteria)) {
            $validator = \Validator::make($post, [
                'criteria.locale'       => 'nullable|string|max:5',
                'criteria.keywords'     => 'required|string',
                'criteria.order.type'   => 'nullable|string',
                'criteria.order.field'  => 'nullable|string',
                'records_per_page'      => 'nullable|integer',
                'page_number'           => 'nullable|integer',
            ]);

            if (!$validator->fails()) {
                $data = [];
                $order = [];
                $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
                $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
                $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : null;
                $page = !empty($post['page_number']) ? $post['page_number'] : null;
                $search = !empty($criteria['keywords']) ? $criteria['keywords'] : null;

                try {
                    $ids = DataSet::search($search)->get()->pluck('id');
                    $query = DataSet::whereIn('id', $ids);

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

                    return $this->successResponse([
                        'datasets'      => $data,
                        'total_records' => $count,
                    ], true);
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Search dataset failure');
    }


    /**
     * API function for getting the information for a Data Set
     *
     * @param Request $request - POST request
     * @return json $response - response with status and Data Set info if successfull
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

                $data['name'] = $data->name;
                $data['sla'] = $data->sla;
                $data['descript'] = $data->descript;
                $data['reported'] = 0;
                $data->category;

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
            } catch (QueryException $e) {
                return $this->errorResponse($e->getMessage());
            }
        }

        return $this->errorResponse('Get dataset details failure');
    }


    /**
     * API function for adding a Data Set to a group
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function addDataSetToGroup (Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'group_id'      => 'required|integer',
            'data_set_uri'  => 'required|string',
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['data_set_uri'])->first();

            if ($dataSet) {
                try {
                    if (DataSetGroup::create([
                            'data_set_id'   => $dataSet->id,
                            'group_id'      => $post['group_id'],
                        ])
                    ) {

                        return $this->successResponse();
                    }
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Add dataset group failure');
    }


    /**
     * API function for removing Data Set from a group
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function removeDataSetFromGroup(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'group_id'      => 'required|integer',
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
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Add dataset group failure');
    }

    public function checkAndCreateTags($tags, $parent)
    {
        try {
            foreach ($tags as $tag) {
                $exists = Category::where(['name' => $tag, 'parent_id' => $parent])->count();

                if (!$exists) {
                    $tag = [
                        'name'              => $tag,
                        'parent_id'         => $parent,
                        'active'            => 1,
                        'ordering'          => Category::ORDERING_ASC,
                    ];

                    $saveTag = Category::create($tag);
                }
            }

            return true;
        } catch (QueryException $ex) {
            return false;
        }
    }
}
