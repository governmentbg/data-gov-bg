<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class OrganisationController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

    }

    /**
     * Loads a view for browsing organisations
     *
     * @param Request $request
     *
     * @return view for browsing organisations
     */
    public function list(Request $request)
    {
        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'active'   => true,
                'approved' => true,
                'locale'    => \LaravelLocalization::getCurrentLocale()
            ]
        ];

        if (isset($request->sort)) {
            $params['criteria']['order']['field'] = $request->sort;
            if (isset($request->order)) {
                $params['criteria']['order']['type'] = $request->order;
            }
        }

        $request = Request::create('/api/listOrganisations', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();

        $paginationData = $this->getPaginationData(
            $result->success ? $result->organisations : [],
            $result->success ? $result->total_records : 0,
            array_except(app('request')->input(), ['q', 'page',]),
            $perPage
        );

        return view(
            'organisation.list',
            [
                'class'         => 'organisation',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate']
            ]
        );
    }

    /**
     * Loads a view for searching organisations
     *
     * @param Request $request
     *
     * @return view with a list of organisations or
     * a list of filtered organisations if search string is provided
     */
    public function search(Request $request)
    {
        $search = $request->q;

        if (empty(trim($search))) {
            return redirect()->route('orgList', array_except(app('request')->query(), ['q']));
        }

        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => [
                'active'   => true,
                'approved' => true,
                'keywords' => $search
            ]
        ];

        if (isset($request->sort)) {
            $params['criteria']['order']['field'] = $request->sort;
            if (isset($request->order)) {
                $params['criteria']['order']['type'] = $request->order;
            }
        }

        $request = Request::create('/api/searchOrganisations', 'POST', $params);
        $api = new ApiOrganisation($request);
        $result = $api->listOrganisations($request)->getData();
        $organisations = !empty($result->organisations) ? $result->organisations : [];
        $count = !empty($result->total_records) ? $result->total_records : 0;

        $getParams = ['q' => $search];
        if ($request->has('sort')) {
            $getParams['sort'] = $request->sort;
        }
        if ($request->has('order')) {
            $getParams['sort'] = $request->order;
        }

        $paginationData = $this->getPaginationData(
            $organisations,
            $count,
            $getParams,
            $perPage
        );

        return view(
            'organisation.list',
            [
                'class'         => 'organisation',
                'organisations' => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search
            ]
        );
    }

    public function view() {
        return view('organisation/profile', ['class' => 'organisation']);
    }

    public function datasets() {

    }

    public function viewDataset() {

    }

    public function chronology() {

    }
}
