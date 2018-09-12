<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\SectionController as ApiSection;

class SectionController extends AdminController
{
     /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getSectionTransFields()
    {
        return [
            [
                'label'    => 'custom.name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
        ];
    }

    /**
     * Lists sections
     *
     * @param Request $request
     *
     * @return view with list of sections
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            $request = Request::create('/api/listSections', 'POST', $params);
            $api = new ApiSection($request);
            $result = $api->listSections($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->sections) ? $result->sections : [],
                isset($result->total_records) ? $result->total_records : 0,
                [],
                $perPage
            );

            return view(
                'admin/sectionsList',
                [
                    'class'      => 'user',
                    'sections'   => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
