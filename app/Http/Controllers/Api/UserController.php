<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use PDOException;
use App\User;
use App\UserSetting;
use App\Locale;
use App\Organisation;
use App\UserToOrgRole;
use App\RoleRight;
Use \DB;
Use Uuid;

class UserController extends ApiController
{
    /**
     * List user records by given criteria
     *
     * @param object $request - POST request
     * @return json $response - response with status and users if successfull
     */
    public function listUsers(Request $request)
    {
        $result = [];
        $criteria = is_array($request->criteria)
            ? $request->criteria
            : null;
        $userFilters = ['active', 'approved', 'is_admin', 'id'];
        $userToOrgRoleFilters = [];
        $orderType = !empty($criteria['order']['type']) ? $criteria['order']['type'] : null;
        $orderField = !empty($criteria['order']['field']) ? $criteria['order']['field'] : null;
        $pagination = is_numeric($request->records_per_page)
            ? $request->records_per_page
            : null;
        $page = is_numeric($request->page_number)
            ? $request->page_number
            : null;

        if (!empty($criteria['org_id'])) {
            $userToOrgRoleFilters['org_id'] = $criteria['org_id'];
        }

        if (!empty($criteria['role_id'])) {
            $userToOrgRoleFilters['role_id'] = $criteria['role_id'];
        }

        if (is_array($criteria)) {
            foreach ($criteria as $key => $value) {
                if (!in_array($key, $userFilters)) {
                    unset($criteria[$key]);
                }
            }
        }

        try {
            $query = User::select();

            if (!is_null($criteria)) {
                $query->where($criteria);
            }

            if (!empty($userToOrgRoleFilters)) {
                $query->whereHas('userToOrgRole', function($q) use($userToOrgRoleFilters)
                    {
                        $q->where($userToOrgRoleFilters);
                    });
            }

            if ($pagination) {
                $query->paginate($pagination, ['*'], 'page', $page);
            }

            if ($orderType && $orderField) {
                $query->orderBy($orderField, $orderType);
            }

            $users = $query->get();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Get user list failure');
        }

        if (!empty($users)) {
            foreach ($users as $user) {
                $result[] = [
                    'id'            => $user->id,
                    'username'      => $user->username,
                    'email'         => $user->email,
                    'firstname'     => $user->firstname,
                    'lastname'      => $user->lastname,
                    'add_info'      => $user->add_info,
                    'is_admin'      => $user->is_admin,
                    'active'        => $user->active,
                    'approved'      => $user->approved,
                    'api_key'       => $user->api_key,
                    'hash_id'       => $user->hash_id,
                    'created_at'    => $user->created,
                    'updated_at'    => $user->updated_at,
                    'created_by'    => $user->created_by,
                    'updated_by'    => $user->updated_by,
                ];
            }
        }

        return $this->successResponse(['users'=> $result, 'total_records' => count($users)], true);
    }

    /**
     * Search in user records by given keywords
     *
     * @param object $request - POST request
     * @return json $response - response with status and users if successfull
     */
    public function searchUsers(Request $request)
    {
        $result = [];
        $criteria = is_array($request->criteria)
            ? $request->criteria
            : null;
        $order = !empty($criteria['order']['type']) && !empty($criteria['order']['field']);
        $search = !empty($criteria['keywords'])
            ? $criteria['keywords']
            : null;
        $pagination = is_numeric($request->records_per_page)
            ? $request->records_per_page
            : null;
        $page = is_numeric($request->page_number)
            ? $request->page_number
            : null;

        try {
            $query = User::select();

            if (!is_null($search)) {
                $query->where(function ($qr) use ($search) {
                    $qr->where('firstname', 'like', '%'. $search .'%')
                        ->orWhere('lastname', 'like', '%'. $search .'%')
                        ->orWhere('username', 'like', '%'. $search .'%')
                        ->orWhere('email', 'like', '%'. $search .'%');
                });
            }

            if ($pagination) {
                $query->paginate($pagination, ['*'], 'page', $page);
            }

            if ($order) {
                $query->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }

            $users = $query->get();

        } catch (QueryException $e) {

            return ApiController::errorResponse('Search users failure');
        }

        if (!empty($users)) {
            foreach ($users as $user) {
                $result[] = [
                    'id'            => $user->id,
                    'username'      => $user->username,
                    'email'         => $user->email,
                    'firstname'     => $user->firstname,
                    'lastname'      => $user->lastname,
                    'add_info'      => $user->add_info,
                    'is_admin'      => $user->is_admin,
                    'active'        => $user->active,
                    'approved'      => $user->approved,
                    'api_key'       => $user->api_key,
                    'hash_id'       => $user->hash_id,
                    'created_at'    => $user->created,
                    'updated_at'    => $user->updated_at,
                    'created_by'    => $user->created_by,
                    'updated_by'    => $user->updated_by,
                ];
            }
        }

        return $this->successResponse(['users'=> $result, 'total_records' => count($users)], true);
    }

