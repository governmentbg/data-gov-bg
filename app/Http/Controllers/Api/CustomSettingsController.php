<?php

namespace App\Http\Controllers\Api;

use App\CustomSetting;
use App\Module;
use App\DataSet;
use App\Resource;
use App\RoleRight;
use App\Organisation;
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
            if (!is_null($query->org_id)) {
                $organisation = Organisation::where('id', $query->org_id)->first();
                if ($organisation->type != Organisation::TYPE_GROUP) {
                    $rightCheck = RoleRight::checkUserRight(
                        Module::ORGANISATIONS,
                        RoleRight::RIGHT_EDIT,
                        [
                            'org_id'       => $organisation->id
                        ],
                        [
                            'created_by'   => $organisation->created_by,
                            'org_id'       => $organisation->id
                        ]
                    );
                } else {
                    $rightCheck = RoleRight::checkUserRight(
                        Module::GROUPS,
                        RoleRight::RIGHT_ALL,
                        [
                            'group_id'       => $organisation->id
                        ],
                        [
                            'created_by'     => $organisation->created_by,
                            'group_ids'      => [$organisation->id]
                        ]
                    );
                }

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }
            }

            if (!is_null($query->data_set_id)) {
                $dataset = DataSet::where('id', $query->data_set_id)->first();
                $rightCheck = RoleRight::checkUserRight(
                    Module::DATA_SETS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by'     => $dataset->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }
            }

            if (!is_null($query->resource_id)) {
                $resource = Resource::where('id', $query->resource_id)->first();
                $rightCheck = RoleRight::checkUserRight(
                    Module::RESOURCES,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by'     => $resource->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }
            }

            try {
                $query->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::CUSTOM_SETTINGS),
                    'action'           => ActionsHistory::TYPE_DEL,
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
