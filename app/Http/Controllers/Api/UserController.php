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
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
Use \DB;
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
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
            'criteria'              => 'nullable|array',
            'criteria.active'       => 'nullable|boolean',
            'criteria.approved'     => 'nullable|boolean',
            'criteria.is_admin'     => 'nullable|integer',
            'criteria.role_id'      => 'nullable|integer',
            'criteria.org_id'       => 'nullable|integer',
            'criteria.id'           => 'nullable|interger',
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
            }

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
     * @return json response with fount users or error
     */
    public function searchUsers(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
            'criteria'              => 'required|array',
            'criteria.keywords'     => 'required|string',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
        ]);

        $search = $request->all();

        if (!$validator->fails()) {
            $ids = User::search($search['criteria']['keywords'])->get()->pluck('id');
            $query = User::whereIn('id', $ids);

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

        $validator = \Validator::make($post, ['id' => 'required|integer']);

        if (!$validator->fails()) {
            $user = User::find($post['id']);

            if ($user) {
                $result = [];

                foreach($user->userToOrgRole as $role) {
                    $result[] = [
                        'org_id' => $role->org_id,
                        'role_id' => $role->role_id,
                    ];
                }
                return $this->successResponse(['roles' => $result]);
            }
        }

        return $this->errorResponse('Get user roles failure', $validator->errors->messages());
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

        $validator = \Validator::make($request->all(), ['id' => 'required|integer']);

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
                            'dataset_id'  => $follow['dataset_id'],
                            'category_id' => $follow['category_id'],
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
     * @return json $response - response with status and api key if successfull
     */
    public function addUser(Request $request)
    {
        $data = $request->data;

        $validator = \Validator::make(
            $request->all(),
            [
                'data.firstname'         => 'required|string',
                'data.lastname'          => 'required|string',
                'data.email'             => 'required|email',
                'data.password'          => 'required|string',
                'data.password_confirm'  => 'required|string|same:data.password',
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse('Add user failure', $validator->errors()->messages());
        }

        $apiKey = Uuid::generate(4)->string;
        $user = new User;

        $user->username = !empty($request->data['username'])
            ? $request->data['username']
            : $this->generateUsername($request->data['email']);
        $user->password = bcrypt($request->data['password']);
        $user->email = $request->data['email'];
        $user->firstname = $request->data['firstname'];
        $user->lastname = $request->data['lastname'];
        $user->add_info = !empty($request->data['addinfo'])
            ? $request->data['addinfo']
            : null;
        $user->is_admin = isset($request->data['is_admin']) ? (int) $request->data['is_admin'] : 0;
        $user->active = 0;
        $user->approved = isset($request->data['approved']) ? (int) $request->data['approved'] : 0;
        $user->api_key = $apiKey;
        $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
        $user->remember_token = null;

        try {
            $user->save();
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse('Add user failure');
        }

        if (isset($data['role_id']) || isset($data['org_id'])) {

            $validator = \Validator::make(
                $request->all(),
                [
                    'data.role_id'         => 'required',
                    'data.org_id'          => 'required',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Add user failure', $validator->errors()->messages());
            }

            $userToOrgRole = new UserToOrgRole;

            $userToOrgRole->user_id = $user->id;
            $userToOrgRole->role_id = (int) $data['role_id'];
            $userToOrgRole->org_id = (int) $data['org_id'];

            try {
                $userToOrgRole->save();
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Add user failure');
            }
        }

        if (!empty($data['user_settings']['locale']) || isset($data['user_settings']['newsletter_digest'])) {
            $userSettings = new UserSetting;

            $userSettings->user_id = $user->id;
            $userSettings->locale = $userLocale;
            $userSettings->created_by = $user->id;

            if(
                isset($data['user_settings']['newsletter_digest'])
                && is_numeric($data['user_settings']['newsletter_digest'])
            ) {
                $userSettings->newsletter_digest = $data['user_settings']['newsletter_digest'];
            }

            try {
                $userSettings->save();
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Add user failure');
            }
        }

        //to do: send mail to user

        return $this->successResponse(['api_key' => $apiKey], true);
    }

    /**
     * Edit existing user record
     *
     * @param string api_key - required
     * @param integer id - required
     * @param array data - required
     * @param string data[firstname] - required
     * @param string data[lastname] - required
     * @param string data[email] - required
     * @param string data[add_info] - optional
     * @param string data[username] - optional
     * @param string data[password] - required
     * @param string data[password_confirm] - required
     * @param integer data[role_id] - optional
     * @param integer data[is_admin] - optional
     * @param array data[user_settings] - optional
     * @param string data[user_settings][locale] - optional
     * @param integer data[user_settings][newsletter_digest] - optional
     *
     * @return json $response - response with status and api key if successfull
     */
    public function editUser(Request $request)
    {
        $data = $request->data;
        $id = $request->id;

        $validator = \Validator::make(
            $request->all(),
            [
                'id'                    => 'required|integer',
                'data'                  => 'required|array',
                'data.firstname'        => 'nullable|string',
                'data.lastname'         => 'nullable|string',
                'data.email'            => 'nullable|email',
                'data.password'         => 'nullable|string',
                'data.is_admin'         => 'nullable|integer',
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

        if (!empty($data['email'])) {
            $newUserData['email'] = $data['email'];
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
            $newUserData['updated_by'] = \Auth::id();

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
                UserSetting::where('user_id', $request->id)->update($newSettings);
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
            $user->deleted_by = \Auth::id();
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
        $validator = \Validator::make($request->all(), ['id' => 'required|integer']);

        if ($validator->fails()) {
            return $this->errorResponse('Generate API key failure', $validator->errors()->messages());
        }

        if (empty($user = User::find($request->id))) {
            return $this->errorResponse('Generate API key failure');
        }

        try {
            $user->api_key = Uuid::generate(4)->string;
            $user->updated_by = \Auth::id();
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
     *
     * @return json $response - response with status
     */
    public function inviteUser(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'id'            => 'required|integer',
                'data'          => 'required|array',
                'data.email'    => 'required|email',
                'data.is_admin' => 'nullable|integer',
                'data.approved' => 'nullable|integer',
                'data.role_id'  => 'nullable|integer',
                'data.org_id'   => 'nullable|integer',
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse('Invite user failure', $validator->errors()->messages());
        }

        $reqOrgId = isset($request->data['org_id']) ? $request->data['org_id']: null;

        $loggedUser = User::with('userToOrgRole')->find(\Auth::id());
        $loggedOrgId = isset($loggedUser['userToOrgRole']['org_id']) ? $loggedUser['userToOrgRole']['org_id'] : null;
        $loggedRoleRight = isset($loggedUser['userToOrgRole']['role_id'])
            ? RoleRight::where('role_id', $loggedUser['userToOrgRole']['role_id'])->value('right')
            : null;

        $user = new User;

        $user->username = $this->generateUsername($request->data['email']);
        $user->password = bcrypt(Uuid::generate(4)->string);
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

        if (
            $loggedUser->is_admin ||
            ($loggedOrgId == $reqOrgId && in_array($loggedRoleRight, [RoleRight::RIGHT_EDIT, RoleRight::RIGHT_ALL]))
        ) {
            if (isset($request->data['approved'])) {
                $user->approved = $request->data['approved'];
            }

            try {
                $user->save();
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Invite user failure');
            }

            if (isset($request->data['role_id']) || isset($request->data['org_id'])) {

                $validator = \Validator::make(
                    $request->all(),
                    [
                        'data.role_id' => 'required',
                        'data.org_id'  => 'required',
                    ]
                );

                if ($validator->fails()) {

                    return $this->errorResponse('Invite user failure');
                }

                $userToOrgRole = new UserToOrgRole;

                $userToOrgRole->user_id = $user->id;
                $userToOrgRole->role_id = $request->data['role_id'];
                $userToOrgRole->org_id = $request->data['org_id'];

                try {
                    $userToOrgRole->save();
                } catch (QueryException $e) {
                    Log::error($e->getMessage());

                    return $this->errorResponse('Invite user failure');
                }
            }
        } else {
            try {
                $user->save();
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('Invite user failure');
            }
        }

        //to do: send mail with hash id to user

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
        $data = $request->data;

        $validator = \Validator::make(
            $request->all(),
            [
                'data.firstname'         => 'required|string',
                'data.lastname'          => 'required|string',
                'data.email'             => 'required|email',
                'data.password'          => 'required|string',
                'data.password_confirm'  => 'required|string|same:data.password',
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse('User registration failure', $validator->errors()->messages());
        }

        $apiKey = Uuid::generate(4)->string;
        $user = new User;

        $user->username = !empty($request->data['username'])
            ? $request->data['username']
            : $this->generateUsername($request->data['email']);
        $user->password = bcrypt($request->data['password']);
        $user->email = $request->data['email'];
        $user->firstname = $request->data['firstname'];
        $user->lastname = $request->data['lastname'];
        $user->add_info = !empty($request->data['addinfo'])
            ? $request->data['addinfo']
            : null;
        $user->is_admin = 0;
        $user->active = 0;
        $user->approved = 0;
        $user->api_key = $apiKey;
        $user->hash_id = str_replace('-', '', Uuid::generate(4)->string);
        $user->remember_token = null;

        try {
            $registered = $user->save();
        } catch (QueryException $e) {
            return $this->errorResponse('User registration failure');
        }

        if (!$registered) {
            return $this->errorResponse('User registration failure');
        }

        try {
            User::where('id', $user->id)->update(['created_by' => $user->id]);
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse('User registration failure');
        }

        if (isset($data['role_id']) || isset($data['org_id'])) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'data.role_id' => 'required|integer',
                    'data.org_id'  => 'required|integer',
                ]
            );

            if ($validator->fails()) {
                return $this->errorResponse('Edit user failure', $validator->errors()->messages());
            }

            $userToOrgRole = new UserToOrgRole;

            $userToOrgRole->user_id = $user->id;
            $userToOrgRole->role_id = (int) $data['role_id'];
            $userToOrgRole->org_id = (int) $data['org_id'];

            try {
                $userToOrgRole->save();
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('User registration failure');
            }
        }

        $userSettings = new UserSetting;

        $userLocale = !empty($data['user_settings']['locale'])
            && !empty(Locale::where('locale', $data['user_settings']['locale'])->value('locale'))
            ? $data['user_settings']['locale']
            : config('app.locale');

        $userSettings->user_id = $user->id;
        $userSettings->locale = $userLocale;

        if(
            isset($data['user_settings']['newsletter_digest'])
            && is_numeric($data['user_settings']['newsletter_digest'])
        ) {
            $userSettings->newsletter_digest = $data['user_settings']['newsletter_digest'];
        }
        $userSettings->created_by = $user->id;

        try {
            $userSettings->save();
        } catch (QueryException $e) {
            Log::error($e->getMessage());

            return $this->errorResponse('User registration failure');
        }

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
            $organisation->name =  $data['org_data']['name'];
            $organisation->descript = $data['org_data']['description'];
            $organisation->activity_info = $data['org_data']['activity_info'];
            $organisation->contacts = $data['org_data']['contacts'];

            try {
                $organisation->save();
            } catch (QueryException $e) {
                Log::error($e->getMessage());

                return $this->errorResponse('User registration failure');
            }
        }

        return $this->successResponse(['api_key' => $apiKey], true);
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
}
