<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Page;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\PageController as ApiPage;

class PageController extends AdminController
{
     /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getPageTransFields()
    {
        return [
            [
                'label'    => 'custom.title',
                'name'     => 'title',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.browser_head',
                'name'     => 'head_title',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => false,
            ],
            [
                'label'    => 'custom.browser_keywords',
                'name'     => 'meta_key_words',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => false,
            ],
            [
                'label'    => 'custom.browser_desc',
                'name'     => 'meta_descript',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => false,
            ],
            [
                'label'    => 'custom.short_txt',
                'name'     => 'abstract',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
            [
                'label'    => 'custom.content',
                'name'     => 'body',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => false,
            ],
        ];
    }

    /**
     * Lists pages
     *
     * @param Request $request
     *
     * @return view with list of pages
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'api_key'          => \Auth::user()->api_key,
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];


            $sections = Section::whereExists(function ($query) {
                    $query->select()
                        ->from('pages')
                        ->whereRaw('sections.id = pages.section_id');
                })
                ->where('parent_id', null)->get();

            $sections = $this->prepareSections($sections);

            if (isset($request->active)) {
                $params['criteria']['active'] = $request->active;
            }

            if (isset($request->section)) {
                $params['criteria']['section_id'] = $request->section;
            }

            $req = Request::create('/api/listPages', 'POST', $params);
            $api = new ApiPage($req);
            $result = $api->listPages($req)->getData();
            $getParams = array_except(app('request')->input(), ['page']);

            $paginationData = $this->getPaginationData(
                $result->pages,
                $result->total_records,
                $getParams,
                $perPage
            );

            return view('/admin/pages', [
                'class'         => 'user',
                'pages'         => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'sections'      => $sections,
            ]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Displays information for a given page
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $req = Request::create('/api/listPages', 'POST', ['criteria' => ['page_id' => $id]]);
            $api = new ApiPage($req);
            $result = $api->listPages($req)->getData();
            $page = isset($result->pages[0]) ? $result->pages[0] : null;

            if (!is_null($page)) {
                $section = Section::where('id', $page->section_id)->where('parent_id', null)->value('name');
                $page = $this->getModelUsernames($page);

                return view(
                    'admin/pagesView',
                    [
                        'class'   => 'user',
                        'page'    => $page,
                        'section' => $section,
                    ]
                );
            }

            return redirect('/admin/pages/list');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit a page based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getPageTransFields();
            $model = Page::find($id);
            $sections = [];


            if (!is_null($model)) {
                $model = $this->getModelUsernames($model->loadTranslations());
                $model->valid_from = date_create($model->valid_from)
                    ? date_format(date_create($model->valid_from), 'd-m-Y')
                    : $model->valid_from;
                $model->valid_to = date_create($model->valid_to)
                    ? date_format(date_create($model->valid_to), 'd-m-Y')
                    : $model->valid_to;

                $sections = Section::where('parent_id', null)->get();
                $sections = $this->prepareSections($sections);
            } else {
                return redirect('admin/pages/list');
            }

            $from = null;
            $to = null;

            if (date_create($request->offsetGet('valid_from'))) {
                $from = date_format(date_create($request->offsetGet('valid_from')), 'Y-m-d H:i:s');
            }

            if (date_create($request->offsetGet('valid_to'))) {
                $to = date_format(date_create($request->offsetGet('valid_to')), 'Y-m-d H:i:s');
            }

            if ($request->has('edit')) {
                $rq = Request::create('/api/editPage', 'POST', [
                    'page_id' => $id,
                    'data' => [
                        'title'            => $request->offsetGet('title'),
                        'section_id'       => $request->offsetGet('section_id'),
                        'body'             => $request->offsetGet('body'),
                        'head_title'       => $request->offsetGet('head_title'),
                        'meta_description' => $request->offsetGet('meta_descript'),
                        'meta_keywords'    => $request->offsetGet('meta_key_words'),
                        'abstract'         => $request->offsetGet('abstract'),
                        'forum_link'       => $request->offsetGet('forum_link'),
                        'active'           => !empty($request->offsetGet('active')),
                        'valid_from'       => $from,
                        'valid_to'         => $to,
                    ]
                ]);

                $api = new ApiPage($rq);
                $result = $api->editPage($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));

                    return back()->withErrors(isset($result->errors) ? $result->errors : []);
                }
            }

            return view('admin/pagesEdit', compact('class', 'fields', 'model', 'sections'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            $sections = Section::where('parent_id', null)->get();
            $sections = $this->prepareSections($sections);

            if ($request->has('create')) {

                $from = null;
                $to = null;

                if (date_create($request->offsetGet('valid_from'))) {
                    $from = date_format(date_create($request->offsetGet('valid_from')), 'Y-m-d H:i:s');
                }

                if (date_create($request->offsetGet('valid_to'))) {
                    $to = date_format(date_create($request->offsetGet('valid_to')), 'Y-m-d H:i:s');
                }

                $rq = Request::create('/api/addPage', 'POST', [
                    'data' => [
                        'title'            => $request->offsetGet('title'),
                        'section_id'       => $request->offsetGet('section_id'),
                        'body'             => $request->offsetGet('body'),
                        'head_title'       => $request->offsetGet('head_title'),
                        'meta_description' => $request->offsetGet('meta_descript'),
                        'meta_keywords'    => $request->offsetGet('meta_key_words'),
                        'abstract'         => $request->offsetGet('abstract'),
                        'forum_link'       => $request->offsetGet('forum_link'),
                        'active'           => !empty($request->offsetGet('active')),
                        'valid_from'       => $from,
                        'valid_to'         => $to,
                    ]
                ]);
                $api = new ApiPage($rq);
                $result = $api->addPage($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/pages/view/'. $result->data->page_id);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view(
                'admin/pagesAdd',
                ['class' => 'user', 'fields' => self::getPageTransFields(), 'sections' => $sections]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Delete a page based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';

            $rq = Request::create('/api/deletePage', 'POST', [
                'page_id' => $id,
            ]);

            $api = new ApiPage($rq);
            $result = $api->deletePage($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect('/admin/pages/list');
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));

                return redirect('/admin/pages/list')->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Prepare collection of sections for response
     *
     * @param Collection $sections - list of sections  records
     * @return array - ready for response records data
     */
    public function prepareSections($sections) {
        $result = [];

        if (!is_null($sections)) {
            foreach ($sections as $section) {
                $result[$section->id] = $section->name;
            }
        }

        return $result;
    }
}
