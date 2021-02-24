<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\NewsController as ApiNews;
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
        $latestNewsCache     = Cache::get('latest_news', '');
//        # Get latest news
//        $newsRequest = Request::create('/api/listNews', 'POST', [
//          'records_per_page'  => 1,
//          'criteria'         => [
//            'active'   => true,
//            'home_page'   => true,
//          ]
//        ]);
//        $apiNews = new ApiNews($newsRequest);
//        $result = $apiNews->listNews($newsRequest)->getData();
//        if(!$result->news) {
//          $newsRequest = Request::create('/api/listNews', 'POST', [
//            'records_per_page'  => 1,
//            'criteria'         => [
//              'active'   => true,
//              'order'    => [
//                'field'    => 'created_at',
//                'type'     => 'desc'
//              ]
//            ]
//          ]);
//          $apiNews = new ApiNews($newsRequest);
//          $result = $apiNews->listNews($newsRequest)->getData();
//        }
//        $latestNewsCache = $result->news;
        //\Log::error(gettype($latestNewsCache));

        $latestNews  = (!empty($latestNewsCache)) ? $latestNewsCache[0] : $latestNewsCache;
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
            'latestNews',
            'lastMonth',
            'mostActiveOrg',
            'categories'
        ));
    }
}
