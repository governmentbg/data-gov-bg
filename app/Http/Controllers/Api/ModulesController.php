<?php

namespace App\Http\Controllers\Api;

use App\Role;
use App\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

class ModulesController extends ApiController
{
    public function listModules(Request $request)
    {
        $modules = Module::getModules();

        if (!empty($modules)) {
            foreach ($modules as $module) {
                $result[] = ['name' => $module];
            }

            return $this->successResponse(['modules' => $result], true);
        }

        return $this->errorResponse(__('custom.data_failure'));
    }

}
