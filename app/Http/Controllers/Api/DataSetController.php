<?php
namespace App\Http\Controllers\Api;

Use Uuid;
use App\DataSet;
use App\Category;
use App\DataSetGroup;
use Illuminate\Http\Request;
use TeamTNT\TNTSearch\TNTSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class DataSetController extends ApiController
{
    // apt-get install php7.0-sqlite for TNT search
    // SQLite PHP Extension - for TNT
    // mbstring PHP Extension - for TNT

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
            'org_id'                => 'integer',
            'data.locale'           => 'string',
            'data.name'             => 'required|string',
            'data.descript'         => 'string',
            'data.tags.*'           => 'string',
            'data.category_id'      => 'required|integer',
            'data.terms_of_use_id'  => 'integer',
            'data.visibility'       => 'integer',
            'data.version'          => 'string',
            'data.author_name'      => 'string',
            'data.author_email'     => 'email',
            'data.support_name'     => 'string',
            'data.support_email'    => 'email',
            'data.sla'              => 'string',
        ]);

        if(!$validator->fails()) {
            DB::beginTransaction();

            $post['data']['uri'] = Uuid::generate(4)->string;
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
            'data.locale'           => 'string',
            'data.name'             => 'string',
            'data.descript'         => 'string',
            'data.category_id'      => 'required|integer',
            'data.uri'              => 'string',
            'data.tags.*'           => 'string',
            'data.terms_of_use_id'  => 'integer',
            'data.visibility'       => 'integer',
            'data.version'          => 'string',
            'data.author_name'      => 'string',
            'data.author_email'     => 'email',
            'data.support_name'     => 'string',
            'data.support_email'    => 'email',
            'data.sla'              => 'string',
            'data.status'           => 'integer',
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
        $criteria = isset($post['criteria']) ? $post['criteria'] : false;

        if ($criteria) {
            $validator = \Validator::make($post, [
                'criteria.locale'            => 'string',
                'criteria.org_id'            => 'integer',
                'criteria.group_id'          => 'integer',
                'criteria.category_id'       => 'integer',
                'criteria.tag_id'            => 'integer',
                'criteria.format'            => 'string',
                'criteria.terms_of_use_id'   => 'integer',
                'criteria.reported'          => 'integer',
                'criteria.order.type'        => 'string',
                'criteria.order.field'       => 'string',
                'records_per_page'           => 'integer',
                'page_number'                => 'integer',
            ]);

            if (!$validator->fails()) {
                $data = [];
                $order = [];
                $reported = [];

                $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
                $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
                $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : 15;
                $page = !empty($post['page_number']) ? $post['page_number'] : 1;

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

        $dataSets = DataSet::withCount('userFollow as followers_count')
            ->with('resource')
            ->orderBy($order['field'], $order['type'])
            ->paginate($pagination, ['*'], 'page', $page);

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
        // Ask how keywords work
        // Ask how to implement locale translations
        $post = $request->all();
        $criteria = isset($post['criteria']) ? $post['criteria'] : false;

        if (!empty($criteria)) {
            $validator = \Validator::make($post, [
                'criteria.locale'       => 'string',
                'criteria.keywords'     => 'string',
                'criteria.order.type'   => 'string',
                'criteria.order.field'  => 'string',
                'records_per_page'      => 'integer',
                'page_number'           => 'integer',
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
                    $query = DataSet::where('visibility', 1);

                    if ($order) {
                        $query->orderBy($order['field'], $order['type']);
                    }

                    if ($pagination && $page) {
                        $query->paginate($pagination, ['*'], 'page', $page);
                    }

                    if ($search) {
                        $data = DataSet::search($search)->constrain($query)->get();
                    } else {
                        $data = $query->get();
                    }

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
                        'total_records' => $data->count()
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
            'locale'        => 'string',
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
            'dataset_uri'   => 'required|string',
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['dataset_uri'])->count();
            if ($dataSet) {
                try {
                    if (DataSetGroup::create($post)) {
                        return $this->successResponse();
                    }
                } catch (QueryException $e) {
                    return $this->errorResponse($ex->getMessage());
                }
            } else {
                return errorResponse('Add dataset group failure');
            }
        }

        return errorResponse('Add dataset group failure');
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
            'dataset_uri'   => 'required|string',
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['dataset_uri'])->count();
            if ($dataSet) {
                try {
                    if (DataSetGroup::where($post)->delete()) {
                        return $this->successResponse();
                    }
                } catch (QueryException $e) {
                    return $this->errorResponse($ex->getMessage());
                }
            } else {
                return errorResponse('Add dataset group failure');
            }
        }

        return errorResponse('Add dataset group failure');
    }

    public function checkAndCreateTags($tags, $parent)
    {
        try {
            foreach ($tags as $tag) {
                $exists = Category::where(['name' => $tag, 'parent_id' => $parent])->count();

                if (!$exists) {
                    $tag = [
                        'name'              => $tag,
                        'icon_file_name'    => 'someFileName', // these parameters need discussion
                        'icon_mime_type'    => 'someMimeType', // these parameters need discussion
                        'icon_data'         => 'someData', // these parameters need discussion
                        'parent_id'         => $parent,
                        'active'            => 1,
                        'ordering'          => Category::ORDERING_ASC,
                    ];

                    $saveTag = Category::create($tag);
                }
            }

            return true;
        } catch (QueryException $e) {
            return false;
        }
    }
}
