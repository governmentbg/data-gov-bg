<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\Locale;
use PDOException;
use App\RoleRight;
use App\UserSetting;
use App\Organisation;
use App\UserToOrgRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
Use Uuid;

class UserController extends ApiController
{
    /**
     * List user records by given criteria
     *
     * @param string api_key - required
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     * @param array criteria - optional
     * @param integer criteria[active] - optional
     * @param integer criteria[is_admin] - optional
     * @param integer criteria[org_id] - optional
     * @param integer criteria[role_id] - optional
     * @param integer criteria[id] - optional
     * @param array criteria[user_ids] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     *
     * @return json response with list of users or error
     */
    public function listUsers(Request $request)
    {
        $result = [];
        $criteria = $request->offsetGet('criteria');

        $validator = \Validator::make($request->all(), [
            'records_per_page'      => 'nullable|int',
            'page_number'           => 'nullable|int',
            'criteria'              => 'nullable|array',
            'criteria.active'       => 'nullable|boolean',
            'criteria.approved'     => 'nullable|boolean',
            'criteria.is_admin'     => 'nullable|int',
            'criteria.role_id'      => 'nullable|int',
            'criteria.org_id'       => 'nullable|int',
            'criteria.id'           => 'nullable|int',
            'criteria.user_ids'     => 'nullable|array',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
        ]);

        if (!$validator->fails()) {
            $query = User::select();

            if (isset($criteria['active'])) {
                $query->where('active', $criteria['active']);
            }

            if (isset($criteria['approved'])) {
                $query->where('approved', $criteria['approved']);
            }

            if (!empty($criteria['is_admin'])) {
                $query->where('is_admin', $criteria['is_admin']);
            }

            if (!empty($criteria['role_id'])) {
                $query->whereHas('userToOrgRole', function($q) use($criteria) {
                    $q->where('role_id', $criteria['role_id']);
                });
            }

            if (!empty($criteria['org_id'])) {
                $query->whereHas('userToOrgRole', function($q) use($criteria) {
                    $q->where('org_id', $criteria['org_id']);
                });
            }

            if (!empty($criteria['id'])) {
                $query->where('id', $criteria['id']);
            } elseif (isset($criteria['user_ids'])) {
                $query->whereIn('id', $criteria['user_ids']);
            }

            $query->where('username', '!=', 'system');

            $count = $query->count();

            if (isset($criteria['order']['field']) && isset($criteria['order']['type'])) {
                $query->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $users = $query->get();

                return $this->successResponse(['users'=> $users, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Get users list failure', $validator->errors()->messages());
    }

    /**
     * Search in user records by given keywords
     *
     * @param string api_key - required
     * @param array criteria - required
     * @param string criteria[keywords] - required
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with found users or error
     */
    public function searchUsers(Request $request)
    {
        $search = $request->all();

        $validator = \Validator::make($search, [
            'records_per_page'      => 'nullable|int',
            'page_number'           => 'nullable|int',
            'criteria'              => 'required|array',
            'criteria.keywords'     => 'required|string',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
        ]);

        if (!$validator->fails()) {
            $ids = User::search($search['criteria']['keywords'])->get()->pluck('id');
            $query = User::whereIn('id', $ids);

            $query->where('username', '!=', 'system');
            $count = $query->count();

            $query->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            try {
                $data = $query->get();

                return $this->successResponse(['users' => $data, 'total_records' => $count], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse('Search users failure', $validator->errors()->messages());
    }

    /**
     * Get user roles and organisations by given user id
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json response with roles or error
     */
    public function getUserRoles(Request $request)
    {
        $post = $request->all();

        $validator = \Validator::make($post, ['id' => 'required|int']);

        if (!$validator->fails()) {
            $user = User::find($post['id']);

            if ($user) {
                $result = [];

                foreach($user->userToOrgRole as $role) {
                    $result[] = [
                        'org_id'    => $role->org_id,
                        'role_id'   => $role->role_id,
                    ];
                }

                return $this->successResponse(['roles' => $result]);
            }
        }

        return $this->errorResponse('Get user roles failure', $validator->errors()->messages());
    }

    /**
     * Get user settings by given user id
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json response with settings or error
     */
    public function getUserSettings(Request $request)
    {
        $result = [];

        $validator = \Validator::make($request->all(), ['id' => 'required|int']);

        if (!$validator->fails()) {
            $user = User::with('userSetting', 'follow')->find($request->id);

            if (!empty($user)) {
                $result['settings'] = [
                    'locale'            => !empty($user['userSetting']) ? $user['userSetting']['locale'] : null,
                    'newsletter_digest' => !empty($user['userSetting']) ? $user['userSetting']['newsletter_digest'] : null,
                    'created_at'        => $user['created_at'],
                    'updated_at'        => $user['updated_at'],
                    'created_by'        => $user['created_by'],
                    'updated_by'        => $user['updated_by'],
                ];

                $result['follows'] = [];

                if (!empty($user['follow'])) {
                    foreach ($user['follow'] as $follow) {
                        $result['follows'][] = [
                            'news'        => $follow['news'],
                            'org_id'      => $follow['org_id'],
                            'group_id'      => $follow['group_id'],
                            'dataset_id'  => $follow['data_set_id'],
                            'category_id' => $follow['category_id'],
                            'tag_id' => $follow['tag_id'],
                            'follow_user_id' => $follow['follow_user_id'],
                        ];
                    }
                }
            }

            return $this->successResponse(['user' => $result], true);
        }

        return $this->errorResponse('Get user settings failure', $validator->errors()->messages());
    }

    /**
     * Add new user record
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - required
     * @param string data[password_confirm] - required
     * @param integer data[role_id] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     *
     * @return json $response - response with status and api key if successful
     */
    public function addUser(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'firstname'         => 'required|string',
            'lastname'          => 'required|string',
            'username'          => 'nullable|string|unique:users,username,NULL,id,deleted_at,NULL',
            'email'             => 'required|email',
            'password'          => 'required|string|min:6',
            'password_confirm'  => 'required|string|same:password',
            'role_id'           => 'nullable|int|required_with:org_id',
            'org_id'            => 'nullable|int|required_with:role_id',
        ]);

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();

                $apiKey = Uuid::generate(4)->string;
                $user = new User;

                $user->username = !empty($request->data['username'])
                    ? $request->data['username']
                    : $this->generateUsername($request->data['email']);
                $user->password = bcrypt($request->data['password']);
                $user->email = $request->data['email'];
                $user->firstname = $request->data['firstname'];
                $user->lastname = $request->data['lastname'];
                $user->add_info = !empty($request->data['add_info'])
                    ? $request->data['add_info']
                    : null;
                $user->is_admin = 0;
                $user->active = 0;
                $user->approved = !empty($request->offsetGet('invite')) ? 1 : 0;
                $user->api_key = $apiKey;
                $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
                $user->remember_token = null;

                $registered = $user->save();

                $mailData = [
                    'user'  => $user->firstname,
                    'hash'  => $user->hash_id,
                ];

                Mail::send('mail/confirmationMail', $mailData, function ($m) use ($user) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($user->email, $user->firstname);
                    $m->subject(__('custom.register_subject'));
                });

                if (isset($data['role_id']) || isset($data['org_id'])) {
                    $userToOrgRole = new UserToOrgRole;

                    $userToOrgRole->user_id = $user->id;
                    $userToOrgRole->role_id = (int) $data['role_id'];
                    $userToOrgRole->org_id = (int) $data['org_id'];

                    $userToOrgRole->save();
                }

                $userSettings = new UserSetting;

                $userLocale = !empty($data['user_settings']['locale'])
                    && !empty(Locale::where('locale', $data['user_settings']['locale'])->value('locale'))
                    ? $data['user_settings']['locale']
                    : config('app.locale');

                $userSettings->user_id = $user->id;
                $userSettings->locale = $userLocale;

                if (
                    isset($data['user_settings']['newsletter_digest'])
                    && is_numeric($data['user_settings']['newsletter_digest'])
                ) {
                    $userSettings->newsletter_digest = $data['user_settings']['newsletter_digest'];
                }

                $userSettings->save();

                DB::commit();

                return $this->successResponse(['api_key' => $apiKey], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            } catch (\Swift_TransportException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());

                $validator->errors()->add('email', __('custom.send_mail_failed'));
            }
        }

        return $this->errorResponse('User registration failure', $validator->errors()->messages());
    }

    /**
     * Edit existing user record
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[firstname] - optional
     * @param string data[lastname] - optional
     * @param string data[email] - optional
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - optional
     * @param string data[password_confirm] - optional
     * @param integer data[role_id] - optional
     * @param integer data[is_admin] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     *
     * @return json $response - response with status and api key if successful
     */
    public function editUser(Request $request)
    {
        $data = $request->data;
        $id = $request->id;


        $validator = \Validator::make(
            $request->all(),
            [
                'id'                    => 'required|int',
                'data'                  => 'required|array',
                'data.firstname'        => 'nullable|string',
                'data.lastname'         => 'nullable|string',
                'data.email'            => 'nullable|email',
                'data.add_info'         => 'nullable|string',
                'data.password'         => 'nullable|string',
                'data.is_admin'         => 'nullable|int',
                'data.password_confirm' => 'nullable|string|same:data.password',
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse('Edit user failure', $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse('Edit user failure');
        }

        $newUserData = [];

        if (!empty($data['firstname'])) {
            $newUserData['firstname'] = $data['firstname'];
        }

        if (!empty($data['lastname'])) {
            $newUserData['lastname'] = $data['lastname'];
        }

        if (!empty($data['email']) && $data['email'] !== $user->email) {
            $newUserData['hash_id'] = str_replace('-', '', Uuid::generate(4)->string);

            $mailData = [
                'user'  => $user->firstname,
                'hash'  => $newUserData['hash_id'],
                'mail'  => $data['email'],
                'id'    => $id,
            ];

            Mail::send('mail/emailChangeMail', $mailData, function ($m) use ($data) {
                $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                $m->to($data['email'], $data['firstname']);
                $m->subject('Смяна на екектронен адрес!');
            });

            if (count(Mail::failures()) > 0) {
                return $this->errorResponse('Failed to send mail');
            }
        }

        if (!empty($data['add_info'])) {
            $newUserData['add_info'] = $data['add_info'];
        }

        if (!empty($data['username'])) {
            $newUserData['username'] = $data['username'];
        }

        if (!empty($data['password'])) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'data.password_confirm'  => 'required|same:data.password',
                ]
            );

            if ($validator->fails()) {

                return $this->errorResponse('Edit user failure', $validator->errors()->messages());
            }

            $newUserData['password'] = bcrypt($data['password']);
        }

        if (isset($data['is_admin'])) {
            $newUserData['is_admin'] = (int) $data['is_admin'];
        }

        if (isset($data['active'])) {
            $newUserData['active'] = (int) $data['active'];
        }

        if (isset($data['approved'])) {
            $newUserData['approved'] = (int) $data['approved'];
        }

        $orgAndRoles = [];

        if (isset($data['role_id']) || isset($data['org_id'])) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'data.role_id' => 'required',
                    'data.org_id'  => 'required',
                ]
            );

