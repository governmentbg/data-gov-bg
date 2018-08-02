<?php
namespace App\Http\Controllers\Api;

use App\DataSet;
use App\Resource;
use App\ElasticDataSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Elasticsearch\Common\Exceptions\RuntimeException;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;

class ResourceController extends ApiController
{
    /**
     * Add resource record
     *
     * @param string api_key - required
     * @param string dataset_uri - required
     * @param array data - required
     * @param string data[name] - required
     * @param string data[description] - optional
     * @param string data[locale] - required
     * @param string data[version] - optional
     * @param string data[schema_description] - required if no schema_url|optional
     * @param string data[schema_url] - required if no schema_description|optional
     * @param int data[type] - required (1 -> File, 2 -> Hiperlink, 3 -> API)
     * @param string data[resource_url] - required if type is Hyperlink or API|optional
     * @param string data[http_rq_type] - required if type is API|optional (post, get)
     * @param string data[authentication] - required if type is API|optional
     * @param string data[http_headers] - required if type is API|optional
     * @param array data[custom_fields] - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with success or error
     */
    public function addResourceMetadata(Request $request)
    {
        $post = $request->all();
        $requestTypes = Resource::getRequestTypes();

        if (isset($post['data']['http_rq_type'])) {
            $post['data']['http_rq_type'] = strtoupper($post['data']['http_rq_type']);
        }

        $validator = \Validator::make($post, [
            'dataset_uri'               => 'required|string|exists:data_sets,uri,deleted_at,NULL',
            'data'                      => 'required|array',
            'data.name'                 => 'required|string',
            'data.description'          => 'nullable|string',
            'data.locale'               => 'required|string|max:5',
            'data.version'              => 'nullable|string',
            'data.schema_description'   => 'nullable|string|required_without:data.schema_url',
            'data.schema_url'           => 'nullable|url|required_without:data.schema_description',
            'data.type'                 => 'required|int|in:'. implode(',', array_keys(Resource::getTypes())),
            'data.resource_url'         => 'nullable|url|required_if:data.type,'. Resource::TYPE_HYPERLINK .','. Resource::TYPE_API,
            'data.http_rq_type'         => 'nullable|string|required_if:data.type,'. Resource::TYPE_API .'|in:'. implode(',', $requestTypes),
            'data.authentication'       => 'nullable|string|required_if:data.type,'. Resource::TYPE_API,
            'data.http_headers'         => 'nullable|string|required_if:data.type,'. Resource::TYPE_API,
            'data.post_data'            => 'nullable|string',
            'data.custom_fields'        => 'nullable|array',
            'data.custom_fields.label'  => 'nullable|string',
            'data.custom_fields.value'  => 'nullable|string',
        ]);

        $validator->sometimes('data.post_data', 'required', function($post) use ($requestTypes) {
            if (
                isset($post['data']['type'])
                && $post['data']['type'] == Resource::TYPE_API
                && isset($post['data']['http_rq_type'])
                && $post['data']['http_rq_type'] == $requestTypes[Resource::HTTP_POST]
            ) {
                return true;
            }

            return false;
        });

        if (!$validator->fails()) {
            $dbData = [
                'data_set_id'       => DataSet::where('uri', $post['dataset_uri'])->first()->id,
                'name'              => $post['data']['name'],
                'descript'          => isset($post['data']['description']) ? $post['data']['description'] : null,
                'uri'               => Uuid::generate(4)->string,
                'version'           => isset($post['data']['version']) ? $post['data']['version'] : 1,
                'resource_type'     => isset($post['data']['type']) ? $post['data']['type'] : null,
                'resource_url'      => isset($post['data']['resource_url']) ? $post['data']['resource_url'] : null,
                'http_rq_type'      => isset($post['data']['http_rq_type']) ? array_flip($requestTypes)[$post['data']['http_rq_type']] : null,
                'authentication'    => isset($post['data']['authentication']) ? $post['data']['authentication'] : null,
                'post_data'         => isset($post['data']['post_data']) ? $post['data']['post_data'] : null,
                'http_headers'      => isset($post['data']['http_headers']) ? $post['data']['http_headers'] : null,
                'schema_descript'   => isset($post['data']['schema_description']) ? $post['data']['schema_description'] : null,
                'schema_url'        => isset($post['data']['schema_url']) ? $post['data']['schema_url'] : null,
            ];

            try {
                $resource = Resource::create($dbData);
                $resource->searchable();

                return $this->successResponse(['uri' => $resource->uri]);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Add resource metadata failure', $validator->errors()->messages());
    }

    /**
     * Add data to elastic search
     *
     * @param string api_key - required
     * @param int resource_uri - required
     * @param array data - required
     *
     * @return json with success or error
     */
    public function addResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL',
            'data'          => 'required|array',
        ]);

