<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Page;
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
                    'sectionId'    => $id,
                ]
            );
        } else {
            return redirect()->back();
        }
    }

    public function add(Request $request, $id)
    {
        if ($request->has('back')) {
            return redirect()->route('adminSubSections', ['id' => $id]);
        }

        $sections = $this->getMainSections();

        if ($request->has('create')) {
            $validator = \Validator::make($request->all(), [
                'parent_id' => 'required|integer|exists:sections,id|digits_between:1,10',
            ]);

            if (!$validator->fails()) {
                $rq = Request::create('/api/addSection', 'POST', [
                    'data' => [
                        'name'       => $request->offsetGet('name'),
                        'active'     => $request->offsetGet('active'),
                        'parent_id'  => $request->offsetGet('parent_id'),
                        'ordering'   => $request->offsetGet('ordering'),
                        'forum_link' => $request->offsetGet('forum_link'),
                    ]
                ]);
                $api = new ApiSection($rq);
                $result = $api->addSection($rq)->getData();
            } else {
                $result = app('App\Http\Controllers\ApiController')->errorResponse(
                    __('custom.add_section_fail'),
                    $validator->errors()->messages()
                )->getData();
            }

            if (!empty($result->success)) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return redirect('/admin/subsections/view/'. $result->id);
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));

                return back()->withErrors($result->errors)->withInput(Input::all());
            }
        }

        return view(
            'admin/subsectionAdd',
            [
                'class'     => 'user',
                'fields'    => self::getSubsectionTransFields(),
                'sections'  => $sections,
                'sectionId' => $id,
            ]
        );
    }

    /**
     * Displays information for a given subsection
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        $sections = $this->getMainSections(true);
        $perPage = 10;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'id' => $id
            ]
        ];

        $request = Request::create('/api/listSubections', 'POST', $params);
        $api = new ApiSection($request);
        $result = $api->listSubsections($request)->getData();
        $section = is_array($result->subsections) && !empty($result->subsections[0]) ? $result->subsections[0] : null;

        if (!is_null($section)) {

            return view(
                'admin/subsectionView',
                [
                    'class'    => 'user',
                    'section'  => $this->getModelUsernames($section),
                    'themes'   => $this->prepareMainCategories(),
                    'sections' => $sections
                ]
            );
        }

        return redirect()->back();
    }

    /**
     * Edit a section based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        $class = 'user';
        $fields = self::getSubsectionTransFields();
        $model = Section::find($id);
        $sections = $this->getMainSections();

        if (!is_null($model)) {
            $model = $this->getModelUsernames($model->loadTranslations());
        }

        if ($request->has('edit')) {
            $rq = Request::create('/api/editSection', 'POST', [
                'id'   => $id,
                'data' => [
                    'name'       => $request->offsetGet('name'),
                    'active'     => $request->offsetGet('active'),
                    'parent_id'  => $request->offsetGet('parent_id'),
                    'ordering'   => $request->offsetGet('ordering'),
                    'forum_link' => $request->offsetGet('forum_link'),
                ]
            ]);

            $api = new ApiSection($rq);
            $result = $api->editSection($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return back();
            } else {
                $request->session()->flash('alert-danger', __('custom.edit_error'));

                return back()->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return view('admin/subsectionEdit', compact('class', 'fields', 'model', 'sections'));
    }

    /**
     * Delete a section based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        $pages = Page::where('section_id', $id)->first();

        if (is_null($pages)) {

            $class = 'user';

            $rq = Request::create('/api/deleteSection', 'POST', [
                'id' => $id,
            ]);

            $api = new ApiSection($rq);
            $result = $api->deleteSection($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }

            return redirect()->back();
        } else {
            $request->session()->flash('alert-danger', __('custom.section_pages_delete_error'));

            return redirect()->back();
        }
    }

    public function getMainSections($parse = false)
    {
        $request = Request::create('/api/listSections', 'POST', []);
        $api = new ApiSection($request);
        $result = $api->listSections($request)->getData();
        $sections = isset($result->sections) ? $result->sections : [];

        if ($parse) {
            $parsedSections = [];

            foreach ($sections as $section) {
                $parsedSections[$section->id] = $section->name;
            }

            return $parsedSections;
        }

        return $sections;
    }
}
