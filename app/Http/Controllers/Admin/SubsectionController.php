<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\SectionController as ApiSection;

class SubsectionController extends AdminController
{
     /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getSubsectionTransFields()
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
     * Lists subsections
     *
     * @param Request $request
     *
     * @return view with list of subsections
     */
    public function list(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $mainSection = Section::where('id', $id)->first();

            if (isset($mainSection->id)) {
                $perPage = 10;
                $params = [
                    'records_per_page' => $perPage,
                    'page_number'      => !empty($request->page) ? $request->page : 1,
                    'criteria'         => [
                        'section_id' => $id,
                    ],
                ];

                $request = Request::create('/api/listSubsections', 'POST', $params);
                $api = new ApiSection($request);
                $result = $api->listSubsections($request)->getData();

                $paginationData = $this->getPaginationData(
                    isset($result->subsections) ? $result->subsections : [],
                    isset($result->total_records) ? $result->total_records : 0,
                    [],
                    $perPage
                );

                return view(
                    'admin/subsectionsList',
                    [
                        'class'        => 'user',
                        'subsections'  => $paginationData['items'],
                        'pagination'   => $paginationData['paginate'],
                        'sectionName'  => $mainSection->name,
                    ]
                );
            } else {
                return redirect()->back();
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
