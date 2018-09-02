<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\UserSetting;
use App\Organisation;
use App\TermsOfUse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;

class TermsOfUseController extends AdminController
{
    /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getTransFields()
    {
        return [
            [
                'label'    => 'custom.name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => true,
            ],
        ];
    }

    public function add(Request $request)
    {
        if ($request->has('create')) {
            $rq = Request::create('/api/addTermsOfUse', 'POST', [
                'data' => [
                    'name'        => $request->offsetGet('name'),
                    'description' => $request->offsetGet('descript'),
                    'active'      => isset($request->active),
                    'is_default'  => $request->offsetGet('default'),
                    'ordering'    => $request->offsetGet('order'),
                ]
            ]);
            $api = new ApiTermsOfUse($rq);
            $result = $api->addTermsOfUse($rq)->getData();

            if (!empty($result->success)) {
                $request->session()->flash('alert-success', __('custom.add_success'));

                return redirect('/admin/terms-of-use/view/'. $result->id);
            } else {
                $request->session()->flash('alert-danger', __('custom.add_error'));

                return back()->withErrors($result->errors)->withInput(Input::all());
            }
        }

        return view('admin/termsOfUseAdd', ['class' => 'user', 'fields' => self::getTransFields()]);
    }

    /**
     * Displays information for a given term of use
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $request = Request::create('/api/getTermsOfUseDetails', 'POST', [
                'terms_id'  => $id,
                'locale'    => \LaravelLocalization::getCurrentLocale(),
            ]);
            $api = new ApiTermsOfUse($request);
            $result = $api->getTermsOfUseDetails($request)->getData();

            if ($result->success) {
                return view('admin/termsOfUseView', ['class' => 'user', 'term' => $result->data]);
            }

            return redirect('/admin/terms-of-use/list');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit a term of use based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getTransFields();

            $model = TermsOfUse::find($id)->loadTranslations();

            if ($request->has('edit')) {
                $rq = Request::create('/api/editTermsOfUse', 'POST', [
                    'terms_id' => $id,
                    'data' => [
                        'name'        => $request->offsetGet('name'),
                        'description' => $request->offsetGet('descript'),
                        'active'      => isset($request->active),
                        'is_default'  => $request->offsetGet('default'),
                        'ordering'    => $request->offsetGet('order'),
                    ]
                ]);

                $api = new ApiTermsOfUse($rq);
                $result = $api->editTermsOfUse($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));

                    return back()->withErrors(isset($result->errors) ? $result->errors : []);
                }

            }

            return view('admin/termsOfUseEdit', compact('class', 'fields', 'model'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Lists terms of use
     *
     * @param Request $request
     *
     * @return view with list of terms of use
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
                'criteria' => []
            ];
            $request = Request::create('/api/listTermsOfUse', 'POST', $params);
            $api = new ApiTermsOfUse($request);
            $result = $api->listTermsOfUse($request)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->terms_of_use) ? $result->terms_of_use : [],
                isset($result->total_records) ? $result->total_records : 0,
                [],
                $perPage
            );

            return view(
                'admin/termsOfUseList',
                [
                    'class'      => 'user',
                    'terms'      => $paginationData['items'],
                    'pagination' => $paginationData['paginate'],
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
    * Deletes terms of use
    *
    * @param Request $request
    * @param integer $id
    *
    * @return view to previous page
    */
   public function delete(Request $request, $id)
   {
        if (Role::isAdmin()) {
            $rq = Request::create('/api/deleteTermsOfUse', 'POST', [
                'terms_id'  => $id,
            ]);
            $api = new ApiTermsOfUse($rq);
            $result = $api->deleteTermsOfUse($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));
            }

            return back();
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
