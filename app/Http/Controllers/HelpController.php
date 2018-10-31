<?php

namespace App\Http\Controllers;

use App\HelpPage;
use App\HelpSection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\HelpController as ApiHelp;

class HelpController extends Controller
{
    public function list(Request $request)
    {
        $params = [];
        $perPage = 6;
        $page = isset($request->page) ? $request->page : 1;

        if (Auth::check()) {
            $params['api_key'] = Auth::user()->api_key;
        }

        $rq = Request::create('/api/listHelpSections', 'POST', $params);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSections($rq)->getData();
        $sections = $result->success ? $result->sections : [];

        $rq = Request::create('/api/listHelpSubsections', 'POST', $params);
        $result = $api->listHelpSubsections($rq)->getData();
        $subsections = $result->success ? $result->subsections : [];

        $rq = Request::create('/api/listHelpPages', 'POST');
        $result = $api->listHelpPages($rq)->getData();
        $pages = $result->success ? $result->pages : [];

        $sections = collect($sections);

        $paginationData = $this->getPaginationData(
            $sections->forPage($page, $perPage),
            count($sections),
            $request->except('page'),
            $perPage
        );

        return view('help/list', [
            'class'             => 'index',
            'sections'          => $paginationData['items'],
            'pagination'        => $paginationData['paginate'],
            'subsections'       => $subsections,
            'pages'             => $pages,
        ]);
    }

    public function search(Request $request)
    {
        $search = $request->offsetGet('q');

        if (empty($search)) {
            return redirect('/help');
        }

        $rq = Request::create('/api/listHelpPages', 'POST', ['criteria' => ['keywords' => $search]]);
        $api = new ApiHelp($rq);
        $result = $api->listHelpPages($rq)->getData();

        $records = $result->success ? $result->pages : [];
        $records = collect($records);

        foreach ($records as $record) {
            if (!empty($record->section_id)) {
                $parent = HelpSection::select('parent_id')->where('id', $record->section_id)->get();
                $record->parent = HelpSection::select('title', 'id')->where('id', $parent[0]->parent_id)->get();
            }
        }

        return view('help/results', [
            'records'           => $records,
            'class'             => 'index',
            'search'            => $search,
        ]);
    }

    public function view(Request $request, $id, $activePage = null)
    {
        $section = HelpSection::find($id);
        $params = [];

        if (Auth::check()) {
            $params['api_key'] = Auth::user()->api_key;
        }

        $params['criteria'] = [
            'section_id'    => $id,
        ];

        $rq = Request::create('/api/listHelpSubsections', 'POST', $params);
        $api = new ApiHelp($rq);
        $result = $api->listHelpSubsections($rq)->getData();
        $subsections = $result->success ? $result->subsections : [];

        $rq = Request::create('/api/listHelpPages', 'POST', $params);
        $result = $api->listHelpPages($rq)->getData();
        $pages = $result->success ? $result->pages : [];

        foreach ($subsections as $sub) {
           $sub->pages = HelpPage::where('section_id', $sub->id)->get();
        }

        return view('help/view', [
            'section'           => $section,
            'subsections'       => $subsections,
            'pages'             => $pages,
            'class'             => 'index',
            'activePage'        => $activePage,
        ]);
    }

    public function pageView(Request $request, $id)
    {
        $class = 'index';
        $page = HelpPage::find($id);

        return view('help/pageView', compact('page', 'class'));
    }
}
