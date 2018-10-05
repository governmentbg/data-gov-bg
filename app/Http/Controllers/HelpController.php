<?php

namespace App\Http\Controllers;

use App\HelpPage;
use App\Resource;
use App\HelpSection;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\HelpController as ApiHelp;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

class HelpController extends Controller {

    public function loadHelpData(Request $request)
    {
        $rq = Request::create('/api/listHelpSections', 'POST');
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();

        $helpSections = $result->success ? $result->sections : [];

        $rq = Request::create('/api/listHelpPages', 'POST');
        $api = new ApiHelp($rq);
        $result = $api->listHelpPages($rq)->getData();

        $helpPages = $result->success ? $result->pages : [];


    }
}