        if (!$validator->fails()) {
            DB::beginTransaction();

            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();
                $id = $resource->id;
                $index = $resource->data_set_id;

                $elasticDataSet = ElasticDataSet::create([
                    'index'         => $index,
                    'index_type'    => ElasticDataSet::ELASTIC_TYPE,
                    'doc'           => $id,
                ]);

                $resource->es_id = $elasticDataSet->id;
                $resource->save();

                \Elasticsearch::index([
                    'body'  => $post['data'],
                    'index' => $index,
                    'type'  => ElasticDataSet::ELASTIC_TYPE,
                    'id'    => $id,
                ]);

                DB::commit();

                return $this->successResponse();
            } catch (RuntimeException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Add resource data failure', $validator->errors()->messages());
    }

    /**
     * Edit resource record
     *
     * @param string api_key - required
     * @param string resource_uri - required
     * @param array data - required
     * @param string data[resource_uri] - optional
     * @param string data[name] - optional
     * @param string data[description] - optional
     * @param string data[locale] - optional
     * @param string data[version] - optional
     * @param string data[schema_description] - optional
     * @param string data[schema_url] - optional
     * @param int data[type] - optional (1 -> File, 2 -> Hiperlink, 3 -> API)
     * @param string data[resource_url] - optional if type is Hyperlink or API
     * @param string data[http_rq_type] - optional if type is API (post, get)
     * @param string data[authentication] - optional if type is API
     * @param string data[http_headers] - optional if type is API
     * @param array data[custom_fields] - optional
     * @param string data[custom_fields][label] - optional
     * @param string data[custom_fields][value] - optional
     *
     * @return json with success or error
     */
    public function editResourceMetadata(Request $request)
    {
        $post = $request->all();
        $requestTypes = Resource::getRequestTypes();

        if (isset($post['data']['http_rq_type'])) {
            $post['data']['http_rq_type'] = strtoupper($post['data']['http_rq_type']);
        }

        $validator = \Validator::make($post, [
            'resource_uri'              => 'required|string|exists:resources,uri,deleted_at,NULL',
            'data'                      => 'required|array',
            'data.resource_uri'         => 'nullable|string|unique:resources,uri',
            'data.name'                 => 'nullable|string',
            'data.description'          => 'nullable|string',
            'data.locale'               => 'nullable|string|required_with:data.name,data.description|max:5',
            'data.version'              => 'nullable|string',
            'data.schema_description'   => 'nullable|string',
            'data.schema_url'           => 'nullable|url',
            'data.type'                 => 'nullable|int|in:'. implode(',', array_keys(Resource::getTypes())),
            'data.resource_url'         => 'nullable|url|required_if:data.type,'. Resource::TYPE_HYPERLINK .','. Resource::TYPE_API,
            'data.http_rq_type'         => 'nullable|string|required_if:data.type,'. Resource::TYPE_API .'|in:'. implode(',', $requestTypes),
            'data.authentication'       => 'nullable|string|required_if:data.type,'. Resource::TYPE_API,
            'data.http_headers'         => 'nullable|string|required_if:data.type,'. Resource::TYPE_API,
            'data.post_data'            => 'nullable|string',
            'data.custom_fields'        => 'nullable|array',
            'data.custom_fields.label'  => 'nullable|string',
            'data.custom_fields.value'  => 'nullable|string',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if (isset($post['data']['resource_uri'])) {
                $resource->uri = $post['data']['resource_uri'];
            }

            if (isset($post['data']['name'])) {
                $resource->name = $post['data']['name'];
            }

            if (isset($post['data']['description'])) {
                $resource->descript = $post['data']['description'];
            }

            if (isset($post['data']['version'])) {
                $resource->version = $post['data']['version'];
            }

            if (isset($post['data']['type'])) {
                $resource->resource_type = $post['data']['type'];
            }

            if (isset($post['data']['resource_url'])) {
                $resource->resource_url = $post['data']['resource_url'];
            }

            if (isset($post['data']['http_rq_type'])) {
                $resource->http_rq_type = array_flip($requestTypes)[$post['data']['http_rq_type']];
            }

            if (isset($post['data']['authentication'])) {
                $resource->authentication = $post['data']['authentication'];
            }

            if (isset($post['data']['post_data'])) {
                $resource->post_data = $post['data']['post_data'];
            }

            if (isset($post['data']['http_headers'])) {
                $resource->http_headers = $post['data']['http_headers'];
            }

            if (isset($post['data']['schema_description'])) {
                $resource->schema_descript = $post['data']['schema_description'];
            }

            if (isset($post['data']['schema_url'])) {
                $resource->schema_url = $post['data']['schema_url'];
            }

            try {
                $resource->save();

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Edit resource metadata failure', $validator->errors()->messages());
    }

    /**
     * Update elastic search data
     *
     * @param string api_key - required
     * @param int resource_uri - required
     * @param array data - required
     *
     * @return json with success or error
     */
    public function updateResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL',
            'data'          => 'required|array',
        ]);

        if (!$validator->fails()) {
            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();

                $id = $resource->id;
                $index = $resource->dataSet->id;

                \Elasticsearch::index([
                    'body'  => $post['data'],
                    'index' => $index,
                    'type'  => ElasticDataSet::ELASTIC_TYPE,
                    'id'    => $id,
                ]);

                return $this->successResponse();
            } catch (RuntimeException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Update resource data failure', $validator->errors()->messages());
    }

    /**
     * Delete resource metadata record
     *
     * @param string api_key - required
     * @param int resource_uri - required
     * @param array data - required
     *
     * @return json with success or error
     */
    public function deleteResource(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL']);

        if (!$validator->fails()) {
            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();
                $resource->deleted_by = \Auth::id();
                $resource->save();
                $resource->delete();

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Delete resource failure', $validator->errors()->messages());
    }

    /**
     * List resource records
     *
     * @param string api_key - optional
     * @param array criteria - required
     * @param string criteria[locale] - optional
     * @param string criteria[dataset_uri] - optional
     * @param string criteria[reported] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param int criteria[records_per_page] - optional
     * @param int criteria[page_number] - optional
     *
     * @return json with success or error
     */
    public function listResources(Request $request)
    {
        $count = 0;
        $results = [];
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria'              => 'required|array',
            'criteria.locale'       => 'nullable|string|max:5',
            'criteria.resource_uri' => 'nullable|string|exists:resources,uri,deleted_at,NULL',
            'criteria.dataset_uri'  => 'nullable|string|exists:data_sets,uri,deleted_at,NULL',
            'criteria.reported'     => 'nullable|boolean',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|int',
            'page_number'           => 'nullable|int',
        ]);

        if (!$validator->fails()) {
            $query = Resource::with('DataSet');

            if (!empty($post['criteria']['dataset_uri'])) {
                $query->whereHas('DataSet', function($q) use ($post) {
                    $q->where('uri', $post['criteria']['dataset_uri']);
                });
            }

            if (!empty($post['criteria']['resource_uri'])) {
                $query->where('uri', $post['criteria']['resource_uri']);
            }

            if (!empty($post['criteria']['reported'])) {
                $query->where('is_reported', $post['criteria']['reported']);
            }

            $count = $query->count();
            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $field = empty($request->criteria['order']['field']) ? 'created_at' : $request->criteria['order']['field'];
            $type = empty($request->criteria['order']['type']) ? 'desc' : $request->criteria['order']['type'];

            $query->orderBy($field, $type);

            $locale = \LaravelLocalization::getCurrentLocale();
            $fileFormats = Resource::getFormats();
            $rqTypes = Resource::getRequestTypes();
            $types = Resource::getTypes();

            foreach ($query->get() as $result) {
                $results[] = [
                    'uri'                   => $result->uri,
                    'dataset_uri'           => $result->dataSet->uri,
                    'name'                  => $result->name,
                    'description'           => $result->descript,
                    'locale'                => $locale,
                    'version'               => $result->version,
                    'schema_description'    => $result->schema_descript,
                    'schema_url'            => $result->schema_url,
                    'type'                  => $types[$result->resource_type],
                    'resource_url'          => $result->resource_url,
                    'http_rq_type'          => $rqTypes[$result->http_rq_type],
                    'authentication'        => $result->authentication,
                    'custom_fields'         => [], // TODO
                    'file_format'           => $fileFormats[$result->file_format],
                    'reported'              => $result->is_reported,
                    'created_at'            => isset($result->created_at) ? $result->created_at->toDateTimeString() : null,
                    'updated_at'            => isset($result->updated_at) ? $result->updated_at->toDateTimeString() : null,
                    'created_by'            => $result->created_by,
                    'updated_by'            => $result->updated_by,
                ];
            }
        }

        return $this->successResponse(['resources' => $results, 'total_records' => $count], true);
    }

