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
Use \DB;
Use Uuid;

class UserController extends ApiController
{
    public function getUserData(Request $request)
    {
        $result = [];
        $criteria = is_array($request->json('criteria')) ? $request->json('criteria') : null;
        $userFilters = ['active', 'approved', 'is_admin', 'id'];
        $userToOrgRoleFilters = [];
        $order = [];
        $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
        $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
        $search = !empty($criteria['search']) ? $criteria['search'] : null;
        $pagination = is_numeric($request->json('records_per_page')) ? $request->json('records_per_page') : null;
        $page = is_numeric($request->json('page_number')) ? $request->json('page_number') : null;

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
            if (is_null($criteria)) {
                $users = \App\User::paginate($pagination, ['*'], 'page', $page);
            } else {
                $users = is_null($search)
                    ? \App\User::where($criteria)
                        ->whereHas('userToOrgRole', function($q) use($userToOrgRoleFilters)
                            {
                                $q->where($userToOrgRoleFilters);
                            })
                            ->orderBy($order['field'], $order['type'])
                                ->paginate($pagination, ['*'], 'page', $page)
                    : \App\User::where($criteria)
                        ->whereHas('userToOrgRole', function($q) use($userToOrgRoleFilters)
                            {
                                $q->where($userToOrgRoleFilters);
                            })
                        ->where('firstname', 'like', '%'. $search .'%')
                        ->orWhere('lastname', 'like', '%'. $search .'%')
                        ->orWhere('username', 'like', '%'. $search .'%')
                        ->orWhere('email', 'like', '%'. $search .'%')
                            ->orderBy($order['field'], $order['type'])
                                ->paginate($pagination, ['*'], 'page', $page);
            }

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

        return response()->json(['success' => true, 'users'=> $result], 200);
    }

    public function register(Request $request)
    {
        $data = $request->json('data');

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
            error_log(1);
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
                error_log(2);
                return ApiController::errorResponse('User registration failure');
            }
        }

        $apiKey = Uuid::generate(4)->string;
        $user = new User;

        $user->username = !empty($request->data['username'])
            ? $request->data['username']
            : '';
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
            error_log(3);
            return ApiController::errorResponse('User registration failure');
        }

        if (!$registered) {
            error_log(5);
            return ApiController::errorResponse('User registration failure');
        }

        try {
            User::where('id', $user->id)->update(['created_by' => $user->id]);
        } catch (QueryException $e) {
            error_log(6);
            return ApiController::errorResponse('User registration failure');
        }

        if (!empty($data['role_id'])) {

            $role = new UserToOrgRole;

            $role->user_id = $user->id;
            $role->role_id = $data['role_id'];

            $userRole = $role->save();
            try {
                $userRole = $role->save();
            } catch (QueryException $e) {
                error_log(8);
                return ApiController::errorResponse('User registration failure');
            }
        }

        $userSettings = new UserSetting;

        $locale = !empty($data['user_settings']['locale'])
            ? Locale::where('locale', $data['user_settings']['locale'])->first()
            : null;

        $userLocale = !empty($locale['id'])
            ? $locale['id']
            : \App::getLocale();

        $userSettings->user_id = $user->id;
        $userSettings->locale_id = $userLocale;

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
            error_log(10);
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
                error_log(12);
                return ApiController::errorResponse('User registration failure');
            }
        }

        return response()->json(['success' => true, 'api_key' => $apiKey], 200);
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
