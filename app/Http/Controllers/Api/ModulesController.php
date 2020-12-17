<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\Module;
use Illuminate\Http\Request;
use App\RoleRight;
use App\Http\Controllers\ApiController;

class ModulesController extends ApiController
{
    public function listModules(Request $request)
    {
        $rightCheck = RoleRight::checkUserRight(
            Module::MODULES,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        $modules = Module::getModules();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $result[] = [
                    'name'  => __('custom.' . $module),
                    'code'  => $module
                ];
            }

            return $this->successResponse(['modules' => $result], true);
        }

        return $this->errorResponse(__('custom.data_failure'));
    }

}