    /**
     * Get resource metadata
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     * @param string locale - optional
     *
     * @return json with success or error
     */
    public function getResourceMetadata(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'resource_uri'  => 'required|string|exists:resources,uri,deleted_at,NULL',
            'locale'        => 'nullable|string|max:5',
        ]);

        if (!$validator->fails()) {
            $resource = Resource::with('DataSet')->where('uri', $post['resource_uri'])->first();
            $fileFormats = Resource::getFormats();
            $rqTypes = Resource::getRequestTypes();
            $types = Resource::getTypes();

            if ($resource) {
                $data = [
                    'uri'                   => $resource->uri,
                    'dataset_uri'           => $resource->dataSet->uri,
                    'name'                  => $resource->name,
                    'description'           => $resource->descript,
                    'locale'                => \LaravelLocalization::getCurrentLocale(),
                    'version'               => $resource->version,
                    'schema_description'    => $resource->schema_descript,
                    'schema_url'            => $resource->schema_url,
                    'type'                  => $types[$resource->resource_type],
                    'resource_url'          => $resource->resource_url,
                    'http_rq_type'          => $rqTypes[$resource->http_rq_type],
                    'authentication'        => $resource->authentication,
                    'http_headers'          => $resource->http_headers,
                    'file_format'           => $fileFormats[$resource->file_format],
                    'reported'              => $resource->is_reported,
                    'created_at'            => isset($resource->created_at) ? $resource->created_at->toDateTimeString() : null,
                    'created_by'            => $resource->created_by,
                    'updated_at'            => isset($resource->updated_at) ? $resource->updated_at->toDateTimeString() : null,
                    'updated_by'            => $resource->updated_by,
                ];

                return $this->successResponse(['resource' => $data], true);
            }
        }

        return $this->errorResponse('Get resource metadata failure', $validator->errors()->messages());
    }

    /**
     * Get description schema of a given resource
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     *
     * @return json with success or error
     */
    public function getResourceSchema(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL']);

        if (!$validator->fails()) {
            $resource = Resource::where('uri', $post['resource_uri'])->first();

            if ($resource) {
                $definition = isset($resource->schema_descript) ? $resource->schema_descript : $resource->schema_url;

                return $this->successResponse(['schema_definition' => $definition], true);
            }
        }

        return $this->errorResponse('Get resource schema failure', $validator->errors()->messages());
    }

    /**
     * Get a view of a given resource
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     *
     * @return json with success or error
     */
    public function getResourceView(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL']);

        if (!$validator->fails()) {
            // TODO tool
            return $this->successResponse(['view' => 'html'], true);
        }

        return $this->errorResponse('Get resource view failure', $validator->errors()->messages());
    }

    /**
     * Get elastic search data of a given resource
     *
     * @param string api_key - optional
     * @param string resource_uri - required
     *
     * @return json with success or error
     */
    public function getResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['resource_uri' => 'required|string|exists:resources,uri,deleted_at,NULL']);

        if (!$validator->fails()) {
            try {
                $resource = Resource::where('uri', $post['resource_uri'])->first();
                $elasticData = $resource->elasticDataSet;

                if (!empty($elasticData)) {
                    $data = \Elasticsearch::get([
                        'index' => $elasticData->index,
                        'type'  => $elasticData->index_type,
                        'id'    => $elasticData->doc,
                    ]);
                }

                return $this->successResponse(!empty($data['_source']) ? $data['_source'] : null);
            } catch (RuntimeException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get resource data failure', $validator->errors()->messages());
    }

    /**
     * Search elastic search data
     *
     * @param string api_key - optional
     * @param string keywords - required
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param int records_per_page - optional
     * @param int page_number - optional
     *
     * @return json with results or error
     */
    public function searchResourceData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'criteria.keywords'     => 'required|string',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|int',
            'page_number'           => 'nullable|int',
        ]);

        if (!$validator->fails()) {
            $pageNumber = !empty($post['page_number']) ? $post['page_number'] : 1;
            $recordsPerPage = $this->getRecordsPerPage($request->offsetGet('records_per_page'));
            $orderType = isset($post['criteria']['order']['type']) ? $post['criteria']['order']['type'] : null;
            $orderField = isset($post['criteria']['order']['field']) ? $post['criteria']['order']['field'] : null;
            $keywords = array_map(function($element) { return '*'. $element .'*'; }, explode(' ', $post['criteria']['keywords']));
            $orderJson = isset($orderType) && isset($orderField)
                ? '"sort": [
                        {
                            "'. $orderField .'": {
                                "order": "'. $orderType .'"
                            }
                        }
                    ],
                '
                : '';

            try {
                $data = \Elasticsearch::search([
                    'body'  => '{
                        "size": '. $recordsPerPage .',
                        "from": '. ($pageNumber * $recordsPerPage - $recordsPerPage + 1) .',
                        '. $orderJson .'
                        "query": {
                            "query_string": {
                                "query": "'. implode(' ', $keywords) .'"
                            }
                        }
                    }',
                ]);

                if (!empty($data['hits'])) {
                    $data = array_merge(['page_number' => $pageNumber], $data['hits']);
                }

                return $this->successResponse(['data' => isset($data['hits']) ? $data['hits'] : []], true);
            } catch (BadRequest400Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Search resource data failure', $validator->errors()->messages());
    }

    public function getLinkedData(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'namespaces'        => 'required|string',
            'query'             => 'required',
            'order.type'        => 'nullable|string',
            'order.field'       => 'nullable|string',
            'format'            => 'nullable|string',
            'records_per_page'  => 'nullable|int',
            'page_number'       => 'nullable|int',
        ]);

        if (!$validator->fails()) {
            preg_match_all('!\d+!', $post['namespaces'], $namespaces);
            $orderType = isset($post['order']['type']) ? $post['order']['type'] : null;
            $orderField = isset($post['order']['field']) ? $post['order']['field'] : null;
            $pageNumber = !empty($post['page_number']) ? $post['page_number'] : 1;
            $recordsPerPage = $this->getRecordsPerPage($request->offsetGet('records_per_page'));
            $orderJson = isset($orderType) && isset($orderField)
                ? '"sort": [
                        {
                            "'. $orderField .'": {
                                "order": "'. $orderType .'"
                            }
                        }
                    ],
                '
                : '';

            try {
                $data = \Elasticsearch::search([
                    'index' => isset($namespaces[0]) ? $namespaces[0] : null,
                    'body'  => '{
                        "size": '. $recordsPerPage .',
                        "from": '. ($pageNumber * $recordsPerPage - $recordsPerPage + 1) .',
                        '. $orderJson .'
                        "query": '. json_encode($post['query']) .'
                    }',
                ]);

                if (!empty($data['hits'])) {
                    $data = array_merge(['page_number' => $pageNumber], $data['hits']);
                }

                return $this->successResponse(['data' => $data], true);
            } catch (BadRequest400Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Linked data failure', $validator->errors()->messages());
    }

    /**
     * Lists the count of the datasets per format
     *
     * @param Request $request
     * @return json response
     */
    public function listDataFormats(Request $request)
    {
        $dataSets = Resource::select('file_format', DB::raw('count(*) as total'))
            ->groupBy('file_format')->pluck('total', 'file_format')
            ->all();
        $formats = Resource::getFormats();
        $formatLabel = '';

        if (!empty($dataSets)) {
            foreach ($dataSets as $key => $value) {
                foreach ($formats as $id => $format) {
                    if ($id == $key) {
                        $formatLabel = $format;
                    }
                }

                $result[] = [
                    'format'            => $formatLabel,
                    'datasets_count'    => $value,
                ];
            }

            return $this->successResponse(['data_formats' => $result], true);
        }
    }

    /**
     * Check if user has reported resources
     *
     * @param int user_id - required
     * @return json with results or error
     */
    public function hasReportedResource(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, [
            'user_id'   => 'required|int|exists:users,id',
        ]);

        if (!$validator->fails()) {
            try {
                $hasReported = Resource::where('created_by', $post['user_id'])
                        ->where('is_reported', 1)->count();

                return $this->successResponse(['flag' => ($hasReported) ? true : false], true);
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Search reported resources failure', $validator->errors()->messages());
    }
}
