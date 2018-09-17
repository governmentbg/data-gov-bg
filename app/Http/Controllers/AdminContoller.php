<?php

namespace App\Http\Controllers;

use App\ActionsHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TagController as ApiTags;
use App\Http\Controllers\Api\UserController as ApiUsers;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\ActionsHistoryController as ApiActionsHistory;

class AdminController extends Controller
{
    public function getOrgDropdown($userId = null, $count = null, $fullData = false)
    {
        $request = Request::create('/api/listOrganisations', 'POST', [
            'api_key'   => \Auth::user()->api_key,
            'criteria'  => [
                'user_id'   => $userId,
            ],
        ]);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();
        $organisations = [];

        foreach ($result->organisations as $index => $row) {
            $organisations[$row->id] = $fullData ? $row : $row->name;

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $organisations;
    }

    public function getGroupDropdown($userId = null, $count = null, $fullData = false)
    {
        $request = Request::create('/api/listGroups', 'POST', [
            'api_key'  => \Auth::user()->api_key,
            'criteria' => [
                'user_id' => $userId,
            ],
        ]);
        $api = new ApiOrganisation($request);
        $result = $api->listGroups($request)->getData();
        $groups = [];

        foreach ($result->groups as $index => $row) {
            $groups[$row->id] = $fullData ? $row : $row->name;

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $groups;
    }

    public function getUserDropdown($count = null)
    {
        $request = Request::create('/api/listUsers', 'POST');
        $api = new ApiUsers($request);
        $result = $api->listUsers($request)->getData();
        $users = [];

        foreach ($result->users as $index => $row) {
            if ($row->id !== 1) {
                $users[$row->id] = $row->firstname .' '. $row->lastname;

                if ($count && $index == $count) {
                    break;
                }
            }
        }

        return $users;
    }

    public function getTermsDropdown($count = null)
    {
        $request = Request::create('/api/listTermsOfUse', 'POST');
        $api = new ApiTermsOfUse($request);
        $result = $api->listTermsOfUse($request)->getData();
        $terms = [];

        foreach ($result->terms_of_use as $index => $row) {
            $terms[$row->id] = $row->name;

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $terms;
    }

    public function getTagsDropdown($count = null)
    {
        $request = Request::create('/api/listTags', 'POST');
        $api = new ApiTags($request);
        $result = $api->listTags($request)->getData();
        $tags = [];

        foreach ($result->tags as $index => $row) {
            $tags[$row->id] = $row->name;

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $tags;
    }

    public function getMainCategoriesDropdown($count = null)
    {
        $request = Request::create('/api/listMainCategories', 'POST');
        $api = new ApiCategory($request);
        $result = $api->listMainCategories($request)->getData();
        $categories = [];

        foreach ($result->categories as $index => $row) {
            $categories[$row->id] = $row->name;

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $categories;
    }

    public function getIpDropdown($view = '', $count = null)
    {
        $ips = [];
        $collection = ActionsHistory::select('ip_address');

        if ($view == 'login') {
            $collection->where('action', ActionsHistory::TYPE_LOGIN);
        }

        $collection = $collection->get();

        foreach ($collection as $index => $ip) {
            if (!in_array($ip->ip_address, $ips)) {
                $ips[] = $ip->ip_address;
            }

            if ($count && $index + 1 == $count) {
                break;
            }
        }

        return $ips;
    }
}
