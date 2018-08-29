<?php

namespace App\Http\Controllers;

use App\Resource;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use Illuminate\Http\Request;

class DataController extends Controller {

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

    public function view(Request $request)
    {
        $class = 'data';

        $mainCats = [
            'healthcare',
            'innovation',
            'education',
            'public_sector',
            'municipalities',
            'agriculture',
            'justice',
            'economy_business',
        ];

        $filter = $request->offsetGet('filter');

        return view('data/list', compact('class','mainCats', 'filter'));
    }

    public function linkedData(Request $request)
    {
        $formats = Resource::getFormats();
        $formats = array_only($formats, [Resource::FORMAT_JSON]);

        $selFormat = $request->input('format', '');

        $searchResultsUrl = '';

        if ($request->filled('query') && !empty($selFormat)) {
            if (($searchQuery = json_decode($request->input('query'))) && isset($searchQuery->query)) {
                $params = [
                    'query'  => json_encode($searchQuery->query),
                    'format' => $selFormat
                ];
                if (isset($searchQuery->sort)) {
                    if (is_array($searchQuery->sort) && is_object(array_first($searchQuery->sort))) {
                        foreach (array_first($searchQuery->sort) as $field => $type) {
                            $params['order']['field'] = $field;
                            if (isset($type->order)) {
                                $params['order']['type'] = $type->order;
                            }
                            break;
                        }
                    } else {
                        return back()->withInput()->withErrors(['query' => [__('custom.invalid_search_query_sort')]]);
                    }
                }
                if ($request->filled('limit_results')) {
                    $params['records_per_page'] = intval($request->limit_results);
                }

                if ($request->has('search_results_url')) {
                    $rq = Request::create('/api/getLinkedData', 'GET', $params);
                    $searchResultsUrl = $request->root() .'/'. $rq->path() .'?'. http_build_query($rq->query());
                } else {
                    $rq = Request::create('/api/getLinkedData', 'POST', $params);
                    $api = new ApiResource($rq);
                    $result = $api->getLinkedData($rq)->getData();

                    if (isset($result->success) && $result->success && isset($result->data)) {
                        if ($selFormat == Resource::FORMAT_JSON) {
                            $content = json_encode($result->data);
                            $filename = time() .'.'. strtolower($formats[$selFormat]);

                            return \Response::make($content, '200', array(
                                'Content-Type' => 'application/octet-stream',
                                'Content-Disposition' => 'attachment; filename="'. $filename .'"'
                            ));
                        }
                    } else {
                        $errors = $result->errors ?: ['common' => $result->error->message];
                        return back()->withInput()->withErrors($errors);
                    }
                }
            } else {
                return back()->withInput()->withErrors(['query' => [__('custom.invalid_search_query')]]);
            }
        }

        return view(
            'data/linkedData',
            [
                'class'            => 'data',
                'formats'          => $formats,
                'selectedFormat'   => $selFormat,
                'searchResultsUrl' => $searchResultsUrl
            ]
        );
    }

    public function reportedList() {

    }

    public function reportedView() {

    }

}
