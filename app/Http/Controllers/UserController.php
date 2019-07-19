<?php

namespace App\Http\Controllers;

use App\Role;
use App\Tags;
use App\User;
use App\Locale;
use App\Module;
use App\DataSet;
use App\Category;
use App\Resource;
use App\UserFollow;
use App\TermsOfUse;
use App\UserSetting;
use App\DataSetGroup;
use App\Organisation;
use App\CustomSetting;
use App\UserToOrgRole;
use App\RoleRight;
use App\ActionsHistory;
use App\ElasticDataSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\Api\RightController;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Api\TagController as ApiTags;
use App\Http\Controllers\Api\RoleController as ApiRole;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\SignalController as ApiSignal;
use App\Http\Controllers\Api\LocaleController as ApiLocale;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;
use App\Http\Controllers\Api\CustomSettingsController as ApiCustomSettings;
use App\Http\Controllers\Api\TermsOfUseRequestController as ApiTermsOfUseRequest;

class UserController extends Controller {
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return redirect()->action('UserController@newsFeed');
    }

    /**
     * Displays a list of datasets created by the logged user
     *
     * @param Request $request
     * @return view with datasets
     *
     */
    public function datasets(Request $request)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_VIEW
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        if (!$rightCheck) {
            return view('user/datasets', [
                'class'         => 'user',
                'datasets'      => [],
                'pagination'    => null,
                'buttons'       => $buttons
            ]);
        }

        $getParams = [];
        $perPage = 6;
        $params = [
            'api_key'           => \Auth::user()->api_key,
            'criteria'          => [
                'created_by'        => \Auth::user()->id,
            ],
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        $search = $request->offsetGet('q');

        if(!empty($search)) {
            $params['criteria']['keywords'] = trim($search);

            $getParams = [
                'q' => $search
            ];
        }

        $rq = Request::create('/api/listDatasets', 'POST', $params);
        $api = new ApiDataSet($rq);
        $result = $api->listDatasets($rq)->getData();
        $datasets = !empty($result->datasets) ? $result->datasets : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        foreach ($datasets as $dataset) {
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_VIEW,
                [],
                [
                    'created_by' => $dataset->created_by
                ]
            );

            $buttons[$dataset->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_EDIT,
                [],
                [
                    'created_by' => $dataset->created_by,
                ]
            );

            $buttons[$dataset->uri]['edit'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $dataset->created_by,
                ]
            );

            $buttons[$dataset->uri]['delete'] = $rightCheck;
        }

        $hasReported = false;

        $reportedReq = Request::create('/api/hasReportedResource', 'POST', ['user_id' => \Auth::user()->id]);
        $apiReported = new ApiResource($rq);
        $resultReported = $apiReported->hasReportedResource($reportedReq)->getData();

        if ($resultReported->flag) {
            $hasReported = true;
        }

        $paginationData = $this->getPaginationData($datasets, $count, $getParams, $perPage);

        // handle dataset delete
        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            return back();
        }

        return view('user/datasets', [
            'class'         => 'user',
            'datasets'      => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'buttons'       => $buttons,
            'hasReported'   => $hasReported,
            'search'        => $search,
        ]);
    }

    public function orgDatasets(Request $request, $uri)
    {
        $org = Organisation::where('uri', $uri)->first();
        $orgId = !is_null($org) ? $org->id : null;

        if (!$orgId) {
            return back();
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_VIEW,
            [
                'org_id' => $org->id
            ],
            [
                'org_id' => $org->id
            ]
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        if (!$rightCheck) {
            return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
        }

        $org->logo = $this->getImageData($org->logo_data, $org->logo_mime_type);
        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'          => [
                'org_ids'       => [$orgId],
                'status'        => DataSet::STATUS_PUBLISHED,
            ],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $q = null;

        if ($request->has('q')) {
            $q = $request->offsetGet('q');
            $params['criteria']['keywords'] = $q;
        }

        $hasRole = !is_null(UserToOrgRole::where('user_id', \Auth::user()->id)->where('org_id', $orgId)->first());

        if (!is_null($orgId) && $hasRole) {
            $rq = Request::create('/api/listDatasets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $datasets = $api->listDatasets($rq)->getData();
            $paginationData = $this->getPaginationData($datasets->datasets, $datasets->total_records, compact($q), $perPage);
        } else {
            $paginationData = $this->getPaginationData([], 0, [], $perPage);
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        if (isset($datasets)) {
            foreach ($datasets->datasets as $dataset) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::DATA_SETS,
                    RoleRight::RIGHT_VIEW,
                    [
                        'org_id'    => $orgId
                    ],
                    [
                        'org_id'    => $orgId
                    ]
                );

                $buttons[$dataset->uri]['view'] = $rightCheck;

                $rightCheck = RoleRight::checkUserRight(
                    Module::DATA_SETS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id'       => $orgId
                    ],
                    [
                        'org_id'        => $orgId
                    ]
                );

                $buttons[$dataset->uri]['edit'] = $rightCheck;

                $rightCheck = RoleRight::checkUserRight(
                    Module::DATA_SETS,
                    RoleRight::RIGHT_ALL,
                    [
                        'org_id'       => $orgId
                    ],
                    [
                        'org_id'        => $orgId
                    ]
                );

                $buttons[$dataset->uri]['delete'] = $rightCheck;
            }
        }

        // handle dataset delete
        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            return back();
        }

        return view(
            'user/orgDatasets',
            [
                'class'         => 'user',
                'datasets'      => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'activeMenu'    => 'organisation',
                'search'        => $q,
                'buttons'       => $buttons,
                'organisation'  => $org,
            ]
        );
    }

    public function orgDatasetEdit(Request $request, DataSet $datasetModel, $orgUri, $uri)
    {
        $model = DataSet::where('uri', $uri)->with('dataSetGroup')->first();

        if (empty($model)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_dataset_found')));
        }

        $model->loadTranslations();
        $orgReq = Request::create('/api/getOrganisationDetails', 'POST', ['org_uri' => $orgUri]);
        $apiOrg = new ApiOrganisation($orgReq);
        $orgData = $apiOrg->getOrganisationDetails($orgReq)->getData();
        $organisation = !empty($orgData->data) ? $orgData->data : null;

        if (!isset($organisation)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_org_found')));
        }

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'       => $model->org_id
            ],
            [
                'created_by'    => $model->created_by,
                'org_id'        => $model->org_id
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'       => $model->org_id
            ],
            [
                'created_by'    => $model->created_by,
                'org_id'        => $model->org_id
            ]
        );

        $buttons['addResource'] = $rightCheck;

        //prepare data for form
        $visibilityOptions = $datasetModel->getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $setGroups = [];

        if (!empty($model->dataSetGroup)) {
            foreach ($model->dataSetGroup as $record) {
                $setGroups[] = $record->group_id;
            }
        }

        // check for resources - show publish button
        $hasResources = Resource::where('data_set_id', $model->id)->count();
        $withModel = CustomSetting::where('data_set_id', $model->id)->get()->loadTranslations();
        $tagModel = Tags::whereHas('dataSetTags', function($q) use ($model) {
                $q->where('data_set_id', $model->id);
            })
            ->get();

        // handle form submit
        if ($request->has('save') || $request->has('publish')) {
            $editData = $request->all();

            if ($editData['uri'] == $uri) {
                unset($editData['uri']);
                $newURI = $uri;
            } else {
                $newURI = $editData['uri'];
            }

            $tagList = $request->offsetGet('tags');
            $editData = $this->prepareTags($editData);
            $groupId = $request->offsetGet('group_id');

            $post = [
                'api_key'       => Auth::user()->api_key,
                'data_set_uri'  => $uri,
                'group_id'      => $groupId,
            ];

            // if all groups are deselected
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
            $api = new ApiDataSet($editRq);
            $success = $api->editDataset($editRq)->getData();

            if ($success->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));
                $root = Role::isAdmin() ? 'admin' : 'user';

                return redirect(url('/'. $root .'/organisations/'. $organisation->uri .'/dataset/edit/'. $newURI));
            } else {
                session()->flash('alert-danger', __('custom.edit_error'));

                return redirect()->back()->withInput()->withErrors($success->errors);
            }
        }

        return view('user/orgDatasetEdit', [
            'class'         => 'user',
            'dataSet'       => $model,
            'tagModel'      => $tagModel,
            'withModel'     => $withModel,
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'fromOrg'       => $organisation,
            'groups'        => $groups,
            'hasResources'  => $hasResources,
            'buttons'       => $buttons,
            'setGroups'     => $setGroups,
            'fields'        => $this->getDatasetTransFields(),
        ]);
    }

    /**
     * Displays detail information for a given dataset
     * created by the given user
     *
     * @param Request $request
     * @return view with dataset information
     *
     */
    public function datasetView(Request $request, $uri)
    {
        $params['dataset_uri'] = $uri;

        $datasetReq = Request::create('/api/getDatasetDetails', 'POST', ['dataset_uri' => $uri]);
        $api = new ApiDataset($datasetReq);
        $result = $api->getDatasetDetails($datasetReq)->getData();
        $dataset = !empty($result->data) ? $result->data : null;

        if (!isset($dataset)) {
            return back();
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [],
            [
                'created_by' => $dataset->created_by
            ]
        );

        $buttons[$dataset->uri]['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_ALL,
            [],
            [
                'created_by' => $dataset->created_by
            ]
        );

        $buttons[$dataset->uri]['delete'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addResource'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addToGroup'] = $rightCheck;
        $groups = $setGroups = [];

        if ($buttons['addToGroup']) {
            $groups = $this->prepareGroups();
        }

        if (!empty($dataset->groups)) {
            foreach ($dataset->groups as $record) {
                $setGroups[] = (int) $record->id;
            }
        }

        // prepera request for resources
        unset($params['dataset_uri']);
        $params['criteria']['dataset_uri'] = $uri;
        $resPerPage = 10;
        $pageNumber = !empty($request->rpage) ? $request->rpage : 1;
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

        // get category details
        if (!empty($dataset->category_id)) {
            $params = [
                'category_id' => $dataset->category_id,
            ];
            $rq = Request::create('/api/getMainCategoryDetails', 'POST', $params);
            $api = new ApiCategory($rq);
            $res = $api->getMainCategoryDetails($rq)->getData();

            $dataset->category_name = isset($res->category) && !empty($res->category) ? $res->category->name : '';
        }

        // get terms of use details
        if (!empty($dataset->terms_of_use_id)) {
            $params = [
                'terms_id' => $dataset->terms_of_use_id,
            ];
            $rq = Request::create('/api/getTermsOfUseDetails', 'POST', $params);
            $api = new ApiTermsOfUse($rq);
            $res = $api->getTermsOfUseDetails($rq)->getData();

            $dataset->terms_of_use_name = isset($res->data) && !empty($res->data) ? $res->data->name : '';
        }

        foreach ($resources as $resource) {
            $rightCheck = RoleRight::checkUserRight(
                Module::RESOURCES,
                RoleRight::RIGHT_VIEW,
                [],
                [
                    'created_by'   => $resource->created_by
                ]
            );

            $buttons[$resource->uri]['view'] = $rightCheck;
        }

        // handle groups change
        if ($request->has('save')) {
            $groupId = $request->offsetGet('group_id');
            $post = [
                'api_key'       => Auth::user()->api_key,
                'data_set_uri'  => $uri,
                'group_id'      => $groupId,
            ];

            // if all groups are deselected
            if (count($setGroups) && is_null($groupId)) {
                $post['group_id'] = $setGroups;
                $removeGroup = Request::create('/api/removeDatasetFromGroup', 'POST', $post);
                $api = new ApiDataSet($removeGroup);
                $remove = $api->removeDatasetFromGroup($removeGroup)->getData();

                if (!$remove->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));

                    return redirect()->back()->withInput()->withErrors($remove->errors);
                }else {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));
                }

                $dataset->groups = $setGroups = [];
            }

            if (!is_null($groupId)) {
                $request->session()->flash('alert-success', __('custom.changes_success_save'));
                $post['group_id'] = $groupId;
                $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $post);
                $api = new ApiDataSet($addGroup);
                $added = $api->addDataSetToGroup($addGroup)->getData();

                if (!$added->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));

                    return redirect()->back()->withInput()->withErrors($added->errors);
                }

                $post = [ 'criteria' => [
                        'group_ids' => $groupId,
                    ],
                ];

                $getGroups = Request::create('/api/listGroups', 'POST', $post);
                $apiGroups = new ApiOrganisation($getGroups);
                $groupResponse = $apiGroups->listGroups($getGroups)->getData();

                if ($groupResponse->success) {
                    $dataset->groups = $groupResponse->groups;
                }

                $setGroups = $groupId;
            }
        }

        // handle dataset delete
        if ($request->has('delete')) {
            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            $root = Role::isAdmin() ? 'admin' : 'user';

            return redirect('/'. $root .'/datasets');
        }

        $paginationData = $this->getPaginationData(
            $resources,
            $resCount,
            array_except(app('request')->input(), ['rpage']),
            $resPerPage,
            'rpage'
        );

        return view('user/datasetView', [
            'class'      => 'user',
            'groups'     => $groups,
            'setGroups'  => $setGroups,
            'dataset'    => $this->getModelUsernames($dataset),
            'resources'  => $paginationData['items'],
            'buttons'    => $buttons,
            'pagination' => $paginationData['paginate'],
            'uri'        => $uri,
            'sorting'    => 'userDatasetView'
        ]);
    }

    /**
     * Displays detailed information for a given dataset
     * created by the given user for the organisation
     *
     * @param Request $request
     * @return view with dataset information
     *
     */
    public function orgDatasetView(Request $request, $uri)
    {
        $datasetReq = Request::create('/api/getDatasetDetails', 'POST', ['dataset_uri' => $uri]);
        $apiDatasets = new ApiDataset($datasetReq);
        $result = $apiDatasets->getDatasetDetails($datasetReq)->getData();
        $datasetData = !empty($result->data) ? $this->getModelUsernames($result->data) : null;

        if (!isset($datasetData)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_dataset_found')));
        }

        if (isset($datasetData->org_id)) {
            $organisation = Organisation::where('id', $datasetData->org_id)->first();

            if (!isset($organisation)) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_org_found')));
            }

            $organisation->logo = $this->getImageData($organisation->logo_data, $organisation->logo_mime_type);
        }

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_VIEW,
            [
                'org_id'    => $datasetData->org_id
            ],
            [
                'org_id'    => $datasetData->org_id
            ]
        );

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_VIEW
        );

        $buttons['view'] = $rightCheck;

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'    => $datasetData->org_id
            ],
            [
                'org_id'    => $datasetData->org_id
            ]
        );

        $buttons[$datasetData->uri]['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_ALL,
            [
                'org_id'    => $datasetData->org_id
            ],
            [
                'org_id'    => $datasetData->org_id
            ]
        );

        $buttons[$datasetData->uri]['delete'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'    => $datasetData->org_id
            ],
            [
                'org_id'    => $datasetData->org_id
            ]
        );

        $buttons['addResource'] = $rightCheck;

        $resPerPage = 10;
        $pageNumber = !empty($request->rpage) ? $request->rpage : 1;
        $params['records_per_page'] = $resPerPage;
        $params['page_number'] = $pageNumber;
        $params['criteria']['dataset_uri'] = $uri;

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

        // get category details
        if (!empty($datasetData->category_id)) {
            $params = [
                'category_id' => $datasetData->category_id,
            ];
            $rq = Request::create('/api/getMainCategoryDetails', 'POST', $params);
            $api = new ApiCategory($rq);
            $res = $api->getMainCategoryDetails($rq)->getData();

            $datasetData->category_name = isset($res->category) && !empty($res->category) ? $res->category->name : '';
        }

        // get terms of use details
        if (!empty($datasetData->terms_of_use_id)) {
            $params = [
                'terms_id' => $datasetData->terms_of_use_id,
            ];
            $rq = Request::create('/api/getTermsOfUseDetails', 'POST', $params);
            $api = new ApiTermsOfUse($rq);
            $res = $api->getTermsOfUseDetails($rq)->getData();

            $datasetData->terms_of_use_name = isset($res->data) && !empty($res->data) ? $res->data->name : '';
        }

        foreach ($resources as $resource) {
            $rightCheck = RoleRight::checkUserRight(
                Module::RESOURCES,
                RoleRight::RIGHT_VIEW,
                [],
                [
                    'created_by'   => $resource->created_by
                ]
            );

            $buttons[$resource->uri]['view'] = $rightCheck;
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addToGroup'] = $rightCheck;
        $groups = $setGroups = [];

        if ($buttons['addToGroup']) {
            $groups = $this->prepareGroups();
        }

        if (!empty($datasetData->groups)) {
            foreach ($datasetData->groups as $record) {
                $setGroups[] = (int) $record->id;
            }
        }

        // handle groups change
        if ($request->has('save')) {
            $groupId = $request->offsetGet('group_id');
            $post = [
                'api_key'       => Auth::user()->api_key,
                'data_set_uri'  => $uri,
                'group_id'      => $groupId,
            ];

            // if all groups are deselected
            if (count($setGroups) && is_null($groupId)) {
                $post['group_id'] = $setGroups;
                $removeGroup = Request::create('/api/removeDatasetFromGroup', 'POST', $post);
                $api = new ApiDataSet($removeGroup);
                $remove = $api->removeDatasetFromGroup($removeGroup)->getData();

                if (!$remove->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));

                    return redirect()->back()->withInput()->withErrors($remove->errors);
                }

                $datasetData->groups = $setGroups = [];
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

                $post = [ 'criteria' => [
                        'group_ids' => $groupId,
                    ],
                ];

                $getGroups = Request::create('/api/listGroups', 'POST', $post);
                $apiGroups = new ApiOrganisation($getGroups);
                $groupResponse = $apiGroups->listGroups($getGroups)->getData();

                if ($groupResponse->success) {
                    $datasetData->groups = $groupResponse->groups;
                }

                $setGroups = $groupId;
            }
        }

        // handle dataset delete
        if ($request->has('delete')) {
            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            $root = Role::isAdmin() ? 'admin' : 'user';

            return redirect('/'. $root .'/organisations/datasets/'. $organisation->uri);
        }

        $paginationData = $this->getPaginationData(
            $resources,
            $resCount,
            array_except(app('request')->input(), ['rpage']),
            $resPerPage,
            'rpage'
        );

        return view(
            'user/orgDatasetView',
            [
                'class'         => 'user',
                'groups'        => $groups,
                'setGroups'     => $setGroups,
                'dataset'       => $datasetData,
                'resources'     => $paginationData['items'],
                'activeMenu'    => 'organisation',
                'fromOrg'       => $organisation,
                'buttons'       => $buttons,
                'pagination'    => $paginationData['paginate'],
                'uri'           => $uri,
                'sorting'       => \Auth::user()->is_admin ? 'adminOrgDatasetView' : 'userOrgDatasetView',
            ]
        );
    }

    /**
     * Attempts to delete a dataset based on uri
     *
     * @param Request $request
     * @return true on success and false on failure
     *
     */
    public function removeDataset($groupId, $uri)
    {
        $dataset = DataSet::where('uri', $uri)->first();

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'       => $groupId
            ],
            [
                'created_by'    => $dataset->created_by,
                'group_ids'     => [$groupId]
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $params['api_key'] = \Auth::user()->api_key;
        $params['group_id'] = [$groupId];
        $params['data_set_uri'] = $uri;

        $request = Request::create('/api/removeDataSetFromGroup', 'POST', $params);
        $api = new ApiDataSet($request);
        $datasets = $api->removeDataSetFromGroup($request)->getData();

        return $datasets->success;
    }

    /**
     * Prepares data and makes an API call to create a dataset
     *
     * @param Request $request
     * @param DataSet $dataSetModel
     *
     * @return view with input fields for creation or with created dataset
     *
     */
    public function datasetCreate(Request $request)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        if ($request->has('back')) {
            return redirect(url('user/datasets'));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addResource'] = $rightCheck;

        $visibilityOptions = DataSet::getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $data = $request->all();

        if ($data) {
            // prepare post data for API request
            $data = $this->prepareTags($data);

            if (!empty($data['group_id'])) {
                $groupId = $data['group_id'];
            }

            unset($data['group_id'], $data['add_resource']);

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
                    $api->addDatasetToGroup($addGroup)->getData();
                }

                $request->session()->flash('alert-success', __('custom.changes_success_save'));

                if ($request->has('add_resource')) {
                    return redirect(url('/user/dataset/resource/create/'. $save->uri));
                }

                return redirect()->route('userDatasetView', ['uri' => $save->uri]);
            }

            $request->session()->flash('alert-danger', $save->error->message);

            return redirect()->back()->withInput()->withErrors($save->errors);
        }

        return view('user/datasetCreate', [
            'class'         => 'user',
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'buttons'       => $buttons,
            'fields'        => $this->getDatasetTransFields(),
        ]);
    }

    public function orgDatasetCreate(Request $request, $uri)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addResource'] = $rightCheck;

        //prepare data for form
        $visibilityOptions = DataSet::getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $data = $request->all();
        $fromOrg = Organisation::where('uri', $uri)->first();

        // handle form submit
        if ($data) {
            if (!empty($data['org_uri']) && is_null($fromOrg)) {
                $fromOrg = Organisation::where('uri', $data['org_uri'])->first();
            }

            // prepare post data for API request
            $data = $this->prepareTags($data);

            if (!empty($data['group_id'])) {
                $groupId = $data['group_id'];
            }

            unset($data['group_id'], $data['add_resource']);

            // make request to API
            $params['api_key'] = \Auth::user()->api_key;
            $params['data'] = $data;

            if (!empty($data['org_id'])) {
                $params['org_id'] = $data['org_id'];
            }

            $savePost = Request::create('/api/addDataset', 'POST', $params);
            $api = new ApiDataSet($savePost);
            $save = $api->addDataset($savePost)->getData();

            if ($save->success) {
                // connect data set to group
                if (isset($groupId)) {
                    $groupParams['group_id'] = $groupId;
                    $groupParams['data_set_uri'] = $save->uri;
                    $addGroup = Request::create('/api/addDatasetToGroup', 'POST', $groupParams);
                    $api->addDatasetToGroup($addGroup)->getData();
                }

                $request->session()->flash('alert-success', __('custom.changes_success_save'));
                $root = Role::isAdmin() ? 'admin' : 'user';

                if ($request->has('add_resource')) {
                    return redirect()->route(
                        $root .'OrgResourceCreate',
                        ['uri' => $save->uri, 'orguri' => !is_null($fromOrg) ? $fromOrg->uri : $fromOrg]);
                }

                return redirect('/'. $root .'/organisations/dataset/view/'. $save->uri);
            } else {
                $request->session()->flash('alert-danger', $save->error->message);

                return redirect()->back()->withInput()->withErrors($save->errors);
            }
        }

        if (!is_null($fromOrg)) {
            $fromOrg->logo = $this->getImageData($fromOrg->logo_data, $fromOrg->logo_mime_type);
        }

        return view('user/orgDatasetCreate', [
            'class'         => 'user',
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'buttons'       => $buttons,
            'fields'        => $this->getDatasetTransFields(),
            'fromOrg'       => $fromOrg,
        ]);
    }

    public function groupDatasetCreate(Request $request, $uri)
    {
        $group = Organisation::where('uri', $uri)->first();

        if (is_null($group)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_group_found')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addResource'] = $rightCheck;

        //prepare data for form
        $visibilityOptions = DataSet::getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $data = $request->all();
        $group->logo = $this->getImageData($group->logo_data, $group->logo_mime_type, 'group');

        // handle form submit
        if ($data) {
            // prepare post data for API request
            $data = $this->prepareTags($data);

            if (!empty($data['group_id'])) {
                $groupId = $data['group_id'];
            }

            unset($data['group_id'], $data['add_resource']);

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
                    $api->addDatasetToGroup($addGroup)->getData();
                }

                $request->session()->flash('alert-success', __('custom.changes_success_save'));
                $root = Role::isAdmin() ? 'admin' : 'user';

                if ($request->has('add_resource')) {
                    return redirect()->route($root .'GroupResourceCreate', ['uri' => $save->uri, 'grpUri' => $group->uri]);
                }

                return redirect()->route($root .'GroupDatasetView', ['uri' => $save->uri, 'grpUri' => $group->uri]);
            }

            $request->session()->flash('alert-danger', $save->error->message);

            return redirect()->back()->withInput()->withErrors($save->errors);
        }

        return view('user/groupDatasetCreate', [
            'class'         => 'user',
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'buttons'       => $buttons,
            'fields'        => $this->getDatasetTransFields(),
            'group'         => $group,
        ]);
    }

    /**
     * Returns a view for editing a dataset
     *
     * @param Request $request
     * @param Dataset $uri
     *
     * @return view for editing a dataset
     */
    public function datasetEdit(Request $request, $uri)
    {
        $model = DataSet::where('uri', $uri)->with('dataSetGroup')->first();

        if (empty($model)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_dataset_found')));
        }

        $model->loadTranslations();

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [],
            [
                'created_by' => $model->created_by
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $buttons[$model->uri]['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addResource'] = $rightCheck;

        $visibilityOptions = Dataset::getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $setGroups = [];

        if (!empty($model->dataSetGroup)) {
            foreach ($model->dataSetGroup as $record) {
                $setGroups[] = (int) $record->group_id;
            }
        }

        $hasResources = Resource::where('data_set_id', $model->id)->count();
        $withModel = CustomSetting::where('data_set_id', $model->id)->get()->loadTranslations();
        $tagModel = Tags::whereHas('dataSetTags', function($q) use ($model) {
                $q->where('data_set_id', $model->id);
            })
            ->get();

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

            // if all groups are deselected
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
            $api = new ApiDataSet($editRq);
            $success = $api->editDataset($editRq)->getData();

            if ($success->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('/user/dataset/edit/'. $newURI));
            } else {
                session()->flash('alert-danger', __('custom.edit_error'));

                return redirect()->back()->withInput()->withErrors($success->errors);
            }
        }

        return view('user/datasetEdit', [
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
            'buttons'       => $buttons,
            'setGroups'     => $setGroups,
            'fields'        => $this->getDatasetTransFields(),
        ]);
    }

    /**
     * Loads a view for editing settings if user is logged
     *
     * @param Request $request
     *
     * @return view to homepage if user is not logged
     * or a message if edit was successful or not
     */
    public function settings(Request $request)
    {
        $class = 'user';
        $user = User::find(Auth::id());
        $digestFreq = UserSetting::getDigestFreq();
        $error = [];
        $message = false;

        $localeData = [
            'criteria'  => [
                'active'    => true,
            ],
        ];

        $localePost = Request::create('/api/listLocale', 'POST', $localeData);
        $locale = new ApiLocale($localePost);
        $localeList = $locale->listLocale($localePost)->getData()->locale_list;

        if ($user) {
            $result = User::getUserRoles($user->id);
            session()->put('roles', $result);

            if ($request->has('save')) {
                $validator = \Validator::make($request->all(), [
                    'firstname' => 'required|string|max:191',
                    'lastname'  => 'required|string|max:191',
                    'username'  => 'required|string|max:191',
                    'email'     => 'required|email',
                ]);

                if ($validator->fails()) {
                    $request->session()->flash('alert-danger', __('custom.changes_success_fail'));

                    return redirect()->back()->withErrors($validator->errors()->messages());
                }

                $saveData = [
                    'api_key'   => $user['api_key'],
                    'id'        => $user['id'],
                    'data'      => [
                        'firstname'     => $request->offsetGet('firstname'),
                        'lastname'      => $request->offsetGet('lastname'),
                        'username'      => $request->offsetGet('username'),
                        'email'         => $request->offsetGet('email'),
                        'add_info'      => $request->offsetGet('add_info'),
                        'user_settings' => [
                            'newsletter_digest' => $request->offsetGet('newsletter'),
                            'locale'            => $request->offsetGet('locale'),
                        ],
                    ],
                ];

                if ($request->offsetGet('email') && $request->offsetGet('email') !== $user['email']) {
                    $request->session()->flash('alert-warning', __('custom.email_change_upon_confirm'));
                }
            }


            if ($request->has('generate_key')) {
                $data = [
                    'api_key'   => $user['api_key'],
                    'id'        => $user['id'],
                ];

                $rightCheck = RoleRight::checkUserRight(
                    Module::USERS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $user->created_by,
                        'object_id'  => $user->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $newKey = Request::create('api/generateAPIKey', 'POST', $data);
                $api = new ApiUser($newKey);
                $result = $api->generateAPIKey($newKey)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.api_key_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.api_key_failure'));
                }
            }

            if ($request->has('delete')) {
                $data = [
                    'api_key'   => $user['api_key'],
                    'id'        => $user['id'],
                ];

                $rightCheck = RoleRight::checkUserRight(
                    Module::USERS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $user->created_by,
                        'object_id'  => $user->id,
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $delUser = Request::create('api/deleteUser', 'POST', $data);
                $api = new ApiUser($delUser);
                $result = $api->deleteUser($delUser)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.user_success_delete'));

                    return redirect('/');
                } else {
                    $request->session()->flash('alert-danger', __('custom.user_failure_delete'));
                }
            }

            if (!empty($saveData)) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::USERS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $user->created_by,
                        'object_id'  => $user->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $editPost = Request::create('api/editUser', 'POST', $saveData);
                $api = new ApiUser($editPost);
                $result = $api->editUser($editPost)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.changes_success_fail'));

                    $error = $result->errors;
                }
            }

            return view('user/settings', compact('class', 'user', 'digestFreq', 'localeList', 'error', 'message'));
        }

        return redirect('/');
    }

    public function changePassword(Request $request)
    {
        $id = $request->offsetGet('id');
        $user = User::find($id);
        $oldPass = $request->offsetGet('old_password');

        if (Hash::check($oldPass, $user->password)) {
            $passData = [
                'api_key'   => Auth::user()->api_key,
                'id'        => $id,
                'data'      => [
                    'password'          => $request->offsetGet('password'),
                    'password_confirm'  => $request->offsetGet('password_confirm'),
                ],
            ];

            $editPost = Request::create('api/editUser', 'POST', $passData);
            $api = new ApiUser($editPost);
            $result = $api->editUser($editPost)->getData();
        } else {
            $result = ['success' => false];
        }

        return json_encode($result);
    }

    /**
     * Loads a view for editing settings if user is logged
     *
     * @param Request $request
     *
     * @return view to homepage if user is not logged
     * or a message if edit was successful or not
     */
    public function registration(Request $request)
    {
        $class = 'user';
        $invMail = $request->offsetGet('mail');

        $digestFreq = UserSetting::getDigestFreq();

        if ($request->isMethod('post')) {
            $params = $request->all();
            $rq = Request::create('/register', 'POST', ['invite' => !empty($invMail), 'data' => $params]);
            $api = new ApiUser($rq);
            $result = $api->register($rq)->getData();

            if ($result->success) {
                if ($request->has('add_org')) {
                    $user = User::where('api_key', $result->api_key)->first();
                    $key = $user->username;

                    return redirect()->route('orgRegistration', compact('key', 'message'));
                }

                $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));

                return redirect('login');
            } else {
                return redirect()->back()->withInput()->withErrors($result->errors);
            }
        }

        return view('user/registration', compact('class', 'digestFreq', 'invMail'));
    }

    /**
     * Loads a view for creating or creates an organisation
     *
     * @param Request $request
     *
     * @return view to login page organisation was created
     * or a view for input
     */
    public function orgRegistration(Request $request)
    {
        $class = 'user';
        $params = [];
        $username = $request->offsetGet('key');
        $orgTypes = Organisation::getPublicTypes();

        if (!empty($username)) {
            if ($request->isMethod('post')) {
                $user = User::where('username', $username)->first();
                $params['org_data'] = $request->all();
                $params['username'] = $user->username;
                $apiKey = $user->api_key;
                if ($user->approved) {
                    $params['org_data']['approved'] = true;
                }

                if (!empty($params['org_data']['logo'])) {
                    $params['org_data']['logo_filename'] = $params['org_data']['logo']->getClientOriginalName();
                    $params['org_data']['logo'] = $params['org_data']['logo']->getPathName();
                }

                $req = Request::create('/register', 'POST', ['api_key' => $apiKey, 'data' => $params]);
                $api = new ApiUser($req);
                $result = $api->register($req)->getData();

                if ($result->success) {
                    session()->flash('alert-success', __('custom.add_org_success'));

                    return redirect('login');
                } else {
                    session()->flash(
                        'alert-danger',
                        isset($result->error) ? $result->error->message : __('custom.add_org_error')
                    );

                    session()->flash ('_old_input', Input::all());
                }
            }
        }

        return isset($result)
            ? view(
                'user/orgRegistration',
                [
                    'class'     => 'user',
                    'fields'    => $this->getTransFields(),
                ]
            )->withErrors($result->errors)
            : view(
                'user/orgRegistration',
                [
                    'class'     => 'user',
                    'fields'    => $this->getTransFields(),
                ]
            );
    }

    public function createLicense()
    {
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
        if (!RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_EDIT)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $class = 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();
        $dataset = DataSet::where('uri', $datasetUri)->first();

        if (empty($dataset)) {
            session()->flash('alert-danger', __('custom.no_dataset_found'));

            return redirect('/user/datasets');
        }

        if ($request->has('ready_metadata')) {
            $data = $request->except('file');
            $file = $request->file('file');
            $data['description'] = $data['descript'];
            $maxResourceRows = 2000;
            $bPaging = true;
            $extension = isset($file) ? $file->getClientOriginalExtension() : '';

            $response = ResourceController::addMetadata($datasetUri, $data, $file);

            if ($response['success']) {
                if (in_array($data['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                    return redirect('/user/resource/view/'. $response['uri']);
                }

                if (
                    is_array($data)
                    && isset($response['data']['csvData'])
                    && count($response['data']['csvData']) > $maxResourceRows
                ) {
                    $bPaging = false;
                    $response['data']['csvData'] = collect($response['data']['csvData'])->paginate(100, 1);
                }

                return view('user/resourceImport', array_merge([
                    'class'         => $class,
                    'types'         => $types,
                    'resourceUri'   => $response['uri'],
                    'action'        => 'create',
                    'bPaging'       => $bPaging,
                    'extension'     => $extension
                ], $response['data']));
            } else {
                // delete resource record on fail
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

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [],
            [
                'created_by'    => $resourceData->created_by,
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

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
                        : Resource::REPORTED_TRUE,
                    'upl_freq_type'         => $request->offsetGet('upl_freq_type'),
                    'upl_freq'              => $request->offsetGet('upl_freq'),
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

        return view('user/resourceEdit', [
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
     * Update resource elasticsearch data
     *
     * @param Request $request - resource metadata, file with resource data
     * @param int $resourceUri - uri of resource to be updated
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

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [],
            [
                'created_by'    => $resourceData->created_by,
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $class = 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();
        $resource = Resource::where('uri', $resourceUri)->first()->loadTranslations();

        if ($parentUri) {
            $parent = Organisation::where('uri', $parentUri)->first();
            $parent->logo = $this->getImageData($parent->logo_data, $parent->logo_mime_type, $parent->type == Organisation::TYPE_GROUP ? 'group' : 'org');
        }

        if ($resource) {
            if ($request->has('ready_metadata')) {

                $data = [
                    'type'             => $resource->resource_type,
                    'resource_url'     => $request->offsetGet('resource_url'),
                    'http_rq_type'     => $request->offsetGet('http_rq_type'),
                    'http_headers'     => $request->offsetGet('http_headers') ?: '',
                    'post_data'        => $request->offsetGet('post_data') ?: '',
                    'upl_freq_type'    => $request->offsetGet('upl_freq_type'),
                    'upl_freq'         => $request->offsetGet('upl_freq'),
                    'extension_format' => $extension
                ];

                $file = $request->file('file');

                $response = ResourceController::addMetadata($resourceUri, $data, $file, true);
                $maxResourceRows = 2000;
                $bPaging = true;

                if ($response['success']) {
                    if (in_array($data['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                        return redirect('/user/resource/view/'. $response['uri']);
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

                    return view('user/resourceImport', array_merge([
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

        return view('user/resourceUpdate', [
            'class'     => $class,
            'resource'  => $resource,
            'uri'       => $resourceUri,
            'types'     => $types,
            'reqTypes'  => $reqTypes,
            'fields'    => $this->getResourceTransFields(),
            'parent'    => isset($parent) ? $parent : false,
            'dataseUri' => $resourceData->dataset_uri,
            'extension' => $extension
        ]);
    }

    public function groupResourceCreate(Request $request, $grpUri, $datasetUri)
    {
        $rightCheck = RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_EDIT);

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $class = 'user';
        $root = Role::isAdmin() ? 'admin' : 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();

        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $grpUri]);
        $apiOrganisation = new ApiOrganisation($groupReq);
        $groupData = $apiOrganisation->getGroupDetails($groupReq)->getData();
        $group = !empty($groupData->data) ? $groupData->data : null;

        if (empty($group)) {
            session()->flash('alert-danger', __('custom.no_group_found'));

            return redirect('/'. $root .'/groups');
        }

        $dataset = DataSet::where('uri', $datasetUri)->first();

        if (!empty($dataset)) {
            if ($request->has('ready_metadata')) {
                $data = $request->except('file');
                $file = $request->file('file');
                $data['description'] = $data['descript'];
                $extension = isset($file) ? $file->getClientOriginalExtension() : '';
                $response = ResourceController::addMetadata($datasetUri, $data, $file);
                $maxResourceRows = 2000;
                $bPaging = true;

                if ($response['success']) {
                    if (in_array($data['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                        return redirect('/'. $root .'/groups/'. $group->uri .'/resource/'. $response['uri']);
                    }

                    if (
                        is_array($data)
                        && isset($response['data']['csvData'])
                        && count($response['data']['csvData']) > $maxResourceRows
                    ) {
                        $bPaging = false;
                        $response['data']['csvData'] = collect($response['data']['csvData'])->paginate(100, 1);
                    }

                    return view('user/resourceImport', array_merge([
                        'class'         => $class,
                        'types'         => $types,
                        'resourceUri'   => $response['uri'],
                        'action'        => 'create',
                        'group'         => $group,
                        'bPaging'       => $bPaging,
                        'extension'     => $extension
                    ], $response['data']));
                } else {
                    // delete resource record on fail
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
        } else {
            session()->flash('alert-danger', __('custom.no_dataset_found'));

            return redirect('/'. $root .'/groups/datasets/'. $grpUri);
        }

        return view('user/resourceCreate', [
            'class'         => $class,
            'uri'           => $datasetUri,
            'types'         => $types,
            'reqTypes'      => $reqTypes,
            'fields'        => $this->getResourceTransFields(),
            'dataSetName'   => $dataset->name,
            'group'         => $group,
        ]);
    }

    public function orgResourceCreate(Request $request, $orgUri, $datasetUri)
    {
        $rightCheck = RoleRight::checkUserRight(Module::RESOURCES, RoleRight::RIGHT_EDIT);

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $class = 'user';
        $root = Role::isAdmin() ? 'admin' : 'user';
        $types = Resource::getTypes();
        $reqTypes = Resource::getRequestTypes();

        $fromOrg = Organisation::where('uri', $orgUri)->first();

        if (empty($fromOrg)) {
            session()->flash('alert-danger', __('custom.no_org_found'));

            return redirect('/'. $root .'/organisations');
        }

        $fromOrg->logo = $this->getImageData($fromOrg->logo_data, $fromOrg->logo_mime_type);

        $dataset = DataSet::where('uri', $datasetUri)->first();

        if (!empty($dataset)) {
            if ($request->has('ready_metadata')) {
                $data = $request->except('file');
                $file = $request->file('file');
                $data['description'] = $data['descript'];
                $extension = isset($file) ? $file->getClientOriginalExtension() : '';

                $response = ResourceController::addMetadata($datasetUri, $data, $file);
                $maxResourceRows = 2000;
                $bPaging = true;

                if ($response['success']) {
                    if (in_array($data['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                        return redirect('/'. $root .'/organisations/'. $fromOrg->uri .'/resource/'. $response['uri']);
                    }

                    if (
                        is_array($data)
                        && isset($response['data']['csvData'])
                        && count($response['data']['csvData']) > $maxResourceRows
                    ) {
                        $bPaging = false;
                        $response['data']['csvData'] = collect($response['data']['csvData'])->paginate(100, 1);
                    }

                    return view('user/resourceImport', array_merge([
                        'class'         => $class,
                        'types'         => $types,
                        'resourceUri'   => $response['uri'],
                        'action'        => 'create',
                        'fromOrg'       => $fromOrg,
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
        } else {
            session()->flash('alert-danger', __('custom.no_dataset_found'));

            return redirect('/'. $root .'/organisations/datasets/'. $orgUri);
        }

        return view('user/resourceCreate', [
            'class'         => $class,
            'uri'           => $datasetUri,
            'types'         => $types,
            'reqTypes'      => $reqTypes,
            'fields'        => $this->getResourceTransFields(),
            'dataSetName'   => $dataset->name,
            'fromOrg'       => $fromOrg,
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
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $resourcesReq = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $uri]);
        $apiResources = new ApiResource($resourcesReq);
        $resource = $apiResources->getResourceMetadata($resourcesReq)->getData();
        $resourceVersionFormat = null;

        if ($resource->resource->file_format) {
            $versionQuery = ElasticDataSet::where('resource_id', $resource->resource->id);

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

        $resourceVersionFormat = is_null($resourceVersionFormat) ? Resource::getFormatsCode($resource->resource->file_format) : $resourceVersionFormat;

        if (empty($resource->resource)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_resource_found')));
        }

        $createdBy = $resource->resource->created_by;
        $resource = $this->getModelUsernames($resource->resource);
        $resource->format_code = Resource::getFormatsCode($resource->file_format);
        $formats = Resource::getFormats(true);
        $versionsPerPage = 10;

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

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_VIEW,
            [],
            ['created_by' => $createdBy]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [],
            [
                'created_by' => $createdBy
            ]
        );

        $buttons['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_ALL,
            [],
            ['created_by' => $createdBy]
        );

        $buttons['delete'] = $rightCheck;

        // get elastic search data for the resource
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

        // handle delete request
        if ($request->has('delete')) {
            $rightCheck = RoleRight::checkUserRight(
                Module::RESOURCES,
                RoleRight::RIGHT_ALL,
                [],
                ['created_by' => $createdBy]
            );

            if (!$rightCheck) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
            }

            $reqDelete = Request::create('/api/deleteResource', 'POST', ['resource_uri' => $uri]);
            $apiDelete = new ApiResource($reqDelete);
            $resDelete = $apiDelete->deleteResource($reqDelete)->getData();

            if ($resDelete->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect()->route('userDatasetView', ['uri' => $resource->dataset_uri]);
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

        $vCount = count($resource->versions_list);
        $verData = collect($resource->versions_list)->paginate($versionsPerPage);

        $paginationData = $this->getPaginationData(
            $verData,
            $vCount,
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

        return view('user/resourceView', [
            'class'         => 'user',
            'resource'      => $resource,
            'search'        => $search,
            'dataPerPage'   => $dataPerPage,
            'data'          => $resourcePaginationData['data'],
            'versionView'   => $version,
            'versions'      => $verData,
            'pagination'    => $paginationData['paginate'],
            'buttons'       => $buttons,
            'dataset'       => $datasetData,
            'supportName'   => !is_null($dataset) ? $dataset->support_name : null,
            'formats'       => $formats,
            'resPagination' => $resourcePaginationData['resPagination'],
            'versionFormat' => $resourceVersionFormat
        ]);
    }

    /**
     * Loads a view for browsing organisational resources
     *
     * @param Request $request
     *
     * @return view for browsing org resources
     */
    public function orgResourceView(Request $request, $orgUri, $uri, $version = null)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $resourcesReq = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $uri]);
        $apiResources = new ApiResource($resourcesReq);
        $resource = $apiResources->getResourceMetadata($resourcesReq)->getData();
        $resourceVersionFormat = null;

        if (empty($resource->resource)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_resource_found')));
        }

        if ($resource->resource->file_format) {
            $versionQuery = ElasticDataSet::where('resource_id', $resource->resource->id);

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

        $resourceVersionFormat = is_null($resourceVersionFormat) ? Resource::getFormatsCode($resource->resource->file_format) : $resourceVersionFormat;

        $versionsPerPage = 10;
        $resource = $this->getModelUsernames($resource->resource);
        $resource->format_code = Resource::getFormatsCode($resource->file_format);
        $formats = Resource::getFormats(true);

        if (empty($version)) {
            $version = $resource->version;
        }

        $fromOrg = Organisation::where('uri', $orgUri)->first();

        if (empty($fromOrg)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_org_found')));
        }

        $fromOrg->logo = $this->getImageData($fromOrg->logo_data, $fromOrg->logo_mime_type);

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

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'       => $dataset->org_id
            ],
            [
                'created_by'     => $dataset->created_by,
                'org_id'         => $dataset->org_id
            ]
        );

        $buttons['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_ALL,
            [
                'org_id'       => $dataset->org_id
            ],
            [
                'created_by'     => $dataset->created_by,
                'org_id'         => $dataset->org_id
            ]
        );

        $buttons['delete'] = $rightCheck;

        // get elastic search data for the resource
        $rq = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $uri, 'version' => $version]);
        $api = new ApiResource($rq);
        $response = $api->getResourceData($rq)->getData();
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

        // handle delete submit
        if ($request->has('delete')) {
            $rightCheck = RoleRight::checkUserRight(
                Module::RESOURCES,
                RoleRight::RIGHT_ALL,
                [
                    'org_id'       => $dataset->org_id
                ],
                [
                    'created_by'     => $dataset->created_by,
                    'org_id'         => $dataset->org_id
                ]
            );

            if (!$rightCheck) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
            }

            $rq = Request::create('/api/deleteResource', 'POST', ['resource_uri' => $uri]);
            $api = new ApiResource($rq);
            $result = $api->deleteResource($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
                $root = Role::isAdmin() ? 'admin' : 'user';

                return redirect()->route($root .'OrgDatasetView', ['uri' => $resource->dataset_uri]);
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

        $vCount = count($resource->versions_list);
        $verData = collect($resource->versions_list)->paginate($versionsPerPage);

        $paginationData = $this->getPaginationData(
            $verData,
            $vCount,
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

        return view('user/orgResourceView', [
            'class'         => 'user',
            'resource'      => $resource,
            'search'        => $search,
            'dataPerPage'   => $dataPerPage,
            'data'          => $resourcePaginationData['data'],
            'versions'      => $verData,
            'pagination'    => $paginationData['paginate'],
            'versionView'   => $version,
            'buttons'       => $buttons,
            'activeMenu'    => 'organisation',
            'fromOrg'       => $fromOrg,
            'dataset'       => $datasetData,
            'supportName'   => !is_null($dataset) ? $dataset->support_name : null,
            'formats'       => $formats,
            'resPagination' => $resourcePaginationData['resPagination'],
            'versionFormat' => $resourceVersionFormat
        ]);
    }

    /**
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function organisations(Request $request)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_VIEW
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        if (!$rightCheck) {
            return view('user/organisations', [
                'class'         => 'user',
                'organisations' => [],
                'pagination'    => null,
                'buttons'       => $buttons
            ]);
        }

        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/getUserOrganisations', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->getUserOrganisations($request)->getData();

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        foreach ($result->organisations as $organisation) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_VIEW,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'org_id'     => $organisation->id
                ]
            );

            $buttons[$organisation->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'org_id'     => $organisation->id,
                    'created_by' => $organisation->created_by
                ]
            );

            $buttons[$organisation->uri]['edit'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_ALL,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'org_id'     => $organisation->id,
                    'created_by' => $organisation->created_by
                ]
            );

            $buttons[$organisation->uri]['delete'] = $rightCheck;
        }

        $paginationData = $this->getPaginationData($result->organisations, $result->total_records, [], $perPage);

        return view(
            'user/organisations',
            [
                'class'         => 'user',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'buttons'       => $buttons
            ]
        );
    }

    /**
     * Loads a view for deleting organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations and request success message
     */
    public function deleteOrg(Request $request, $id)
    {
        $request = Request::create('/api/getOrganisationDetails', 'POST', ['org_id' => $id]);
        $api = new ApiOrganisation($request);
        $result = $api->getOrganisationDetails($request)->getData();

        if (!$result->success) {
            return redirect()->back();
        }

        $params = [
            'api_key' => \Auth::user()->api_key,
            'org_id'  => $id,
        ];

        $request = Request::create('/api/deleteOrganisation', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->deleteOrganisation($request)->getData();

        if ($result->success) {
            session()->flash('alert-success', __('custom.delete_success'));

            return redirect('/user/organisations');
        }

        session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.delete_error'));

        return redirect('/user/organisations');
    }

    /**
     * Loads a view for searching organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations or
     * a list of filtered organisations if search string is provided
     */
    public function searchOrg(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect('/user/organisations');
        }

        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'         => [
                'keywords' => $search,
                'user_id'  => \Auth::user()->id
            ],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();
        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_VIEW
        );

        $buttons['view'] = $rightCheck;

        foreach ($organisations as $organisation) {
            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_VIEW,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'org_id'     => $organisation->id
                ]
            );

            $buttons[$organisation->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'created_by' => $organisation->created_by,
                    'org_id'     => $organisation->id,
                ]
            );

            $buttons[$organisation->uri]['edit'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_ALL,
                [
                    'org_id'       => $organisation->id
                ],
                [
                    'created_by' => $organisation->created_by,
                    'org_id'     => $organisation->id
                ]
            );

            $buttons[$organisation->uri]['delete'] = $rightCheck;
        }

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($organisations, $count, $getParams, $perPage);

        return view(
            'user/organisations',
            [
                'class'         => 'user',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search,
                'buttons'       => $buttons
            ]
        );
    }

    /**
     * Loads a view for searching datasets
     *
     * @param Request $request
     *
     * @return view with a list of datasets or
     * a list of filtered datasets if search string is provided
     */
    public function searchDataset(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect('/user/organisations/datasets');
        }

        $perPage = 6;
        $params = [
            'criteria' => [
                'keywords' => $search,
                'user_id'  => \Auth::user()->id
            ],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/listDatasets', 'POST', $params);
        $api = new ApiDataSet($request);
        $result = $api->listDatasets($request)->getData();
        $datasets = !empty($result->datasets) ? $result->datasets : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_VIEW
        );

        $buttons['view'] = $rightCheck;

        foreach ($datasets as $dataset) {
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_VIEW,
                [
                    'org_id'       => $dataset->org_id
                ],
                [
                    'created_by' => $dataset->created_by,
                    'org_id'     => $dataset->org_id
                ]
            );

            $buttons[$dataset->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id'       => $dataset->org_id
                ],
                [
                    'created_by' => $dataset->created_by,
                    'org_id'     => $dataset->org_id,
                ]
            );

            $buttons[$dataset->uri]['edit'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_ALL,
                [
                    'org_id'       => $dataset->org_id
                ],
                [
                    'created_by' => $dataset->created_by,
                    'org_id'     => $dataset->org_id
                ]
            );

            $buttons[$dataset->uri]['delete'] = $rightCheck;
        }

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($datasets, $count, $getParams, $perPage);

        return view(
            'user/orgDatasets',
            [
                'class'      => 'user',
                'datasets'   => $paginationData['items'],
                'pagination' => $paginationData['paginate'],
                'search'     => $search,
                'activeMenu' => 'organisation',
                'buttons'    => $buttons
            ]
        );
    }

    /**
     * Loads a view for registering an organisation
     *
     * @param Request $request
     *
     * @return view to register an organisation or
     * a view to view the registered organisation
     */
    public function registerOrg(Request $request)
    {
        $post = ['data' => $request->all()];

        if (!empty($post['data']['logo'])) {
            $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
            $post['data']['logo'] = $post['data']['logo']->getPathName();
        }

        $post['data']['description'] = $post['data']['descript'];
        $request = Request::create('/api/addOrganisation', 'POST', $post);
        $api = new ApiOrganisation($request);
        $result = $api->addOrganisation($request)->getData();

        if ($result->success) {
            RoleRight::refreshSession();
            session()->flash('alert-success', __('custom.add_org_success'));
        } else {
            session()->flash(
                'alert-danger',
                isset($result->error) ? $result->error->message : __('custom.add_org_error')
            );
        }

        return $result->success
            ? redirect('user/organisations/view/'. Organisation::find($result->org_id)->uri)
            : redirect('user/organisations/register')->withInput(Input::all())->withErrors($result->errors);
    }

    /**
     * Loads a view for viewing an organisation
     *
     * @param Request $request
     *
     * @return view to view the a registered organisation
     */
    public function viewOrg(Request $request, $uri)
    {
        $request = Request::create('/api/getOrganisationDetails', 'POST', ['org_uri' => $uri]);
        $api = new ApiOrganisation($request);
        $result = $api->getOrganisationDetails($request)->getData();

        if (!$result->success) {
            return redirect()->back();
        }

        $orgData = $result->data;

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'       => $orgData->id
            ],
            [
                'created_by' => $orgData->created_by,
                'org_id'     => $orgData->id
            ]
        );

        $buttons[$orgData->uri]['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_ALL,
            [
                'org_id'       => $orgData->id
            ],
            [
                'created_by' => $orgData->created_by,
                'org_id'     => $orgData->id
            ]
        );

        $buttons[$orgData->uri]['delete'] = $rightCheck;

        if ($result->success) {
            return view('user/orgView', ['class' => 'user', 'organisation' => $result->data, 'buttons' => $buttons]);
        }

        return redirect('/user/organisations');
    }

    /**
     * Checks if the logged user belongs to an organisation
     *
     * @param Request $request
     *
     * @return true or false
     */
    private function checkUserOrg($orgId)
    {
        if (UserToOrgRole::where(['user_id' => \Auth::user()->id, 'org_id' => $orgId])->count()) {
            return true;
        }

        return false;
    }

    public function viewOrgMembers(Request $request, $uri)
    {
        $perPage = 6;
        $filter = $request->offsetGet('filter');
        $userId = $request->offsetGet('user_id');
        $roleId = $request->offsetGet('role_id');
        $keywords = $request->offsetGet('keywords');
        $org = Organisation::where('uri', $uri)->first();

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'        => $org->id
            ],
            [
                'created_by'    => $org->created_by,
                'org_id'        => $org->id
            ]
        );

        $buttons[$org->id]['editMember'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_ALL,
            [
                'org_id'        => $org->id
            ],
            [
                'created_by'    => $org->created_by,
                'org_id'        => $org->id
            ]
        );

        $buttons[$org->id]['deleteMember'] = $rightCheck;

        if ($org) {
            if ($request->has('edit_member')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id'        => $org->id
                    ],
                    [
                        'created_by'    => $org->created_by,
                        'org_id'        => $org->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                if(empty($roleId)) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.empty_role')));
                }

                $rq = Request::create('/api/editMember', 'POST', [
                    'org_id'    => $org->id,
                    'user_id'   => $userId,
                    'role_id'   => $roleId,
                ]);
                $api = new ApiOrganisation($rq);
                $result = $api->editMember($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back();
            }

            if ($request->has('delete')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_ALL,
                    [
                        'org_id'        => $org->id
                    ],
                    [
                        'created_by'    => $org->created_by,
                        'org_id'        => $org->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                if ($this->delMember($userId, $org->id)) {
                    $request->session()->flash('alert-success', __('custom.delete_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.delete_error'));
                }

                return back();
            }

            if ($request->has('invite_existing')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id'        => $org->id
                    ],
                    [
                        'created_by'    => $org->created_by,
                        'org_id'        => $org->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $newUser = $request->offsetGet('user');
                $newRole = $request->offsetGet('role');

                $rq = Request::create('/api/addMember', 'POST', [
                    'org_id'    => $org->id,
                    'user_id'   => $newUser,
                    'role_id'   => $newRole,
                ]);
                $api = new ApiOrganisation($rq);
                $result = $api->addMember($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }

                return back();
            }

            if ($request->has('invite')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::ORGANISATIONS,
                    RoleRight::RIGHT_EDIT,
                    [
                        'org_id'        => $org->id
                    ],
                    [
                        'created_by'    => $org->created_by,
                        'org_id'        => $org->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $email = $request->offsetGet('email');
                $role = $request->offsetGet('role');

                $rq = Request::create('/inviteUser', 'POST', [
                    'api_key'   => Auth::user()->api_key,
                    'data'      => [
                        'email'     => $email,
                        'org_id'    => $org->id,
                        'role_id'   => $role,
                        'generate'  => true,
                    ],
                ]);
                $api = new ApiUser($rq);
                $result = $api->inviteUser($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }

                return back();
            }

            $org->logo = $this->getImageData($org->logo_data, $org->logo_mime_type);

            $criteria = ['org_id' => $org->id];

            if ($filter == 'for_approval') {
                $criteria['for_approval'] = true;
            }

            if (is_numeric($filter)) {
                $criteria['role_id'] = $filter;
            }

            if (!empty($keywords)) {
                $criteria['keywords'] = $keywords;
            }

            $criteria['records_per_page'] = $perPage;
            $criteria['page_number'] = $request->offsetGet('page', 1);

            $rq = Request::create('/api/getMembers', 'POST', $criteria);
            $api = new ApiOrganisation($rq);
            $result = $api->getMembers($rq)->getData();
            $paginationData = $this->getPaginationData(
                $result->members,
                $result->total_records,
                $request->except('page'),
                $perPage
            );

            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_org' => 1]]);
            $api = new ApiRole($rq);
            $result = $api->listRoles($rq)->getData();
            $roles = isset($result->roles) ? $result->roles : [];

            return view('user/orgMembers', [
                'class'         => 'user',
                'members'       => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'organisation'  => $org,
                'roles'         => $roles,
                'filter'        => $filter,
                'keywords'      => $keywords,
                'buttons'       => $buttons
            ]);
        }

        return redirect('/user/organisations');
    }

    public function addOrgMembersNew(Request $request, $uri)
    {
        $organisation = Organisation::where('uri', $uri)->first();
        $class = 'user';

            $rightCheck = RoleRight::checkUserRight(
                Module::ORGANISATIONS,
                RoleRight::RIGHT_EDIT,
                [
                    'org_id'        => $organisation->id
                ],
                [
                    'created_by'    => $organisation->created_by,
                    'org_id'        => $organisation->id
                ]
            );

            if (!$rightCheck) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
            }

        if ($organisation) {
            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_org' => 1]]);
            $api = new ApiRole($rq);
            $result = $api->listRoles($rq)->getData();
            $roles = isset($result->roles) ? $result->roles : [];
            $digestFreq = UserSetting::getDigestFreq();

            if ($request->has('save')) {
                $post = [
                    'api_key'   => Auth::user()->api_key,
                    'data'      => [
                        'firstname'         => $request->offsetGet('firstname'),
                        'lastname'          => $request->offsetGet('lastname'),
                        'username'          => $request->offsetGet('username'),
                        'email'             => $request->offsetGet('email'),
                        'password'          => $request->offsetGet('password'),
                        'password_confirm'  => $request->offsetGet('password_confirm'),
                        'role_id'           => $request->offsetGet('role_id'),
                        'org_id'            => $organisation->id,
                        'invite'            => true,
                    ],
                ];

                $rq = Request::create('/api/addUser', 'POST', $post);
                $api = new ApiUser($rq);
                $result = $api->register($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));

                    return redirect('/user/organisations/members/'. $uri);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return redirect()->back()->withInput()->withErrors($result->errors);
                }
            }
        }

        return view('user/addOrgMembersNew', compact('class', 'error', 'digestFreq', 'invMail', 'roles', 'organisation'));
    }

    public function delMember($id, $orgId)
    {
        $rq = Request::create('/api/delMember', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'org_id'    => $orgId,
            'user_id'   => $id,
        ]);
        $api = new ApiOrganisation($rq);
        $result = $api->delMember($rq)->getData();

        return $result->success;
    }

    /**
     * Sends a confirmation email when changing email
     *
     * @param Request $request
     *
     * @return view login on success or error on fail
     */
    public function mailConfirmation(Request $request)
    {
        Auth::logout();
        $class = 'user';
        $hash = $request->offsetGet('hash');
        $mail = $request->offsetGet('mail');
        $id = $request->offsetGet('id');

        if ($hash && $mail) {
            $user = User::find($id);

            if ($user->hash_id == $hash) {
                $user->email = $request->offsetGet('mail');

                try {
                    $user->save();
                    $request->session()->flash('alert-success', __('custom.email_change_success'));

                    return redirect('login');
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }

            if ($request->has('generate')) {
                $mailData = [
                    'user'      => $user->firstname,
                    'username'  => $user->username,
                    'hash'      => $user->hash_id,
                    'mail'      => $mail,
                    'id'        => $id,
                ];

                Mail::send('mail/emailChangeMail', $mailData, function ($m) use ($mailData) {
                    $m->from(config('app.MAIL_FROM'), config('app.APP_NAME'));
                    $m->to($mailData['mail'], $mailData['user']);
                    $m->subject(__('custom.mail_change'));
                });

                $request->session()->flash('alert-warning', __('custom.mail_sent_again'));

                return redirect('login');
            }
        }

        return view('confirmError', compact('class'));
    }

    /**
     * Loads a view for registering an organisation
     *
     * @return view login on success or error on fail
     */
    public function showOrgRegisterForm()
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

        $query->whereHas('userToOrgRole', function($q) {
            $q->where('user_id', \Auth::user()->id);
        });

        $parentOrgs = $query->get();

        return view(
            'user/orgRegister',
            [
                'class'      => 'user',
                'fields'     => $this->getTransFields(),
                'parentOrgs' => $parentOrgs
            ]
        );
    }

    /**
     * Loads a view for editing an organisation
     *
     * @param Request $request
     *
     * @return view for editing org details
     */
    public function editOrg(Request $request, $uri)
    {
        $rq = Request::create('/api/getOrganisationDetails', 'POST', ['org_uri' => $uri]);
        $api = new ApiOrganisation($rq);
        $res = $api->getOrganisationDetails($rq)->getData();

        if (!$res->success) {
            return redirect()->back();
        }

        $orgData = $res->data;

        $rightCheck = RoleRight::checkUserRight(
            Module::ORGANISATIONS,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'       => $orgData->id
            ],
            [
                'created_by' => $orgData->created_by,
                'org_id'     => $orgData->id
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $org = Organisation::where('uri', $uri)
            ->whereIn('type', array_flip(Organisation::getPublicTypes()))
            ->first();

        if (!empty($org) && $this->checkUserOrg($org->id)) {
            $query = Organisation::select('id', 'name')->where('type', '!=', Organisation::TYPE_GROUP);

            $query->whereHas('userToOrgRole', function($q) {
                $q->where('user_id', \Auth::user()->id);
            });

            $parentOrgs = $query->get();

            $orgModel = Organisation::with('CustomSetting')->find($org->id)->loadTranslations();
            $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
            $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);
            $root = 'user';

            $viewData = [
                'class'      => 'user',
                'model'      => $orgModel,
                'withModel'  => $customModel,
                'fields'     => $this->getTransFields(),
                'parentOrgs' => $parentOrgs,
                'root'       => $root
            ];

            if (isset($request->view)) {
                return view('user/orgEdit', $viewData);
            }

            $post = [
                'data'          => $request->all(),
                'org_id'        => $org->id,
                'parentOrgs'    => $parentOrgs,
            ];

            if (!empty($post['data']['logo'])) {
                $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                $post['data']['logo'] = $post['data']['logo']->getPathName();
            }

            if (isset($post['data']['descript'])) {
                $post['data']['description'] = $post['data']['descript'];
            }

            if ($request->has('save')) {
                $request = Request::create('/api/editOrganisation', 'POST', $post);
                $api = new ApiOrganisation($request);
                $result = $api->editOrganisation($request)->getData();
                $errors = !empty($result->errors) ? $result->errors : [];

                $orgModel = Organisation::with('CustomSetting')->find($org->id)->loadTranslations();
                $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
                $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

                if ($result->success) {
                    session()->flash('alert-success', __('custom.edit_success'));

                    if (!empty($post['data']['uri'])) {
                        return redirect(url('/user/organisations/edit/'. $post['data']['uri']));
                    }
                } else {
                    session()->flash('alert-danger', isset($result->error) ? $result->error->message : __('custom.edit_error'));
                }
            }

            return view('user/orgEdit', $viewData)->withErrors(isset($result->errors) ? $result->errors : []);
        }

        return redirect('/user/organisations');
    }

    /**
     * Checks if pregenerated credentials are correct
     *
     * @param Request $request
     * @return redirect to corresponding route
     */
    public function preGenerated(Request $request)
    {
        $data = $request->all();

        $validator = \Validator::make($data, [
            'username'  => 'required',
            'pass'      => 'required',
        ]);

        if (!$validator->fails()) {
            $cred = [
                'username'  => $data['username'],
                'password'  => $data['pass'],
            ];

            if (Auth::attempt($cred)) {
                $user = User::find(Auth::user()->id);

                $user->active = true;

                try {
                    $user->save();
                    $request->session()->flash('alert-success', __('custom.successful_account_activation'));
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }

                return redirect()->route('settings');
            }
        } else {
            $request->session()->flash('alert-danger', __('custom.incorrect_request_params'));

            return redirect('/');
        }
    }

    /**
     * Loads the newsfeed list if user is logged
     *
     * @param Request $request
     *
     * @return view newsfeed or redirect to home if user is not logged
     */
    public function newsFeed(Request $request)
    {
        $user = isset($request->news_user_id)
            ? User::where('id', $request->news_user_id)->first()
            : Auth::user();

        if ($user) {
            $filter = $request->offsetGet('filter');
            $objIdFilter = $request->offsetGet('objId');
            $filters = $this->getNewsFeedFilters();

            $criteria = [];
            $actObjData = [];

            $locale = \LaravelLocalization::getCurrentLocale();

            $params = [
                'api_key' => $user->api_key,
                'id'      => $user->id
            ];

            $rq = Request::create('/api/getUserSettings', 'POST', $params);
            $api = new ApiUser($rq);
            $result = $api->getUserSettings($rq)->getData();

            if (empty($filter)) {
                if (!empty($result->user->follows)) {
                    $firstInterest = array_first($result->user->follows);

                    foreach ($firstInterest as $type => $object) {
                        if (!is_null($object) && $object != 0) {
                            switch ($type) {
                                case 'org_id':
                                    $filter = 'organisations';
                                    break;
                                case 'group_id':
                                    $filter = 'groups';
                                    break;
                                case 'dataset_id':
                                    $filter = 'datasets';
                                    break;
                                case 'category_id':
                                    $filter = 'categories';
                                    break;
                                case 'tag_id':
                                    $filter = 'tags';
                                    break;
                                case 'follow_user_id':
                                    $filter = 'users';
                                    break;
                                default:
                                $filter = 'datasets';
                            }
                        }
                    }
                }
            }

            if (!empty($result->user) && !empty($result->user->follows)) {
                $userFollows = [
                    'org_id'         => [],
                    'group_id'       => [],
                    'category_id'    => [],
                    'tag_id'         => [],
                    'follow_user_id' => [],
                    'dataset_id'     => []
                ];

                foreach ($result->user->follows as $follow) {
                    foreach ($follow as $followProp => $followId) {
                        if (
                            $filter == 'organisations' && $followProp != 'org_id'
                            || $filter == 'groups' && $followProp != 'group_id'
                            || $filter == 'categories' && $followProp != 'category_id'
                            || $filter == 'tags' && $followProp != 'tag_id'
                            || $filter == 'users' && $followProp != 'follow_user_id'
                            || $filter == 'datasets' && $followProp != 'dataset_id'
                        ) {
                            continue;
                        }

                        if ($followId) {
                            $userFollows[$followProp][] = $followId;
                        }
                    }
                }

                if (!empty($userFollows['org_id'])) {
                    $params = [
                        'api_key'  => $user->api_key,
                        'criteria' => [
                            'org_ids' => $userFollows['org_id'],
                            'locale' => $locale
                        ]
                    ];

                    $rq = Request::create('/api/listOrganisations', 'POST', $params);
                    $api = new ApiOrganisation($rq);
                    $res = $api->listOrganisations($rq)->getData();

                    if (isset($res->success) && $res->success && !empty($res->organisations)) {
                        $objType = Module::getModules()[Module::ORGANISATIONS];
                        $actObjData[$objType] = [];

                        foreach ($res->organisations as $org) {
                            if ($filter != 'datasets') {
                                if (isset($filters[$filter])) {
                                    $filters[$filter]['data'][$org->id] = $org->name;
                                }

                                if ($objIdFilter && $objIdFilter != $org->id) {
                                    continue;
                                }

                                $criteria['org_ids'][] = $org->id;
                            }

                            $actObjData[$objType][$org->id] = $this->getActObjectData(
                                $org->id,
                                $org->name,
                                $org->logo,
                                ultrans('custom.organisations'),
                                'org',
                                '/organisation/profile/'. $org->uri
                            );

                            $params = [
                                'criteria' => [
                                    'org_ids' => [$org->id],
                                    'locale'  => $locale
                                ]
                            ];

                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }

                if (!empty($userFollows['group_id'])) {
                    $params = [
                        'criteria' => [
                            'group_ids' => $userFollows['group_id'],
                            'locale' => $locale
                        ]
                    ];

                    $rq = Request::create('/api/listGroups', 'POST', $params);
                    $api = new ApiOrganisation($rq);
                    $res = $api->listGroups($rq)->getData();

                    if (isset($res->success) && $res->success && !empty($res->groups)) {
                        $objType = Module::getModules()[Module::GROUPS];
                        $actObjData[$objType] = [];

                        foreach ($res->groups as $group) {
                            if ($filter != 'datasets') {
                                if (isset($filters[$filter])) {
                                    $filters[$filter]['data'][$group->id] = $group->name;
                                }

                                if ($objIdFilter && $objIdFilter != $group->id) {
                                    continue;
                                }
                                $criteria['group_ids'][] = $group->id;
                            }

                            $actObjData[$objType][$group->id] = $this->getActObjectData(
                                $group->id,
                                $group->name,
                                $group->logo,
                                ultrans('custom.groups'),
                                'group',
                                '/groups/view/'. $group->uri
                            );

                            $params = [
                                'criteria' => [
                                    'group_ids' => [$group->id],
                                    'locale'    => $locale
                                ]
                            ];

                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }

                if (!empty($userFollows['category_id'])) {
                    $params = [
                        'criteria' => [
                            'category_ids'  => $userFollows['category_id'],
                            'locale'        => $locale
                        ]
                    ];

                    $rq = Request::create('/api/listMainCategories', 'POST', $params);
                    $api = new ApiCategory($rq);
                    $res = $api->listMainCategories($rq)->getData();

                    if (isset($res->success) && $res->success && !empty($res->categories)) {
                        $objType = Module::getModules()[Module::MAIN_CATEGORIES];
                        $actObjData[$objType] = [];

                        foreach ($res->categories as $category) {
                            if ($filter != 'datasets') {
                                if (isset($filters[$filter])) {
                                    $filters[$filter]['data'][$category->id] = $category->name;
                                }

                                if ($objIdFilter && $objIdFilter != $category->id) {
                                    continue;
                                }

                                $criteria['category_ids'][] = $category->id;
                            }

                            $actObjData[$objType][$category->id] = $this->getActObjectData(
                                $category->id,
                                $category->name,
                                $category->icon_data,
                                ultrans('custom.main_topic'),
                                'category'
                            );

                            $params = [
                                'criteria' => [
                                    'category_ids' => [$category->id],
                                    'locale'       => $locale
                                ]
                            ];

                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }

                if (!empty($userFollows['tag_id'])) {
                    $params = [
                        'criteria' => [
                            'tag_ids'   => $userFollows['tag_id'],
                        ]
                    ];

                    $rq = Request::create('/api/listTags', 'POST', $params);
                    $api = new ApiTags($rq);
                    $res = $api->listTags($rq)->getData();

                    if (isset($res->success) && $res->success && !empty($res->tags)) {
                        $objType = Module::getModules()[Module::TAGS];
                        $actObjData[$objType] = [];

                        foreach ($res->tags as $tag) {
                            if ($filter != 'datasets') {
                                if (isset($filters[$filter])) {
                                    $filters[$filter]['data'][$tag->id] = $tag->name;
                                }

                                if ($objIdFilter && $objIdFilter != $tag->id) {
                                    continue;
                                }

                                $criteria['tag_ids'][] = $tag->id;
                            }

                            $actObjData[$objType][$tag->id] = $this->getActObjectData(
                                $tag->id,
                                $tag->name,
                                null,
                                ultrans('custom.tags'),
                                'tag'
                            );

                            $params = [
                                'criteria' => [
                                    'tag_ids' => [$tag->id],
                                    'locale'  => $locale
                                ]
                            ];

                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }

                if (!empty($userFollows['follow_user_id'])) {
                    $params = [
                        'criteria' => [
                            'user_ids' => $userFollows['follow_user_id']
                        ]
                    ];

                    $rq = Request::create('/api/listUsers', 'POST', $params);
                    $api = new ApiUser($rq);
                    $res = $api->listUsers($rq)->getData();

                    if (isset($res->success) && $res->success && !empty($res->users)) {
                        $objType = Module::getModules()[Module::USERS];
                        $actObjData[$objType] = [];

                        foreach ($res->users as $followUser) {
                            if ($filter != 'datasets') {
                                if (isset($filters[$filter])) {
                                    $filters[$filter]['data'][$followUser->id] = $followUser->firstname .' '. $followUser->lastname;
                                }

                                if ($objIdFilter && $objIdFilter != $followUser->id) {
                                    continue;
                                }

                                $criteria['user_ids'][] = $followUser->id;
                            }

                            $actObjData[$objType][$followUser->id] = $this->getActObjectData(
                                $followUser->id,
                                $followUser->firstname .' '. $followUser->lastname,
                                null,
                                ultrans('custom.users'),
                                'user',
                                '/user/profile/'. $followUser->id
                            );

                            $params = [
                                'criteria' => [
                                    'created_by' => $followUser->id,
                                    'locale'     => $locale
                                ]
                            ];

                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }

                if (!empty($userFollows['dataset_id'])) {
                    $params = [
                        'criteria' => [
                            'dataset_ids' => $userFollows['dataset_id'],
                            'locale'      => $locale
                        ]
                    ];

                    $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                }
            }

            // user profile actions
            if (!isset($filters[$filter])) {
                $objType = Module::getModules()[Module::USERS];

                $actObjData[$objType][$user->id] = $this->getActObjectData(
                    $user->id,
                    $user->firstname .' '. $user->lastname,
                    null,
                    ultrans('custom.users'),
                    'user',
                    '/user/profile/'. $user->id
                );

                $criteria['user_ids'][] = $user->id;
            }

            $paginationData = [];
            $actTypes = [];

            if (!empty($criteria)) {
                $rq = Request::create('/api/listActionTypes', 'GET', ['locale' => $locale, 'publicOnly' => true]);
                $api = new ApiActionsHistory($rq);
                $res = $api->listActionTypes($rq)->getData();

                if ($res->success && !empty($res->types)) {
                    $linkWords = ActionsHistory::getTypesLinkWords();
                    foreach ($res->types as $type) {
                        $actTypes[$type->id] = [
                            'name'     => $type->name,
                            'linkWord' => $linkWords[$type->id]
                        ];
                    }

                    $criteria['actions'] = array_keys($actTypes);

                    if (isset($request->newsletter)) {
                        $criteria = $request->all();
                    }

                    $criteria['period_from'] = !empty($request->from) ? $request->from : null;
                    $criteria['period_to'] = !empty($request->to) ? $request->to : null;
                    $perPage = isset($request->perPage) ? $request->perPage : 5;
                    $params = [
                        'api_key'          => $user->api_key,
                        'criteria'         => $criteria,
                        'records_per_page' => $perPage,
                        'page_number'      => !empty($request->page) ? $request->page : 1,
                    ];

                    $rq = Request::create('/api/listActionHistory', 'POST', $params);
                    $api = new ApiActionsHistory($rq);
                    $result = $api->listActionHistory($rq)->getData();
                    $result->actions_history = isset($result->actions_history) ? $result->actions_history : [];
                    $paginationData = $this->getPaginationData($result->actions_history, $result->total_records, [], $perPage);
                }
            }

            return view(
                'user/newsFeed',
                [
                    'class'          => 'user',
                    'actionsHistory' => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                    'filterData'     => isset($filters[$filter]) ? $filters[$filter] : [],
                    'filter'         => $filter,
                    'objIdFilter'    => $objIdFilter,
                ]
            );
        }

        return redirect('/');
    }

    /**
     * Prepares newsfeed datasets
     *
     * @param mixed $params
     * @param mixed $criteria
     * @param mixed $actObjData
     * @param mixed $filters
     * @param mixed $filter
     * @param boolean $objIdFilter
     * @return void
     */
    private function prepareNewsFeedDatasets($params, &$criteria, &$actObjData, &$filters, $filter, $objIdFilter = false) {
        $rq = Request::create('/api/listDatasets', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->listDatasets($rq)->getData();

        if (isset($res->success) && $res->success && !empty($res->datasets)) {
            $objType = Module::getModules()[Module::DATA_SETS];

            if (!isset($actObjData[$objType])) {
                $actObjData[$objType] = [];
            }

            foreach ($res->datasets as $dataset) {
                if (!isset($actObjData[$objType][$dataset->id])) {
                    if ($dataset->org_id) {
                        $params = [
                            'org_id' => $dataset->org_id,
                        ];

                        if (!isset($actObjData['Organisation']) || !isset($actObjData['Organisation'][$dataset->org_id])) {
                            $rq = Request::create('/api/getOrganisationDetails', 'GET', $params);
                            $api = new ApiOrganisation($rq);
                            $res = $api->getOrganisationDetails($rq)->getData();

                            if (isset($res->data)) {
                                $actObjData['Organisation'][$res->data->id] = $this->getActObjectData(
                                    $res->data->id,
                                    $res->data->name,
                                    $res->data->logo,
                                    ultrans('custom.organisations'),
                                    'org',
                                    '/organisation/profile/'. $res->data->uri
                                );
                            }
                        }

                        $objOwner = [
                            'id' => (isset($actObjData['Organisation']) && isset($actObjData['Organisation'][$dataset->org_id])) ? $actObjData['Organisation'][$dataset->org_id]['obj_id'] : '',
                            'name' => (isset($actObjData['Organisation']) && isset($actObjData['Organisation'][$dataset->org_id])) ? $actObjData['Organisation'][$dataset->org_id]['obj_name'] : '',
                            'logo' => (isset($actObjData['Organisation']) && isset($actObjData['Organisation'][$dataset->org_id])) ? $actObjData['Organisation'][$dataset->org_id]['obj_logo'] : '',
                            'view' => (isset($actObjData['Organisation']) && isset($actObjData['Organisation'][$dataset->org_id])) ? $actObjData['Organisation'][$dataset->org_id]['obj_view'] : '',
                        ];
                    } else {
                        $params = [
                            'api_key'  => Auth::user()->api_key,
                            'criteria' => [
                                'id' => $dataset->created_by,
                            ],
                        ];

                        if (!isset($actObjData['User']) || !isset($actObjData['User'][$dataset->created_by])) {
                            $rq = Request::create('/api/listUsers', 'POST', $params);
                            $api = new ApiUser($rq);
                            $res = $api->listUsers($rq)->getData();
                            $user = isset($res->users) ? array_first($res->users) : null;

                            if (isset($user)) {
                                $actObjData['User'][$user->id] = $this->getActObjectData(
                                    $user->id,
                                    ($user->firstname || $user->lastname ? trim($user->firstname .' '. $user->lastname) : $user->username),
                                    null,
                                    ultrans('custom.users'),
                                    'user',
                                    '/user/profile/'. $user->id
                                );
                            }
                        }

                        $objOwner = [
                            'id' => (isset($actObjData['User']) && isset($actObjData['User'][$dataset->created_by])) ? $actObjData['User'][$dataset->created_by]['obj_id'] : '',
                            'name' => (isset($actObjData['User']) && isset($actObjData['User'][$dataset->created_by])) ? $actObjData['User'][$dataset->created_by]['obj_name'] : '',
                            'logo' => (isset($actObjData['User']) && isset($actObjData['User'][$dataset->created_by])) ? $actObjData['User'][$dataset->created_by]['obj_logo'] : '',
                            'view' => (isset($actObjData['User']) && isset($actObjData['User'][$dataset->created_by])) ? $actObjData['User'][$dataset->created_by]['obj_view'] : '',
                        ];
                    }

                    if ($filter == 'datasets') {
                        $filters[$filter]['data'][$dataset->uri] = $dataset->name;

                        if ($objIdFilter && $objIdFilter != $dataset->uri) {
                            continue;
                        }
                    }

                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'         => $dataset->uri,
                        'obj_name'       => $dataset->name,
                        'obj_module'     => ultrans('custom.dataset'),
                        'obj_type'       => 'dataset',
                        'obj_view'       => '/data/view/'. $dataset->uri,
                        'parent_obj_id'  => '',
                        'obj_owner_id'   => $objOwner['id'],
                        'obj_owner_name' => $objOwner['name'],
                        'obj_owner_logo' => $objOwner['logo'],
                        'obj_owner_view' => $objOwner['view']
                    ];

                    $criteria['dataset_ids'][] = $dataset->id;

                    if (!empty($dataset->resource)) {
                        $objTypeRes = Module::getModules()[Module::RESOURCES];

                        foreach ($dataset->resource as $resource) {
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => ultrans('custom.resource'),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/data/resourceView/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => ultrans('custom.dataset'),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri,
                                'obj_owner_id'      => $objOwner['id'],
                                'obj_owner_name'    => $objOwner['name'],
                                'obj_owner_logo'    => $objOwner['logo'],
                                'obj_owner_view'    => $objOwner['view']
                            ];

                            $criteria['resource_uris'][] = $resource->uri;
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns an array of newfeed filters
     *
     * @return array
     */
    private function getNewsFeedFilters() {
        return [
            'organisations' => [
                'key'   => 'organisation',
                'label' => 'custom.select_org',
                'data'  => []
            ],
            'groups'        => [
                'key'   => 'group',
                'label' => 'custom.select_group',
                'data'  => []
            ],
            'categories'    => [
                'key'   => 'category',
                'label' => 'custom.select_main_topic',
                'data'  => []
            ],
            'tags'          => [
                'key'   => 'tag',
                'label' => 'custom.select_label',
                'data'  => []
            ],
            'users'         => [
                'key'   => 'user',
                'label' => 'custom.select_user',
                'data'  => []
            ],
            'datasets'      => [
                'key'   => 'dataset',
                'label' => 'custom.select_dataset',
                'data'  => []
            ]
        ];
    }

    /**
     * Returns an array with formatted action object data
     *
     * @param integer id
     * @param string name
     * @param string type
     * @param string view
     * @param integer parentObjId
     *
     * @return array
     */
    private function getActObjectData($id, $name, $logo, $module, $type, $view = null, $parentObjId = null) {
        return [
            'obj_id'        => $id,
            'obj_name'      => $name,
            'obj_logo'      => $logo,
            'obj_module'    => $module,
            'obj_type'      => $type,
            'obj_view'      => $view,
            'parent_obj_id' => $parentObjId
        ];
    }

    /**
     * Activates an account on confirmation
     *
     * @param Request $request
     * @return view error view on error or sends email on success
     */
    public function confirmation(Request $request)
    {
        $class = 'user';
        $confirmed = false;
        $hash = $request->offsetGet('hash');

        if ($hash) {
            $user = User::where('hash_id', $hash)->first();

            if ($user) {
                $confirmed = ActionsHistory::where('user_id', $user->id)
                    ->where('action', ActionsHistory::TYPE_CONFIRM_ACCOUNT)
                    ->first()
                        ? true
                        : false;

                if (!$confirmed) {
                    DB::beginTransaction();
                    $user->active = true;

                    try {
                        $user->save();
                        $request->session()->flash('alert-success', __('custom.successful_acc_activation'));
                        $ip = request()->ip();

                        $dbData = [
                            'module_name'   => Module::getModuleName(Module::USERS),
                            'action'        => ActionsHistory::TYPE_CONFIRM_ACCOUNT,
                            'action_msg'    => 'Confirmed account',
                            'ip_address'    => !empty($ip) ? $ip : 'N/A',
                            'user_agent'    => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A',
                            'occurrence'    => date('Y-m-d H:i:s'),
                            'user_id'       => $user->id,
                            'action_object' => '',
                        ];

                        ActionsHistory::create($dbData);
                        DB::commit();

                        return redirect('login');
                    } catch (QueryException $ex) {
                        DB::rollback();
                        Log::error($ex->getMessage());
                    }
                }
            }
        }

        if ($request->has('generate')) {
            $user = User::where('id', $request->offsetGet('id'))->first();

            $mailData = [
                'user'  => $user->firstname,
                'hash'  => $user->hash_id,
                'mail'  => $user->email,
                'id'    => $user->id,
            ];

            Mail::send('mail/confirmationMail', $mailData, function ($m) use ($mailData) {
                $m->from(config('app.MAIL_FROM'), config('app.APP_NAME'));
                $m->to($mailData['mail'], $mailData['user']);
                $m->subject(__('custom.register_confirmation'));
            });

            $request->session()->flash('alert-warning', __('custom.mail_sent_again'));

            return redirect('login');
        }

        return view('confirmError', compact('class', 'confirmed'));
    }

    /**
     * Loads a view with a list of users
     *
     * @param Request $request
     * @return view with list of users
     */
    public function listUsers(Request $request)
    {
        $perPage = 6;
        $class = 'user';
        $users = [];
        $q = null;
        $params = [
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
            'criteria'          => [
                'active'            => Organisation::ACTIVE_TRUE,
            ],
        ];

        if ($request->has('q')) {
            $q = $request->get('q');
            $params['criteria']['keywords'] = $q;
        }

        $listReq = Request::create('/api/listUsers', 'POST', $params);
        $api = new ApiUser($listReq);
        $result = $api->listUsers($listReq)->getData();

        $paginationData = $this->getPaginationData($result->users, $result->total_records, compact('q'), $perPage);

        return view('/user/list', [
            'class'         => $class,
            'users'         => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'search'        => $q,
        ]);
    }

    /**
     * Filters users based on input
     *
     * @param Request $request
     * @return view with list of users
     */
    public function searchUsers(Request $request)
    {
        $perPage = 6;
        $search = $request->search;

        if (empty(trim($search))) {
            return redirect()->route('usersList');
        }

        $params = [
            'api_key'           => Auth::user()->api_key,
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
            'criteria'          => [
                'keywords'          => $search,
            ],
        ];

        $searchReq = Request::create('/api/listUsers', 'POST', $params);
        $api = new ApiUser($searchReq);
        $result = $api->listUsers($searchReq)->getData();

        $users = !empty($result->users) ? $result->users : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $getParams = [
            'search' => $search
        ];

        $paginationData = $this->getPaginationData($users, $count, $getParams, $perPage);

        return view(
            'user/list',
            [
                'class'         => 'user',
                'users'         => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search
            ]
        );
    }

    /**
     * Loads profile information
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view with profile data
     */
    public function profile(Request $request, $id)
    {
        $followersCount = 0;
        $followed = false;
        $params = [
            'criteria'  => [
                'id'        => $id,
            ],
        ];

        $listReq = Request::create('/api/listUsers', 'POST', $params);
        $apiUser = new ApiUser($listReq);
        $result = $apiUser->listUsers($listReq)->getData();

        if ($result->success && !empty($result->users[0])) {
            $follReq = Request::create('api/getFollowersCount', 'POST', $params);
            $apiFollow = new ApiFollow($follReq);
            $followers = $apiFollow->getFollowersCount($follReq)->getData();

            if ($followers->success) {
                $followersCount = $followers->count;

                foreach($followers->followers as $follower) {
                    if (Auth::check()) {
                        if ($follower->user_id == Auth::user()->id) {
                            $followed = true;

                            break;
                        }
                    }
                }
            }

            $setsReq = Request::create('api/getUsersDataSetCount', 'POST', $params);
            $apiDataSet = new ApiDataSet($setsReq);
            $setsCount = $apiDataSet->getUsersDataSetCount($setsReq)->getData();

            if ($request->has('follow')) {
                $follow = Request::create('api/addFollow', 'POST', [
                    'api_key'           => Auth::user()->api_key,
                    'user_id'           => Auth::user()->id,
                    'follow_user_id'    => $id,
                ]);

                $followResult = $apiFollow->addFollow($follow)->getData();

                if ($followResult->success) {

                    return back();
                }
            }

            if ($request->has('unfollow')) {
                $follow = Request::create('api/unFollow', 'POST', [
                    'api_key'           => Auth::user()->api_key,
                    'user_id'           => Auth::user()->id,
                    'follow_user_id'    => $id,
                ]);

                $followResult = $apiFollow->unFollow($follow)->getData();

                if ($followResult->success) {

                    return back();
                }
            }

            return view('user/profile', [
                'user'              => $result->users[0],
                'class'             => 'user',
                'ownProfile'        => $id == Auth::id(),
                'followersCount'    => $followersCount,
                'followed'          => $followed,
                'dataSetsCount'     => $setsCount->success ? $setsCount->count : 0,
            ]);
        } else {

            return redirect('/users/list');
        }
    }

    public function userChronology(Request $request, $id)
    {
        $locale = \LaravelLocalization::getCurrentLocale();
        // $user = User::find($id);
        $actObjData = [];
        $criteria = [];

        $params = [
            'criteria'  => [
                'id'        => $id,
            ],
        ];

        $listReq = Request::create('/api/listUsers', 'POST', $params);
        $apiUser = new ApiUser($listReq);
        $result = $apiUser->listUsers($listReq)->getData();

        if ($result->success) {
            $criteria['user_id'] = $id;

            $objType = Module::getModules()[Module::USERS];
            $actObjData[$objType] = [];
            $actObjData[$objType][$result->users[0]->id] = [
                'obj_id'        => $result->users[0]->id,
                'obj_name'      => $result->users[0]->username,
                'obj_module'    => 'User',
                'obj_type'      => 'user',
                'obj_view'      => '/user/profile/'. $result->users[0]->id,
                'parent_obj_id' => ''
            ];

            $rq = Request::create('/api/listDataSets', 'POST', [
                'criteria' => [
                    'created_by' => $id
                ]
            ]);
            $api = new ApiDataSet($rq);
            $res = $api->listDataSets($rq)->getData();

            if ($res->success && !empty($res->datasets)) {
                $objType = Module::getModules()[Module::DATA_SETS];
                $objTypeRes = Module::getModules()[Module::RESOURCES];
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => ultrans('custom.dataset'),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/data/view/'. $dataset->uri,
                        'parent_obj_id' => ''
                    ];

                    if (!empty($dataset->resource)) {
                        foreach ($dataset->resource as $resource) {
                            $criteria['resource_uris'][] = $resource->uri;
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => ultrans('custom.resource'),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/data/resourceView/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => ultrans('custom.dataset'),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri
                            ];
                        }
                    }
                }
            }

            $rq = Request::create('/api/listOrganisations', 'POST', [
                'api_key'   => User::where('id', $result->users[0]->id)->value('api_key'),
                'criteria'  => [
                    'user_id'   => $id,
                ],
            ]);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();

            if ($res->success && !empty($res->organisations)) {
                $objType = Module::getModules()[Module::ORGANISATIONS];
                $actObjData[$objType] = [];

                foreach ($res->organisations as $organisations) {
                    $criteria['org_ids'][] = $organisations->id;
                    $actObjData[$objType][$organisations->id] = [
                        'obj_id'        => $organisations->uri,
                        'obj_name'      => $organisations->name,
                        'obj_module'    => ultrans('custom.organisations'),
                        'obj_type'      => 'org',
                        'obj_view'      => '/organisation/profile/'. $organisations->uri,
                        'parent_obj_id' => ''
                    ];
                }
            }

            $paginationData = [];
            $actTypes = [];

            if (!empty($criteria)) {
                $rq = Request::create('/api/listActionTypes', 'GET', ['locale' => $locale, 'publicOnly' => true]);
                $api = new ApiActionsHistory($rq);
                $res = $api->listActionTypes($rq)->getData();

                if ($res->success && !empty($res->types)) {
                    $linkWords = ActionsHistory::getTypesLinkWords();
                    foreach ($res->types as $type) {
                        $actTypes[$type->id] = [
                            'name'     => $type->name,
                            'linkWord' => $linkWords[$type->id]
                        ];
                    }

                    $criteria['actions'] = array_keys($actTypes);
                    $perPage = 10;
                    $params = [
                        'criteria'         => $criteria,
                        'records_per_page' => $perPage,
                        'page_number'      => !empty($request->page) ? $request->page : 1,
                    ];

                    $rq = Request::create('/api/listActionHistory', 'POST', $params);
                    $api = new ApiActionsHistory($rq);
                    $res = $api->listActionHistory($rq)->getData();
                    $res->actions_history = isset($res->actions_history) ? $res->actions_history : [];

                    $paginationData = $this->getPaginationData($res->actions_history, $res->total_records, [], $perPage);
                }
            }

            return view(
                'user/userChronology',
                [
                    'class'          => 'user',
                    'user'           => $result->users[0],
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect(url('user/profile/'. $id));
    }

    /**
     * Registers a group
     *
     * @param Request $request
     *
     * @return view with registered group
     */
    public function registerGroup(Request $request)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $class = 'user';
        $fields = $this->getGroupTransFields();

        if ($request->has('create')) {
            $data = $request->all();
            $data['description'] = $data['descript'];

            if (!empty($data['logo'])) {
                $data['logo_filename'] = $data['logo']->getClientOriginalName();
                $data['logo'] = $data['logo']->getPathName();
            }

            $params = [
                'api_key'   => Auth::user()->api_key,
                'data'      => $data,
            ];

            $groupReq = Request::create('api/addGroup', 'POST', $params);
            $orgApi = new ApiOrganisation($groupReq);
            $result = $orgApi->addGroup($groupReq)->getData();

            if ($result->success) {
                RoleRight::refreshSession();
                $request->session()->flash('alert-success', __('custom.successful_group_creation'));

                return redirect('/user/groups/view/'. Organisation::find($result->id)->uri);
            } else {
                $request->session()->flash('alert-danger', __('custom.failed_group_creation'));

                return back()->withErrors($result->errors)->withInput(Input::all());
            }
        }

        return view('/user/groupRegistration', compact('class', 'fields'));
    }

    /**
     * Lists the groups in which the user is a member of
     *
     * @param Request $request
     *
     * @return view with list of groups
     */
    public function groups(Request $request)
    {
        $class = 'user';
        $groups = [];
        $perPage = 6;

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_VIEW
        );

        $buttons['view'] = $rightCheck;

        if (!$rightCheck) {
            return view('user/groups', [
                'class'         => $class,
                'groups'        => [],
                'pagination'    => null,
                'buttons'       => $buttons
            ]);
        }

        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'         => [
                'user_id'           => \Auth::user()->id,
            ],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $orgReq = Request::create('/api/listGroups', 'POST', $params);
        $api = new ApiOrganisation($orgReq);
        $result = $api->listGroups($orgReq)->getData();

        if (!empty($result->groups)) {
            $groups = $result->groups;
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;

        foreach ($groups as $group) {
            $rightCheck = RoleRight::checkUserRight(
                Module::GROUPS,
                RoleRight::RIGHT_VIEW,
                [
                    'group_id'       => $group->id
                ],
                [
                    'created_by'    => $group->created_by,
                    'group_ids'     => [$group->id]
                ]
            );

            $buttons[$group->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::GROUPS,
                RoleRight::RIGHT_ALL,
                [
                    'group_id'       => $group->id
                ],
                [
                    'created_by'    => $group->created_by,
                    'group_ids'     => [$group->id]
                ]
            );

            $buttons[$group->uri]['edit'] = $rightCheck;
            $buttons[$group->uri]['delete'] = $rightCheck;
        }

        $paginationData = $this->getPaginationData($groups, count($groups), [], $perPage);

        return view('/user/groups', [
            'class'         => $class,
            'groups'        => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'buttons'       => $buttons,
        ]);
    }

    /**
     * Displays information for a given group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function viewGroup(Request $request, $uri)
    {
        $orgId = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->value('id');

        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $uri]);
        $apiGroups = new ApiOrganisation($groupReq);
        $groupData = $apiGroups->getGroupDetails($groupReq)->getData();
        $groupData = !empty($groupData->data) ? $groupData->data : null;

        if (!$groupData) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_group_found')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'       => $groupData->id
            ],
            [
                'group_ids'      => [$groupData->id]
            ]
        );

        $buttons[$groupData->uri]['edit'] = $rightCheck;
        $buttons[$groupData->uri]['delete'] = $rightCheck;

        $request = Request::create('/api/getGroupDetails', 'POST', [
            'group_id'  => $orgId,
            'locale'    => \LaravelLocalization::getCurrentLocale(),
        ]);
        $api = new ApiOrganisation($request);
        $result = $api->getGroupDetails($request)->getData();

        if ($result->success) {
            return view('user/groupView', ['class' => 'user', 'group' => $result->data, 'id' => $orgId, 'buttons' => $buttons]);
        }

        return redirect('/user/groups');
    }

    /**
     * Deletes a group
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view to previous page
     */
    public function deleteGroup(Request $request, $id)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'  => $id
            ],
            [
                'group_ids' => [$id]
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $delArr = [
            'api_key'   => Auth::user()->api_key,
            'group_id'  => $id,
        ];

        $delReq = Request::create('/api/deleteGroup', 'POST', $delArr);
        $api = new ApiOrganisation($delReq);
        $result = $api->deleteGroup($delReq)->getData();

        if ($result->success) {
            $request->session()->flash('alert-success', __('custom.delete_success'));

            return redirect('/user/groups');
        }

        $request->session()->flash('alert-danger', __('custom.delete_error'));

        return redirect('/user/groups');
    }

    /**
     * Edit a group based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function editGroup(Request $request, $uri)
    {
        $org = Organisation::where('uri', $uri)
            ->where('type', Organisation::TYPE_GROUP)
            ->first();

        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $uri]);
        $apiGroups = new ApiOrganisation($groupReq);
        $groupData = $apiGroups->getGroupDetails($groupReq)->getData();
        $groupData = !empty($groupData->data) ? $groupData->data : null;

        if (!isset($groupData)) {
            return back();
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'  => $groupData->id
            ],
            [
                'group_ids' => [$groupData->id]
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        if (!empty($org) && $this->checkUserOrg($org->id)) {
            $class = 'user';
            $fields = $this->getGroupTransFields();
            $root = 'user';
            $model = Organisation::find($org->id)->loadTranslations();
            $withModel = CustomSetting::where('org_id', $org->id)->get()->loadTranslations();
            $model->logo = $this->getImageData($model->logo_data, $model->logo_mime_type, 'group');

            if ($request->has('edit')) {
                $data = $request->all();

                $data['description'] = isset($data['descript']) ? $data['descript'] : null;

                if (!empty($data['logo'])) {
                    $data['logo_filename'] = $data['logo']->getClientOriginalName();
                    $data['logo'] = $data['logo']->getPathName();
                }

                $params = [
                    'api_key'   => Auth::user()->api_key,
                    'group_id'  => $org->id,
                    'data'      => $data,
                ];

                $editReq = Request::create('/api/editGroup', 'POST', $params);
                $api = new ApiOrganisation($editReq);
                $result = $api->editGroup($editReq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    if (!empty($params['data']['uri'])) {
                        return redirect(url('/user/groups/edit/'. $params['data']['uri']));
                    }
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back()->withErrors(isset($result->errors) ? $result->errors : []);
            }

            return view('user/groupEdit', compact('class', 'fields', 'model', 'withModel', 'root'));
        }

        return redirect('/user/groups');
    }

    /**
     * Forgotten password
     *
     * @param string username - required
     *
     * @return true - if user is found and email is sent false - otherwise
     */
    public function forgottenPassword(Request $request)
    {
        $errors = [];

        if ($request->isMethod('post')) {
            $params['username'] = $request->input('username');

            $req = Request::create('/api/forgottenPassword', 'POST', ['data' => $params]);
            $api = new ApiUser($req);
            $result = $api->forgottenPassword($req)->getData();

            if ($result->success) {
                $request->session()->flash('alert-warning', __('custom.receive_email'));

                return redirect('/login');
            } else {
                foreach ($result->errors as $field => $msg) {
                    $errors[substr($field, strpos($field, ".") )] = $msg[0];
                }
            }
        }

        return view('user/forgottenPassword', ['class' => 'index'])->with('errors', $errors);
    }

    /**
     * Password reset
     *
     * @param string hash - required
     * @param string password - required
     * @param string password_confirm - required
     *
     * @return true - if password is changed false - otherwise
     */
    public function passwordReset(Request $request)
    {
        Auth::logout();
        $hash = $request->offsetGet('hash');
        $username = $request->offsetGet('username');
        $errors = [];

        $user = User::where('hash_id', $request->offsetGet('hash'))->first();

        if (!$user) {
            $request->session()->flash('alert-danger', __('custom.wrong_reset_link'));

            return redirect('/login');
        }

        if ($request->isMethod('post')) {
            $params['hash'] = $hash;
            $params['password'] = $request->input('password');
            $params['password_confirm'] = $request->input('password_confirm');

            $req = Request::create('/api/passwordReset', 'POST', ['data' => $params]);
            $api = new ApiUser($req);
            $result = $api->passwordReset($req)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.pass_change_succ'));

                return redirect('login');
            } else {
                foreach ($result->errors as $field => $msg) {
                    $errors[substr($field, strpos($field, '.') )] = $msg[0];
                }
            }
        }

        return view(
            'user/passwordReset',[
                'class' => 'index',
            ]
        )->with('errors', $errors);
    }

    /**
     * Send terms of use request
     *
     * @param Request $request
     *
     * @return json response with result
     */
    public function sendTermsOfUseReq(Request $request)
    {
        $params = [
            'api_key'   => Auth::user()->api_key,
            'data'      => $request->all(),
        ];

        $sendRequest = Request::create('api/sendTermsOfUseRequest', 'POST', $params);
        $apiTermsOfUseReq = new ApiTermsOfUseRequest($sendRequest);
        $result = $apiTermsOfUseReq->sendTermsOfUseRequest($sendRequest)->getData();

        return json_encode($result);
    }

    /**
     * Loads a list of group datasets
     *
     * @param Request $request
     *
     * @return view with list of datasets
     */
    public function groupDatasets(Request $request, $uri)
    {
        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $uri]);
        $apiOrgs = new ApiOrganisation($groupReq);
        $group = $apiOrgs->getGroupDetails($groupReq)->getData();
        $groupData = !empty($group->data) ? $group->data : null;

        if (!$groupData) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_group_found')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_VIEW,
            [
                'group_id' => $groupData->id
            ],
            [
                'group_ids' => [$groupData->id]
            ]
        );

        $buttons['add'] = $rightCheck;
        $buttons['view'] = $rightCheck;

        if (!$rightCheck) {
            return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
        }

        $perPage = 6;
        $params = [
            'api_key'           => \Auth::user()->api_key,
            'records_per_page'  => $perPage,
            'criteria' => [
                'group_ids'     => [$groupData->id],
            ],
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        if (!Role::isAdmin()) {
            $params['criteria']['status'] = DataSet::STATUS_PUBLISHED;
        }

        $q = null;

        if ($request->has('q')) {
            $q = $request->offsetGet('q');
            $params['criteria']['keywords'] = $q;
        }

        $dataRq = Request::create('/api/listDatasets', 'POST', $params);
        $dataApi = new ApiDataSet($dataRq);
        $datasets = $dataApi->listDatasets($dataRq)->getData();
        $paginationData = $this->getPaginationData($datasets->datasets, $datasets->total_records, compact('q'), $perPage);

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [
                'group_id' => $groupData->id
            ],
            [
                'group_ids' => [$groupData->id]
            ]
        );

        $buttons['add'] = $rightCheck;

        foreach ($datasets->datasets as $dataset) {
            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_VIEW,
                [
                    'group_id'       => $groupData->id
                ],
                [
                    'group_ids'     => [$groupData->id]
                ]
            );

            $buttons[$dataset->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_EDIT,
                [
                    'group_id'       => $groupData->id
                ],
                [
                    'group_ids'     => [$groupData->id]
                ]
            );

            $buttons[$dataset->uri]['edit'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::DATA_SETS,
                RoleRight::RIGHT_ALL,
                [
                    'group_id'       => $groupData->id
                ],
                [
                    'group_ids'     => [$groupData->id]
                ]
            );

            $buttons[$dataset->uri]['delete'] = $rightCheck;
        }

        // handle dataset delete
        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->removeDataset($groupData->id, $uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            return back();
        }

        return view('user/groupDatasets', [
                'class'         => 'user',
                'datasets'      => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'activeMenu'    => 'group',
                'search'        => $q,
                'buttons'       => $buttons,
                'uri'           => $uri,
                'group'         => $groupData
        ]);
    }

    public function groupDatasetView(Request $request, $grpUri, $uri)
    {
        $datasetReq = Request::create('/api/getDatasetDetails', 'POST', ['dataset_uri' => $uri]);
        $apiDatasets = new ApiDataset($datasetReq);
        $result = $apiDatasets->getDatasetDetails($datasetReq)->getData();
        $datasetData = !empty($result->data) ? $this->getModelUsernames($result->data) : null;

        if (!isset($datasetData)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_dataset_found')));
        }

        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $grpUri]);
        $apiOrganisation = new ApiOrganisation($groupReq);
        $groupData = $apiOrganisation->getGroupDetails($groupReq)->getData();
        $group = !empty($groupData->data) ? $groupData->data : null;

        if (!isset($group)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_group_found')));
        }

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addResource'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_VIEW
        );

        $buttons['seeResource'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [
                'group_id'       => $datasetData->org_id
            ],
            [
                'created_by'    => $datasetData->created_by,
                'group_ids'     => [$datasetData->org_id]
            ]
        );

        $buttons[$datasetData->uri]['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'       => $datasetData->org_id
            ],
            [
                'created_by'    => $datasetData->created_by,
                'group_ids'     => [$datasetData->org_id]
            ]
        );

        $buttons[$datasetData->uri]['delete'] = $rightCheck;

        $resPerPage = 10;
        $pageNumber = !empty($request->rpage) ? $request->rpage : 1;
        $params['records_per_page'] = $resPerPage;
        $params['page_number'] = $pageNumber;
        $params['criteria']['dataset_uri'] = $uri;

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

        // get category details
        if (!empty($datasetData->category_id)) {
            $params = [
                'category_id' => $datasetData->category_id,
            ];
            $rq = Request::create('/api/getMainCategoryDetails', 'POST', $params);
            $api = new ApiCategory($rq);
            $res = $api->getMainCategoryDetails($rq)->getData();

            $datasetData->category_name = isset($res->category) && !empty($res->category) ? $res->category->name : '';
        }

        // get terms of use details
        if (!empty($datasetData->terms_of_use_id)) {
            $params = [
                'terms_id' => $datasetData->terms_of_use_id,
            ];
            $rq = Request::create('/api/getTermsOfUseDetails', 'POST', $params);
            $api = new ApiTermsOfUse($rq);
            $res = $api->getTermsOfUseDetails($rq)->getData();

            $datasetData->terms_of_use_name = isset($res->data) && !empty($res->data) ? $res->data->name : '';
        }

        foreach ($resources as $resource) {
            $rightCheck = RoleRight::checkUserRight(
                Module::RESOURCES,
                RoleRight::RIGHT_VIEW,
                [],
                [
                    'created_by'   => $resource->created_by
                ]
            );

            $buttons[$resource->uri]['view'] = $rightCheck;
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['addToGroup'] = $rightCheck;
        $groups = $setGroups = [];

        if ($buttons['addToGroup']) {
            $groups = $this->prepareGroups();
        }

        if (!empty($datasetData->groups)) {
            foreach ($datasetData->groups as $record) {
                $setGroups[] = (int) $record->id;
            }
        }

        // handle groups change
        if ($request->has('save')) {
            $groupId = $request->offsetGet('group_id');
            $post = [
                'api_key'       => Auth::user()->api_key,
                'data_set_uri'  => $uri,
                'group_id'      => $groupId,
            ];

            // if all groups are deselected
            if (count($setGroups) && is_null($groupId)) {
                $post['group_id'] = $setGroups;
                $removeGroup = Request::create('/api/removeDatasetFromGroup', 'POST', $post);
                $api = new ApiDataSet($removeGroup);
                $remove = $api->removeDatasetFromGroup($removeGroup)->getData();

                if (!$remove->success) {
                    session()->flash('alert-danger', __('custom.edit_error'));

                    return redirect()->back()->withInput()->withErrors($remove->errors);
                }

                $datasetData->groups = $setGroups = [];
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

                $post = [ 'criteria' => [
                        'group_ids' => $groupId,
                    ],
                ];

                $getGroups = Request::create('/api/listGroups', 'POST', $post);
                $apiGroups = new ApiOrganisation($getGroups);
                $groupResponse = $apiGroups->listGroups($getGroups)->getData();

                if ($groupResponse->success) {
                    $datasetData->groups = $groupResponse->groups;
                }

                $setGroups = $groupId;
            }
        }

        // handle dataset delete
        if ($request->has('delete')) {
            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', __('custom.success_dataset_delete'));
            } else {
                $request->session()->flash('alert-danger', __('custom.fail_dataset_delete'));
            }

            $root = Role::isAdmin() ? 'admin' : 'user';

            return redirect('/'. $root .'/groups/datasets/'. $group->uri);
        }

        $paginationData = $this->getPaginationData(
            $resources,
            $resCount,
            array_except(app('request')->input(), ['rpage']),
            $resPerPage,
            'rpage'
        );

        return view(
            'user/groupDatasetView',
            [
                'class'         => 'user',
                'groups'        => $groups,
                'setGroups'     => $setGroups,
                'dataset'       => $datasetData,
                'resources'     => $paginationData['items'],
                'activeMenu'    => 'group',
                'buttons'       => $buttons,
                'group'         => $group,
                'pagination'    => $paginationData['paginate'],
                'grpUri'        => $grpUri,
                'uri'           => $uri,
                'sorting'       => \Auth::user()->is_admin ? 'adminGroupDatasetView' : 'userGroupDatasetView',
            ]
        );
    }

    public function groupDatasetEdit(Request $request, DataSet $datasetModel, $grpUri, $uri)
    {
        $model = DataSet::where('uri', $uri)->with('dataSetGroup')->first();

        if (empty($model)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_dataset_found')));
        }

        $model->loadTranslations();
        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $grpUri]);
        $apiOrg = new ApiOrganisation($groupReq);
        $groupData = $apiOrg->getGroupDetails($groupReq)->getData();
        $group = !empty($groupData->data) ? $groupData->data : null;

        if (!isset($group)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_group_found')));
        }

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::DATA_SETS,
            RoleRight::RIGHT_EDIT,
            [
                'group_id'       => $model->org_id
            ],
            [
                'created_by'    => $model->created_by,
                'group_ids'     => [$model->org_id]
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [
                'group_id'       => $model->org_id
            ],
            [
                'created_by'    => $model->created_by,
                'group_ids'     => [$model->org_id]
            ]
        );

        $buttons['addResource'] = $rightCheck;

        //prepare data for form
        $visibilityOptions = $datasetModel->getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $setGroups = [];

        if (!empty($model->dataSetGroup)) {
            foreach ($model->dataSetGroup as $record) {
                $setGroups[] = $record->group_id;
            }
        }

        // check for resources - show publish button
        $hasResources = Resource::where('data_set_id', $model->id)->count();
        $withModel = CustomSetting::where('data_set_id', $model->id)->get()->loadTranslations();
        $tagModel = Tags::whereHas('dataSetTags', function($q) use ($model) {
                $q->where('data_set_id', $model->id);
            })
            ->get();

        // handle form submit
        if ($request->has('save') || $request->has('publish')) {
            $editData = $request->all();

            if ($editData['uri'] == $uri) {
                unset($editData['uri']);
                $newURI = $uri;
            } else {
                $newURI = $editData['uri'];
            }

            $tagList = $request->offsetGet('tags');
            $editData = $this->prepareTags($editData);
            $groupId = $request->offsetGet('group_id');

            $post = [
                'api_key'       => Auth::user()->api_key,
                'data_set_uri'  => $uri,
                'group_id'      => $groupId,
            ];

            // if all groups are deselected
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
            $api = new ApiDataSet($editRq);
            $success = $api->editDataset($editRq)->getData();

            if ($success->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));
                $root = Role::isAdmin() ? 'admin' : 'user';

                return redirect(url('/'. $root .'/groups/'. $group->uri .'/dataset/edit/'. $newURI));
            } else {
                session()->flash('alert-danger', __('custom.edit_error'));

                return redirect()->back()->withInput()->withErrors($success->errors);
            }
        }

        return view('user/groupDatasetEdit', [
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
            'buttons'       => $buttons,
            'setGroups'     => $setGroups,
            'group'         => $group,
            'fields'        => $this->getDatasetTransFields(),
        ]);
    }

    public function groupResourceView(Request $request, $grpUri, $uri, $version = null)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        $resourcesReq = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $uri]);
        $apiResources = new ApiResource($resourcesReq);
        $resource = $apiResources->getResourceMetadata($resourcesReq)->getData();
        $resourceVersionFormat = null;

        if (empty($resource->resource)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_resource_found')));
        }

        if ($resource->resource->file_format) {
            $versionQuery = ElasticDataSet::where('resource_id', $resource->resource->id);

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

        $resourceVersionFormat = is_null($resourceVersionFormat) ? Resource::getFormatsCode($resource->resource->file_format) : $resourceVersionFormat;

        $resource = $this->getModelUsernames($resource->resource);
        $resource->format_code = Resource::getFormatsCode($resource->file_format);
        $formats = Resource::getFormats(true);

        if (empty($version)) {
            $version = $resource->version;
        }

        $groupReq = Request::create('/api/getGroupDetails', 'POST', ['group_uri' => $grpUri]);
        $apiOrganisation = new ApiOrganisation($groupReq);
        $groupData = $apiOrganisation->getGroupDetails($groupReq)->getData();

        if (empty($groupData->data)) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.no_group_found')));
        }

        $group = $groupData->data;

        $datasetReq = Request::create('/api/getDatasetDetails', 'POST', ['dataset_uri' => $resource->dataset_uri]);
        $apiDatasets = new ApiDataset($datasetReq);
        $dataset = $apiDatasets->getDatasetDetails($datasetReq)->getData();
        $dataset = !empty($dataset->data) ? $dataset->data : null;
        $versionsPerPage = 10;

        if (!is_null($dataset)) {
            $datasetData = [
                'name'  => $dataset->name,
                'uri'   => $dataset->uri,
            ];
        } else {
            $datasetData = null;
        }

        // rights checks
        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_EDIT,
            [
                'org_id'       => $dataset->org_id
            ],
            [
                'created_by'     => $dataset->created_by,
                'org_id'         => $dataset->org_id
            ]
        );

        $buttons['edit'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::RESOURCES,
            RoleRight::RIGHT_ALL,
            [
                'group_id'       => $dataset->org_id
            ],
            [
                'created_by'     => $dataset->created_by,
                'groups_ids'     => [$dataset->org_id]
            ]
        );

        $buttons['delete'] = $rightCheck;

        // get elastic search data for the resource
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

        // handle delete
        if ($request->has('delete')) {

            if (!$buttons['delete']) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
            }

            $rq = Request::create('/api/deleteResource', 'POST', ['resource_uri' => $uri]);
            $api = new ApiResource($rq);
            $result = $api->deleteResource($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
                $root = Role::isAdmin() ? 'admin' : 'user';

                return redirect()->route($root .'GroupDatasetView', ['uri' => $resource->dataset_uri, 'grpUri' => $group->uri]);
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

        $vCount = count($resource->versions_list);
        $verData = collect($resource->versions_list)->paginate($versionsPerPage);

        $paginationData = $this->getPaginationData(
            $verData,
            $vCount,
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

        return view('user/groupResourceView', [
            'class'         => 'user',
            'resource'      => $resource,
            'search'        => $search,
            'dataPerPage'   => $dataPerPage,
            'data'          => $resourcePaginationData['data'],
            'versionView'   => $version,
            'versions'      => $verData,
            'pagination'    => $paginationData['paginate'],
            'buttons'       => $buttons,
            'group'         => $group,
            'dataset'       => $datasetData,
            'supportName'   => !is_null($dataset) ? $dataset->support_name : null,
            'formats'       => $formats,
            'resPagination' => $resourcePaginationData['resPagination'],
            'versionFormat' => $resourceVersionFormat
        ]);
    }

    /**
     * Filters groups based on search string
     *
     * @param Request $request
     *
     * @return view with filtered group list
     */
    public function searchGroups(Request $request)
    {
        $perPage = 6;
        $search = $request->offsetGet('q');

        if (empty($search)) {
            return redirect('user/groups');
        }

        $params = [
            'records_per_page'  => $perPage,
            'criteria'          => [
                'keywords'          => $search,
                'user_id'           => Auth::user()->id,
            ]
        ];

        $searchRq = Request::create('/api/listGroups', 'POST', $params);
        $api = new ApiOrganisation($searchRq);
        $grpData = $api->listGroups($searchRq)->getData();

        $groups = !empty($grpData->groups) ? $grpData->groups : [];
        $count = !empty($grpData->total_records) ? $grpData->total_records : 0;

        $getParams = [
            'search' => $search
        ];

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_EDIT
        );

        $buttons['add'] = $rightCheck;

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_VIEW
        );

        $buttons['view'] = $rightCheck;

        foreach ($groups as $group) {
            $rightCheck = RoleRight::checkUserRight(
                Module::GROUPS,
                RoleRight::RIGHT_VIEW,
                [
                    'group_id'       => $group->id
                ],
                [
                    'created_by'    => $group->created_by,
                    'group_ids'     => [$group->id]
                ]
            );

            $buttons[$group->uri]['view'] = $rightCheck;

            $rightCheck = RoleRight::checkUserRight(
                Module::GROUPS,
                RoleRight::RIGHT_ALL,
                [
                    'group_id'       => $group->id
                ],
                [
                    'created_by'    => $group->created_by,
                    'group_ids'     => [$group->id]
                ]
            );

            $buttons[$group->uri]['edit'] = $rightCheck;
            $buttons[$group->uri]['delete'] = $rightCheck;
        }

        $paginationData = $this->getPaginationData($groups, $count, $getParams, $perPage);

        return view('user/groups', [
            'class'         => 'user',
            'groups'        => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'buttons'       => $buttons
        ]);
    }

    public function viewGroupMembers(Request $request, $uri)
    {
        $perPage = 6;
        $filter = $request->offsetGet('filter');
        $userId = $request->offsetGet('user_id');
        $roleId = $request->offsetGet('role_id');
        $keywords = $request->offsetGet('keywords');
        $group = Organisation::where('uri', $uri)->first();

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'  => $group->id
            ],
            [
                'created_by'    => $group->created_by,
                'group_ids'     => [$group->id]
            ]
        );

        $buttons[$group->id]['edit'] = $rightCheck;
        $buttons[$group->id]['deleteMember'] = $rightCheck;

        if ($group) {
            if ($request->has('edit_member')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_ALL,
                    [
                        'group_id'  => $group->id
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                if(empty($roleId)) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.empty_role')));
                }

                $rq = Request::create('/api/editMember', 'POST', [
                    'org_id'    => $group->id,
                    'user_id'   => $userId,
                    'role_id'   => $roleId,
                ]);
                $api = new ApiOrganisation($rq);
                $result = $api->editMember($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));
                }

                return back();
            }

            if ($request->has('delete')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_ALL,
                    [
                        'group_id'  => $group->id
                    ],
                    [
                        'group_ids' => [$group->id]
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                if ($this->delMember($userId, $group->id)) {
                    $request->session()->flash('alert-success', __('custom.delete_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.delete_error'));
                }

                return back();
            }

            if ($request->has('invite_existing')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_ALL,
                    [
                        'group_id'  => $group->id
                    ],
                    [
                        'group_ids' => [$group->id]
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $newUser = $request->offsetGet('user');
                $newRole = $request->offsetGet('role');

                $rq = Request::create('/api/addMember', 'POST', [
                    'org_id'    => $group->id,
                    'user_id'   => $newUser,
                    'role_id'   => $newRole,
                ]);
                $api = new ApiOrganisation($rq);
                $result = $api->addMember($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }

                return back();
            }

            if ($request->has('invite')) {
                $rightCheck = RoleRight::checkUserRight(
                    Module::GROUPS,
                    RoleRight::RIGHT_ALL,
                    [
                        'group_id'  => $group->id
                    ],
                    [
                        'group_ids' => [$group->id]
                    ]
                );

                if (!$rightCheck) {
                    return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
                }

                $email = $request->offsetGet('email');
                $role = $request->offsetGet('role');

                $rq = Request::create('/inviteUser', 'POST', [
                    'api_key'   => Auth::user()->api_key,
                    'data'      => [
                        'email'     => $email,
                        'org_id'    => $group->id,
                        'role_id'   => $role,
                        'generate'  => true,
                    ],
                ]);
                $api = new ApiUser($rq);
                $result = $api->inviteUser($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));
                }

                return back();
            }

            $group->logo = $this->getImageData($group->logo_data, $group->logo_mime_type, 'group');

            $criteria = ['org_id' => $group->id];

            if ($filter == 'for_approval') {
                $criteria['for_approval'] = true;
            }

            if (is_numeric($filter)) {
                $criteria['role_id'] = $filter;
            }

            if (!empty($keywords)) {
                $criteria['keywords'] = $keywords;
            }

            $criteria['records_per_page'] = $perPage;
            $criteria['page_number'] = $request->offsetGet('page', 1);

            $rq = Request::create('/api/getMembers', 'POST', $criteria);
            $api = new ApiOrganisation($rq);
            $result = $api->getMembers($rq)->getData();
            $paginationData = $this->getPaginationData(
                $result->members,
                $result->total_records,
                $request->except('page'),
                $perPage
            );

            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_group' => 1]]);
            $api = new ApiRole($rq);
            $result = $api->listRoles($rq)->getData();
            $roles = isset($result->roles) ? $result->roles : [];

            return view('user/groupMembers', [
                'class'         => 'user',
                'members'       => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'group'         => $group,
                'roles'         => $roles,
                'filter'        => $filter,
                'keywords'      => $keywords,
                'buttons'       => $buttons
            ]);
        }

        return redirect('/user/groups');
    }

    public function addGroupMembersNew(Request $request, $uri)
    {
        $group = Organisation::where('uri', $uri)->first();
        $class = 'user';

        $rightCheck = RoleRight::checkUserRight(
            Module::GROUPS,
            RoleRight::RIGHT_ALL,
            [
                'group_id'       => $group->id
            ]
        );

        if (!$rightCheck) {
            return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.access_denied')));
        }

        if ($group) {
            $rq = Request::create('/api/listRoles', 'POST', ['criteria' => ['for_group' => 1]]);
            $api = new ApiRole($rq);
            $result = $api->listRoles($rq)->getData();
            $roles = isset($result->roles) ? $result->roles : [];
            $digestFreq = UserSetting::getDigestFreq();

            if ($request->has('save')) {
                $post = [
                    'api_key'   => Auth::user()->api_key,
                    'data'      => [
                        'firstname'         => $request->offsetGet('firstname'),
                        'lastname'          => $request->offsetGet('lastname'),
                        'username'          => $request->offsetGet('username'),
                        'email'             => $request->offsetGet('email'),
                        'password'          => $request->offsetGet('password'),
                        'password_confirm'  => $request->offsetGet('password_confirm'),
                        'role_id'           => $request->offsetGet('role_id'),
                        'org_id'            => $group->id,
                        'invite'            => true,
                    ],
                ];

                $rq = Request::create('/api/addUser', 'POST', $post);
                $api = new ApiUser($rq);
                $result = $api->register($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.confirm_mail_sent'));

                    return redirect('/user/groups/members/'. $uri);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return redirect()->back()->withInput()->withErrors($result->errors);
                }
            }
        }

        return view('user/addGroupMembersNew', compact('class', 'error', 'digestFreq', 'invMail', 'roles', 'group'));
    }

    public function deleteCustomSettings(Request $request)
    {
        $id = $request->offsetGet('id');

        $rq = Request::create('/api/deleteCustomSetting', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'id'        => $id,
        ]);
        $api = new ApiCustomSettings($rq);
        $result = $api->delete($rq)->getData();

        return ['success' => $result->success];
    }

    public function groupChronology(Request $request, $uri)
    {
        $class = 'user';
        $group = Organisation::where('uri', $uri)->first();
        $group->logo = $this->getImageData($group->logo_data, $group->logo_mime_type, 'group');
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'group_uri' => $uri,
            'locale'    => $locale
        ];

        $rq = Request::create('/api/getGroupDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getGroupDetails($rq)->getData();

        if ($result->success && !empty($result->data)) {
            $params = [
                'api_key'   => \Auth::user()->api_key,
                'criteria'  => [
                    'group_ids' => [$result->data->id],
                    'locale'    => $locale
                ]
            ];

            $rq = Request::create('/api/listDataSets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDataSets($rq)->getData();

            $criteria = [
                'group_ids' => [$result->data->id]
            ];

            $objType = Module::getModules()[Module::GROUPS];
            $actObjData[$objType] = [];
            $actObjData[$objType][$group->id] = [
                'obj_id'        => $group->uri,
                'obj_name'      => $group->name,
                'obj_module'    => ultrans('custom.groups'),
                'obj_type'      => 'org',
                'obj_view'      => '/user/groups/view/'. $group->uri,
                'parent_obj_id' => ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModules()[Module::DATA_SETS];
                $objTypeRes = Module::getModules()[Module::RESOURCES];
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => ultrans('custom.dataset'),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/user/groups/'. $group->uri .'/dataset/'. $dataset->uri,
                        'parent_obj_id' => ''
                    ];

                    if (!empty($dataset->resource)) {
                        foreach ($dataset->resource as $resource) {
                            $criteria['resource_uris'][] = $resource->uri;
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => ultrans('custom.resource'),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/user/groups/'. $group->uri .'/resource/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => ultrans('custom.dataset'),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri
                            ];
                        }
                    }
                }
            }

            $paginationData = [];
            $actTypes = [];

            if (!empty($criteria)) {
                $rq = Request::create('/api/listActionTypes', 'GET', ['locale' => $locale, 'publicOnly' => true]);
                $api = new ApiActionsHistory($rq);
                $res = $api->listActionTypes($rq)->getData();

                if ($res->success && !empty($res->types)) {
                    $linkWords = ActionsHistory::getTypesLinkWords();

                    foreach ($res->types as $type) {
                        $actTypes[$type->id] = [
                            'name'     => $type->name,
                            'linkWord' => $linkWords[$type->id]
                        ];
                    }

                    $criteria['actions'] = array_keys($actTypes);
                    $perPage = 6;
                    $params = [
                        'criteria'         => $criteria,
                        'records_per_page' => $perPage,
                        'page_number'      => !empty($request->page) ? $request->page : 1,
                    ];

                    $rq = Request::create('/api/listActionHistory', 'POST', $params);
                    $api = new ApiActionsHistory($rq);
                    $res = $api->listActionHistory($rq)->getData();
                    $history = isset($res->actions_history) ? $res->actions_history : [];

                    $paginationData = $this->getPaginationData($history, $res->total_records, [], $perPage);
                }
            }

            return view(
                'user/groupChronology',
                [
                    'class'          => $class,
                    'organisation'   => $group,
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect('user/groups');
    }

    public function orgChronology(Request $request, $uri)
    {
        $class = 'user';
        $org = Organisation::where('uri', $uri)->first();
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
            'org_uri'   => $uri,
            'locale'    => $locale
        ];

        $rq = Request::create('/api/getOrganisationDetails', 'POST', $params);
        $api = new ApiOrganisation($rq);
        $result = $api->getOrganisationDetails($rq)->getData();

        if ($result->success && !empty($result->data)) {
            $params = [
                'api_key'   => \Auth::user()->api_key,
                'criteria' => [
                    'org_ids' => [$result->data->id],
                    'locale'  => $locale
                ]
            ];

            $rq = Request::create('/api/listDataSets', 'POST', $params);
            $api = new ApiDataSet($rq);
            $res = $api->listDataSets($rq)->getData();

            $criteria = [
                'org_ids' => [$org->id]
            ];

            $objType = Module::getModules()[Module::ORGANISATIONS];
            $actObjData[$objType] = [];
            $actObjData[$objType][$org->id] = [
                'obj_id'        => $org->uri,
                'obj_name'      => $org->name,
                'obj_module'    => ultrans('custom.organisations'),
                'obj_type'      => 'org',
                'obj_view'      => '/user/organisations/view/'. $org->uri,
                'parent_obj_id' => ''
            ];

            if (isset($res->success) && $res->success && !empty($res->datasets)) {
                $objType = Module::getModules()[Module::DATA_SETS];
                $objTypeRes =Module::getModules()[Module::RESOURCES];
                $actObjData[$objType] = [];

                foreach ($res->datasets as $dataset) {
                    $criteria['dataset_ids'][] = $dataset->id;
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id'        => $dataset->uri,
                        'obj_name'      => $dataset->name,
                        'obj_module'    => ultrans('custom.dataset'),
                        'obj_type'      => 'dataset',
                        'obj_view'      => '/user/organisations/dataset/view/'. $dataset->uri,
                        'parent_obj_id' => ''
                    ];

                    if (!empty($dataset->resource)) {
                        foreach ($dataset->resource as $resource) {
                            $criteria['resource_uris'][] = $resource->uri;
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'            => $resource->uri,
                                'obj_name'          => $resource->name,
                                'obj_module'        => ultrans('custom.resource'),
                                'obj_type'          => 'resource',
                                'obj_view'          => '/user/organisations/'. $org->uri .'/resource/'. $resource->uri,
                                'parent_obj_id'     => $dataset->uri,
                                'parent_obj_name'   => $dataset->name,
                                'parent_obj_module' => ultrans('custom.dataset'),
                                'parent_obj_type'   => 'dataset',
                                'parent_obj_view'   => '/data/view/'. $dataset->uri
                            ];
                        }
                    }
                }
            }

            $paginationData = [];
            $actTypes = [];

            if (!empty($criteria)) {
                $rq = Request::create('/api/listActionTypes', 'GET', ['locale' => $locale, 'publicOnly' => true]);
                $api = new ApiActionsHistory($rq);
                $res = $api->listActionTypes($rq)->getData();

                if ($res->success && !empty($res->types)) {
                    $linkWords = ActionsHistory::getTypesLinkWords();

                    foreach ($res->types as $type) {
                        $actTypes[$type->id] = [
                            'name'     => $type->name,
                            'linkWord' => $linkWords[$type->id]
                        ];
                    }

                    $criteria['actions'] = array_keys($actTypes);
                    $perPage = 6;
                    $params = [
                        'criteria'         => $criteria,
                        'records_per_page' => $perPage,
                        'page_number'      => !empty($request->page) ? $request->page : 1,
                    ];

                    $rq = Request::create('/api/listActionHistory', 'POST', $params);
                    $api = new ApiActionsHistory($rq);
                    $res = $api->listActionHistory($rq)->getData();
                    $res->actions_history = isset($res->actions_history) ? $res->actions_history : [];
                    $paginationData = $this->getPaginationData($res->actions_history, $res->total_records, [], $perPage);
                }
            }

            return view(
                'user/orgChronology',
                [
                    'class'          => $class,
                    'organisation'   => $result->data,
                    'chronology'     => !empty($paginationData['items']) ? $paginationData['items'] : [],
                    'pagination'     => !empty($paginationData['paginate']) ? $paginationData['paginate'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => $actTypes,
                ]
            );
        }

        return redirect('user/groups');
    }

    public function removeSignal(Request $request)
    {
        if ($request->has('remove_signal')) {
            $params['signal_id'] = $request->offsetGet('signal');
            $params['data']['status'] = \App\Signal::STATUS_PROCESSED;
            $rq = Request::create('/api/editSignal', 'POST', $params);
            $api = new ApiSignal($rq);
            $response = $api->editSignal($rq)->getData();
        }

        if ($response->success) {
            $request->session()->flash('alert-success', __('custom.edit_success'));
        } else {
            $request->session()->flash('alert-danger', __('custom.edit_error'));
        }

        return back();
    }
}