            if ($validator->fails()) {

                return $this->errorResponse('Edit user failure');
            }

            $orgAndRoles['role_id'] = (int) $data['role_id'];
            $orgAndRoles['org_id'] = (int) $data['org_id'];
        }

        $newSettings = [];

        if (isset($data['user_settings']['locale'])) {
            $newSettings['locale'] = $data['user_settings']['locale'];
        }

        if (isset($data['user_settings']['newsletter_digest'])) {
            $newSettings['newsletter_digest'] = (int) $data['user_settings']['newsletter_digest'];
        }

        if (empty($newUserData) && empty($newSettings) && empty($orgAndRoles)) {
            return $this->errorResponse('Edit user failure');
        }

        if (!empty($newUserData)) {
            $newUserData['updated_by'] = Auth::id();

            try {
                User::where('id', $request->id)->update($newUserData);
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Edit user failure');
            }
        }

        if (!empty($orgAndRoles)) {
            try {
                UserToOrgRole::where('user_id', $request->id)->update($orgAndRoles);
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Edit user failure');
            }
        }

        if (!empty($newSettings)) {
            try {
                UserSetting::updateOrCreate(['user_id' => $request->id], $newSettings);
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Edit user failure');
            }
        }

        return $this->successResponse(['api_key' => $user['api_key']], true);
    }

    /**
     * Delete existing user record
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json $response - response with status
     */
    public function deleteUser(Request $request)
    {
        $id = $request->id;

        $validator = \Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ]
        );

        if ($validator->fails()) {

            return $this->errorResponse('Delete user failure', $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse('Delete user failure');
        }

        try {
            $user->delete();
        } catch (QueryException $e) {

            return $this->errorResponse('Delete user failure');
        }

        try {
            $user->deleted_by = Auth::id();
            $user->save();
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse('Delete user failure');
        }

        return $this->successResponse();
    }

    /**
     * Generate new api key for existing user
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json $response - response with status
     */
    public function generateAPIKey(Request $request)
    {
        $validator = \Validator::make($request->all(), ['id' => 'required|int']);

        if ($validator->fails()) {
            return $this->errorResponse('Generate API key failure', $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse('Generate API key failure');
        }

        try {
            $user->api_key = Uuid::generate(4)->string;
            $user->updated_by = Auth::id();
            $user->save();
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse('Generate API key failure');
        }

        return $this->successResponse();
    }

    /**
     * Invite user to register
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[email] - required
     * @param integer data[is_admin] - optional
     * @param integer data[approved] - optional
     * @param integer data[role_id] - optional
     * @param integer data[org_id] - optional
     *
     * @return json $response - response with status
     */
    public function inviteUser(Request $request)
    {
        $errors = [];
        $post = $request->all();

        $validator = \Validator::make($post, ['data' => 'required|array']);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $validator = \Validator::make($post['data'], [
                'email'    => 'required|email',
                'is_admin' => 'nullable|int',
                'approved' => 'nullable|int',
                'role_id'  => 'nullable|int|required_with:org_id',
                'org_id'   => 'nullable|int|required_with:role_id',
                'generate' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->messages();
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse('Invite user failure', $errors);
        }

        if (!empty($post['data']['generate'])) {
            DB::beginTransaction();

            $password = Uuid::generate(4)->string;
            $reqOrgId = isset($request->data['org_id']) ? $request->data['org_id']: null;

            $loggedUser = User::with('userToOrgRole')->find(Auth::id());
            $loggedOrgId = isset($loggedUser['userToOrgRole']['org_id']) ? $loggedUser['userToOrgRole']['org_id'] : null;
            $loggedRoleRight = isset($loggedUser['userToOrgRole']['role_id'])
                ? RoleRight::where('role_id', $loggedUser['userToOrgRole']['role_id'])->value('right')
                : null;

            $user = new User;

            $user->username = $this->generateUsername($request->data['email']);
            $user->password = bcrypt($password);
            $user->email = $request->data['email'];
            $user->firstname = '';
            $user->lastname = '';
            $user->add_info = '';
            $user->is_admin = isset($request->data['is_admin']) && $loggedUser->is_admin
                ? (int) $request->data['is_admin']
                : 0;
            $user->active = 0;
            $user->approved = 0;
            $user->api_key = Uuid::generate(4)->string;
            $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
            $user->remember_token = null;

            try {
                $user->save();

                if (
                    $loggedUser->is_admin
                    || (
                        $loggedOrgId == $reqOrgId
                        && in_array($loggedRoleRight, [RoleRight::RIGHT_EDIT, RoleRight::RIGHT_ALL])
                    )
                ) {
                    if (isset($request->data['approved'])) {
                        $user->approved = $request->data['approved'];
                    }

                    $template = 'mail/generateMail';
                    $mailData = [
                        'user'      => Auth::user()->firstname .' '. Auth::user()->lastname,
                        'username'  => $user->username,
                        'pass'      => $password,
                    ];

                    if (isset($request->data['role_id']) && isset($request->data['org_id'])) {
                        $userToOrgRole = new UserToOrgRole;

                        $userToOrgRole->user_id = $user->id;
                        $userToOrgRole->role_id = $request->data['role_id'];
                        $userToOrgRole->org_id = $request->data['org_id'];

                        $userToOrgRole->save();
                    }
                }

                DB::commit();
            } catch (QueryException $e) {
                $mailData = null;

                DB::rollback();

                Log::error($e->getMessage());
            }
        } else {
            $template = 'mail/inviteMail';
            $mailData = [
                'user'  => Auth::user()->firstname .' '. Auth::user()->lastname,
                'mail'  => $post['data']['email'],
            ];
        }

        if (!empty($mailData)) {
            if ($mailData['user'] == ' ') {
                $mailData['user'] = Auth::user()->username;
            }

            try {
                Mail::send($template, $mailData, function ($m) use ($post) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($post['data']['email']);
                    $m->subject(__('custom.invite_subject'));
                });
            } catch (\Swift_TransportException $ex) {
                Log::error($ex->getMessage());

                $validator->errors()->add('email', __('custom.send_mail_failed'));

                return $this->errorResponse('Invite user failure', $validator->errors()->messages());
            }
        }

        return $this->successResponse();
    }

    /**
     * Register new user
     *
     * @param array data - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - required
     * @param string data[password_confirm] - required
     * @param integer data[role_id] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     * @param array data[org_data] - optional
     * @param integer data[org_data][parent_org_id] - optional
     * @param string data[org_data][locale] - required
     * @param string data[org_data][name] - required
     * @param integer data[org_data][type] - required
     * @param string data[org_data][description] - required
     * @param string data[org_data][logo_file_name] - optional
     * @param string data[org_data][logo_mime_type] - optional
     * @param string data[org_data][logo_data] - optional
     * @param string data[org_data][activity_info] - optional
     * @param string data[org_data][contacts] - optional
     *
     * @return json response with api key or error
     */
    public function register(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'firstname'         => 'required|string',
            'lastname'          => 'required|string',
            'username'          => 'required|string|unique:users,username,NULL,id,deleted_at,NULL',
            'email'             => 'required|email',
            'password'          => 'required|string|min:6',
            'password_confirm'  => 'required|string|same:password',
            'role_id'           => 'nullable|int|required_with:org_id',
            'org_id'            => 'nullable|int|required_with:role_id',
        ]);

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();

                $apiKey = Uuid::generate(4)->string;
                $user = new User;

                $user->username = !empty($request->data['username'])
                    ? $request->data['username']
                    : $this->generateUsername($request->data['email']);
                $user->password = bcrypt($request->data['password']);
                $user->email = $request->data['email'];
                $user->firstname = $request->data['firstname'];
                $user->lastname = $request->data['lastname'];
                $user->add_info = !empty($request->data['add_info'])
                    ? $request->data['add_info']
                    : null;
                $user->is_admin = 0;
                $user->active = 0;
                $user->approved = !empty($request->offsetGet('invite')) ? 1 : 0;
                $user->api_key = $apiKey;
                $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
                $user->remember_token = null;

                $registered = $user->save();

                $mailData = [
                    'user'  => $user->firstname,
                    'hash'  => $user->hash_id,
                ];

                Mail::send('mail/confirmationMail', $mailData, function ($m) use ($user) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($user->email, $user->firstname);
                    $m->subject(__('custom.register_subject'));
                });

                if (isset($data['role_id']) || isset($data['org_id'])) {
                    $userToOrgRole = new UserToOrgRole;

                    $userToOrgRole->user_id = $user->id;
                    $userToOrgRole->role_id = (int) $data['role_id'];
                    $userToOrgRole->org_id = (int) $data['org_id'];

                    $userToOrgRole->save();
                }

                $userSettings = new UserSetting;

                $userLocale = !empty($data['user_settings']['locale'])
                    && !empty(Locale::where('locale', $data['user_settings']['locale'])->value('locale'))
                    ? $data['user_settings']['locale']
                    : config('app.locale');

                $userSettings->user_id = $user->id;
                $userSettings->locale = $userLocale;

                if (
                    isset($data['user_settings']['newsletter_digest'])
                    && is_numeric($data['user_settings']['newsletter_digest'])
                ) {
                    $userSettings->newsletter_digest = $data['user_settings']['newsletter_digest'];
                }

                $userSettings->save();

                if (!empty($data['org_data'])) {
                    $organisation = new Organisation;

                    $organisation->type = $data['org_data']['type'];
                    $organisation->parent_org_id = !empty($data['org_data']['parent_org_id'])
                        ? $data['org_data']['parent_org_id']
                        : null;
                    $organisation->logo_file_name = !empty($data['org_data']['logo_file_name'])
                        ? $data['org_data']['logo_file_name']
                        : null;
                    $organisation->logo_mime_type = !empty($data['org_data']['logo_mime_type'])
                        ? $data['org_data']['logo_mime_type']
                        : null;
                    $organisation->logo_data = !empty($data['org_data']['logo_data'])
                        ? $data['org_data']['logo_data']
                        : null;
                    $organisation->active = 0;
                    $organisation->approved = 0;
                    $organisation->created_by = $user->id;
                    $organisation->name = $this->trans($locale, $data['org_data']['name']);
                    $organisation->descript = $this->trans($locale, $data['org_data']['description']);
                    $organisation->activity_info = $data['org_data']['activity_info'];
                    $organisation->contacts = $data['org_data']['contacts'];

                    $organisation->save();
                }

                DB::commit();

                return $this->successResponse(['api_key' => $apiKey], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            } catch (\Swift_TransportException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());

                $validator->errors()->add('email', __('custom.send_mail_failed'));
            }
        }

        return $this->errorResponse('User registration failure', $validator->errors()->messages());
    }

    /**
     * Get available username using the first part of an email
     *
     * @param string $email
     * @return string $availableUsername
     */
    public function generateUsername($email)
    {
        $parts = explode('@', $email);
        $username = $parts[0];
        $count = 0;

        while (User::where('username', $username)->count()) {
            $count++;
            $username = $username . $count;
        }

        return $username;
    }

    /**
     * Forgotten password
     *
     * @param string username - required
     *
     * @return json response with status (true - if user is found and email is sent false - otherwise)
     */
    public function forgottenPassword(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'username' => 'required|string|exists:users,username,deleted_at,NULL'
        ]);

        if (!$validator->fails()) {
            $user = User::where('username', '=', $data['username'])->first();

            if (!empty($user['email'])) {
                $mailData = [
                    'user'      => $user['firstname'] .' '. $user['lastname'],
                    'hash'      => str_replace('-', '', Uuid::generate(4)->string),
                    'username'  => $user['username']
                ];

                Mail::send('mail/passReset', $mailData, function ($m) use ($user) {
                    $m->from(env('MAIL_FROM', 'no-reply@finite-soft.com'), env('APP_NAME'));
                    $m->to($user['email'], $user['firstname']);
                    $m->subject(__('custom.pass_change').'!');
                });

                $newUserData['hash_id'] = $mailData['hash'];
                $newUserData['updated_by'] = $user['id'];

                if (count(Mail::failures()) <= 0) {
                    try {
                        User::where('username', $user['username'])->update($newUserData);
                    } catch (QueryException $e) {
                        Log::error($e->getMessage());
                    }
                } else {
                    return $this->errorResponse(__('custm.email_send_err'));
                }

                return $this->successResponse();
            }
        }

        return $this->errorResponse('Reset Passoword failure', $validator->errors()->messages());
    }

    /**
     * Password reset
     *
     * @param string hash - required
     * @param string password - required
     * @param string password_confirm - required
     *
     * @return json response with status (true - if password is changed false - otherwise)
     */
    public function passwordReset(Request $request)
    {
        $data = $request->get('data', []);

        $validator = \Validator::make($data, [
            'hash'              => 'required|string',
            'password'          => 'required|string|min:6',
            'password_confirm'  => 'required|string|same:password',
        ]);

        if (!$validator->fails()) {
            $user = User::where('hash_id', '=', $data['hash'])->first();

            if ($user) {
                $newUserData['password'] = bcrypt($data['password']);

                if (!empty($newUserData)) {
                    $newUserData['updated_by'] = $user->id;
                    try {
                        User::where('id', $user->id)->update($newUserData);
                    } catch (QueryException $e) {
                        Log::error($e->getMessage());
                    }

                    return $this->successResponse();
                }
            } else {
                return $this->errorResponse(__('custom.wrong_reset_link'));
            }
        }

        return $this->errorResponse('custom.pass_change_err', $validator->errors()->messages());
    }
}
