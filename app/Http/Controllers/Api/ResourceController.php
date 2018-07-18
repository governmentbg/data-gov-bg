<?php
namespace App\Http\Controllers\Api;

Use Uuid;
use App\DataSet;
use App\Category;
use App\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class ResourceController extends ApiController
{
    /**
     * API function for adding resource to data set
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function addResourceMetadata(Request $request)
    {
        // Ask about file_format

        $post = $request->all();

        $validator = \Validator::make($post, [
            'dataset_uri'           => 'required|string',
            'data.name'             => 'required|string',
            'data.descript'         => 'required|string',
            'data.locale'           => 'required|string',
            'data.version'          => 'required|string',
            'data.schema_descript'  => 'required|string',
            'data.schema_url'       => 'required|string',
            'data.resource_type'    => 'required|integer',
            'data.resource_url'     => 'string',
            'data.http_rq_type'     => 'integer',
            'data.authentication'   => 'string',
            'data.http_headers'     => 'string',
        ]);

        if (!$validator->fails()) {
            $dataSet = DataSet::where('uri', $post['dataset_uri'])->first();

            if ($dataSet) {
                $post['data']['data_set_id'] = $dataSet->id;
                $post['data']['is_reported'] = false;
                $post['data']['uri'] = Uuid::generate(4)->string;
                // Ask about these not null fields in the table
                $post['data']['file_format'] = 1;
                $post['data']['post_data'] = 'some post data';

                unset($post['data']['locale']);

                try {
                    $newResource = Resource::create($post['data']);

                    return $this->successResponse(['uri' => $newResource->uri]);
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Add resource metadata failure');
    }

    /**
     * API function for adding data to resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function addResourceData(Request $request)
    {
        // Ask what does the `data` object contain, from the request parameters
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
            'data'          => 'required|string',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri']);

            if ($resource) {
                try {
                    $resource->update(['post_data' => $post['data']]);

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Add resource data failure');
    }

    /**
     * API function for editing metadata of a resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status
     */
    public function editResourceMetadata(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'          => 'required|string',
            'data.name'             => 'string',
            'data.decript'          => 'string',
            'data.locale'           => 'string',
            'data.version'          => 'string',
            'data.schema_descript'  => 'string',
            'data.schema_url'       => 'string',
            'data.type'             => 'required|integer',
            'data.resource_url'     => 'string',
            'data.http_rq_type'     => 'integer',
            'data.authentication'   => 'string',
            'data.http_headers'     => 'string',
        ]);

        $updates = $post['data'];

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                if (!empty($updates['name'])) {
                    $resource->name = $updates['name'];
                }

                if (!empty($updates['descript'])) {
                    $resource->descript = $updates['descript'];
                }

                if (!empty($updates['version'])) {
                    $resource->version = $updates['version'];
                }

                if (!empty($updates['schema_descript'])) {
                    $resource->schema_descript = $updates['schema_descript'];
                }

                if (!empty($updates['schema_url'])) {
                    $resource->schema_url = $updates['schema_url'];
                }

                if (!empty($updates['type'])) {
                    $resource->resource_type = $updates['type'];
                }

                if (!empty($updates['resource_url'])) {
                    $resource->resource_url = $updates['resource_url'];
                }

                if (!empty($updates['http_rq_type'])) {
                    $resource->http_rq_type = $updates['http_rq_type'];
                }

                if (!empty($updates['authentication'])) {
                    $resource->authentication = $updates['authentication'];
                }

                if (!empty($updates['http_headers'])) {
                    $resource->http_headers = $updates['http_headers'];
                }

                try {
                    $resource->save();

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Edit resource metadata failure');
    }

    /**
     * API function for updating data of a resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function updateResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
            'data'          => 'required|string',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                try {
                    $resource->post_data = $post['data'];
                    $resource->save();

                    return $this->successResponse();
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }

        return $this->errorResponse('Update resource data failure');
    }

    /**
     * API function for deleting a resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function deleteResource(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
        ]);

        if (!$validator->fails()) {
            try {
                if (Resource::where('uri', $post['resource_uri'])->delete()) {

                    return $this->successResponse();
                }
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('Delete resource failure');
    }

    /**
     * API function for listing resources
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function listResources(Request $request)
    {
        // Ask why dataset_uri is integer
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria.locale'       => 'string',
            'criteria.resource_uri' => 'string',
            'criteria.dataset_uri'  => 'string',
            'criteria.reported'     => 'boolean',
            'criteria.order.type'   => 'string',
            'criteria.order.field'  => 'string',
            'records_per_page'      => 'integer',
            'page_number'           => 'integer',
        ]);

        if (!$validator->fails()) {
            $order = [];
            $order['type'] = !empty($post['criteria']['order']['type']) ? $post['criteria']['order']['type'] : 'asc';
            $order['field'] = !empty($post['criteria']['order']['field']) ? $post['criteria']['order']['field'] : 'id';
            $pagination = $this->getRecordsPerPage($post['records_per_page']);
            $page = !empty($post['page_number']) ? $post['page_number'] : 1;

            try {
                $query = Resource::where('resource_type', 2);

                if (!empty($post['criteria'])) {
                    if (!empty($post['criteria']['dataset_uri'])) {
                        $dataSet = DataSet::where('uri', $post['criteria']['dataset_uri'])->first();

                        if ($dataSet) {
                            $query->where('data_set_id', $dataSet->id);
                        } else {

                            return $this->errorResponse('List resources failure');
                        }
                    }

                    if (!empty($post['criteria']['resource_uri'])) {
                        $query->where('uri', $post['criteria']['resource_uri']);
                    }

                    if (!empty($post['criteria']['reported'])) {
                        $query->where('is_reported', $post['criteria']['reported']);
                    }
                }

                if ($order) {
                    $query->orderBy($order['field'], $order['type']);
                }

                if ($pagination && $page) {
                    $query->paginate($pagination, ['*'], 'page', $page);
                }

                $resourceData = $query->get();

                return $this->successResponse(['resources' => $resourceData]);
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }
        }

        return $this->errorResponse('List resources failure');
    }

    /**
     * API function for getting the metadata of a given resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function getResourceMetadata(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
            'locale'        => 'string|max:5',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                $data = [
                    'uri'                   => $resource->uri,
                    'dataset_uri'           => $resource->dataSet->uri,
                    'name'                  => $resource->name,
                    'description'           => $resource->descript,
                    // 'locale'                => $this->getLocale(),
                    'version'               => $resource->version,
                    'schema_description'    => $resource->schema_descript,
                    'schema_url'            => $resource->schema_url,
                    'type'                  => $resource->resource_type,
                    'resource_url'          => $resource->resource_url,
                    'http_rq_type'          => $resource->http_rq_type,
                    'authentication'        => $resource->authentication,
                    'http_headers'          => $resource->http_headers,
                    'file_format'           => $resource->file_format,
                    'reported'              => $resource->is_reported,
                    'created_at'            => $resource->created_at,
                    'created_by'            => $resource->created_by,
                    'updated_at'            => $resource->updated_at,
                    'updated_by'            => $resource->updated_by,
                ];

                return $this->successResponse(['resource' => $data]);
            }
        }

        return $this->errorResponse('Get resource metadata failure');
    }

    /**
     * API function for getting the description schema of a given resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function getResourceSchema(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                return $this->successResponse(['schema_definition' => $resource->schema_descript]);
            }
        }

        return $this->errorResponse('Get resource schema failure');
    }

    /**
     * API function for getting the resource view
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function getResourceView(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
        ]);

        if (!$validator->fails()) {
            // PENDING we need clarification TO DO
            return $this->successResponse();
        }

        return $this->errorResponse('Get resource view failure');
    }

    /**
     * API function for retrieving data form a resource
     *
     * @param Request $request - POST request
     * @return json $response - response with status and uri of resource if successfull
     */
    public function getResourceData(Request $request)
    {
        // TO DO ELASTIC DATA TRANSFERS
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                return $this->successResponse($resource->post_data);
            }
        }

        return $this->errorResponse('Get resource data failure');
    }

    public function searchResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria.keywords'     => 'string',
            'criteria.order.type'   => 'string',
            'criteria.order.field'  => 'string',
            'records_per_page'      => 'integer',
            'page_number'           => 'integer',
        ]);

        if (!$validator->fails()) {
            $order = [];
            $order['type'] = !empty($post['criteria']['order']['type']) ? $post['criteria']['order']['type'] : 'asc';
            $order['field'] = !empty($post['criteria']['order']['field']) ? $post['criteria']['order']['field'] : 'id';
            $pagination = $this->getRecordsPerPage($post['records_per_page']);
            $page = !empty($post['page_number']) ? $post['page_number'] : 1;
            $search = !empty($post['criteria']['keywords']) ? $post['criteria']['keywords'] : false;

            try {
                $query = Resource::where('is_reported', 0);

                if ($order) {
                    $query->orderBy($order['field'], $order['type']);
                }

                if ($pagination && $page) {
                    $query->paginate($pagination, ['*'], 'page', $page);
                }

                if ($search) {
                    $results = Resource::search($search)->constrain($query)->get();
                } else {
                    $results = $query->get();
                }

                foreach ($results as $resource) {
                    $data[] = [
                        'data'  => $resource->post_data,
                    ];
                }

                return $this->successResponse($data);
            } catch (QueryException $ex) {
                return $this->errorResponse($ex->getMessage());
            }

        }

        return $this->errorResponse('Search resource data failure');
    }
}
