<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\CategoryController as ApiCategory;

class ThemeController extends AdminController
{
     /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getThemeTransFields()
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
     * Lists themes
     *
     * @param Request $request
     *
     * @return view with list of themes
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $criteria = [];
            $perPage = 10;

            if (isset($request->q)) {
                $criteria['keywords'] = $request->q;
            }

            $criteria['order']['type'] = 'asc';
            $criteria['order']['field'] = 'ordering';

            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria'         => $criteria
            ];
            $request = Request::create('/api/listMainCategories', 'POST', $params);
            $api = new ApiCategory($request);
            $result = $api->listMainCategories($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->categories) ? $result->categories : [],
                isset($result->total_records) ? $result->total_records : 0,
                isset($criteria['keywords']) ? ['q' => $criteria['keywords']] : [],
                $perPage
            );

            return view(
                'admin/themesList',
                [
                    'class'      => 'user',
                    'themes'     => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                    'search'     => isset($criteria['keywords']) ? $criteria['keywords'] : null,
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            if ($request->has('create')) {
                $params = [];

                if (!empty($request->file)) {
                    $params['filename'] = $request->file->getClientOriginalName();
                    $path = $request->file->getPathName();
                    $params['data'] = \File::get($path);
                    $ext = $request->file->getClientOriginalExtension();
                    $params['mimetype'] = $ext == Category::IMG_EXT_SVG
                        ? Category::IMG_MIME_SVG
                        : $request->file->getMimeType();
                }

                $rq = Request::create('/api/addMainCategory', 'POST', [
                    'data' => [
                        'name'             => $request->offsetGet('name'),
                        'icon_filename'    => isset($params['filename']) ? $params['filename'] : null,
                        'icon_mimetype'    => isset($params['mimetype']) ? $params['mimetype'] : null,
                        'icon_data'        => isset($params['data']) ? $params['data'] : null,
                        'ordering'         => $request->offsetGet('ordering'),
                    ]
                ]);
                $api = new ApiCategory($rq);
                $result = $api->addMainCategory($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/themes/view/'. $result->category_id);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view('admin/themeAdd', ['class' => 'user', 'fields' => self::getThemeTransFields()]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Displays information for a given theme
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $req = Request::create('/api/getMainCategoryDetails', 'POST', ['category_id' => $id]);
            $api = new ApiCategory($req);
            $result = $api->getMainCategoryDetails($req)->getData();
            $theme = isset($result->category) ? $result->category : null;

            if (!is_null($theme)) {
                $theme->image = $this->getImageData(utf8_decode($theme->icon_data), $theme->icon_mime_type);

                return view(
                    'admin/themeView',
                    [
                        'class'    => 'user',
                        'theme'    => $this->getModelUsernames($theme)
                    ]
                );
            }

            return redirect('/admin/themes/list');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit a theme based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getThemeTransFields();
            $model = Category::find($id);

            if (!is_null($model)) {
                $model = $this->getModelUsernames($model->loadTranslations());
            }

            if ($request->has('edit')) {
                if (!empty($request->file)) {
                    $params['filename'] = $request->file->getClientOriginalName();
                    $path = $request->file->getPathName();
                    $params['data'] = \File::get($path);
                    $params['mimetype'] = $request->file->getMimeType();
                }

                $rq = Request::create('/api/editMainCategory', 'POST', [
                    'category_id' => $id,
                    'data' => [
                        'name'             => $request->offsetGet('name'),
                        'active'           => $request->offsetGet('active'),
                        'icon_filename'    => isset($params['filename']) ? $params['filename'] : null,
                        'icon_mimetype'    => isset($params['mimetype']) ? $params['mimetype'] : null,
                        'icon_data'        => isset($params['data']) ? $params['data'] : null,
                        'ordering'         => $request->offsetGet('ordering'),
                    ]
                ]);

                $api = new ApiCategory($rq);
                $result = $api->editMainCategory($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));

                    return back()->withErrors(isset($result->errors) ? $result->errors : []);
                }
            }

            return view('admin/themeEdit', compact('class', 'fields', 'model'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Delete a theme based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';

            $rq = Request::create('/api/deleteMainCategory', 'POST', [
                'category_id' => $id,
            ]);

            $api = new ApiCategory($rq);
            $result = $api->deleteMainCategory($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect('/admin/themes/list');
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));

                return redirect('/admin/themes/list')->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
