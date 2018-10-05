<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\LocaleController as ApiLocale;

class LangController extends AdminController {
    /**
     * Show the language list.
     *
     * @return view with list and actions
     */
    public function list(Request $request)
    {
        $class = 'user';

        $rq = Request::create('/api/listLocale', 'POST');
        $api = new ApiLocale($rq);
        $result = $api->listLocale($rq)->getData();
        $languages = isset($result->locale_list) ? $result->locale_list : [];

        if ($request->has('delete')) {
            if ($this->deleteLocale($request->offsetGet('locale'))) {
                $request->session()->flash('alert-success', __('custom.delete_success'));
            } else {
                $request->session()->flash('alert-danger', __('custom.default_locale_error'));
            }

            return back();
        }

        return view('admin/langList', compact('class', 'languages'));
    }

    /**
     * Show the language creation.
     *
     * @return view with inpits
     */
    public function addLang(Request $request)
    {
        if ($request->has('back')) {
            return redirect()->route('adminLangs');
        }

        $class = 'user';
        $locales = \LaravelLocalization::getSupportedLocales();
        $errors = [];

        if ($request->has('save')) {
            $rq = Request::create('/api/addLocale', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'data'      => [
                    'locale'    => $request->offsetGet('lang'),
                    'active'    => $request->offsetGet('active') ? $request->offsetGet('active') : false,
                ],
            ]);
            $api = new ApiLocale($rq);
            $result = $api->addLocale($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('admin/languages'));
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/langAdd', compact('class', 'locales'));
    }

    /**
     * Show edit language.
     *
     * @return view with inpits
     */
    public function editLang(Request $request, $id)
    {
        $class = 'user';
        $errors = [];

        $rq = Request::create('/api/getLocaleDetails', 'POST', ['locale' => $id]);
        $api = new ApiLocale($rq);
        $result = $api->getLocaleDetails($rq)->getData();
        $locale = isset($result->locale) ? $result->locale : [];

        if ($request->has('edit')) {
            $rq = Request::create('/api/editLocale', 'POST', [
                'api_key'   => Auth::user()->api_key,
                'locale'    => $id,
                'data'      => [
                    'active'    => $request->offsetGet('active') ? $request->offsetGet('active') : false,
                ],
            ]);
            $api = new ApiLocale($rq);
            $result = $api->editLocale($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.edit_success'));

                return redirect(url('admin/languages'));
            } else {
                $errors = $result->errors;
                $request->session()->flash('alert-danger', __('custom.edit_error'));
            }

            return back()->withInput()->withErrors($errors);
        }

        return view('admin/langEdit', compact('class', 'locale'));
    }

    public function deleteLocale(Request $request, $id)
    {
        $rq = Request::create('/api/deleteLocale', 'POST', [
            'api_key'   => Auth::user()->api_key,
            'locale'    => $id,
        ]);
        $api = new ApiLocale($rq);
        $result = $api->deleteLocale($rq)->getData();

        if ($result->success) {
            $request->session()->flash('alert-success', __('custom.delete_success'));

            return redirect(url('admin/languages'));
        }

        $request->session()->flash('alert-danger', __('custom.delete_error'));

        return redirect(url('admin/languages'));
    }

}
