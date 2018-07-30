<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLang($locale)
    {
        error_log('locale: '. print_r($locale, true));
        if (!empty($locale)) {
            Session::put('applocale', $locale);
            \App::setLocale($locale);
            \LaravelLocalization::setLocale($locale);
        }

        return Redirect::back();
    }
}
