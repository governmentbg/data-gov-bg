<?php

namespace App\Http\Controllers\Api;

use App\User;
use App\UserFollow;
use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Http\Controllers\ApiController;

class UserFollowController extends ApiController
{

    /**
     * Function for creating follows
     *
     * @param string api_key - required
     * @param integer user_id - required
     * @param integer org_id - optional
     * @param integer group_id - optional
     * @param integer data_set_id - optional
     * @param integer category_id - optional
     * @param integer tag_id - optional
     * @param integer follow_user_id - optional
     * @param integer news - optional
     *
     * @return json $response - response with success or error
     */
    public function addFollow(Request $request)
    {
        $data = $request->all();

        $validator = \Validator::make($data, [
            'user_id'           => 'required|integer|digits_between:1,10',
            'org_id'            => 'nullable|integer|digits_between:1,10',
            'group_id'          => 'nullable|integer|digits_between:1,10',
            'data_set_id'       => 'nullable|integer|digits_between:1,10',
            'category_id'       => 'nullable|integer|digits_between:1,10',
            'tag_id'            => 'nullable|integer|digits_between:1,10',
            'follow_user_id'    => 'nullable|integer|digits_between:1,10',
            'news'              => 'nullable|boolean',
        ]);

        if (!$validator->fails()) {
            $follow = new UserFollow;

            $follow->user_id = $data['user_id'];

            if (!empty($data['org_id'])) {
                $follow->org_id = $data['org_id'];
            }

            if (!empty($data['group_id'])) {
                $follow->group_id = $data['group_id'];
            }

            if (!empty($data['data_set_id'])) {
                $follow->data_set_id = $data['data_set_id'];
            }

            if (!empty($data['category_id'])) {
                $follow->category_id = $data['category_id'];
            }

            if (!empty($data['tag_id'])) {
                $follow->tag_id = $data['tag_id'];
            }

            if (!empty($data['follow_user_id'])) {
                $follow->follow_user_id = $data['follow_user_id'];
            }

            if (!empty($data['news'])) {
                $follow->news = $data['news'];
            } else {
                $follow->news = 0;
            }

            try {
                $follow->save();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::USERS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'user_id'          => \Auth::user()->id,
                    'ip_address'       => $_SERVER['REMOTE_ADDR'],
                    'user_agent'       => $_SERVER['HTTP_USER_AGENT'],
                    'action_object'    => $follow->id,
                    'action_msg'       => 'User followed',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_follow_fail'), $validator->errors()->messages());
    }

    /**
     * Function for deleting follows
     *
     * @param string api_key - required
     * @param integer user_id - required
     * @param integer org_id - optional
     * @param integer group_id - optional
     * @param integer data_set_id - optional
     * @param integer category_id - optional
     * @param integer tag_id - optional
     * @param integer follow_user_id - optional
     *
     * @return json $response - response with success or error
     */
    public function unFollow(Request $request)
    {
        $data = $request->all();

        $validator = \Validator::make($data, [
            'user_id'           => 'required|integer|digits_between:1,10',
            'org_id'            => 'nullable|integer|digits_between:1,10',
            'group_id'          => 'nullable|integer|digits_between:1,10',
            'data_set_id'       => 'nullable|integer|digits_between:1,10',
            'category_id'       => 'nullable|integer|digits_between:1,10',
            'tag_id'            => 'nullable|integer|digits_between:1,10',
            'follow_user_id'    => 'nullable|integer|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $query = UserFollow::where('user_id', $data['user_id']);

            if (!empty($data['org_id'])) {
                $query->where('org_id', $data['org_id']);
            }

            if (!empty($data['group_id'])) {
                $query->where('group_id', $data['group_id']);
            }

            if (!empty($data['data_set_id'])) {
                $query->where('data_set_id', $data['data_set_id']);
            }

            if (!empty($data['category_id'])) {
                $query->where('category_id', $data['category_id']);
            }

            if (!empty($data['tag_id'])) {
                $query->where('tag_id', $data['tag_id']);
            }

            if (!empty($data['follow_user_id'])) {
                $query->where('follow_user_id', $data['follow_user_id']);
            }

            try {
                $query->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::USERS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'user_id'          => \Auth::user()->id,
                    'ip_address'       => $_SERVER['REMOTE_ADDR'],
                    'user_agent'       => $_SERVER['HTTP_USER_AGENT'],
                    'action_msg'       => 'User unfollowed',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.stop_following'), $validator->errors()->messages());
    }

    /**
     * Function for getting the number of follows
     * a given user has
     *
     * @param string api_key - required
     * @param array criteria - required
     * @param integer criteria[id] - required
     *
     * @return json $response - response with success or error
     */
    public function getFollowersCount(Request $request)
    {
        $data = $request->get('criteria', []);

        $validator = \Validator::make($data, ['id' => 'required|integer|digits_between:1,10']);

        if (!$validator->fails()) {
            $query = UserFollow::where('follow_user_id', $data['id']);
            $count = $query->count();
            $users = $query->select('user_id')->get()->toArray();

            $logData = [
                'module_name'      => Module::getModuleName(Module::USERS),
                'action'           => ActionsHistory::TYPE_DEL,
                'user_id'          => \Auth::user()->id,
                'ip_address'       => $_SERVER['REMOTE_ADDR'],
                'user_agent'       => $_SERVER['HTTP_USER_AGENT'],
                'action_object'    => $data['id'],
                'action_msg'       => 'Got user followers count',
            ];

            Module::add($logData);
            return $this->successResponse(['count' => $count, 'followers' => $users], true);
        }

        return $this->errorResponse(__('custom.retrieve_count_fail'), $validator->errors()->messages());
    }
}
