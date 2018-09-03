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
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;

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

            if (isset($request->from)) {
                $params['criteria']['date_from'] = date_format(date_create($request->from), 'Y-m-d H:i:s');
            }

            if (isset($request->to)) {
                $params['criteria']['date_to'] = date_format(date_create($request->to .' 23:59'), 'Y-m-d H:i:s');
            }

            if (isset($selectedOrgs)) {
                $selectedOrgs = array_unique($selectedOrgs);
                $params['criteria']['org_ids'] = $selectedOrgs;
            }

            if (isset($selectedGroups)) {
                $selectedGroups = array_unique($selectedGroups);
                $params['criteria']['group_ids'] = $selectedGroups;
            }

            $params = [
                'api_key'           => \Auth::user()->api_key,
                'records_per_page'  => $perPage,
                'page_number'       => !empty($request->page) ? $request->page : 1,
            ];

            $rq = Request::create('/api/listDataSets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $result = $api->listDataSets($rq)->getData();
            $datasets = !empty($result->datasets) ? $result->datasets : [];
            $count = !empty($result->total_records) ? $result->total_records : 0;

            $paginationData = $this->getPaginationData($datasets, $count, [], $perPage);

            return view('admin/datasets', [
                'class'             => 'user',
                'datasets'          => $paginationData['items'],
                'pagination'        => $paginationData['paginate'],
                'organisations'     => $organisations,
                'orgDropCount'      => count($this->getOrgDropdown()),
                'selectedOrgs'      => $selectedOrgs,
                'groups'            => $groups,
                'groupDropCount'    => count($this->getGroupDropdown()),
                'selectedGroups'    => $selectedGroups,
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

                $savePost = Request::create('/api/addDataSet', 'POST', $params);
                $api = new ApiDataSet($savePost);
                $save = $api->addDataSet($savePost)->getData();

                if ($save->success) {
                    if (isset($groupId)) {
                        $groupParams['group_id'] = $groupId;
                        $groupParams['data_set_uri'] = $save->uri;
                        $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $groupParams);
                        $res = $api->addDataSetToGroup($addGroup)->getData();

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

        $detailsReq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($detailsReq);
        $dataset = $api->getDataSetDetails($detailsReq)->getData();
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

        $setRq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSet($setRq);
        $result = $api->getDataSetDetails($setRq)->getData();

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

            $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $post);
            $added = $api->addDataSetToGroup($addGroup)->getData();

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

            $editRq = Request::create('/api/editDataSet', 'POST', $edit);
            $success = $api->editDataSet($editRq)->getData();

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

            $apiRequest = Request::create('/api/deleteDataSet', 'POST', $params);
            $api = new ApiDataSet($apiRequest);
            $result = $api->deleteDataSet($apiRequest)->getData();
            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }
        }

        return redirect('/admin/datasets');
    }
}
