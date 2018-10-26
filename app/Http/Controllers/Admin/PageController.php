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
use App\Http\Controllers\Api\HelpController as ApiHelp;
use App\Http\Controllers\Api\PageController as ApiPage;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

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
                'label'    => 'custom.content',
                'name'     => 'body',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'addClass' => 'js-summernote',
                'required' => true,
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
        $perPage = 10;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        $sections = Section::whereExists(function ($query) {
                $query->select()
                    ->from('pages')
                    ->whereRaw('pages.type = '. Page::TYPE_PAGE)
                    ->whereRaw('sections.id = pages.section_id');
            })
            ->get();

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
        if ($request->has('back')) {
            return redirect()->route('adminPages');
        }

        $req = Request::create('/api/listPages', 'POST', ['criteria' => ['page_id' => $id]]);
        $api = new ApiPage($req);
        $result = $api->listPages($req)->getData();
        $page = isset($result->pages[0]) ? $result->pages[0] : null;

        if (!is_null($page)) {
            $section = Section::where('id', $page->section_id)->where('parent_id', null)->value('name');
            $page = $this->getModelUsernames($page);
            preg_match_all('#<script(.*?)</script>#is', $page->body, $matches);
            $js = '';

            if (!empty($matches[0])) {
                foreach ($matches[0] as $value) {
                    $js .= $value;
                }
                $page->body = preg_replace('#<script(.*?)</script>#is', '', $page->body);
            }

            return view(
                'admin/pagesView',
                [
                    'class'   => 'user',
                    'page'    => $page,
                    'section' => $section,
                    'script'  => $js,
                ]
            );
        }

        return redirect('/admin/pages/list');
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

        $class = 'user';
        $fields = self::getPageTransFields();
        $model = Page::find($id);
        $helpPages = $this->getHelpPages();
        $sections = [];

        if (!is_null($model)) {
            $model = $this->getModelUsernames($model->loadTranslations());
            $model->valid_from = !is_null($model->valid_from) && date_create($model->valid_from)
                ? date_format(date_create($model->valid_from), 'd-m-Y')
                : $model->valid_from;
            $model->valid_to = !is_null($model->valid_to) && date_create($model->valid_to)
                ? date_format(date_create($model->valid_to), 'd-m-Y')
                : $model->valid_to;

            $sections = Section::select()->get();
            $sections = $this->prepareSections($sections);
        } else {
            return redirect('admin/pages/list');
        }

        $from = null;
        $to = null;

        if (!is_null($request->offsetGet('valid_from')) && date_create($request->offsetGet('valid_from'))) {
            $from = date_format(date_create($request->offsetGet('valid_from')), 'Y-m-d H:i:s');
        }

        if (!is_null($request->offsetGet('valid_to')) && date_create($request->offsetGet('valid_to'))) {
            $to = date_format(date_create($request->offsetGet('valid_to')), 'Y-m-d H:i:s');
        }

        if ($request->has('edit')) {
            $rq = Request::create('/api/editPage', 'POST', [
                'page_id' => $id,
                'data' => [
                    'title'             => $request->offsetGet('title'),
                    'section_id'        => $request->offsetGet('section_id'),
                    'body'              => $request->offsetGet('body'),
                    'head_title'        => $request->offsetGet('head_title'),
                    'meta_description'  => $request->offsetGet('meta_descript'),
                    'meta_keywords'     => $request->offsetGet('meta_key_words'),
                    'abstract'          => $request->offsetGet('abstract'),
                    'forum_link'        => $request->offsetGet('forum_link'),
                    'help_page'         => $request->offsetGet('help_page'),
                    'active'            => !empty($request->offsetGet('active')),
                    'valid_from'        => $from,
                    'valid_to'          => $to,
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

        return view('admin/pagesEdit', compact('class', 'fields', 'model', 'sections', 'helpPages'));
    }

    public function add(Request $request)
    {
        $sections = Section::select()->get();
        $sections = $this->prepareSections($sections);
        $helpPages = $this->getHelpPages();
        $resourceFormats = Page::getResourceResponseFormats();

        if ($request->has('back')) {
            return redirect()->route('adminPages');
        }

        if ($request->has('create')) {

            $from = null;
            $to = null;

            if (date_create($request->offsetGet('valid_from')) && !is_null($request->offsetGet('valid_from')) ) {
                $from = date_format(date_create($request->offsetGet('valid_from')), 'Y-m-d H:i:s');
            }

            if (date_create($request->offsetGet('valid_to')) && !is_null($request->offsetGet('valid_to'))) {
                $to = date_format(date_create($request->offsetGet('valid_to')), 'Y-m-d H:i:s');
            }

            $rq = Request::create('/api/addPage', 'POST', [
                'data' => [
                    'title'             => $request->offsetGet('title'),
                    'section_id'        => $request->offsetGet('section_id'),
                    'body'              => $request->offsetGet('body'),
                    'head_title'        => $request->offsetGet('head_title'),
                    'meta_description'  => $request->offsetGet('meta_descript'),
                    'meta_keywords'     => $request->offsetGet('meta_key_words'),
                    'abstract'          => $request->offsetGet('abstract'),
                    'forum_link'        => $request->offsetGet('forum_link'),
                    'help_page'         => $request->offsetGet('help_page'),
                    'active'            => !empty($request->offsetGet('active')),
                    'valid_from'        => $from,
                    'valid_to'          => $to,
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

        return view('admin/pagesAdd', [
            'class'           => 'user',
            'fields'          => self::getPageTransFields(),
            'sections'        => $sections,
            'helpPages'       => $helpPages,
            'resourceFormats' => $resourceFormats
        ]);
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

    public function getHelpPages()
    {
        $rq = Request::create('/api/listHelpPages', 'POST', ['page_number' => 1, 'records_per_page' => 1000]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpPages($rq)->getData();

        return $result->success ? $result->pages : [];
    }

    public function execResourceQueryScript(Request $request)
    {
        $format = $request->format;
        $resourceParams = ['resource_uri' => $request->uri, 'version' => $request->version];

        $rq = Request::create('/api/getResourceData', 'POST', $resourceParams);
        $api = new ApiResource($rq);
        $res = $api->getResourceData($rq)->getData();

        if ($res->success) {
            $data = isset($res->data) ? $res->data : [];

            if ($format == Page::RESOURCE_RESPONSE_CSV) {
                $convertData = ['data' => $data];
                $reqConvert = Request::create('/json2csv', 'POST', $convertData);
                $apiConvert = new ApiConversion($reqConvert);
                $resultConvert = $apiConvert->json2csv($reqConvert)->getData();
                $data = isset($resultConvert->data) ? $resultConvert->data : [];
                $res->success = $resultConvert->success;
            }
        } else {
            $data = isset($res->errors) ? $res->errors : [];
        }

        return [
            'success' => $res->success,
            'data'    => json_encode($data)
        ];
    }
}
