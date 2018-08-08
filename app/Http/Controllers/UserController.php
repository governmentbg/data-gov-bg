<?php

namespace App\Http\Controllers;

use App\User;
use App\Locale;
use App\DataSet;
use App\UserSetting;
use App\Organisation;
use App\UserToOrgRole;
use App\ActionsHistory;
use App\CustomSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Api\RoleController as ApiRole;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\LocaleController as ApiLocale;
use App\Http\Controllers\Api\DataSetController as ApiDataSets;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\TermsOfUseRequestController as ApiTermsOfUseRequest;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisations;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;

class UserController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getTransFields()
    {
        return [
            [
                'label'    => 'custom.label_name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.activity',
                'name'     => 'activity_info',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.contact',
                'name'     => 'contacts',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['custom.title', 'custom.value'],
                'name'     => 'custom_fields',
                'type'     => 'text',
                'view'     => 'translation_custom',
                'val'      => ['key', 'value'],
                'required' => false,
            ],
        ];
    }

     /**
     * Function for getting an array of translatable fields for datasets
     *
     * @return array of fields
     */
    public static function getDatasetTransFields()
    {
        return [
            [
                'label'    => 'custom.label_name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'description',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.label',
                'name'     => 'tags',
                'type'     => 'text',
                'view'     => 'translation_tags',
                'required' => false,
            ],
            [
                'label'    => 'custom.sla_agreement',
                'name'     => 'sla',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['custom.title', 'custom.value'],
                'name'     => 'custom_fields',
                'type'     => 'text',
                'view'     => 'translation_custom',
                'val'      => ['key', 'value'],
                'required' => false,
            ],
        ];
    }

    /**
     * Function for getting an array of translatable fields for groups
     *
     * @return array of fields
     */
    public static function getGroupTransFields()
    {
        return [
            [
                'label'    => 'custom.label_name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['custom.title', 'custom.value'],
                'name'     => 'custom_fields',
                'type'     => 'text',
                'view'     => 'translation_custom',
                'val'      => ['key', 'value'],
                'required' => false,
            ],
        ];
    }

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
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['created_by'] = \Auth::user()->id;
        $params['records_per_page'] = '10';
        $params['page_number'] = '1';

        $rq = Request::create('/api/listDataSets', 'POST', $params);
        $api = new ApiDataSets($rq);
        $datasets = $api->listDataSets($rq)->getData();

        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', 'Наборът беше успешно изтрит!');
            } else {
                $request->session()->flash('alert-danger', 'Неуспешно изтриване на набор от данни!');
            }

            return back();
        }

        return view('user/datasets', ['class' => 'user', 'datasets' => $datasets->datasets, 'activeMenu' => 'dataset']);
    }

     /**
     * Displays a list of datasets created by the logged user
     * for the given organisation
     *
     * @param Request $request
     * @return view with datasets
     *
     */
    public function datasetSearch(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect('/user/datasets');
        }

        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'         => ['keywords' => $search],
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $searchRq = Request::create('/api/searchDataSet', 'POST', $params);
        $api = new ApiDataSets($searchRq);
        $result = $api->searchDataSet($searchRq)->getData();
        $datasets = !empty($result->datasets) ? $result->datasets : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($datasets, $count, $getParams, $perPage);

        return view(
            'user/datasets',
            [
                'class'         => 'user',
                'datasets'      => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search
            ]
        );
    }

    public function orgDatasets(Request $request) {
        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];
        $userOrgIds = UserToOrgRole::where('user_id', \Auth::user()->id)->pluck('org_id')->toArray();
        $dataSetIds = DataSet::whereIn('org_id', $userOrgIds)->pluck('id')->toArray();

        if (!empty($dataSetIds)) {
            $params['criteria']['dataset_ids'] = $dataSetIds;
            $rq = Request::create('/api/listDataSets', 'POST', $params);
            $api = new ApiDataSets($rq);
            $datasets = $api->listDataSets($rq)->getData();
            $paginationData = $this->getPaginationData($datasets->datasets, $datasets->total_records, [], $perPage);
        } else {
            $paginationData = $this->getPaginationData([], 0, [], $perPage);
        }

        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', 'Наборът беше успешно изтрит!');
            } else {
                $request->session()->flash('alert-danger', 'Неуспешно изтриване на набор от данни!');
            }

            return back();
        }

        return view(
            'user/orgDatasets',
            [
                'class'         => 'user',
                'datasets'      => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'activeMenu'    => 'organisation'
            ]
        );
    }

    /**
     * Displays detail information for a given dataset
     * created by the given user
     *
     * @param Request $request
     * @return view with dataset information
     *
     */
    public function datasetView(Request $request)
    {
        $params['dataset_uri'] = $request->uri;

        $detailsReq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSets($detailsReq);
        $dataset = $api->getDataSetDetails($detailsReq)->getData();
        // prepera request for resources
        unset($params['dataset_uri']);
        $params['criteria']['dataset_uri'] = $request->uri;

        $resourcesReq = Request::create('/api/listResources', 'POST', $params);
        $apiResources = new ApiResource($resourcesReq);
        $resources = $apiResources->listResources($resourcesReq)->getData();

        return view('user/datasetView', [
            'class'     => 'user',
            'dataset'   => $dataset->data,
            'resources' => $resources->resources
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
    public function orgDatasetView(Request $request)
    {
        $params['dataset_uri'] = $request->uri;

        $detailsReq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSets($detailsReq);
        $dataset = $api->getDataSetDetails($detailsReq)->getData();
        unset($params['dataset_uri']);
        $params['criteria']['dataset_uri'] = $request->uri;

        $resourcesReq = Request::create('/api/listResources', 'POST', $params);
        $apiResources = new ApiResource($resourcesReq);
        $resources = $apiResources->listResources($resourcesReq)->getData();

        if (isset($dataset->data->name)) {

            if (
                $dataset->data->updated_by == $dataset->data->created_by
                && !is_null($dataset->data->created_by)
            ) {
                $username = User::find($dataset->data->created_by)->value('username');
                $dataset->data->updated_by = $username;
                $dataset->data->created_by = $username;
            } else {
                $dataset->data->updated_by = is_null($dataset->data->updated_by) ? null : User::find($dataset->data->updated_by)->value('username');
                $dataset->data->created_by = is_null($dataset->data->created_by) ? null : User::find($dataset->data->created_by)->value('username');
            }
        }

        return view(
            'user/orgDatasetView',
            [
                'class'      => 'user',
                'dataset'    => $dataset->data,
                'resources'  => $resources->resources,
                'activeMenu' => 'organisation'
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
    public function datasetDelete($uri)
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['dataset_uri'] = $uri;

        $request = Request::create('/api/deleteDataSet', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->deleteDataSet($request)->getData();

        if ($datasets->success) {
            return true;
        }

        return false;
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
    public function datasetCreate(Request $request, DataSet $datasetModel)
    {
        $visibilityOptions = $datasetModel->getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $errors = [];
        $data = $request->all();

        if ($data) {
            // prepare post data for API request
            $data['locale'] = \LaravelLocalization::getCurrentLocale();
            if (isset($data['tags'])) {
                foreach ($data['tags'] as $locale => $tags) {
                    $data['tags'][$locale] = explode(',', $tags);
                }
            }

            if (!empty($data['group_id'])) {
                $groupId = $data['group_id'];
            }

            unset($data['group_id']);

            // make request to API
            $params['api_key'] = \Auth::user()->api_key;
            $params['data'] = $data;
            $savePost = Request::create('/api/addDataSet', 'POST', $params);
            $api = new ApiDataSets($savePost);
            $result = $api->addDataSet($savePost)->getData();

            if ($result->success) {
                // connect data set to group
                if (isset($groupId)) {
                    $gropupParams['group_id'] = $groupId;
                    $gropupParams['data_set_uri'] = $result->uri;
                    $addGroup = Request::create('/api/addDataSetToGroup', 'POST', $gropupParams);
                    $resultGroup = $api->addDataSetToGroup($addGroup)->getData();
                }

                $request->session()->flash('alert-success', 'Промените бяха успешно запазени!');
                return redirect()->route('datasetView', ['uri' => $result->uri]);
            } else {

                foreach ($result->errors as $field => $msg) {
                    $errors[substr($field, strpos($field, ".") + 1)] = $msg[0];
                }

                $request->flash();
                $request->session()->flash('errors', $errors);
                $request->session()->flash('alert-danger', $result->error->message);
            }
        }

        return view('user/datasetCreate', [
            'class'         => 'user',
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'fields'        => self::getDatasetTransFields(),
        ])->with('errors', $errors);
    }

    /**
     * Returns a view for editing a dataset
     *
     * @param Request $request
     * @param Dataset $datasetModel
     *
     * @return view for edditing a dataset
     */
    public function datasetEdit(Request $request, DataSet $datasetModel)
    {
        $visibilityOptions = $datasetModel->getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $errors = [];
        $data = $request->all();

        $params['dataset_uri'] = $request->uri;
        $detailsReq = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSets($detailsReq);
        $dataset = $api->getDataSetDetails($detailsReq)->getData();

        $datasetData = $dataset->data;

        return view('user/datasetEdit', [
            'class'         => 'user',
            'fields'        => $dataset,
            'visibilityOpt' => $visibilityOptions,
            'categories'    => $categories,
            'termsOfUse'    => $termsOfUse,
            'organisations' => $organisations,
            'groups'        => $groups,
            'fields'        => self::getDatasetTransFields(),
        ])->with('errors', $errors);
    }

    public function translate()
    {
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
            if ($request->has('save')) {
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
                    $request->session()->flash('alert-warning', 'Електронната поща ще се промени, когато я потвърдите!');
                }
            }

            if ($request->has('change_pass')) {
                $oldPass = $request->offsetGet('old_password');

                if (Hash::check($oldPass, $user['password'])) {
                    $saveData = [
                        'api_key'   => $user['api_key'],
                        'id'        => $user['id'],
                        'data'      => [
                            'password'          => $request->offsetGet('password'),
                            'password_confirm'  => $request->offsetGet('password_confirm'),
                        ],
                    ];
                } else {
                    $request->session()->flash('alert-danger', 'Грешна парола!');
                }
            }

            if ($request->has('generate_key')) {
                $data = [
                    'api_key'   => $user['api_key'],
                    'id'        => $user['id'],
                ];

                $newKey = Request::create('api/generateAPIKey', 'POST', $data);
                $api = new ApiUser($newKey);
                $result = $api->generateAPIKey($newKey)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', 'Успешно генериран АПИ ключ!');

                    return back();
                } else {
                    $request->session()->flash('alert-danger', 'Възникна грешка при генериране на АПИ ключ!');
                }
            }

            if ($request->has('delete')) {
                $data = [
                    'api_key'   => $user['api_key'],
                    'id'        => $user['id'],
                ];

                $delUser = Request::create('api/deleteUser', 'POST', $data);
                $api = new ApiUser($delUser);
                $result = $api->deleteUser($delUser)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', 'Успешно изтрит потребител!');

                    return redirect('/');
                } else {
                    $request->session()->flash('alert-danger', 'Възникна грешка при изтриване на потребител!');
                }
            }

            if (!empty($saveData)) {
                $editPost = Request::create('api/editUser', 'POST', $saveData);
                $api = new ApiUser($editPost);
                $result = $api->editUser($editPost)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', 'Промените бяха успешно запазени!');

                    return back();
                } else {
                    $request->session()->flash('alert-danger', 'Промените не бяха запазени!');

                    $error = $result->errors;
                }
            }

            return view('user/settings', compact('class', 'user', 'digestFreq', 'localeList', 'error', 'message'));
        }

        return redirect('/');
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
        $params = [];
        $error = [];
        $invMail = $request->offsetGet('mail');

        $digestFreq = UserSetting::getDigestFreq();

        if ($request->isMethod('post')) {
            $params = $request->all();

            $req = Request::create('/register', 'POST', ['invite' => !empty($invMail), 'data' => $params]);
            $api = new ApiUser($req);
            $result = $api->register($req)->getData();

            if ($result->success) {
                $user = User::where('api_key', $result->api_key)->first();

                if ($request->has('add_org')) {
                    $key = $user->username;

                    return redirect()->route(
                        'orgRegistration', compact('key', 'message')
                    );
                }
                $request->session()->flash('alert-success', 'Пратено е съобщение за потвърждение, на посоченият от вас адрес.');

                return redirect('login');
            } else {
                $error = $result->errors;
            }
        }

        return view('user/registration', compact('class', 'error', 'digestFreq', 'invMail'));
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
        $error = [];
        $username = $request->offsetGet('key');
        $orgTypes = Organisation::getPublicTypes();

        if (!empty($username)) {
            if ($request->isMethod('post')) {
                $user = User::where('username', $username)->first();
                $params = $request->all();
                $apiKey = $user->api_key;

                if (!empty($params['logo'])) {
                    try {
                        $img = \Image::make($params['logo']);
                    } catch (NotReadableException $ex) {
                        Log::error($ex->getMessage());
                    }

                    if (!empty($img)) {
                        $img->resize(300, 200);
                        $params['logo_filename'] = $params['logo']->getClientOriginalName();
                        $params['logo_mimetype'] = $img->mime();
                        $params['logo_data'] = $img->encode('data-url');

                        unset($params['logo']);
                    }
                }

                $params['locale'] = \LaravelLocalization::getCurrentLocale();

                if (empty($params['type'])) {
                    $params['type'] = Organisation::TYPE_CIVILIAN;
                }

                $req = Request::create('/addOrganisation', 'POST', ['api_key' => $apiKey,'data' => $params]);
                $api = new ApiOrganisations($req);
                $result = $api->addOrganisation($req)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', 'Успешно създадена организация!');

                    return redirect('login');
                } else {
                    $error = $result->errors;
                }
            }
        }

        return view('user/orgRegistration', compact('class', 'error', 'orgTypes'));
    }

    public function createLicense()
    {
    }

    public function resourceView()
    {
    }

    /**
     * Loads a view for browsing organisational resources
     *
     * @param Request $request
     *
     * @return view for browsing org resources
     */
    public function orgResourceView(Request $request)
    {
        $uri = $request->uri;

        $resourcesReq = Request::create('/api/listResources', 'POST', ['criteria' => ['resource_uri' => $uri]]);
        $apiResources = new ApiResource($resourcesReq);
        $resources = $apiResources->listResources($resourcesReq)->getData();
        $resource = !empty($resources->resources) ? $resources->resources[0] : null;

        if (!is_null($resource) && isset($resource->name)) {

            if (
                $resource->updated_by == $resource->created_by
                && !is_null($resource->created_by)
            ) {
                $username = User::find($resource->created_by)->value('username');
                $resource->updated_by = $username;
                $resource->created_by = $username;
            } else {
                $resource->updated_by = is_null($resource->updated_by) ? null : User::find($resource->updated_by)->value('username');
                $resource->created_by = is_null($resource->created_by) ? null : User::find($resource->created_by)->value('username');
            }
        }

        return view('user/orgResourceView', ['class' => 'user', 'resource' => $resource, 'activeMenu' => 'organisation']);
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
        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/getUserOrganisations', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->getUserOrganisations($request)->getData();

        $paginationData = $this->getPaginationData($result->organisations, $result->total_records, [], $perPage);

        return view(
            'user/organisations',
            [
                'class'         => 'user',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate']
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
    public function deleteOrg(Request $request)
    {
        $params = [
            'api_key' => \Auth::user()->api_key,
            'org_id'  => $request->org_id,
        ];

        $request = Request::create('/api/deleteOrganisation', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->deleteOrganisation($request)->getData();

        return !$result->success
            ? redirect('/user/organisations')->with('result', $result)
            : redirect('/user/organisations')->with('success', 'Организацията беше изтрита успешно!');
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

        $request = Request::create('/api/searchOrganisations', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->searchOrganisations($request)->getData();
        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

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
                'search'        => $search
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

        $request = Request::create('/api/searchDataset', 'POST', $params);
        $api = new ApiDatasets($request);
        $result = $api->searchDataset($request)->getData();
        $datasets = !empty($result->datasets) ? $result->datasets : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

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
                'activeMenu' => 'organisation'
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
        $post = [
            'data' => $request->all()
        ];

        if (!empty($post['data']['logo'])) {
            try {
                $img = \Image::make($post['data']['logo']);

                $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                $post['data']['logo_mimetype'] = $img->mime();
                $post['data']['logo_data'] = file_get_contents($post['data']['logo']);

                unset($post['data']['logo']);
            } catch (NotReadableException $ex) {
                Log::error($ex->getMessage());
            }
        }

        $post['data']['description'] = $post['data']['descript'];
        $request = Request::create('/api/addOrganisation', 'POST', $post);
        $api = new ApiOrganisations($request);
        $result = $api->addOrganisation($request)->getData();

        if ($result->success) {
            session()->flash('alert-success', 'Промените бяха запазени успешно!');
        } else {
            session()->flash(
                'alert-danger',
                isset($result->error) ? $result->error->message : __('Add organisation failure!')
            );
        }

        return $result->success
            ? redirect()->route('userOrgView', ['org_id' => $result->org_id])
            : redirect('user/organisations/register')->withInput(Input::all());
    }

     /**
     * Loads a view for viewing an organisation
     *
     * @param Request $request
     *
     * @return view to view the a registered organisation
     */
    public function viewOrg(Request $request)
    {
        $uri = $request->uri;
        $orgId = $request->has('org_id') ? $request->org_id : Organisation::where('uri', $uri)->value('id');
        $request = Request::create('/api/getOrganisationDetails', 'POST', ['org_id' => $orgId]);
        $api = new ApiOrganisations($request);
        $result = $api->getOrganisationDetails($request)->getData();

        return view('user/orgView', ['class' => 'user', 'organisation' => $result->data]);
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
        \Session::flush();
        $class = 'user';
        $hash = $request->offsetGet('hash');
        $mail = $request->offsetGet('mail');

        if ($hash && $mail) {
            $user = User::where('hash_id', $request->offsetGet('hash'))->first();

            if ($user) {
                $user->email = $request->offsetGet('mail');

                try {
                    $user->save();
                    $request->session()->flash('alert-success', 'Успешно променихте електронната си поща');

                    return redirect('login');
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }

            if ($request->has('generate')) {
                $mailData = [
                    'user'  => $user->firstname,
                    'hash'  => $user->hash_id,
                    'mail'  => $mail
                ];

                Mail::send('mail/emailChangeMail', $mailData, function ($m) use ($mailData) {
                    $m->from('info@finite-soft.com', 'Open Data');
                    $m->to($mailData['mail'], $mailData['user'])->subject('Смяна на екектронен адрес!');
                });
            }
        }

        return view('confirmError', compact('class'));
    }

    /**
     * Loads a view for registering an organisations
     *
     * @return view login on success or error on fail
     */
    public function showOrgRegisterForm() {

        return view('user/orgRegister', ['class' => 'user', 'fields' => self::getTransFields()]);
    }

    /**
     * Loads a view for editing an organisation
     *
     * @param Request $request
     *
     * @return view for editing  org details
     */
    public function editOrg(Request $request)
    {
        if (isset($request->view)) {
            $orgModel = Organisation::with('CustomSetting')->find($request->org_id)->loadTranslations();
            $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
            $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

            return view(
                'user/orgEdit',
                [
                    'class'     => 'user',
                    'model'     => $orgModel,
                    'withModel' => $customModel,
                    'fields'    => self::getTransFields()
                ]
            );
        }

        $post = [
            'data'   => $request->all(),
            'org_id' => $request->org_id
        ];

        if (!empty($post['data']['logo'])) {
            try {
                $img = \Image::make($post['data']['logo']);

                $post['data']['logo_filename'] = $post['data']['logo']->getClientOriginalName();
                $post['data']['logo_mimetype'] = $img->mime();
                $post['data']['logo_data'] = file_get_contents($post['data']['logo']);

                unset($post['data']['logo']);
            } catch (NotReadableException $ex) {
                Log::error($ex->getMessage());
            }
        }

        $post['data']['locale'] = \LaravelLocalization::getCurrentLocale();
        $post['data']['description'] = $post['data']['descript'];
        $request = Request::create('/api/editOrganisation', 'POST', $post);
        $api = new ApiOrganisations($request);
        $result = $api->editOrganisation($request)->getData();
        $errors = !empty($result->errors) ? $result->errors : [];

        $orgModel = Organisation::with('CustomSetting')->find($request->org_id)->loadTranslations();
        $customModel = CustomSetting::where('org_id', $orgModel->id)->get()->loadTranslations();
        $orgModel->logo = $this->getImageData($orgModel->logo_data, $orgModel->logo_mime_type);

        if ($result->success) {
            session()->flash('alert-success', 'Промените бяха запазени успешно!');
        } else {
            session()->flash(
                'alert-danger',
                isset($result->error) ? $result->error->message : __('Add organisation failure!')
            );
        }

        return !$result->success
            ? view(
                'user/orgEdit',
                [
                    'class'     => 'user',
                    'model'     => $orgModel,
                    'withModel' => $customModel,
                    'fields'    => self::getTransFields(),
                    'result'    => $result
                ]
            )
            : view(
                'user/orgEdit',
                [
                    'class'     => 'user',
                    'model'     => $orgModel,
                    'withModel' => $customModel,
                    'fields'    => self::getTransFields()
                ]
            );
    }

    /**
     * Prepares an array of categories
     *
     * @return array categories
     */
    private function prepareMainCategories()
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['active'] = 1;
        $request = Request::create('/api/listMainCategories', 'POST', $params);
        $api = new ApiCategory($request);
        $result = $api->listMainCategories($request)->getData();
        $categories = [];

        foreach ($result->categories as $row) {
            $categories[$row->id] = $row->name;
        }

        return $categories;
    }

    /**
     * Prepares an array of terms of use
     *
     * @return array termsOfUse
     */
    private function prepareTermsOfUse()
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['active'] = 1;
        $request = Request::create('/api/listTermsOfUse', 'POST', $params);
        $api = new ApiTermsOfUse($request);
        $result = $api->listTermsOfUse($request)->getData();
        $termsOfUse = [];

        foreach ($result->terms_of_use as $row) {
            $termsOfUse[$row->id] = $row->name;
        }

        return $termsOfUse;
    }

    /**
     * Prepares an array of organisations
     *
     * @return array organisations
     */
    private function prepareOrganisations()
    {
        $params['criteria']['user_id'] = \Auth::user()->id;
        $request = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->listOrganisations($request)->getData();
        $organisations = [];

        foreach ($result->organisations as $row) {
            $organisations[$row->id] = $row->name;
        }

        return $organisations;
    }

    /**
     * Prepares an array of groups
     *
     * @return array groups
     */
    private function prepareGroups()
    {
        $params['criteria']['user_id'] = \Auth::user()->id;
        $request = Request::create('/api/listGroups', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->listGroups($request)->getData();
        $groups = [];

        foreach ($result->groups as $row) {
            $groups[$row->id] = $row->name;
        }

        return $groups;
    }

    /**
     * Generates an email with user credential for an invited user or
     * sends an invite email
     *
     * @param Request $request
     *
     * @return view on success or fail with corresponding messages
     */
    public function inviteUser(Request $request)
    {
        $class = 'user';
        $invData = $request->all();

        $roleReqData = [
            'api_key'   => Auth::user()->api_key,
            'criteria'  => [
                'active'    => 1,
            ],
        ];

        $roleReq = Request::create('/api/listRoles', 'POST', $roleReqData);
        $roleApi = new ApiRole($roleReq);
        $roleResult = $roleApi->listRoles($roleReq)->getData();

        if ($roleResult->success) {
            $roleList = $roleResult->roles;
        } else {
            $request->session()->flash('alert-danger', 'Не успяхме да се свържем с РОЛИ!');

            return back();
        }

        if ($request->has('generate')) {
            $invData['api_key'] = Auth::user()->api_key;

            $invRequset = Request::create('/api/inviteUser', 'POST', ['data' => $invData]);
            $api = new ApiUser($invRequset);
            $result = $api->inviteUser($invRequset)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', 'Успешно изпращане на покана!');
            } else {
                foreach ($result->errors as $key => $msg) {
                    $request->session()->flash('alert-danger', $msg[0]);
                }
            }
        }

        if ($request->has('send')) {
            $mailData = [
                'user'  => Auth::user()->firstname .' '. Auth::user()->lastname,
                'mail'  => $invData['email'],
            ];

            Mail::send('mail/inviteMail', $mailData, function ($m) use ($invData) {
                $m->from('info@finite-soft.com', 'Open Data');
                $m->to($invData['email'])->subject('Получихте покана за opendata.bg!');
            });

            if (count(Mail::failures()) > 0) {
                $request->session()->flash('alert-danger', 'Неуспешно изпращане на покана!');
            } else {
                $request->session()->flash('alert-success', 'Успешно изпратена покана!');
            }
        }

        return view('/user/invite', compact('class', 'roleList'));
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
                $request->session()->flash('alert-success', 'Моля попълнете вашите данни');

                return redirect()->route('settings');
            }
        } else {
            $request->session()->flash('alert-danger', 'Грешни параметри на заявка');

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
        $user = User::find(Auth::id());
        if ($user) {
            $filter = $request->offsetGet('filter');
            $objIdFilter = $request->offsetGet('objId');
            $filters = $this->getNewsFeedFilters();

            $criteria = [];
            $actObjData = [];

            $params = [
                'api_key' => $user->api_key,
                'id'      => $user->id
            ];
            $rq = Request::create('/api/getUserSettings', 'POST', $params);
            $api = new ApiUser($rq);
            $result = $api->getUserSettings($rq)->getData();
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
                        if ($filter == 'organisations' && $followProp != 'org_id' ||
                            $filter == 'groups' && $followProp != 'group_id' ||
                            $filter == 'categories' && $followProp != 'category_id' ||
                            $filter == 'tags' && $followProp != 'tag_id' ||
                            $filter == 'users' && $followProp != 'follow_user_id') {
                            continue;
                        }
                        if ($followId) {
                            $userFollows[$followProp][] = $followId;
                        }
                    }
                }

                $locale = \LaravelLocalization::getCurrentLocale();
                if (!empty($userFollows['org_id'])) {
                    $params = [
                        'criteria' => ['org_ids' => $userFollows['org_id'], 'locale' => $locale]
                    ];
                    $rq = Request::create('/api/listOrganisations', 'POST', $params);
                    $api = new ApiOrganisations($rq);
                    $res = $api->listOrganisations($rq)->getData();
                    if (isset($res->success) && $res->success == 1 && !empty($res->organisations)) {
                        $objType = ActionsHistory::MODULE_NAMES[2];
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
                            $actObjData[$objType][$org->id] = [
                                'obj_id'   => $org->id,
                                'obj_name' => $org->name,
                                'obj_type' => 'org',
                                'obj_view' => '/organisation/profile',
                                'parent_obj_id' => ''
                            ];
                            $params = [
                                'criteria' => ['org_id' => $org->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }
                if (!empty($userFollows['group_id'])) {
                    $params = [
                        'criteria' => ['group_ids' => $userFollows['group_id'], 'locale' => $locale]
                    ];
                    $rq = Request::create('/api/listGroups', 'POST', $params);
                    $api = new ApiOrganisations($rq);
                    $res = $api->listGroups($rq)->getData();
                    if (isset($res->success) && $res->success == 1 && !empty($res->groups)) {
                        $objType = ActionsHistory::MODULE_NAMES[3];
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
                            $actObjData[$objType][$group->id] = [
                                'obj_id'   => $group->id,
                                'obj_name' => $group->name,
                                'obj_type' => 'group',
                                'obj_view' => '/group/profile',
                                'parent_obj_id' => ''
                            ];
                            $params = [
                                'criteria' => ['group_id' => $group->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }
                if (!empty($userFollows['category_id'])) {
                    $params = [
                        'criteria' => ['category_ids' => $userFollows['category_id'], 'locale' => $locale]
                    ];
                    $rq = Request::create('/api/listMainCategories', 'POST', $params);
                    $api = new ApiCategory($rq);
                    $res = $api->listMainCategories($rq)->getData();
                    if (isset($res->success) && $res->success == 1 && !empty($res->categories)) {
                        $objType = ActionsHistory::MODULE_NAMES[0];
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
                            $actObjData[$objType][$category->id] = [
                                'obj_id'   => $category->id,
                                'obj_name' => $category->name,
                                'obj_type' => 'category',
                                'obj_view' => '',
                                'parent_obj_id' => ''
                            ];
                            $params = [
                                'criteria' => ['category_id' => $category->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }
                if (!empty($userFollows['tag_id'])) {
                    $params = [
                        'criteria' => ['tag_ids' => $userFollows['tag_id'], 'locale' => $locale]
                    ];
                    $rq = Request::create('/api/listTags', 'POST', $params);
                    $api = new ApiCategory($rq);
                    $res = $api->listTags($rq)->getData();
                    if (isset($res->success) && $res->success == 1 && !empty($res->tags)) {
                        $objType = ActionsHistory::MODULE_NAMES[1];
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
                            $actObjData[$objType][$tag->id] = [
                                'obj_id'   => $tag->id,
                                'obj_name' => $tag->name,
                                'obj_type' => 'tag',
                                'obj_view' => '',
                                'parent_obj_id' => ''
                            ];
                            $params = [
                                'criteria' => ['tag_id' => $tag->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }
                if (!empty($userFollows['follow_user_id'])) {
                    $params = [
                        'criteria' => ['user_ids' => $userFollows['follow_user_id']]
                    ];
                    $rq = Request::create('/api/listUsers', 'POST', $params);
                    $api = new ApiUser($rq);
                    $res = $api->listUsers($rq)->getData();
                    if (isset($res->success) && $res->success == 1 && !empty($res->users)) {
                        $objType = ActionsHistory::MODULE_NAMES[4];
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
                            $actObjData[$objType][$followUser->id] = [
                                'obj_id'   => $followUser->id,
                                'obj_name' => $followUser->firstname .' '. $followUser->lastname,
                                'obj_type' => 'user',
                                'obj_view' => '/user/profile',
                                'parent_obj_id' => ''
                            ];
                            $params = [
                                'criteria' => ['created_by' => $followUser->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                        }
                    }
                }
                if (!empty($userFollows['dataset_id'])) {
                    $params = [
                        'criteria' => ['dataset_ids' => $userFollows['dataset_id'], 'locale' => $locale]
                    ];
                    $this->prepareNewsFeedDatasets($params, $criteria, $actObjData, $filters, $filter, $objIdFilter);
                }
            }

            // user profile actions
            if (!isset($filters[$filter])) {
                $objType = ActionsHistory::MODULE_NAMES[4];
                $actObjData[$objType] = [
                    $user->id => [
                        'obj_id'   => $user->id,
                        'obj_name' => $user->firstname .' '. $user->lastname,
                        'obj_type' => 'user',
                        'obj_view' => '/user/profile',
                        'parent_obj_id' => ''
                    ]
                ];
                $criteria['user_ids'][] = $user->id;
            }

            $paginationData = [];
            if (!empty($criteria)) {
                $perPage = 5;
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

            return view(
                'user/newsFeed',
                [
                    'class'          => 'user',
                    'actionsHistory' => !empty($paginationData) ? $paginationData['items'] : [],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => ActionsHistory::getTypes(),
                    'pagination'     => !empty($paginationData) ? $paginationData['paginate'] : [],
                    'filterData'     => isset($filters[$filter]) ? $filters[$filter] : [],
                    'filter'         => $filter,
                    'objIdFilter'    => $objIdFilter
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
        $rq = Request::create('/api/listDataSets', 'POST', $params);
        $api = new ApiDataSets($rq);
        $res = $api->listDataSets($rq)->getData();
        if (isset($res->success) && $res->success == 1 && !empty($res->datasets)) {
            $objType = ActionsHistory::MODULE_NAMES[5];
            if (!isset($actObjData[$objType])) {
                $actObjData[$objType] = [];
            }
            foreach ($res->datasets as $dataset) {
                if (!isset($actObjData[$objType][$dataset->id])) {
                    if ($dataset->org_id) {
                        $params = [
                            'org_id' => $dataset->org_id,
                        ];
                        $rq = Request::create('/api/getOrganisationDetails', 'GET', $params);
                        $api = new ApiOrganisations($rq);
                        $res = $api->getOrganisationDetails($rq)->getData();
                        $objOwner = [
                            'id' => (isset($res->data) && isset($res->data->id)) ? $res->data->id : '',
                            'name' => (isset($res->data) && isset($res->data->name)) ? $res->data->name : '',
                            'logo' => (isset($res->data) && isset($res->data->logo)) ? $res->data->logo : '',
                            'view' => '/organisation/profile'
                        ];
                    } else {
                        $params = [
                            'api_key'  => Auth::user()->api_key,
                            'criteria' => [
                                'id' => $dataset->created_by,
                            ],
                        ];
                        $rq = Request::create('/api/listUsers', 'POST', $params);
                        $api = new ApiUser($rq);
                        $res = $api->listUsers($rq)->getData();
                        $user = isset($res->users) ? array_first($res->users) : [];
                        $objOwner = [
                            'id' => isset($user->id) ? $user->id : '',
                            'name' => (isset($user->firstname) && isset($user->lastname)) ? $user->firstname .' '. $user->lastname : '',
                            'logo' => null,
                            'view' => '/user/profile'
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
                        'obj_type'       => 'dataset',
                        'obj_view'       => '/data/view',
                        'parent_obj_id'  => '',
                        'obj_owner_id'   => $objOwner['id'],
                        'obj_owner_name' => $objOwner['name'],
                        'obj_owner_logo' => $objOwner['logo'],
                        'obj_owner_view' => $objOwner['view']
                    ];
                    $criteria['dataset_ids'][] = $dataset->id;
                    if (!empty($dataset->resource)) {
                        $objTypeRes = ActionsHistory::MODULE_NAMES[6];
                        foreach ($dataset->resource as $resource) {
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id'          => $resource->uri,
                                'obj_name'        => $resource->name,
                                'obj_type'        => 'resource',
                                'obj_view'        => '/data/resourceView',
                                'parent_obj_id'   => $dataset->uri,
                                'parent_obj_name' => $dataset->name,
                                'parent_obj_type' => 'dataset',
                                'parent_obj_view' => '/data/view',
                                'obj_owner_id'    => $objOwner['id'],
                                'obj_owner_name'  => $objOwner['name'],
                                'obj_owner_logo'  => $objOwner['logo'],
                                'obj_owner_view'  => $objOwner['view']
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
     * Activates an account on confirmation
     *
     * @param Request $request
     * @return view error view on error or sends email on success
     */
    public function confirmation(Request $request)
    {
        $class = 'user';
        $hash = $request->offsetGet('hash');

        if ($hash) {
            $user = User::where('hash_id', $request->offsetGet('hash'))->first();

            if ($user) {
                $user->active = true;

                try {
                    $user->save();
                    $request->session()->flash('alert-success', 'Успешно активирахте акаунта си!');

                    return redirect('login');
                } catch (QueryException $ex) {
                    Log::error($ex->getMessage());
                }
            }

            if ($request->has('generate')) {
                $mailData = [
                    'user'  => $user->firstname,
                    'hash'  => $user->hash_id,
                ];

                Mail::send('mail/confirmationMail', $mailData, function ($m) use ($user) {
                    $m->from('info@finite-soft.com', 'Open Data');
                    $m->to($user->email, $user->firstname)->subject('Акаунтът ви беше успешно създаден!');
                });
            }
        }

        return view('confirmError', compact('class'));
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
        $params = [
            'api_key'           => Auth::user()->api_key,
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        $listReq = Request::create('/api/listUsers', 'POST', $params);
        $api = new ApiUser($listReq);
        $result = $api->listUsers($listReq)->getData();

        $paginationData = $this->getPaginationData($result->users, $result->total_records, [], $perPage);

        return view('/user/list', [
            'class'         => $class,
            'users'         => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
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

        $searchReq = Request::create('/api/searchUsers', 'POST', $params);
        $api = new ApiUser($searchReq);
        $result = $api->searchUsers($searchReq)->getData();

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
            'api_key'   => Auth::user()->api_key,
            'criteria'  => [
                'id'        => $id,
            ],
        ];

        $listReq = Request::create('/api/listUsers', 'POST', $params);
        $apiUser = new ApiUser($listReq);
        $result = $apiUser->listUsers($listReq)->getData();

        if ($result->success) {
            $follReq = Request::create('api/getFollowersCount', 'POST', $params);
            $apiFollow = new ApiFollow($follReq);
            $followers = $apiFollow->getFollowersCount($follReq)->getData();

            if ($followers->success) {
                $followersCount = $followers->count;

                foreach($followers->followers as $follower) {
                    if ($follower->user_id == Auth::user()->id) {
                        $followed = true;

                        break;
                    }
                }
            }

            $setsReq = Request::create('api/getUsersDataSetCount', 'POST', $params);
            $apiDataSet = new ApiDataSets($setsReq);
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

            return redirect('/');
        }
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
        $class = 'user';
        $fields = self::getGroupTransFields();

        if ($request->has('create')) {
            $data = $request->all();
            $data['description'] = $data['descript'];

            if (!empty($data['logo'])) {
                try {
                    $img = \Image::make($data['logo']);

                    $data['logo_filename'] = $data['logo']->getClientOriginalName();
                    $data['logo_mimetype'] = $img->mime();
                    $data['logo_data'] = file_get_contents($data['logo']);

                    unset($data['logo']);
                } catch (NotReadableException $ex) {
                    Log::error($ex->getMessage());
                }
            }

            $params = [
                'api_key'   => Auth::user()->api_key,
                'data'      => $data,
            ];

            $groupReq = Request::create('api/addGroup', 'POST', $params);
            $orgApi = new ApiOrganisations($groupReq);
            $result = $orgApi->addGroup($groupReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', 'Успешно създадена група!');

                return redirect('/user/groupView/'. $result->id);
            } else {
                $request->session()->flash('alert-danger', 'Възникна грешла при създаване на група!');
                $request->session()->flash('result', $result);

                return back();
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
    public function userGroups(Request $request)
    {
        $class = 'user';
        $groups = [];
        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $orgReq = Request::create('/api/getUserOrganisations', 'POST', $params);
        $api = new ApiOrganisations($orgReq);
        $result = $api->getUserOrganisations($orgReq)->getData();

        if ($result->success) {
            foreach ($result->organisations as $org) {
                if ($org->type == Organisation::TYPE_GROUP) {
                    $groups[] = $org;
                }
            }
        }

        $paginationData = $this->getPaginationData($groups, count($groups), [], $perPage);

        return view('/user/groups', [
            'class'         => 'user',
            'groups'        => $paginationData['items'],
            'pagination'    => $paginationData['paginate']
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
    public function groupView(Request $request, $id)
    {
        $class = 'user';

        $params = ['group_id' => $id];

        $grpReq = Request::create('/api/getGroupDetails', 'POST', $params);
        $api = new ApiOrganisations($grpReq);
        $result = $api->getGroupDetails($grpReq)->getData();

        if ($result->success) {
            $group = $result->data;

            return view('user/groupView', compact('class', 'group'));
        } else {
            $request->session()->flash('alert-danger', 'Не беше намерена група!');

            return redirect('/');
        }
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
        $delArr = [
            'api_key'   => Auth::user()->api_key,
            'group_id'  => $id,
        ];

        $delReq = Request::create('/api/deleteGroup', 'POST', $delArr);
        $api = new ApiOrganisations($delReq);
        $delRes = $api->deleteGroup($delReq)->getData();

        if ($delRes->success) {
            $request->session()->flash('alert-success', 'Успешно изтрита група!');

            return back();
        } else {
            $request->session()->flash('alert-danger', 'Неуспешно изтриване на група!');

            return back();
        }
    }

    /**
     * Edit a group based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function editGroup(Request $request, $id)
    {
        $class = 'user';
        $fields = self::getGroupTransFields();

        $model = Organisation::find($id)->loadTranslations();
        $withModel = CustomSetting::where('org_id', $id)->get()->loadTranslations();
        $model->logo = $this->getImageData($model->logo_data, $model->logo_mime_type);

        if ($request->has('edit')) {
            $data = $request->all();
            $data['locale'] = \LaravelLocalization::getCurrentLocale();
            $data['description'] = $data['descript'];

            $params = [
                'api_key'   => Auth::user()->api_key,
                'group_id'  => $id,
                'data'      => $data,
            ];

            $editReq = Request::create('/api/editGroup', 'POST', $params);
            $api = new ApiOrganisations($editReq);
            $result = $api->editGroup($editReq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', 'Успешно запазени данни!');

                return back();
            } else {
                $request->session()->flash('alert-danger', 'Грешно въведени данни!');

                return back();
            }
        }

        return view('user/groupEdit', compact('class', 'fields', 'model', 'withModel'));
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

        return view(
            'user/forgottenPassword',
            [
                'class' => 'index',
            ]
        )->with('errors', $errors);
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
                    $errors[substr($field, strpos($field, ".") )] = $msg[0];
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
    public function groupDatasets(Request $request)
    {
        $class = 'user';
        $actMenu = 'group';
        $groups = [];
        $perPage = 6;

        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $orgReq = Request::create('/api/getUserOrganisations', 'POST', $params);
        $api = new ApiOrganisations($orgReq);
        $result = $api->getUserOrganisations($orgReq)->getData();

        if ($result->success) {
            foreach ($result->organisations as $org) {
                if ($org->type == Organisation::TYPE_GROUP) {
                    $groups[] = $org->id;
                }
            }
        }

        $dataSetIds = DataSet::whereIn('org_id', $groups)->pluck('id')->toArray();

        if (!empty($dataSetIds)) {
            $params['criteria']['dataset_ids'] = $dataSetIds;
            $dataRq = Request::create('/api/listDataSets', 'POST', $params);
            $dataApi = new ApiDataSets($dataRq);
            $datasets = $dataApi->listDataSets($dataRq)->getData();
            $paginationData = $this->getPaginationData($datasets->datasets, $datasets->total_records, [], $perPage);
        } else {
            $request->session()->flash('alert-danger', 'Вашите групи, нямат свързани набори от данни!');

            return back();
        }

        if ($request->has('delete')) {
            $uri = $request->offsetGet('dataset_uri');

            if ($this->datasetDelete($uri)) {
                $request->session()->flash('alert-success', 'Наборът беше успешно изтрит!');
            } else {
                $request->session()->flash('alert-danger', 'Неуспешно изтриване на набор от данни!');
            }

            return back();
        }

        return view('user/datasets', [
                'class'         => 'user',
                'datasets'      => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'activeMenu'    => $actMenu,
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
        $search = $request->offsetGet('search');

        if (empty($search)) {
            return redirect('user/userGroups');
        }

        $params = [
            'records_per_page'  => $perPage,
            'criteria'          => [
                'keywords'          => $search,
                'user_id'           => Auth::user()->id,
            ]
        ];

        $searchRq = Request::create('/api/searchGroups', 'POST', $params);
        $api = new ApiOrganisations($searchRq);
        $grpData = $api->searchGroups($searchRq)->getData();

        $groups = !empty($grpData->groups) ? $grpData->groups : [];
        $count = !empty($grpData->total_records) ? $grpData->total_records : 0;

        $getParams = [
            'search' => $search
        ];

        $paginationData = $this->getPaginationData($groups, $count, $getParams, $perPage);

        return view('user/groups', [
            'class'         => 'user',
            'groups'        => $paginationData['items'],
            'pagination'    => $paginationData['paginate']
        ]);
    }
}
