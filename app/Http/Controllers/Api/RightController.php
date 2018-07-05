<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\RoleRight;

class RightController extends ApiController
{
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

            return new JsonResponse([
                'success'   => true,
                'rights'    => $result,
            ], 200);
        } else {
            return new JsonResponse([
                'success'   => false,
                'status'    => 500,
                'error'     => [
                    'type'      => parent::ERROR_GENERAL,
                    'message'   => "Get rights data failure"
                ],
            ], 200);
        }
    }
}
