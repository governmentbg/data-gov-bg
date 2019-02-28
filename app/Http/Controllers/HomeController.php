<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use \App\Http\Controllers\Api\CategoryController as ApiCategories;

class HomeController extends Controller {
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $class = 'index';

        $updates = Cache::get('home_updates', 'N/A');
        $users = Cache::get('home_users', 'N/A');
        $organisations = Cache::get('home_organisations', 'N/A');
        $datasets = Cache::get('home_datasets', 'N/A');
        $mostActiveOrg = Cache::get('home_active', 'N/A');

        $lastMonth = __('custom.'. strtolower(date('F', strtotime('last month'))));
        $lastMonth .= ' '. date('Y', strtotime('last month'));

        $params = [
            'criteria' => [
                'active' => 1
            ]
        ];

        $categoryReq = Request::create('/api/listMainCategories', 'POST', $params);
        $categoryApi = new ApiCategories($categoryReq);
        $resultCategories = $categoryApi->listMainCategories($categoryReq)->getData();
        $categories = $resultCategories->categories;

        return view('/home/index', compact(
            'class',
            'updates',
            'users',
            'organisations',
            'datasets',
            'lastMonth',
            'mostActiveOrg',
            'categories'
        ));
    }
}
