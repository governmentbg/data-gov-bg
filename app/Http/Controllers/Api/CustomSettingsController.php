<?php

namespace App\Http\Controllers\Api;

use App\CustomSetting;
use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class CustomSettingsController extends ApiController
{
    /**
     * Function for deleting custom settings
     *
     * @param string api_key - required
     * @param integer id - required
     *
     * @return json $response - response with success or error
     */
    public function delete(Request $request)
    {
        $data = $request->all();
        $validator = \Validator::make($data, ['id' => 'required|int|exists:custom_settings,id|digits_between:1,10']);

        if (!$validator->fails()) {
            $query = CustomSetting::find($data['id']);

            try {
                $query->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::CUSTOM_SETTINGS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'user_id'          => \Auth::user()->id,
                    'ip_address'       => $_SERVER['REMOTE_ADDR'],
                    'user_agent'       => $_SERVER['HTTP_USER_AGENT'],
                    'action_object'    => $data['id'],
                    'action_msg'       => 'Deleted custom setting',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_custom_fail'), $validator->errors()->messages());
    }
}
