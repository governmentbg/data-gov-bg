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
use App\Http\Controllers\Api\NewsController as ApiNews;

class NewsController extends AdminController
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
                'addClass' => 'js-summernote',
                'required' => true,
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
     * Lists news
     *
     * @param Request $request
     *
     * @return view with list of news
     */
    public function list(Request $request)
    {
        $perPage = 10;
        $params = [
            'api_key'          => \Auth::user()->api_key,
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
        ];

        if (isset($request->active)) {
            $params['criteria']['active'] = $request->active;
        }

        if (!empty($request->offsetGet('date_from'))) {
            $params['criteria']['date_from'] = date_format(date_create($request->offsetGet('date_from')), 'Y-m-d H:i:s');
        }

        if (!empty($request->offsetGet('date_to'))) {
            $params['criteria']['date_to'] = date_format(date_create($request->offsetGet('date_to') .' 23:59'), 'Y-m-d H:i:s');
        }

        if (!empty($request->offsetGet('date_to')) || !empty($request->offsetGet('date_from'))) {
            $params['criteria']['date_type'] = Page::DATE_TYPE_VALID;
        }

        $req = Request::create('/api/listNews', 'POST', $params);
        $api = new ApiNews($req);
        $result = $api->listNews($req)->getData();
        $getParams = array_except(app('request')->input(), ['news']);

        $paginationData = $this->getPaginationData(
            $result->news,
            $result->total_records,
            $getParams,
            $perPage
        );

        return view('/admin/news', [
            'class'         => 'user',
            'news'          => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'range'             => [
                'from'              => isset($request->date_from) ? $request->date_from : null,
                'to'                => isset($request->date_to) ? $request->date_to : null
            ]
        ]);
    }

    /**
     * Displays information for a given piece of news
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if ($request->has('back')) {
            return redirect()->route('adminNews');
        }

        $req = Request::create('/api/getNewsDetails', 'POST', ['news_id' => $id]);
        $api = new ApiNews($req);
        $result = $api->getNewsDetails($req)->getData();

        $news = isset($result->news) ? $result->news : null;

        if (!is_null($news)) {
            $news = $this->getModelUsernames($news);

            return view(
                'admin/newsView',
                [
                    'class'   => 'user',
                    'news'    => $news,
                ]
            );
        }

        return redirect('/admin/news/list');
    }

    /**
     * Edit a piece of news based on id
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

        if (!is_null($model)) {
            $model = $this->getModelUsernames($model->loadTranslations());
            $model->valid_from = isset($model->valid_from)
                ? date_format(date_create($model->valid_from), 'd-m-Y')
                : null;
            $model->valid_to = isset($model->valid_to)
                ? date_format(date_create($model->valid_to), 'd-m-Y')
                : null;
        } else {
            return redirect('admin/news/list');
        }

        $from = null;
        $to = null;

        if (!is_null($request->valid_from)){
            if (date_create($request->offsetGet('valid_from'))) {
                $from = date_format(date_create($request->offsetGet('valid_from')), 'Y-m-d H:i:s');
            }
        }

        if (!is_null($request->valid_to)){
            if (date_create($request->offsetGet('valid_to'))) {
                $to = date_format(date_create($request->offsetGet('valid_to')), 'Y-m-d H:i:s');
            }
        }

        if ($request->has('edit')) {
            $rq = Request::create('/api/editNews', 'POST', [
                'news_id' => $id,
                'data' => [
                    'title'            => $request->offsetGet('title'),
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

            $api = new ApiNews($rq);
            $result = $api->editNews($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return back();
            } else {
                $request->session()->flash('alert-danger', __('custom.edit_error'));

                return back()->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return view('admin/newsEdit', compact('class', 'fields', 'model'));
    }

    /**
     * Add a piece of news
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function add(Request $request)
    {
        if ($request->has('back')) {
            return redirect()->route('adminNews');
        }

        if ($request->has('create')) {

            $from = null;
            $to = null;

            if (!is_null($request->valid_from)){
                if (date_create($request->offsetGet('valid_from'))) {
                    $from = date_format(date_create($request->offsetGet('valid_from')), 'Y-m-d H:i:s');
                }
            }

            if (!is_null($request->valid_to)){
                if (date_create($request->offsetGet('valid_to'))) {
                    $to = date_format(date_create($request->offsetGet('valid_to')), 'Y-m-d H:i:s');
                }
            }

            $rq = Request::create('/api/addNews', 'POST', [
                'data' => [
                    'title'            => $request->offsetGet('title'),
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

            $api = new ApiNews($rq);
            $result = $api->addNews($rq)->getData();

            if (!empty($result->success)) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return redirect('/admin/news/view/'. $result->news_id);
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));

                return back()->withErrors($result->errors)->withInput(Input::all());
            }
        }

        return view(
            'admin/newsAdd', [
                'class' => 'user',
                'fields' => self::getPageTransFields()
            ]);
    }

    /**
     * Delete a piece of news based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        $rq = Request::create('/api/deleteNews', 'POST', [
            'news_id' => $id,
        ]);

        $api = new ApiNews($rq);
        $result = $api->deleteNews($rq)->getData();

        if ($result->success) {
            $request->session()->flash('alert-success', __('custom.delete_success'));

            return redirect('/admin/news/list');
        } else {
            $request->session()->flash('alert-danger', __('custom.delete_error'));

            return redirect('/admin/news/list')->withErrors(isset($result->errors) ? $result->errors : []);
        }
    }
}
