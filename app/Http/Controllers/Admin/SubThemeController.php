<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\TagController as ApiTag;

class SubThemeController extends AdminController
{
    /**
     * Lists subthemes
     *
     * @param Request $request
     *
     * @return view with list of subthemes
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $criteria = [];
            $perPage = 10;

            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => $criteria
            ];
            $request = Request::create('/api/listTags', 'POST', $params);
            $api = new ApiTag($request);
            $result = $api->listTags($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->tags) ? $result->tags : [],
                isset($result->total_records) ? $result->total_records : 0,
                [],
                $perPage
            );

            return view(
                'admin/subThemesList',
                [
                    'class'       => 'user',
                    'themes'      => $paginationData['items'],
                    'pagination'  => $paginationData['paginate'],
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function search(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;

            if (!empty($request->q)) {
                $name = $request->q;
            } else {
                return redirect('admin/categories/list');
            }

            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'name'             => $name
            ];
            $request = Request::create('/api/searchTag', 'POST', $params);
            $api = new ApiTag($request);
            $result = $api->searchTag($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->tag) ? [$result->tag] : [],
                isset($result->total_records) ? $result->total_records : 0,
                isset($name) ? ['q' => $name] : [],
                $perPage
            );

            return view(
                'admin/subThemesList',
                [
                    'class'       => 'user',
                    'themes'      => $paginationData['items'],
                    'pagination'  => $paginationData['paginate'],
                    'search'      => isset($name) ? $name : null,
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            if ($request->has('create')) {

                $rq = Request::create('/api/addTag', 'POST', [
                    'data' => [
                        'name' => $request->offsetGet('name'),
                    ]
                ]);
                $api = new ApiTag($rq);
                $result = $api->addTag($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/categories/view/'. $result->id);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view('admin/subThemeAdd', ['class' => 'user']);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Displays information for a given subtheme
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $req = Request::create('/api/getTagDetails', 'POST', ['tag_id' => $id]);
            $api = new ApiTag($req);
            $result = $api->getTagDetails($req)->getData();
            $theme = isset($result->tag) ? $result->tag : null;

            if (!is_null($theme)) {

                return view(
                    'admin/subThemeView',
                    [
                        'class'    => 'user',
                        'theme'    => $theme
                    ]
                );
            }

            return redirect('/admin/categories/list');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit a subtheme based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $model = $this->getModelUsernames(Tags::where('id', $id)->first());

            if ($request->has('edit')) {

                $rq = Request::create('/api/editTag', 'POST', [
                    'tag_id' => $id,
                    'data'   => [
                        'name'             => $request->offsetGet('name'),
                    ]
                ]);

                $api = new ApiTag($rq);
                $result = $api->editTag($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));

                    return back()->withErrors(isset($result->errors) ? $result->errors : []);
                }
            }

            return view('admin/subThemeEdit', compact('class', 'model'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Delete a subtheme based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';

            $rq = Request::create('/api/deleteTag', 'POST', [
                'tag_id' => $id,
            ]);

            $api = new ApiTag($rq);
            $result = $api->deleteTag($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect('/admin/categories/list');
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));

                return redirect('/admin/categories/list')->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
