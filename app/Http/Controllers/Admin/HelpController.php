<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Category;
use App\HelpPage;
use App\HelpSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\HelpController as ApiHelp;

class HelpController extends AdminController
{
    public static function getHelpSectionTransFields()
    {
        return [
            [
                'label'    => 'custom.title',
                'name'     => 'title',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
        ];
    }

    public static function getHelpPagesTransFields()
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
                'label'    => 'custom.description',
                'name'     => 'body',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'addClass' => 'js-summernote',
                'required' => true,
            ]
        ];
    }

    /**
     *  show list of help sections
     */
    public function listSections(Request $request)
    {
        $rq = Request::create('/api/listHelpSections', 'POST', [
            'api_key' => Auth::user()->api_key,
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $helpSections = $result->success ? $result->sections : [];

        return view('admin/helpList', compact('helpSections'));
    }

    /**
     *  show list of help subsections
     */
    public function listSubsections(Request $request, $id)
    {
        $rq = Request::create('/api/listHelpSubsections', 'POST', [
            'api_key'       => Auth::user()->api_key,
            'criteria'      => [
                'section_id'    => $id,
            ]
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSubsections($rq)->getData();

        $helpSections = $result->success ? $result->subsections : [];

        return view('admin/helpSubsectionsList', compact('helpSections', 'id'));
    }

    /**
     * show form for creation of help sections
     */
    public function addHelpSecton(Request $request, $parent = null)
    {
        if ($request->has('back')) {
            return redirect('admin/help/sections/list');
        }

        if ($request->has('create')) {
            $rq = Request::create('/api/addHelpSection', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'name'      => $request->offsetGet('name'),
                    'title'     => $request->offsetGet('title'),
                    'parent_id' => $request->offsetGet('parent'),
                    'active'    => $request->offsetGet('active') ?: false,
                    'ordering'  => $request->offsetGet('ordering'),
                ],
            ]);
            $api = new ApiHelp($rq);
            $result = $api->addHelpSection($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return redirect('admin/help/sections/list');
            }

            $request->session()->flash('alert-danger', __('custom.add_error'));

            return redirect()->back()->withInput()->withErrors($result->errors);
        }

        return view('admin/addHelpSection', [
            'fields'    => self::getHelpSectionTransFields(),
            'parents'   => self::getMainSections(),
            'ordering'  => Category::getOrdering(),
            'parentId'  => $parent,
        ]);
    }

    public function deleteHelpSection(Request $request, $id)
    {
        if ($this->delete($id)) {
            $request->session()->flash('alert-success', __('custom.delete_success'));
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));
        }

        return redirect('admin/help/sections/list');
    }

    /**
     * show form for help section edit page
     */
    public function editHelpSection(Request $request, $id)
    {
        $section = HelpSection::find($id);

        if ($request->has('save')) {
            $rq = Request::create('/api/editHelpSection', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'id'        => $id,
                'data'      => [
                    'name'      => $request->offsetGet('name'),
                    'title'     => $request->offsetGet('title'),
                    'parent_id' => $request->offsetGet('parent'),
                    'active'    => $request->offsetGet('active') ?: false,
                    'ordering'  => $request->offsetGet('ordering'),
                ],
            ]);
            $api = new ApiHelp($rq);
            $result = $api->editHelpSection($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect('admin/help/sections/list');
            }

            $request->session()->flash('alert-danger', __('custom.edit_error'));

            return redirect()->back()->withInput()->withErrors($result->errors);
        }

        return view('admin/editHelpSection', [
            'section'   => $section,
            'fields'    => self::getHelpSectionTransFields(),
            'parents'   => self::getMainSections($id),
            'ordering'  => Category::getOrdering(),
        ]);
    }

    /**
     * show view of help section
     */
    public function viewHelpSection(Request $request, $id)
    {
        $rq = Request::create('/api/listHelpSections', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'criteria'  => [
                'id'        => $id
            ]
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $section = $result->success ? $result->sections[0] : [];

        return view('admin/viewHelpSection', compact('section'));
    }

    /**
     * show view of help subsection
     */
    public function viewHelpSubsection(Request $request, $id)
    {
        $rq = Request::create('/api/listHelpSubsections', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'criteria'  => [
                'id'        => $id
            ]
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSubsections($rq)->getData();

        $section = $result->success ? $result->subsections[0] : [];

        return view('admin/viewHelpSection', compact('section'));
    }

    /**
     * show view with help pages
     */
    public function listPages(Request $request)
    {
        $criteria = [];
        $perPage = 3;

        $criteria = [
            'records_per_page'  => $perPage,
            'page_number'       => !empty($request->page) ? $request->page : 1,
        ];

        if (isset($request->search)) {
            $criteria['criteria']['keywords'] = $request->search;
        }

        $rq = Request::create('api/listHelpPages', 'POST', $criteria);
        $api = new ApiHelp($rq);
        $result = $api->listHelpPages($rq)->getData();

        $helpPages = $result->success ? $result->pages : [];
        $getParams = array_except(app('request')->input(), ['page']);

        $paginationData = $this->getPaginationData(
            $helpPages,
            $result->total_records,
            $getParams,
            $perPage
        );

        return view('admin/helpPagesList', [
            'helpPages'     => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'getParams'     => $getParams,
        ]);
    }

    /**
     * show help page creation view
     */
    public function addHelpPage(Request $request)
    {
        $rq = Request::create('/api/listHelpSections', 'POST', [
            'api_key' => Auth::user()->api_key,
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $helpSections = $result->success ? $result->sections : [];

        if ($request->has('back')) {
            return redirect('admin/help/pages/list');
        }

        if ($request->has('create')) {
            $rq = Request::create('/api/addHelpPage', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'section_id'    => $request->offsetGet('section_id'),
                    'name'          => $request->offsetGet('name'),
                    'keywords'      => $request->offsetGet('keywords'),
                    'title'         => $request->offsetGet('title'),
                    'body'          => $request->offsetGet('body'),
                    'ordering'      => $request->offsetGet('ordering'),
                    'active'        => $request->offsetGet('active') ?: false,
                ],
            ]);
            $result = $api->addHelpPage($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return redirect('admin/help/pages/list');
            }

            $request->session()->flash('alert-danger', __('custom.add_error'));

            return redirect()->back()->withInput()->withErrors($result->errors);
        }

        return view('admin/addHelpPage', [
            'fields'    => self::getHelpPagesTransFields(),
            'ordering'  => Category::getOrdering(),
            'sections'  => $helpSections,
        ]);
    }

    public function deleteHelpPage(Request $request, $id)
    {
        if ($this->deletePage($id)) {
            $request->session()->flash('alert-success', __('custom.delete_success'));
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));
        }

        return redirect('admin/help/pages/list');
    }

    /**
     * show view help page edit
     */
    public function editHelpPage(Request $request, $id)
    {
        $rq = Request::create('/api/listHelpSections', 'POST', [
            'api_key' => Auth::user()->api_key,
        ]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $helpSections = $result->success ? $result->sections : [];

        $page = HelpPage::find($id);

        if ($request->has('back')) {
            return redirect('admin/help/pages/list');
        }

        if ($request->has('save')) {
            $rq = Request::create('/api/editHelpPage', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'page_id'   => $id,
                'data'      => [
                    'name'          => $request->offsetGet('name'),
                    'section_id'    => $request->offsetGet('section_id'),
                    'keywords'      => $request->offsetGet('keywords'),
                    'title'         => $request->offsetGet('title'),
                    'body'          => $request->offsetGet('body'),
                    'ordering'      => $request->offsetGet('ordering'),
                    'active'        => $request->offsetGet('active') ?: false,
                ],
            ]);
            $result = $api->editHelpPage($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect('admin/help/pages/list');
            }

            $request->session()->flash('alert-danger', __('custom.edit_error'));

            return redirect()->back()->withInput()->withErrors($result->errors);
        }

        return view('admin/editHelpPage', [
            'page'      => $page,
            'fields'    => self::getHelpPagesTransFields(),
            'ordering'  => Category::getOrdering(),
            'sections'  => $helpSections,
        ]);
    }

    /**
     * show view of help page
     */
    public function viewHelpPage(Request $request, $id)
    {
        $rq = Request::create('api/getHelpPageDetails', 'POST', ['page_id' => $id]);
        $api = new ApiHelp($rq);
        $result = $api->getHelpPageDetails($rq)->getData();

        $page = $result->page;

        if (empty($page)) {
            return redirect('admin/help/pages/list');
        }

        return view('admin/viewHelpPage', compact('page'));
    }

    public static function getMainSections($id = null)
    {
        $sections = HelpSection::where('parent_id', null);

        if ($id) {
            $sections->where('id', '!=', $id);
        }

        return $sections->get() ?: [];
    }

    public function delete($id)
    {
        $rq = Request::create('/api/deleteHelpSection', 'POST', ['api_key' => Auth::user()->api_key, 'id' => $id]);
        $api = new ApiHelp($rq);
        $result = $api->deleteHelpSection($rq)->getData();

        if ($result->success) {
            return true;
        }

        return false;
    }

    public function deletePage($id)
    {
        $rq = Request::create('/api/deletePage', 'POST', ['api_key' => Auth::user()->api_key, 'page_id' => $id]);
        $api = new ApiHelp($rq);
        $result = $api->deleteHelpPage($rq)->getData();

        if ($result->success) {
            return true;
        }

        return false;
    }
}