    /**
     * Get user roles and organisations by given user id
     *
     * @param object $request - POST request
     * @return json $response - response with status and user if successfull
     */
    public function getUserRoles(Request $request)
    {
        $result = [];

        $validator = \Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Get user roles failure');
        }

        $roles = \App\UserToOrgRole::where('user_id', $request->id)->get();

        if (!empty($roles)) {
            foreach ($roles as $role) {
                $result['roles'][] = [
                    'org_id'  => $role['org_id'],
                    'role_id' => $role['role_id'],
                ];
            }
        }

        return $this->successResponse(['user' => $result], true);
    }

    /**
     * Get user settings by given user id
     *
     * @param object $request - POST request
     * @return json $response - response with status and user if successfull
     */
    public function getUserSettings(Request $request)
    {
        $result = [];

        $validator = \Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Get user settings failure');
        }

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

    /**
     * Add new user record
     *
     * @param object $request - POST request
     * @return json $response - response with status and api key if successfull
     */
    public function addUser(Request $request)
    {
        $data = $request->data;

        $validator = \Validator::make(
            $request->all(),
            [
                'data.firstname'         => 'required',
                'data.lastname'          => 'required',
                'data.email'             => 'required|email',
                'data.password'          => 'required',
                'data.password_confirm'  => 'required|same:data.password',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Add user failure');
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

            return ApiController::errorResponse('Add user failure');
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

                return ApiController::errorResponse('Add user failure');
            }

            $userToOrgRole = new UserToOrgRole;

            $userToOrgRole->user_id = $user->id;
            $userToOrgRole->role_id = (int) $data['role_id'];
            $userToOrgRole->org_id = (int) $data['org_id'];

            try {
                $userToOrgRole->save();
            } catch (QueryException $e) {

                return ApiController::errorResponse('Add user failure');
            }
        }

        if (!empty($data['user_settings']['locale']) || isset($data['user_settings']['newsletter_digest'])) {
            $userSettings = new UserSetting;

            $userLocale = !empty($data['user_settings']['locale']) && !empty(Locale::where('locale', $data['user_settings']['locale'])->value('locale'))
                ? $data['user_settings']['locale']
                : config('app.locale');

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

                return ApiController::errorResponse('Add user failure');
            }
        }

        //to do: send mail to user

        return $this->successResponse(['api_key' => $apiKey], true);
    }

    /**
     * Edit existing user record
     *
     * @param object $request - POST request
     * @return json $response - response with status and api key if successfull
     */
    public function editUser(Request $request)
    {
        $data = $request->data;
        $id = $request->id;

        $validator = \Validator::make(
            $request->all(),
            [
                'data' => 'required',
                'id'   => 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Edit user failure');
        }

        if (empty($user = User::find($request->id))) {
            return ApiController::errorResponse('Edit user failure');
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

                return ApiController::errorResponse('Edit user failure');
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

                return ApiController::errorResponse('Edit user failure');
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
            return ApiController::errorResponse('Edit user failure');
        }

        if (!empty($newUserData)) {
            $newUserData['updated_by'] = \Auth::id();

            try {
                User::where('id', $request->id)->update($newUserData);
            } catch (QueryException $e) {

                return ApiController::errorResponse('Edit user failure');
            }
        }

        if (!empty($orgAndRoles)) {
            try {
                UserToOrgRole::where('user_id', $request->id)->update($orgAndRoles);
            } catch (QueryException $e) {

                return ApiController::errorResponse('Edit user failure');
            }
        }

        if (!empty($newSettings)) {
            try {
                UserSetting::where('user_id', $request->id)->update($newSettings);
            } catch (QueryException $e) {

                return ApiController::errorResponse('Edit user failure');
            }
        }

        return $this->successResponse(['api_key' => $user['api_key']], true);
    }

    /**
     * Delete existing user record
     *
     * @param object $request - POST request
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

            return ApiController::errorResponse('Delete user failure');
        }

        if (empty($user = User::find($request->id))) {
            return ApiController::errorResponse('Delete user failure');
        }

        try {
            $user->delete();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Delete user failure');
        }

        try {
            $user->deleted_by = \Auth::id();
            $user->save();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Delete user failure');
        }

        return $this->successResponse();
    }

    /**
     * Generate new api key for existing user
     *
     * @param object $request - POST request
     * @return json $response - response with status
     */
    public function generateAPIKey(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'id' => 'required',
            ]
        );

        if ($validator->fails()) {

            return ApiController::errorResponse('Generate API key failure');
        }

        if (empty($user = User::find($request->id))) {
            return ApiController::errorResponse('Generate API key failure');
        }

        try {
            $user->api_key = Uuid::generate(4)->string;
            $user->updated_by = \Auth::id();
            $user->save();
        } catch (QueryException $e) {

            return ApiController::errorResponse('Generate API key failure');
        }

        return $this->successResponse();
    }

    /**
     * Invite user to register
     *
     * @param object $request - POST request
     * @return json $response - response with status
     */
    public function inviteUser(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'data.email' => 'required|email',
            ]
        );

        if ($validator->fails()) {
            return ApiController::errorResponse('Invite user failure');
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

                return ApiController::errorResponse('Invite user failure');
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

                    return ApiController::errorResponse('Invite user failure');
                }

                $userToOrgRole = new UserToOrgRole;

                $userToOrgRole->user_id = $user->id;
                $userToOrgRole->role_id = $request->data['role_id'];
                $userToOrgRole->org_id = $request->data['org_id'];

                try {
                    $userToOrgRole->save();
                } catch (QueryException $e) {

                    return ApiController::errorResponse('Invite user failure');
                }
            }
        } else {
            try {
                $user->save();
            } catch (QueryException $e) {

                return ApiController::errorResponse('Invite user failure');
            }
        }

        //to do: send mail with hash id to user

        return $this->successResponse();
    }

    /**
     * Register new user
     *
     * @param object $request - POST request
     * @return json $response - response with status and api key if successfull
     */
    public function register(Request $request)
    {
        $data = $request->data;

        $validator = \Validator::make(
            $request->all(),
            [
                'data.firstname'         => 'required',
                'data.lastname'          => 'required',
                'data.email'             => 'required|email',
                'data.password'          => 'required',
                'data.password_confirm'  => 'required|same:data.password',
            ]
        );

        if ($validator->fails()) {
            return ApiController::errorResponse('User registration failure');
        }

        if (!empty($data['orgdata'])) {
            if (
                \Validator::make(
                    $request->all(),
                    [
                        'data.orgdata.name'          => 'required',
                        'data.orgdata.type'          => 'required',
                        'data.orgdata.description'   => 'required',
                    ]
                )->fails()
            ) {
                return ApiController::errorResponse('User registration failure');
            }
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
            return ApiController::errorResponse('User registration failure');
        }

        if (!$registered) {
            return ApiController::errorResponse('User registration failure');
        }

        try {
            User::where('id', $user->id)->update(['created_by' => $user->id]);
        } catch (QueryException $e) {

            return ApiController::errorResponse('User registration failure');
        }

        if (isset($data['role_id']) || isset($data['org_id'])) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'data.role_id' => 'required',
                    'data.org_id'  => 'required',
                ]
            );

            if ($validator->fails()) {

                return ApiController::errorResponse('Edit user failure');
            }

            $userToOrgRole = new UserToOrgRole;

            $userToOrgRole->user_id = $user->id;
            $userToOrgRole->role_id = (int) $data['role_id'];
            $userToOrgRole->org_id = (int) $data['org_id'];

            try {
                $userToOrgRole->save();
            } catch (QueryException $e) {

                return ApiController::errorResponse('User registration failure');
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

            return ApiController::errorResponse('User registration failure');
        }

        if (!empty($data['org_data'])) {
            $organisation = new Organisation;

            $orgLocale = !empty($data['org_data']['locale']) && !empty(Locale::where('locale', $data['org_data']['locale'])->value('locale'))
                ? $data['org_data']['locale']
                : config('app.locale');

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
            $organisation->name = [$orgLocale => $data['org_data']['name']];
            $organisation->descript = [$orgLocale => $data['org_data']['description']];
            $organisation->activity_info = [$orgLocale => $data['org_data']['activity_info']];
            $organisation->contacts = [$orgLocale => $data['org_data']['contacts']];

            try {
                $organisation->save();
            } catch (QueryException $e) {

                return ApiController::errorResponse('User registration failure');
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
