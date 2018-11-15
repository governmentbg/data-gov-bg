<?php

namespace App\Http\Controllers\Admin;

use App\Page;
use App\Role;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\HelpController as ApiHelp;
use App\Http\Controllers\Api\ThemeController as ApiTheme;
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
        $perPage = 10;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $params['criteria']['order'] = [
            'type'  => $request->offsetGet('order'),
            'field' => 'created_at',
        ];

        $request = Request::create('/api/listSections', 'POST', $params);
        $api = new ApiSection($request);
        $result = $api->listSections($request)->getData();
        $getParams = array_except(app('request')->input(), ['page']);

        $paginationData = $this->getPaginationData(
            isset($result->sections) ? $result->sections : [],
            isset($result->total_records) ? $result->total_records : 0,
            $getParams,
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

    public function add(Request $request)
    {
        if ($request->has('back')) {
            return redirect()->route('adminSections');
        }

        $themes = $this->getColorThemes(true);

        if ($request->has('create')) {
            $rq = Request::create('/api/addSection', 'POST', [
                'data' => [
                    'name'          => $request->offsetGet('name'),
                    'active'        => $request->offsetGet('active'),
                    'parent_id'     => $request->offsetGet('parent_id'),
                    'ordering'      => $request->offsetGet('ordering'),
                    'forum_link'    => $request->offsetGet('forum_link'),
                    'theme'         => $request->offsetGet('theme'),
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
                'class'         => 'user',
                'fields'        => self::getSectionTransFields(),
                'themes'        => $themes,
            ]
        );
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
            if ($request->has('back')) {
                return redirect()->route('adminSections');
            }

            $perPage = 10;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'api_key'          => Auth::user()->api_key,
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
                        'themes'   => $this->getColorThemes(true)
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
            $themes = $this->getColorThemes(true);

            if (!is_null($model)) {
                $model = $this->getModelUsernames($model->loadTranslations());
            }

            if ($request->has('edit')) {
                $rq = Request::create('/api/editSection', 'POST', [
                    'id'   => $id,
                    'data' => [
                        'name'          => $request->offsetGet('name'),
                        'active'        => $request->offsetGet('active'),
                        'parent_id'     => $request->offsetGet('parent_id'),
                        'ordering'      => $request->offsetGet('ordering'),
                        'forum_link'    => $request->offsetGet('forum_link'),
                        'theme'         => $request->offsetGet('theme'),
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
            $subSections = Section::where('parent_id', $id)->first();

            if (is_null($pages)) {
                if (is_null($subSections)) {
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
                    $request->session()->flash('alert-danger', __('custom.section_subsections_delete_error'));

                    return redirect('/admin/sections/list');
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.section_pages_delete_error'));

                return redirect('/admin/sections/list');
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Function for getting an array of color themes
     *
     * @return array of themes
     */
    public function getColorThemes($parse = false)
    {
        $request = Request::create('/api/listThemes', 'POST', []);
        $api = new ApiTheme($request);
        $result = $api->listThemes($request)->getData();

        if ($parse) {
            $themes = [];

            if (isset($result->data)) {
                foreach ($result->data as $theme) {
                    $themes[$theme->id] = $theme->name;
                }
            }

            return $themes;
        }

        return isset($result->data) ? $result->data : $result;
    }
}
