<?php
namespace App\Http\Controllers\Api;

use App\Module;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Role;

class RightController extends ApiController
{
    /**
     * API function for listing all types of rights
     *
     * @param string api_key required
     *
     * @return json with list of rights or error
     */
    public function listRights(Request $request)
    {
        $rights = Role::getRights();

        if (!empty($rights)) {
            foreach ($rights as $id => $right) {
                $result[] = [
                    'id' => $id,
                    'name' => $right,
                ];
            }

            $logData = [
                'module_name'      => Module::getModuleName(Module::RIGHTS),
                'action'           => ActionsHistory::TYPE_SEE,
                'action_msg'       => 'Listed rights',
            ];

            Module::add($logData);
            return $this->successResponse(['rights' => $result], true);
        }

        return $this->errorResponse(__('custom.get_right_fail'));
    }
}
