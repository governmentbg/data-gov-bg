<?php

namespace App\Http\Controllers\Api;

use App\CustomSetting;
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
        $validator = \Validator::make($data, ['id' => 'required|int|exists:custom_settings,id|max:10']);

        if (!$validator->fails()) {
            $query = CustomSetting::find($data['id']);

            try {
                $query->delete();

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_custom_fail'), $validator->errors()->messages());
    }
}
