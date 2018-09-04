<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\CategoryController as ApiCategory;

class ThemeController extends AdminController
{
    /**
     * Lists themes
     *
     * @param Request $request
     *
     * @return view with list of themes
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $criteria = [];
            $perPage = 10;

            if (isset($request->q)) {
                $criteria['keywords'] = $request->q;
            }

            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => $criteria
            ];
            $request = Request::create('/api/listMainCategories', 'POST', $params);
            $api = new ApiCategory($request);
            $result = $api->listMainCategories($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->categories) ? $result->categories : [],
                isset($result->total_records) ? $result->total_records : 0,
                isset($criteria['keywords']) ? ['q' => $criteria['keywords']] : [],
                $perPage
            );

            return view(
                'admin/themesList',
                [
                    'class'      => 'user',
                    'themes'     => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                    'search'     => isset($criteria['keywords']) ? $criteria['keywords'] : null,
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
