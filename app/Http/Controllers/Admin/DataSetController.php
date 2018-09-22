<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Tags;
use App\DataSet;
use App\Resource;
use App\Organisation;
use App\CustomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

class DataSetController extends AdminController
{
    /**
     * Loads a view for browsing datasets
     *
     * @param Request $request
     *
     * @return view for browsing datasets
     */
    public function listDatasets(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $search = $request->has('q') ? $request->offsetGet('q') : '';

            $orgDropCount = $request->offsetGet('orgs_count') ? $request->offsetGet('orgs_count') : Organisation::INIT_FILTER;
            $selectedOrgs = $request->offsetGet('org') ? $request->offsetGet('org') : [];
            $organisations = $this->getOrgDropdown(null, $orgDropCount);

            $groupDropCount = $request->offsetGet('groups_count') ? $request->offsetGet('groups_count') : Organisation::INIT_FILTER;
            $selectedGroups = $request->offsetGet('group') ? $request->offsetGet('group') : [];
            $groups = $this->getGroupDropdown(null, $groupDropCount);

            $userDropCount = $request->offsetGet('users_count') ? $request->offsetGet('users_count') : Organisation::INIT_FILTER;
            $selectedUser = $request->offsetGet('user') ? $request->offsetGet('user') : '';
            $users = $this->getUserDropdown($userDropCount);

            $termsDropCount = $request->offsetGet('terms_count') ? $request->offsetGet('terms_count') : Organisation::INIT_FILTER;
            $selectedTerms = $request->offsetGet('term') ? $request->offsetGet('term') : [];
            $terms = $this->getTermsDropdown($termsDropCount);

            $tagsDropCount = $request->offsetGet('tags_count') ? $request->offsetGet('tags_count') : Organisation::INIT_FILTER;
            $selectedTags = $request->offsetGet('tag') ? $request->offsetGet('tag') : [];
            $tags = $this->getTagsDropdown($tagsDropCount);

            $catDropCount = $request->offsetGet('categories_count') ? $request->offsetGet('categories_count') : Organisation::INIT_FILTER;
            $selectedCategories = $request->offsetGet('category') ? $request->offsetGet('category') : [];
            $categories = $this->getMainCategoriesDropdown($catDropCount);

            $formats = Resource::getFormats();
            $formatsCount = count($formats);
            $selectedFormats = $request->offsetGet('format') ? $request->offsetGet('format') : [];

            $signaledFilter = $request->offsetGet('signaled', false);

            $params = [
                'api_key'           => \Auth::user()->api_key,
                'records_per_page'  => $perPage,
                'page_number'       => !empty($request->page) ? $request->page : 1,
            ];

            if (!empty($request->from)) {
                $params['criteria']['date_from'] = date_format(date_create($request->from), 'Y-m-d H:i:s');
            }

            if (!empty($request->to)) {
                $params['criteria']['date_to'] = date_format(date_create($request->to .' 23:59'), 'Y-m-d H:i:s');
            }

            if (!empty($search)) {
                $params['criteria']['keywords'] = $search;
            }

            if ($request->has('order_field') && !empty($request->order_field)) {
                $params['criteria']['order']['field'] = $request->order_field;
            }

            if ($request->has('order_type') && !empty($request->order_type)) {
                $params['criteria']['order']['type'] = $request->order_type;
            }

            if (!empty($selectedOrgs)) {
                $selectedOrgs = array_unique($selectedOrgs);
                $params['criteria']['org_ids'] = $selectedOrgs;
            }

            if (!empty($selectedGroups)) {
                $selectedGroups = array_unique($selectedGroups);
                $params['criteria']['group_ids'] = $selectedGroups;
            }

            if (!empty($selectedUser)) {
                $params['criteria']['created_by'] = $selectedUser;
            }

            if (!empty($selectedCategories)) {
                $selectedCategories = array_unique($selectedCategories);
                $params['criteria']['category_ids'] = $selectedCategories;
            }

            if (!empty($selectedTags)) {
                $selectedTags = array_unique($selectedTags);
                $params['criteria']['tag_ids'] = $selectedTags;
            }

            if (!empty($selectedFormats)) {
                $selectedFormats = array_unique($selectedFormats);
                $params['criteria']['formats'] = $selectedFormats;
            }

            if (!empty($selectedTerms)) {
                $selectedTerms = array_unique($selectedTerms);
                $params['criteria']['terms_of_use_ids'] = $selectedTerms;
            }

            if (!empty($signaledFilter)) {
                $params['criteria']['reported'] = $signaledFilter;
            }

            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $result = $api->listDatasets($rq)->getData();
            $datasets = !empty($result->datasets) ? $result->datasets : [];
            $count = !empty($result->total_records) ? $result->total_records : 0;

            $paginationData = $this->getPaginationData(
                $datasets,
                $count,
                array_except(app('request')->input(), ['q', 'page',]),
                $perPage
            );

            return view('admin/datasets', [
                'class'                 => 'user',
                'search'                => $search,
                'datasets'              => $paginationData['items'],
                'pagination'            => $paginationData['paginate'],
                'organisations'         => $organisations,
                'orgDropCount'          => count($this->getOrgDropdown()),
                'selectedOrgs'          => $selectedOrgs,
                'groups'                => $groups,
                'groupDropCount'        => count($this->getGroupDropdown()),
                'selectedGroups'        => $selectedGroups,
                'users'                 => $users,
                'userDropCount'         => count($this->getUserDropdown()),
                'selectedUser'          => $selectedUser,
                'terms'                 => $terms,
                'termsDropCount'        => count($this->getTermsDropdown()),
                'selectedTerms'         => $selectedTerms,
                'tags'                  => $tags,
                'tagsDropCount'         => count($this->getTagsDropdown()),
                'selectedTags'          => $selectedTags,
                'categories'            => $categories,
                'catDropCount'          => count($this->getMainCategoriesDropdown()),
                'selectedCategories'    => $selectedCategories,
                'formats'               => $formats,
                'formatsCount'          => $formatsCount,
                'selectedFormats'       => $selectedFormats,
                'signaledFilter'        => $signaledFilter,
                'range'      => [
                    'from' => isset($request->from) ? $request->from : null,
                    'to'   => isset($request->to) ? $request->to : null
                ],
            ]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for creating a dataset
     *
     * @param Request $request
     *
     * @return view to view the a registered dataset
     */
    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            $visibilityOptions = DataSet::getVisibility();
            $categories = $this->prepareMainCategories();
            $termsOfUse = $this->prepareTermsOfUse();
            $organisations = $this->getOrgDropdown();
            $groups = $this->getGroupDropdown();

            if ($request->isMethod('post') && $request->has('create')) {
                $data = $request->all();

                // prepare post data for API request
                $data = $this->prepareTags($data);

                if (!empty($data['group_id'])) {
                    $groupId = $data['group_id'];
                }

                unset($data['group_id'], $data['add_resource'], $data['create']);

                // make request to API
                $params['api_key'] = \Auth::user()->api_key;
                $params['data'] = $data;

                $savePost = Request::create('/api/addDataset', 'POST', $params);
                $api = new ApiDataSet($savePost);
                $save = $api->addDataset($savePost)->getData();

                if ($save->success) {
                    if (isset($groupId)) {
                        $groupParams['group_id'] = $groupId;
                        $groupParams['data_set_uri'] = $save->uri;
                        $addGroup = Request::create('/api/addDatasetToGroup', 'POST', $groupParams);
                        $res = $api->addDatasetToGroup($addGroup)->getData();

                        if (!$res->success) {
                            $request->session()->flash('alert-danger', __('custom.add_error'));

                            return redirect()->back()->withInput()->withErrors($res->errors);
                        }
                    }

                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    if ($request->has('add_resource')) {
                        return redirect()->route('resourceCreate', ['uri' => $save->uri]);
                    }

                    return redirect(url('/admin/dataset/view/'. $save->uri));
                }

                $request->session()->flash('alert-danger', $save->error->message);

                return redirect()->back()->withInput()->withErrors($save->errors);
            }

            return view('admin/datasetCreate', [
                'class'         => 'user',
                'visibilityOpt' => $visibilityOptions,
                'categories'    => $categories,
                'termsOfUse'    => $termsOfUse,
                'organisations' => $organisations,
                'groups'        => $groups,
                'fields'        => $this->getDatasetTransFields(),
            ]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Loads a view for viewing a dataset
     *
     * @param Request $request
     *
     * @return redirects to dataset review view
     */
    public function view(Request $request, $uri)
    {
        $params['dataset_uri'] = $uri;

        $detailsReq = Request::create('/api/getDatasetDetails', 'POST', $params);
        $api = new ApiDataSet($detailsReq);
        $dataset = $api->getDatasetDetails($detailsReq)->getData();
        // prepera request for resources
        unset($params['dataset_uri']);
        $params['criteria']['dataset_uri'] = $uri;

        $resourcesReq = Request::create('/api/listResources', 'POST', $params);
        $apiResources = new ApiResource($resourcesReq);
        $resources = $apiResources->listResources($resourcesReq)->getData();

        return view('admin/datasetView', [
            'class'     => 'user',
            'dataset'   => $this->getModelUsernames($dataset->data),
            'resources' => $resources->resources,
        ]);
    }

    /**
     * Loads a view for editing a dataset
     *
     * @param Request $request
     *
     * @return view for editing dataset details
     */
    public function edit(Request $request, $uri)
    {
        $visibilityOptions = Dataset::getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->getOrgDropdown();
        $groups = $this->getGroupDropdown();
        $params = ['dataset_uri' => $uri];
        $setGroups = [];

        $model = DataSet::where('uri', $uri)->with('dataSetGroup')->first()->loadTranslations();

        if (!empty($model->dataSetGroup)) {
            foreach ($model->dataSetGroup as $record) {
                $setGroups[] = $record->group_id;
            }
        }

        $hasResources = Resource::where('data_set_id', $model->id)->count();
        $withModel = CustomSetting::where('data_set_id', $model->id)->get()->loadTranslations();
        $tagModel = Tags::whereHas('dataSetTags', function($q) use ($model) {
                $q->where('data_set_id', $model->id);
            })
            ->get();

        $setRq = Request::create('/api/getDatasetDetails', 'POST', $params);
        $api = new ApiDataSet($setRq);
        $result = $api->getDatasetDetails($setRq)->getData();

        if (!$result->success) {
            $request->session()->flash('alert-danger', __('custom.no_dataset'));

            return back();
        }

        if ($request->has('save') || $request->has('publish')) {
            $editData = $request->all();

            if ($editData['uri'] == $uri) {
                unset($editData['uri']);
                $newURI = $uri;
            } else {
                $newURI = $editData['uri'];
            }

            $editData = $this->prepareTags($editData);

            $groupId = $request->offsetGet('group_id');

            $post = [
                'api_key'       => Auth::user()->api_key,
                'data_set_uri'  => $uri,
                'group_id'      => $groupId,
            ];

            $addGroup = Request::create('/api/addDatasetToGroup', 'POST', $post);
            $added = $api->addDatasetToGroup($addGroup)->getData();

            if (!$added->success) {
                session()->flash('alert-danger', __('custom.edit_error'));

                return redirect()->back()->withInput()->withErrors($added->errors);
            }

            if ($request->has('publish')) {
                $editData['status'] = DataSet::STATUS_PUBLISHED;
            }

            $edit = [
                'api_key'       => Auth::user()->api_key,
                'dataset_uri'   => $uri,
                'data'          => $editData,
            ];

            $editRq = Request::create('/api/editDataset', 'POST', $edit);
            $success = $api->editDataset($editRq)->getData();

            if ($success->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));
                $newUri = DataSet::where('id', $model->id)->first();

                return redirect(url('/admin/dataset/edit/'. $newURI));
            } else {
                session()->flash('alert-danger', __('custom.edit_error'));

                return redirect()->back()->withInput()->withErrors($success->errors);
            }
        }

        return view('admin/datasetEdit', [
            'class'         => 'user',
            'dataSet'       => $model,
            'tagModel'      => $tagModel,
            'withModel'     => $withModel,
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'hasResources'  => $hasResources,
            'setGroups'     => $setGroups,
            'fields'        => $this->getDatasetTransFields(),
        ]);
    }

    /**
     * Requests dataset deleting API
     *
     * @param Request $request
     *
     * @return view with a list of datasets and request success message
     */
    public function delete(Request $request)
    {
        if ($request->has('delete')) {
            $params['api_key'] = \Auth::user()->api_key;
            $params['dataset_uri'] = $request->offsetGet('dataset_uri');

            $apiRequest = Request::create('/api/deleteDataset', 'POST', $params);
            $api = new ApiDataSet($apiRequest);
            $result = $api->deleteDataset($apiRequest)->getData();
            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }
        }

        return redirect('/admin/datasets');
    }

