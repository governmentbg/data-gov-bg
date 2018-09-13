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

    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            $themes = $this->prepareMainCategories();

            if ($request->has('create')) {
                $rq = Request::create('/api/addSection', 'POST', [
                    'data' => [
                        'name'       => $request->offsetGet('name'),
                        'active'     => $request->offsetGet('active'),
                        'parent_id'  => $request->offsetGet('parent_id'),
                        'ordering'   => $request->offsetGet('ordering'),
                        'read_only'  => $request->offsetGet('read_only'),
                        'forum_link' => $request->offsetGet('forum_link'),
                        'theme'      => $request->offsetGet('theme'),
                    ]
                ]);
                $api = new ApiSection($rq);
                $result = $api->addSection($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/sections/view/'. $result->id);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view(
                'admin/sectionAdd',
                [
                    'class'  => 'user',
                    'fields' => self::getSectionTransFields(),
                    'themes' => $themes
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Displays information for a given section
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => [
                    'id' => $id
                ]
            ];

            $request = Request::create('/api/listSections', 'POST', $params);
            $api = new ApiSection($request);
            $result = $api->listSections($request)->getData();
            $section = is_array($result->sections) && !empty($result->sections[0]) ? $result->sections[0] : null;

            if (!is_null($section)) {

                return view(
                    'admin/sectionView',
                    [
                        'class'    => 'user',
                        'section'  => $this->getModelUsernames($section),
                        'themes'   => $this->prepareMainCategories()
                    ]
                );
            }

            return redirect('/admin/sections/list');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getSectionTransFields();
            $model = Section::find($id);
            $themes = $this->prepareMainCategories();

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
                        'read_only'  => $request->offsetGet('read_only'),
                        'forum_link' => $request->offsetGet('forum_link'),
                        'theme'      => $request->offsetGet('theme'),
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

            return view('admin/sectionEdit', compact('class', 'fields', 'model', 'themes'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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
        if (Role::isAdmin()) {
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

                    return redirect('/admin/sections/list');
                } else {
                    $request->session()->flash('alert-danger', __('custom.delete_error'));

                    return redirect('/admin/sections/list')->withErrors(isset($result->errors) ? $result->errors : []);
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.section_pages_delete_error'));

                return redirect('/admin/sections/list');
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
