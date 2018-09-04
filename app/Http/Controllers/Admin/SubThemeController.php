<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\CategoryController as ApiCategory;

class SubThemeController extends AdminController
{
    /**
     * Lists subthemes
     *
     * @param Request $request
     *
     * @return view with list of subthemes
     */
    public function list(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $criteria = [];
            $perPage = 10;

            if (isset($request->q)) {
                $criteria['keywords'] = $request->q;
            }

            $criteria['category_id'] = $id;

            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => $criteria
            ];
            $request = Request::create('/api/listTags', 'POST', $params);
            $api = new ApiCategory($request);
            $result = $api->listTags($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->tags) ? $result->tags : [],
                isset($result->total_records) ? $result->total_records : 0,
                isset($criteria['keywords']) ? ['q' => $criteria['keywords']] : [],
                $perPage
            );

            return view(
                'admin/subThemesList',
                [
                    'class'       => 'user',
                    'themes'      => $paginationData['items'],
                    'pagination'  => $paginationData['paginate'],
                    'search'      => isset($criteria['keywords']) ? $criteria['keywords'] : null,
                    'mainThemeId' => $id,
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