    /**
     * Adds resource metadata and prepares the resource elasticsearch data
     *
     * @param Request $request - resource metadata, file with resource data
     * @param int $datasetUri - associated dataset uri
     *
     * @return type
     */
    public function resourceCreate(Request $request, $datasetUri)
    {
        $class = 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();

        if (DataSet::where('uri', $datasetUri)->count()) {
            if ($request->has('ready_metadata')) {
                $data = $request->except('file');
                $file = $request->file('file');

                $response = ResourceController::addMetadata($datasetUri, $data, $file);

                if ($response['success']) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    if ($data['type'] == Resource::TYPE_HYPERLINK) {
                        return redirect('/admin/resource/view/'. $response['uri']);
                    }

                    return view('admin/resourceImport', array_merge([
                        'class'         => $class,
                        'types'         => $types,
                        'resourceUri'   => $response['uri'],
                        'action'        => 'create',
                    ], $response['data']));
                } else {
                    $request->session()->flash('alert-danger', __('custom.changes_success_fail'));

                    return redirect()->back()->withInput()->withErrors($response['errors']);
                }
            }
        } else {
            return redirect('/admin/datasets');
        }

        return view('admin/resourceCreate', [
            'class'     => $class,
            'uri'       => $datasetUri,
            'types'     => $types,
            'reqTypes'  => $reqTypes,
            'fields'    => $this->getResourceTransFields()
        ]);
    }

    /**
     * Loads a view for checking out resource details
     *
     * @param Request $request
     *
     * @return view
     */
    public function resourceView(Request $request, $uri, $version = null)
    {
        $reqMetadata = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $uri]);
        $apiMetadata = new ApiResource($reqMetadata);
        $result = $apiMetadata->getResourceMetadata($reqMetadata)->getData();
        $resource = !empty($result->resource) ? $result->resource : null;

        if (!empty($resource)) {
            $data = [];

            if (!empty($resource)) {
                $resource->format_code = Resource::getFormatsCode($resource->file_format);
                $resource = $this->getModelUsernames($resource);

                if (empty($version)) {
                    $version = $resource->version;
                }

                if ($request->has('delete')) {
                    $reqDelete = Request::create('/api/deleteResource', 'POST', ['resource_uri' => $uri]);
                    $apiDelete = new ApiResource($reqDelete);
                    $result = $apiDelete->deleteResource($reqDelete)->getData();

                    if ($result->success) {
                        $request->session()->flash('alert-success', __('custom.delete_success'));

                        return redirect('/admin/dataset/view/'. $resource->dataset_uri);
                    }

                    $request->session()->flash('alert-success', __('custom.delete_error'));
                }

                $reqEsData = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $uri, 'version' => $version]);
                $apiEsData = new ApiResource($reqEsData);
                $response = $apiEsData->getResourceData($reqEsData)->getData();

                $versions = [];
                $versionsList = Resource::where('id', $resource->id)->first()->elasticDataSet()->get();
                foreach ($versionsList as $row) {
                    $versions[] = $row->version;
                }

                $data = !empty($response->data) ? $response->data : [];

                if ($resource->format_code == Resource::FORMAT_XML) {
                    $convertData = [
                        'api_key'   => \Auth::user()->api_key,
                        'data'      => $data,
                    ];
                    $reqConvert = Request::create('/json2xml', 'POST', $convertData);
                    $apiConvert = new ApiConversion($reqConvert);
                    $resultConvert = $apiConvert->json2xml($reqConvert)->getData();
                    $data = isset($resultConvert->data) ? $resultConvert->data : [];
                }

                return view('admin/resourceView', [
                    'class'         => 'user',
                    'resource'      => $resource,
                    'data'          => $data,
                    'versionView'   => $version,
                    'versions'      => $versions,
                ]);
            }
        }

        return back();
    }

    /**
     * Edit resource metadata
     *
     * @param Request $request - resource metadata, file with resource data
     * @param int $uri - uri of resource to be edited
     *
     * @return view - resource edit page
     */
    public function resourceEditMeta(Request $request, $uri, $parentUri = null)
    {
        $rq = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $uri]);
        $api = new ApiResource($rq);
        $res = $api->getResourceMetadata($rq)->getData();

        if (!$res->success) {
            return redirect()->back();
        }

        $resourceData = !empty($res->resource) ? $res->resource : null;

        if (!isset($resourceData)) {
            return back()->withErrors(session()->flash('alert-danger', __('custom.record_not_found')));
        }

        $class = 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();
        $resource = Resource::where('uri', $uri)->first()->loadTranslations();

        if ($parentUri) {
            $parent = Organisation::where('uri', $parentUri)->first();
            $parent->logo = $this->getImageData($parent->logo_data, $parent->logo_mime_type, $parent->type == Organisation::TYPE_GROUP ? 'group' : 'org');
        }

        if ($resource) {
            if ($request->has('ready_metadata')) {

                $data = [
                    'name'                  => $request->offsetGet('name'),
                    'description'           => $request->offsetGet('descript'),
                    'schema_description'    => $request->offsetGet('schema_description'),
                    'schema_url'            => $request->offsetGet('schema_url'),
                    'is_reported'           => is_null($request->offsetGet('reported'))
                                                ? Resource::REPORTED_FALSE
                                                : Resource::REPORTED_TRUE
                    ];

                if ($resource->resource_type == Resource::TYPE_HYPERLINK) {
                    $data['type'] = $resource->resource_type;
                    $data['resource_url'] = $request->offsetGet('resource_url');
                }

                $metadata = [
                    'api_key'       => Auth::user()->api_key,
                    'resource_uri'  => $uri,
                    'data'          => $data,
                ];

                $savePost = Request::create('/api/editResourceMetadata', 'POST', $metadata);
                $api = new ApiResource($savePost);
                $response = $api->editResourceMetadata($savePost)->getData();

                if ($response->success) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', $response->error->message);
                }
            }
        } else {
            return back()->withErrors(session()->flash('alert-danger', __('custom.record_not_found')));
        }

        return view('admin/resourceEdit', [
            'class'     => $class,
            'resource'  => $resource,
            'uri'       => $uri,
            'types'     => $types,
            'reqTypes'  => $reqTypes,
            'fields'    => $this->getResourceTransFields(),
            'parent'    => isset($parent) ? $parent : false,
        ]);
    }

    /**
     * Edit resource metadata
     *
     * @param Request $request - resource metadata, file with resource data
     * @param int $resourceUri - uri of resource to be edited
     *
     * @return view - resource edit page
     */
    public function resourceUpdate(Request $request, $resourceUri)
    {
        $rq = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $resourceUri]);
        $api = new ApiResource($rq);
        $res = $api->getResourceMetadata($rq)->getData();

        if (!$res->success) {
            return redirect()->back();
        }

        $resourceData = !empty($res->resource) ? $res->resource : null;

        if (!isset($resourceData)) {
            return back()->withErrors(session()->flash('alert-danger', __('custom.record_not_found')));
        }

        $class = 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();
        $resource = Resource::where('uri', $resourceUri)->first()->loadTranslations();

        if ($resource) {
            if ($request->has('ready_metadata')) {

                $data = [
                    'type'          => $resource->resource_type,
                    'resource_url'  => $request->offsetGet('resource_url'),
                    'http_rq_type'  => $request->offsetGet('http_rq_type'),
                    'http_headers'  => $request->offsetGet('http_headers'),
                    'post_data'     => $request->offsetGet('post_data'),
                    'version'       => strval(intval($resource->version) + 1),
                ];

                $file = $request->file('file');

                $response = ResourceController::addMetadata($resourceUri, $data, $file, true);

                if ($response['success']) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    if ($data['type'] == Resource::TYPE_HYPERLINK) {
                        return redirect('/admin/resource/view/'. $response['uri']);
                    }

                    return view('admin/resourceImport', array_merge([
                        'class'         => $class,
                        'types'         => $types,
                        'resourceUri'   => $response['uri'],
                        'action'        => 'update',
                    ], $response['data']));
                } else {
                    $request->session()->flash('alert-danger', __('custom.changes_success_fail'));

                    return redirect()->back()->withInput()->withErrors($response['errors']);
                }
            }
        } else {
            return back()->withErrors(session()->flash('alert-danger', __('custom.record_not_found')));
        }

        return view('admin/resourceUpdate', [
            'class'     => $class,
            'resource'  => $resource,
            'uri'       => $resourceUri,
            'types'     => $types,
            'reqTypes'  => $reqTypes,
            'fields'    => $this->getResourceTransFields()
        ]);
    }
}
