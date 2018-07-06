<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\RoleRight;

class RightController extends ApiController
{
    /**
     * API function for listing all types of rights
     *
     * @param Request $request - POST request
     * @return json $response - response with status and list of rights successfull
     */
    public function listRights(Request $request)
    {
        $rights = RoleRight::getRights();

        if (!empty($rights)) {
            foreach ($rights as $id => $right) {
                $result[] = [
                    'id' => $id,
                    'name' => $right,
                ];
            }

            return $this->successResponse($result);
        } else {
            return $this->errorResponse('Get rights data failure');
        }
    }
}
