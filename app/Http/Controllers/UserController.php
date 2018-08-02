<?php

namespace App\Http\Controllers;

use App\User;
use App\Locale;
use App\DataSet;
use App\UserSetting;
use App\Organisation;
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
use App\Http\Controllers\ApiController as ApiController;
use App\Http\Controllers\Api\LocaleController as ApiLocale;
use App\Http\Controllers\Api\DataSetController as ApiDataSets;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
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

    public static function getTransFields()
    {
        return [
            [
                'label'    => 'Наименование',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'Описание',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'Дейност',
                'name'     => 'activity_info',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'Контакти',
                'name'     => 'contacts',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['Заглавие', 'Стойност'],
                'name'     => 'custom_fields',
                'type'     => 'text',
                'view'     => 'translation_custom',
                'val'      => ['key', 'value'],
                'required' => false,
            ],
        ];
    }

    public static function getDatasetTransFields()
    {
        return [
            [
                'label'    => 'Наименование',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'Описание',
                'name'     => 'description',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'Етикети',
                'name'     => 'tags',
                'type'     => 'text',
                'view'     => 'translation_tags',
                'required' => false,
            ],
            [
                'label'    => 'Споразумение за ниво на обсужване',
                'name'     => 'sla',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => ['Заглавие', 'Стойност'],
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

    public function datasets(Request $request)
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['created_by'] = \Auth::user()->id;
        $params['records_per_page'] = '10';
        $params['page_number'] = '1';

        $request = Request::create('/api/listDataSets', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->listDataSets($request)->getData();

        return view('user/datasets', ['class' => 'user', 'datasets' => $datasets->datasets]);
    }

    public function datasetView(Request $request)
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

        return view('user/datasetView', ['class' => 'user', 'dataset' => $dataset->data, 'resources' => $resources->resources]);
    }

    public function datasetDelete(Request $request)
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['dataset_uri'] = $request->input('dataset_uri');

        $request = Request::create('/api/deleteDataSet', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->deleteDataSet($request)->getData();

        return redirect('user/datasets');
    }

    public function datasetCreate(Request $request, DataSet $datasetModel)
    {
        $visibilityOptions = $datasetModel->getVisibility();
        $mainCategories = $datasetModel->getVisibility();
        $categories = $this->prepareMainCategories();
        $termsOfUse = $this->prepareTermsOfUse();
        $organisations = $this->prepareOrganisations();
        $groups = $this->prepareGroups();
        $errors = [];
        $data = $request->all();

        if ($data) {
            // prepare post data for API request
            $data['locale'] = \LaravelLocalization::getCurrentLocale();
            Log::debug($data['tags']);
            if (isset($data['tags'])) {
                foreach ($data['tags'] as $locale => $tags) {
                    $data['tags'][$locale] = explode(',', $tags);
                }
            }
            Log::debug($data['tags']);

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
                    $result = $api->addDataSetToGroup($addGroup)->getData();
                }

                $request->session()->flash('alert-success', 'Промените бяха успешно запазени!');
                return redirect()->route('datasetView', ['uri' => $request->uri]);
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

    public function datasetEdit()
    {
        return view('user/datasetEdit', [
            'class' => 'user',
        ]);
    }

    public function translate()
    {
    }

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

    public function searchOrg(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect('/user/organisations');
        }

        $perPage = 6;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'criteria'         => ['keywords' => $search],
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
            session()->flash('success', 'Промените бяха запазени успешно!');
        } else {
            session()->flash('result', $result);
        }

        return $result->success
            ? redirect('/organisation/profile')
            : redirect('user/organisations/register')->withInput(Input::all());
    }


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

    public function showOrgRegisterForm() {

        return view('user/orgRegister', ['class' => 'user', 'fields' => self::getTransFields()]);
    }

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

        return !$result->success
            ? view(
                'user/orgEdit',
                [
                    'class'     => 'user',
                    'model'     => $orgModel,
                    'withModel' => $customModel,
                    'fields'    => self::getTransFields()
                ]
            )->with('result', $result)
            : view(
                'user/orgEdit',
                [
                    'class'     => 'user',
                    'model'     => $orgModel,
                    'withModel' => $customModel,
                    'fields'    => self::getTransFields()
                ]
            )->with('success', 'Промените бяха запазени успешно!');
    }

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

    private function prepareTermsOfUse()
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['active'] = 1;
        $request = Request::create('/api/listTermsOfUse', 'POST', $params);
        $api = new ApiTermsOfUse($request);
        $result = $api->listTermsOfUse($request)->getData();
        $termsOfUse = [];

        foreach ($result->data as $row) {
            $termsOfUse[$row->id] = $row->name;
        }

        return $termsOfUse;
    }

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

    public function newsFeed(Request $request)
    {
        $user = User::find(Auth::id());
        if ($user) {
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
                            $actObjData[$objType][$org->id] = [
                                'obj_id'   => $org->id,
                                'obj_name' => $org->name,
                                'obj_type' => 'org',
                                'obj_view' => '/organisation/profile',
                                'parent_obj_id' => ''
                            ];
                            $criteria['org_ids'][] = $org->id;
                            $params = [
                                'criteria' => ['org_id' => $org->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData);
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
                            $actObjData[$objType][$group->id] = [
                                'obj_id'   => $group->id,
                                'obj_name' => $group->name,
                                'obj_type' => 'group',
                                'obj_view' => '/group/profile',
                                'parent_obj_id' => ''
                            ];
                            $criteria['group_ids'][] = $group->id;
                            $params = [
                                'criteria' => ['group_id' => $group->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData);
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
                            $actObjData[$objType][$category->id] = [
                                'obj_id'   => $category->id,
                                'obj_name' => $category->name,
                                'obj_type' => 'category',
                                'obj_view' => '',
                                'parent_obj_id' => ''
                            ];
                            $criteria['category_ids'][] = $category->id;
                            $params = [
                                'criteria' => ['category_id' => $category->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData);
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
                            $actObjData[$objType][$tag->id] = [
                                'obj_id'   => $tag->id,
                                'obj_name' => $tag->name,
                                'obj_type' => 'tag',
                                'obj_view' => '',
                                'parent_obj_id' => ''
                            ];
                            $criteria['tag_ids'][] = $tag->id;
                            $params = [
                                'criteria' => ['tag_id' => $tag->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData);
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
                        foreach ($res->users as $followUser) {
                            $objType = ActionsHistory::MODULE_NAMES[4];
                            $actObjData[$objType] = [];
                            $actObjData[$objType][$followUser->id] = [
                                'obj_id'   => $followUser->id,
                                'obj_name' => $followUser->firstname .' '. $followUser->lastname,
                                'obj_type' => 'user',
                                'obj_view' => '/user/profile',
                                'parent_obj_id' => ''
                            ];
                            $criteria['user_ids'][] = $followUser->id;
                            $params = [
                                'criteria' => ['created_by' => $followUser->id, 'locale' => $locale]
                            ];
                            $this->prepareNewsFeedDatasets($params, $criteria, $actObjData);
                        }
                    }
                }
                if (!empty($userFollows['dataset_id'])) {
                    $params = [
                        'criteria' => ['dataset_ids' => $userFollows['dataset_id'], 'locale' => $locale]
                    ];
                    $this->prepareNewsFeedDatasets($params, $criteria, $actObjData);
                }
            }

            // user profile actions
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

            return view(
                'user/newsFeed',
                [
                    'class'          => 'user',
                    'actionsHistory' => $paginationData['items'],
                    'actionObjData'  => $actObjData,
                    'actionTypes'    => ActionsHistory::getTypes(),
                    'pagination'     => $paginationData['paginate']
                ]
            );
        }

        return redirect('/');
    }

    private function prepareNewsFeedDatasets($params, &$criteria, &$actObjData) {
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
                    $actObjData[$objType][$dataset->id] = [
                        'obj_id' => $dataset->id,
                        'obj_name' => $dataset->name,
                        'obj_type' => 'dataset',
                        'obj_view' => '/data/view',
                        'parent_obj_id' => ''
                    ];
                    $criteria['dataset_ids'][] = $dataset->id;
                    if (!empty($dataset->resource)) {
                        $objTypeRes = ActionsHistory::MODULE_NAMES[6];
                        foreach ($dataset->resource as $resource) {
                            $actObjData[$objTypeRes][$resource->uri] = [
                                'obj_id' => $resource->uri,
                                'obj_name' => $resource->name,
                                'obj_type' => 'resource',
                                'obj_view' => '/data/resourceView',
                                'parent_obj_id' => $dataset->id,
                                'parent_obj_name' => $dataset->name,
                                'parent_obj_type' => 'dataset',
                                'parent_obj_view' => '/data/view'
                            ];
                            $criteria['resource_uris'][] = $resource->uri;
                        }
                    }
                }
            }
        }
    }

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
}
