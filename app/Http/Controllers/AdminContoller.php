<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class AdminController extends Controller
{
    public function getOrgDropdown($userId = null, $count = null)
    {
        $request = Request::create('/api/listOrganisations', 'POST', ['criteria' => ['user_id' => $userId]]);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();
        $organisations = [];

        foreach ($result->organisations as $index => $row) {
            $organisations[$row->id] = $row->name;

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $organisations;
    }
}
