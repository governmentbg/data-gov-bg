<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\User;
use App\Tags;
use App\DataSet;
use App\Resource;
use App\Organisation;
use App\DataSetGroup;
use App\CustomSetting;
use App\UserToOrgRole;
use App\ElasticDataSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
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

        $hasReported = false;

        $reportedReq = Request::create('/api/hasReportedResource', 'POST');
        $apiReported = new ApiResource($rq);
        $resultReported = $apiReported->hasReportedResource($reportedReq)->getData();

        if ($resultReported->flag) {
            $hasReported = true;
        }

        $paginationData = $this->getPaginationData(
            $datasets,
            $count,
            array_except(app('request')->input(), ['page']),
            $perPage
        );

        // Handle dataset delete
        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            return back();
        }

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
            'hasReported'           => $hasReported,
            'range'      => [
                'from' => isset($request->from) ? $request->from : null,
                'to'   => isset($request->to) ? $request->to : null
            ],
            'view' => 'datasets'
        ]);
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
        $visibilityOptions = DataSet::getVisibility();
        $accessTypes = Dataset::getAccessTypes();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->getAllOrganisations();
        $groups = $this->getGroupDropdown();

        if ($request->has('back')) {
            return redirect()->route('adminDataSets');
        }

        if ($request->isMethod('post') && ($request->has('create') || $request->has('add_resource'))){
            $data = $request->all();

            // Prepare post data for API request
            $data = $this->prepareTags($data);

            if (!empty($data['group_id'])) {
                $groupId = $data['group_id'];
            }

            unset($data['group_id'], $data['add_resource'], $data['create']);

            // Make request to API
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
                    return redirect(url('/admin/dataset/resource/create/'. $save->uri));
                }

                return redirect(url('/admin/dataset/view/'. $save->uri));
            }

            $request->session()->flash('alert-danger', $save->error->message);

            return redirect()->back()->withInput()->withErrors($save->errors);
        }

        return view('admin/datasetCreate', [
            'class'         => 'user',
            'visibilityOpt' => $visibilityOptions,
            'accessTypes'   => $accessTypes,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'fields'        => $this->getDatasetTransFields(),
            'orgRequired'   => (bool) UserToOrgRole::where('user_id', Auth::user()->id)->whereNotNull('org_id')->count()
        ]);
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
        $groups = $this->getGroupDropdown();

        if ($request->has('back')) {
            return redirect()->route('adminDataSets');
        }

        if ($request->has('save')) {
            if ($request->input('group_id', false) == false) {

                $getGroupsParams = [
                    'api_key'       => Auth::user()->api_key,
                    'dataset_uri'   => $uri,
                ];
                $ownedGroupsReq = Request::create('/api/getDatasetDetails', 'POST', $getGroupsParams);
                $api = new ApiDataset($ownedGroupsReq);
                $result = $api->getDatasetDetails($ownedGroupsReq)->getData();
                $ownedGroups = $result->data->groups;
                $ownedGroupsIds = [];
                foreach ($ownedGroups as $group) {
                    $ownedGroupsIds[] = $group->id;
                }
                $removeGroupsReq = [
                    'api_key'       => Auth::user()->api_key,
                    'data_set_uri'  => $uri,
                    'group_id'      => $ownedGroupsIds,
                ];
                $result = Request::create('/api/removeDatasetFromGroup', 'POST', $removeGroupsReq);
                $api = new ApiDataset($result);
                $result = $api->removeDatasetFromGroup($result)->getData();

                if (!$result->success) {
                    $request->session()->flash('alert-danger', __('custom.add_datasetgroup_fail'));
                }

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));
                }
            } else {
                $groupParams = [
                    'api_key'       => Auth::user()->api_key,
                    'data_set_uri'  => $uri,
                    'group_id'      => $request->input('group_id', []),
                ];

                $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $groupParams);
                $api = new ApiDataset($addGroup);

                $added = $api->addDataSetToGroup($addGroup)->getData();
                if (!$added->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));
                }

                if ($added->success) {
                    $request->session()->flash('alert-success', __('custom.add_success'));
                }
            }
        }

        $detailsReq = Request::create('/api/getDatasetDetails', 'POST', $params);
        $api = new ApiDataSet($detailsReq);
        $result = $api->getDatasetDetails($detailsReq)->getData();
        $dataset = !empty($result->data) ? $result->data : null;
        $setGroups = [];

        if (!empty($dataset->groups)) {
            foreach ($dataset->groups as $record) {
                $setGroups[] = $record->id;
            }
        }

        // Prepare request for resources
        $resPerPage = 10;
        $pageNumber = !empty($request->rpage) ? $request->rpage : 1;

        unset($params['dataset_uri']);
        $params['criteria']['dataset_uri'] = $uri;
        $params['records_per_page'] = $resPerPage;
        $params['page_number'] = $pageNumber;

        if (isset($request->order)) {
            $params['criteria']['order']['field'] = $request->order;
        }

        if (isset($request->order_type)) {
            $params['criteria']['order']['type'] = $request->order_type;
        }

        $resourcesReq = Request::create('/api/listResources', 'POST', $params);
        $apiResources = new ApiResource($resourcesReq);
        $resources = $apiResources->listResources($resourcesReq)->getData();

        $resCount = isset($resources->total_records) ? $resources->total_records : 0;
        $resources = !empty($resources->resources) ? $resources->resources : [];

        // Get category details
        if (!empty($dataset->category_id)) {
            $params = [
                'category_id' => $dataset->category_id,
            ];
            $rq = Request::create('/api/getMainCategoryDetails', 'POST', $params);
            $api = new ApiCategory($rq);
            $res = $api->getMainCategoryDetails($rq)->getData();

            $dataset->category_name = isset($res->category) && !empty($res->category) ? $res->category->name : '';
        }

        // Get terms of use details
        if (!empty($dataset->terms_of_use_id)) {
            $params = [
                'terms_id' => $dataset->terms_of_use_id,
            ];
            $rq = Request::create('/api/getTermsOfUseDetails', 'POST', $params);
            $api = new ApiTermsOfUse($rq);
            $res = $api->getTermsOfUseDetails($rq)->getData();

            $dataset->terms_of_use_name = isset($res->data) && !empty($res->data) ? $res->data->name : '';
        }

        // Handle dataset delete
        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $alert = 'alert-success';
                $message = __('custom.success_dataset_delete');
            } else {
                $alert = 'alert-danger';
                $message = __('custom.fail_dataset_delete');
            }

            return redirect('/admin/datasets')->with($alert, $message);
        }

        $paginationData = $this->getPaginationData(
            $resources,
            $resCount,
            array_except(app('request')->input(), ['rpage']),
            $resPerPage,
            'rpage'
        );

        return view('admin/datasetView', [
            'class'      => 'user',
            'dataset'    => $this->getModelUsernames($dataset),
            'groups'     => $groups,
            'setGroups'  => $setGroups,
            'resources'  => $paginationData['items'],
            'pagination' => $paginationData['paginate'],
            'uri'        => $uri,
            'sorting'    => 'adminMyData'
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
        $accessTypes = Dataset::getAccessTypes();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->getAllOrganisations();
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
            $request->session()->flash('alert-danger', __('custom.no_dataset_found'));

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

            // If all groups are deselected
            if (count($setGroups) && is_null($groupId)) {
                $post['group_id'] = $setGroups;
                $removeGroup = Request::create('/api/removeDatasetFromGroup', 'POST', $post);
                $api = new ApiDataSet($removeGroup);
                $remove = $api->removeDatasetFromGroup($removeGroup)->getData();

                if (!$remove->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));

                    return redirect()->back()->withInput()->withErrors($remove->errors);
                }
            }

            if (!is_null($groupId)) {
                $post['group_id'] = $groupId;
                $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $post);
                $api = new ApiDataSet($addGroup);
                $added = $api->addDataSetToGroup($addGroup)->getData();

                if (!$added->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));

                    return redirect()->back()->withInput()->withErrors($added->errors);
                }
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
            'accessTypes'   => $accessTypes,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'hasResources'  => $hasResources,
            'setGroups'     => $setGroups,
            'fields'        => $this->getDatasetTransFields(),
            'orgRequired'   => (bool) UserToOrgRole::where('user_id', Auth::user()->id)->whereNotNull('org_id')->count()
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
        $dataset = DataSet::where('uri', $datasetUri)->first();
        $maxResourceRows = 2000;
        $bPaging = true;

        if (empty($dataset)) {
            session()->flash('alert-danger', __('custom.no_dataset_found'));

            return redirect('/user/datasets');
        }

        if ($request->has('ready_metadata')) {
            $data = $request->except('file');
            $file = $request->file('file');
            $data['description'] = $data['descript'];

            $extension = isset($file) ? $file->getClientOriginalExtension() : '';

            $response = ResourceController::addMetadata($datasetUri, $data, $file);

            if ($response['success']) {
                if (in_array($data['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                    $request->session()->flash('alert-success', __('custom.add_success'));
                    return redirect('/admin/resource/view/'. $response['uri']);
                }

                if(
                  is_array($data)
                  && isset($response['data']['zip'])
                ) {
                  $request->session()->flash('alert-success', __('custom.add_success'));
                  return redirect('/admin/resource/view/'. $response['uri']);
                }

                if ($data['type'] == Resource::TYPE_API) {
                    $extension = $response['extension'];
                }

                if (
                    is_array($data)
                    && isset($response['data']['csvData'])
                    && count($response['data']['csvData']) > $maxResourceRows
                ) {
                    $bPaging = false;
                    $response['data']['csvData'] = collect($response['data']['csvData'])->paginate(100, 1);
                }

                return view('admin/resourceImport', array_merge([
                    'class'         => $class,
                    'types'         => $types,
                    'resourceUri'   => $response['uri'],
                    'action'        => 'create',
                    'bPaging'       => $bPaging,
                    'extension'     => $extension
                ], $response['data']));
            } else {
                // Delete resource record on fail
                if (isset($response['uri'])) {
                    $failMetadata = Resource::where('uri', $response['uri'])->forceDelete();
                }

                $request->session()->flash(
                    'alert-danger',
                    empty($response['data']['error']) ? __('custom.changes_success_fail') : $response['data']['error']
                );

                return redirect()->back()->withInput()->withErrors($response['errors']);
            }
        }

        return view('user/resourceCreate', [
            'class'         => $class,
            'uri'           => $datasetUri,
            'types'         => $types,
            'reqTypes'      => $reqTypes,
            'fields'        => $this->getResourceTransFields(),
            'dataSetName'   => $dataset->name,
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
        $versionsPerPage = 10;
        $resourceVersionFormat = null;

        if (empty($result->resource)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_resource_found')));
        }

        if ($result->resource->file_format) {
            $versionQuery = ElasticDataSet::where('resource_id', $result->resource->id);

            if (is_null($version)) {
                $versionQuery->orderBy('id', 'desc');
            } else {
                $versionQuery->where('version', $version);
            }

            $versionResult = $versionQuery->first();

            if ($versionResult) {
                $resourceVersionFormat = $versionResult->format;
            }
        }

        $resourceVersionFormat = is_null($resourceVersionFormat) ? Resource::getFormatsCode($result->resource->file_format) : $resourceVersionFormat;

        $resource = $this->getModelUsernames($result->resource);
        $resource->format_code = Resource::getFormatsCode($resource->file_format);
        $formats = Resource::getFormats(true);

        if (empty($version)) {
            $version = $resource->version;
        }

        $datasetReq = Request::create('/api/getDatasetDetails', 'POST', ['dataset_uri' => $resource->dataset_uri]);
        $apiDatasets = new ApiDataset($datasetReq);
        $dataset = $apiDatasets->getDatasetDetails($datasetReq)->getData();
        $dataset = !empty($dataset->data) ? $dataset->data : null;

        if (!is_null($dataset)) {
            $datasetData = [
                'name'  => $dataset->name,
                'uri'   => $dataset->uri,
            ];
        } else {
            $datasetData = null;
        }

        // Get elastic search data for the resource
        $reqEsData = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $uri, 'version' => $version]);
        $apiEsData = new ApiResource($reqEsData);
        $response = $apiEsData->getResourceData($reqEsData)->getData();

        $data = !empty($response->data) ? $response->data : [];

        if (
            $resourceVersionFormat == Resource::FORMAT_XML
            || $resourceVersionFormat == Resource::FORMAT_RDF
        ) {
            $convertData = [
                'api_key'   => \Auth::user()->api_key,
                'data'      => $data,
            ];

            $method = 'json2'. strtolower(Resource::getFormats()[$resourceVersionFormat]);
            $reqConvert = Request::create('/'. $method, 'POST', $convertData);
            $apiConvert = new ApiConversion($reqConvert);
            $resultConvert = $apiConvert->$method($reqConvert)->getData();
            $data = isset($resultConvert->data) ? $resultConvert->data : [];
        }

        // Handle delete request
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

        if (!empty($resource->versions_list)) {
            usort($resource->versions_list, function($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                return ($a > $b) ? -1 : 1;
            });
        }

        $count = count($resource->versions_list);
        $verData = collect($resource->versions_list)->paginate($versionsPerPage);

        $paginationData = $this->getPaginationData(
            $verData,
            $count,
            array_except(app('request')->input(), ['page']),
            $versionsPerPage
        );

        $dataPerPage = 25;
        $maxDataRows = 2000;
        $backPaging = count($data) > $maxDataRows;
        $search = $request->has('q') ? $request->offsetGet('q') : '';
        $pageNumber = $request->has('rpage') ? $request->offsetGet('rpage') : 1;
        $perPage = $request->has('per_page') ? $request->offsetGet('per_page') : $dataPerPage;

        if (!empty($search) && $resource->format_code == Resource::FORMAT_CSV) {
            $data = $this->searchResourceRows($search, $data);
        }

        if ($request->has('order') && $request->has('order_type')) {
            $oType = $request->order_type == 'asc' ? SORT_ASC : SORT_DESC;
            $tHeader = isset($data[0]) ? $data[0] : [];

            if (!empty($tHeader) && is_numeric($request->order) && count($tHeader) > $request->order) {
                unset($data[0]);
                $orderArr = array_column($data, $request->order);

                if (count($orderArr) == count($data)) {
                    array_multisort($orderArr, $oType, $data);
                }

                $data = array_merge([0 => $tHeader], $data);
            }
        }

        $resourcePaginationData = $this->getResourcePaginationData(
            $data,
            $resource,
            $pageNumber,
            $perPage,
            $backPaging
        );

        if (
            $resourcePaginationData['resPagination'] instanceof \Illuminate\Pagination\LengthAwarePaginator
            && $pageNumber > $resourcePaginationData['resPagination']->lastPage()
        ) {
            return redirect($request->fullUrlWithQuery(['rpage' => '1']));
        }

        return view('admin/resourceView', [
            'class'         => 'user',
            'search'        => $search,
            'resource'      => $resource,
            'versions'      => $verData,
            'pagination'    => $paginationData['paginate'],
            'resPagination' => $resourcePaginationData['resPagination'],
            'data'          => $resourcePaginationData['data'],
            'dataPerPage'   => $dataPerPage,
            'versionView'   => $version,
            'dataset'       => $datasetData,
            'supportName'   => !is_null($dataset) ? $dataset->support_name : null,
            'formats'       => $formats,
            'versionFormat' => $resourceVersionFormat
        ]);
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

        if (empty($res->resource)) {
            return back()->withErrors(session()->flash('alert-danger', __('custom.record_not_found')));
        }

        $resourceData = $res->resource;

        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();
        $resource = Resource::where('uri', $uri)->first()->loadTranslations();
        $custFields = CustomSetting::where('resource_id', $resource->id)->get()->loadTranslations();

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
                    'custom_fields'         => $request->offsetGet('custom_fields'),
                    'is_reported'           => is_null($request->offsetGet('reported'))
                        ? Resource::REPORTED_FALSE
                        : Resource::REPORTED_TRUE
                    ];

                if ($resource->resource_type == Resource::TYPE_HYPERLINK) {
                    $data['type'] = $resource->resource_type;
                    $data['resource_url'] = $request->offsetGet('resource_url');
                }

                if ($resource->resource_type == Resource::TYPE_API) {
                    $data['type'] = $resource->resource_type;
                    $data['resource_url'] = $request->offsetGet('resource_url');
                    $data['http_rq_type'] = $request->offsetGet('http_rq_type');
                    $data['http_headers'] = $request->offsetGet('http_headers') ?: '';
                    $data['post_data'] = $request->offsetGet('post_data') ?: '';
                    $data['upl_freq_type'] = $request->offsetGet('upl_freq_type');
                    $data['upl_freq'] = $request->offsetGet('upl_freq');
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
            'class'         => 'user',
            'resource'      => $resource,
            'uri'           => $uri,
            'types'         => $types,
            'reqTypes'      => $reqTypes,
            'custFields'    => $custFields,
            'fields'        => $this->getResourceTransFields(),
            'parent'        => isset($parent) ? $parent : false,
            'dataseUri'     => $resourceData->dataset_uri
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
    public function resourceUpdate(Request $request, $resourceUri, $parentUri = null)
    {
        $rq = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $resourceUri]);
        $api = new ApiResource($rq);
        $res = $api->getResourceMetadata($rq)->getData();

        if (empty($res->resource)) {
            return back()->withErrors(session()->flash('alert-danger', __('custom.record_not_found')));
        }

        $resourceData = $res->resource;
        $file = $request->file('file');
        $extension = isset($file) ? $file->getClientOriginalExtension() : '';
        $class = 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();
        $resource = Resource::where('uri', $resourceUri)->first()->loadTranslations();
        $maxResourceRows = 2000;
        $bPaging = true;

        if ($parentUri) {
            $parent = Organisation::where('uri', $parentUri)->first();
            $parent->logo = $this->getImageData($parent->logo_data, $parent->logo_mime_type, $parent->type == Organisation::TYPE_GROUP ? 'group' : 'org');
        }

        if ($resource) {
            if ($request->has('ready_metadata')) {
                $data = [
                    'type'          => $resource->resource_type,
                    'resource_url'  => $request->offsetGet('resource_url'),
                    'http_rq_type'  => $request->offsetGet('http_rq_type'),
                    'http_headers'  => $request->offsetGet('http_headers') ?: '',
                    'post_data'     => $request->offsetGet('post_data') ?: '',
                    'upl_freq_type' => $request->offsetGet('upl_freq_type'),
                    'upl_freq'      => $request->offsetGet('upl_freq'),
                ];

                $file = $request->file('file');

                $response = ResourceController::addMetadata($resourceUri, $data, $file, true);

                if ($response['success']) {
                    if (in_array($data['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                        return redirect('/admin/resource/view/'. $response['uri']);
                    }

                    if ($data['type'] == Resource::TYPE_API) {
                        $extension = $response['extension'];
                    }

                    if (!empty($parent)) {
                        $key = $parent->type == Organisation::TYPE_GROUP ? 'group' : 'fromOrg';
                        $response['data'][$key] = $parent;
                    }

                    if (
                        is_array($data)
                        && isset($response['data']['csvData'])
                        && count($response['data']['csvData']) > $maxResourceRows
                    ) {
                        $bPaging = false;
                        $response['data']['csvData'] = collect($response['data']['csvData'])->paginate(100, 1);
                    }

                    return view('admin/resourceImport', array_merge([
                        'class'         => $class,
                        'types'         => $types,
                        'resourceUri'   => $response['uri'],
                        'action'        => 'update',
                        'bPaging'       => $bPaging,
                        'extension'     => $extension
                    ], $response['data']));
                } else {
                    $request->session()->flash(
                        'alert-danger',
                        empty($response['data']['error']) ? __('custom.changes_success_fail') : $response['data']['error']
                    );

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
            'fields'    => $this->getResourceTransFields(),
            'parent'    => isset($parent) ? $parent : false,
            'dataseUri' => $resourceData->dataset_uri
        ]);
    }

    public function listDeletedDatasets(Request $request)
    {
        $perPage = 10;
        $page = !empty($request->page) ? $request->page : 1;
        $allowActionsForDataset = [];
        $locale = \LaravelLocalization::getCurrentLocale();

        $query = Dataset::whereNotNull('deleted_at')->withTrashed();

        $search = $request->has('q') ? $request->offsetGet('q') : '';

        if (!empty($search)) {
            $tntIds = DataSet::search($search)->withTrashed()->get()->pluck('id');

            $fullMatchIds = DataSet::select('data_sets.id')
                ->leftJoin('translations', 'translations.group_id', '=', 'data_sets.name')
                ->where('translations.locale', $locale)
                ->where('translations.text', 'like', '%'. $search .'%')
                ->withTrashed()
                ->pluck('id');

            $ids = $fullMatchIds->merge($tntIds)->unique();

            $query->whereIn('data_sets.id', $ids);

            if (count($ids)) {
                $strIds = $ids->implode(',');
                $query->orderByRaw(\DB::raw('FIELD(data_sets.id, '. $strIds .')'));
            }
        }

        $totalDatasets = $query->count();
        $datasets = $query->orderBy('deleted_at', 'desc')
            ->forPage($page, $perPage)
            ->get();

        if (\Elasticsearch::ping()) {
            foreach ($datasets as $singleDataset) {
                $allowActionsForDataset[] = $singleDataset->id;
            }
        }

        $paginationData = $this->getPaginationData($datasets, $totalDatasets, array_except(app('request')->input(), ['page']), $perPage);

        return view('admin/datasetsDeleted',
            [
                'view'                   => 'deletedDatasets',
                'datasets'               => $paginationData['items'],
                'pagination'             => $paginationData['paginate'],
                'allowActionsForDataset' => $allowActionsForDataset,
                'search'                 => $search,
            ]
        );
    }

    public function viewDeletedDataset(Request $request, $uri)
    {
        $perPage = 6;
        $allowDelete = false;
        $groupsIdArray = [];
        $page = !empty($request->page) ? $request->page : 1;
        $dataset = Dataset::where('uri', $uri)->withTrashed()->first();

        $groups = DataSetGroup::where('data_set_id', $dataset->id)->get();

        foreach ($groups as $singleGroup) {
            $groupsIdArray[] = $singleGroup->group_id;
        }

        $dataset->groups = Organisation::whereIn('id', $groupsIdArray)->get();
        $resourcesTotal = $dataset->resource()->withTrashed()->count();
        $resources = $dataset->resource()->withTrashed()->forPage($page, $perPage)->get();
        $dataset->created_by = User::where('id', $dataset->created_by)->value('username');
        $dataset->updated_by = User::where('id', $dataset->updated_by)->value('username');
        $dataset->deleted_by = User::where('id', $dataset->deleted_by)->value('username');

        if (\Elasticsearch::ping()) {
            $allowDelete = true;
        }

        $paginationData = $this->getPaginationData($resources, $resourcesTotal, [], $perPage);

        return view('admin/datasetDeletedView',
            [
                'view'            => 'deletedDatasets',
                'dataset'         => $dataset,
                'resources'       => $paginationData['items'],
                'pagination'      => $paginationData['paginate'],
                'allowDelete'     => $allowDelete
            ]
        );
    }
}
