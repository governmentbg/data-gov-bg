<?php

namespace App\Http\Controllers;

use App\User;
use App\UserSetting;
use App\Organisation;
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
use App\Http\Controllers\Api\OrganisationController as ApiOrganisations;

class UserController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {


        return view('user/newsFeed', ['class' => 'user']);
    }

    public function datasets(Request $request) {
        $params['api_key'] = \Auth::user()->api_key;
        $params['criteria']['created_by'] = \Auth::user()->id;
        $params['records_per_page'] = '10';
        $params['page_number'] = '1';

        $request = Request::create('/api/listDataSets', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->listDataSets($request)->getData();

        return view('user/datasets', ['class' => 'user', 'datasets' => $datasets->datasets]);
    }

    public function datasetView(Request $request) {
        $params['dataset_uri'] = $request->uri;

        $request = Request::create('/api/getDataSetDetails', 'POST', $params);
        $api = new ApiDataSets($request);
        $dataset = $api->getDataSetDetails($request)->getData();

        return view('user/datasetView', ['class' => 'user', 'dataset' => $dataset->data]);
    }

    public function deleteDataset(Request $request)
    {
        $params['api_key'] = \Auth::user()->api_key;
        $params['dataset_uri'] = $request->input('dataset_uri');

        $request = Request::create('/api/deleteDataSet', 'POST', $params);
        $api = new ApiDataSets($request);
        $datasets = $api->deleteDataSet($request)->getData();

        return redirect('user/datasets');
    }

    public function create() {
    }

    public function translate() {
    }

    public function settings(Request $request) {
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

    public function registration(Request $request) {
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

    public function orgRegistration(Request $request) {
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

    public function createLicense() {
    }

    public function organisations(Request $request)
    {
        $perPage = 6;
        $params = [
            'api_key'        => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $request = Request::create('/api/getOrganisations', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->getOrganisations($request)->getData();

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
            'org_id'  => $request->id,
        ];

        $request = Request::create('/api/deleteOrganisation', 'POST', $params);
        $api = new ApiOrganisations($request);
        $result = $api->deleteOrganisation($request)->getData();

        return redirect('/user/organisations');
    }

    public function searchOrg(Request $request)
    {
        $search = trim($request->q);

        if (empty($search)) {
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
}
