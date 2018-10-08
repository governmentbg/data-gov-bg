<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\NewsController as ApiNews;

class NewsController extends Controller {
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

    }

    public function viewNews(Request $request, $id)
    {
        $params['news_id'] = $id;
        $params['active'] = true;
        $newsRequest = Request::create('/api/getNewsDetails', 'POST', $params);
        $apiNews = new ApiNews($newsRequest);
        $newsList = $apiNews->getNewsDetails($newsRequest)->getData();

        if ($newsList->success == false) {
            return back();
        }

        if (!is_null($newsList->news)) {
            return view(
                'news/view',
                [
                    'class'          => 'news',
                    'newsList'       => $newsList->news,
                    'activeSections' => $this->getActiveSections()
                ]
            );
        }
    }

    public function listNews(Request $request)
    {
        $perPage = 6;
        $pageNumber = !empty($request->page) ? $request->page : 1;
        if (\Auth::check()) {
            $params['api_key'] = \Auth::user()->api_key;
        }

        $params = [
            'records_per_page' => $perPage,
            'page_number'      => $pageNumber,
            'criteria'         => [
                'active'   => true,
                'order'    => [
                    'type'     => 'desc',
                    'field'    => 'created_at'
                ]
            ]
        ];

        $newsRequest = Request::create('/api/listNews', 'POST', $params);
        $apiNews = new ApiNews($newsRequest);
        $newsList = $apiNews->listNews($newsRequest)->getData();

        $paginationData = $this->getPaginationData(
            $newsList->news,
            $newsList->total_records,
            [],
            $perPage
        );

        return view('news/list',
            [
                'class'          => 'news',
                'newsList'       => $paginationData['items'],
                'pagination'     => $paginationData['paginate'],
                'activeSections' => $this->getActiveSections()
            ]
        );
    }

    public function searchNews(Request $request)
    {
        $perPage = 6;
        $search = $request->offsetGet('q');

        if (empty($search)) {
            return redirect('news');
        }

        $params = [
            'records_per_page'  => $perPage,
            'criteria'          => [
                'keywords' => $search,
            ]
        ];

        $searchNews = Request::create('/api/searchNews', 'POST', $params);
        $api = new ApiNews($searchNews);
        $newsData = $api->searchNews($searchNews)->getData();

        $news = !empty($newsData->news) ? $newsData->news : [];
        $count = !empty($newsData->total_records) ? $newsData->total_records : 0;

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($news, $count, $getParams, $perPage);

        return view('news/list', [
            'class'          => 'news',
            'newsList'       => $paginationData['items'],
            'pagination'     => $paginationData['paginate'],
            'search'         => $search,
            'activeSections' => $this->getActiveSections()
        ]);
    }
}
