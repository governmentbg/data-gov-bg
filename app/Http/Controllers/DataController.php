<?php

namespace App\Http\Controllers;

use App\DataSet;
use App\Resource;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\CategoryController as ApiCategory;
use App\Http\Controllers\Api\TagController as ApiTag;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\SignalController as ApiSignal;
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

    /**
     * List datasets
     *
     * @param Request $request
     *
     * @return view for browsing datasets
     */
    public function list(Request $request)
    {
        $locale = \LaravelLocalization::getCurrentLocale();

        $criteria = [];

        // filters
        $organisations = [];
        $users = [];
        $groups = [];
        $categories = [];
        $tags = [];
        $formats = [];
        $termsOfUse = [];
        $getParams = [];
        $display = [];

        // organisations / users filter
        $userDatasetsOnly = false;
        if ($request->filled('org') && is_array($request->org)) {
            $criteria['org_ids'] = $request->org;
            $getParams['org'] = $request->org;
            $getParams['user'] = [];
        } else {
            $getParams['org'] = [];
            if ($request->filled('user') && is_array($request->user)) {
                $criteria['user_ids'] = $request->user;
                $userDatasetsOnly = true;
                $getParams['user'] = $request->user;
            } else {
                $getParams['user'] = [];
            }
        }

        // groups filter
        if ($request->filled('group') && is_array($request->group)) {
            $criteria['group_ids'] = $request->group;
            $getParams['group'] = $request->group;
        } else {
            $getParams['group'] = [];
        }

        // main categories filter
        if ($request->filled('category') && is_array($request->category)) {
            $criteria['category_ids'] = $request->category;
            $getParams['category'] = $request->category;
        } else {
            $getParams['category'] = [];
        }

        // tags filter
        if ($request->filled('tag') && is_array($request->tag)) {
            $criteria['tag_ids'] = $request->tag;
            $getParams['tag'] = $request->tag;
        } else {
            $getParams['tag'] = [];
        }

        // data formats filter
        if ($request->filled('format') && is_array($request->format)) {
            $criteria['formats'] = array_map('strtoupper', $request->format);
            $getParams['format'] = $request->format;
        } else {
            $getParams['format'] = [];
        }

        // terms of use filter
        if ($request->filled('license') && is_array($request->license)) {
            $criteria['terms_of_use_ids'] = $request->license;
            $getParams['license'] = $request->license;
        } else {
            $getParams['license'] = [];
        }

        // prepare datasets parameters
        $perPage = 6;
        $params = [
            'records_per_page' => $perPage,
            'page_number'      => !empty($request->page) ? $request->page : 1,
            'criteria'         => $criteria,
        ];
        $params['criteria']['locale'] = $locale;
        $params['criteria']['status'] = DataSet::STATUS_PUBLISHED;
        $params['criteria']['visibility'] = DataSet::VISIBILITY_PUBLIC;
        $params['criteria']['user_datasets_only'] = $userDatasetsOnly;

        // apply search
        if ($request->filled('q') && !empty(trim($request->q))) {
            $getParams['q'] = trim($request->q);
            $params['criteria']['keywords'] = $getParams['q'];
        }

        // apply sort parameters
        if ($request->has('sort')) {
            $getParams['sort'] = $request->sort;
            if ($request->sort != 'relevance') {
                $params['criteria']['order']['field'] = $request->sort;
                if ($request->has('order')) {
                    $params['criteria']['order']['type'] = $request->order;
                }
                $getParams['order'] = $request->order;
            }
        }

        // list datasets
        $rq = Request::create('/api/listDatasets', 'POST', $params);
        $api = new ApiDataSet($rq);
        $res = $api->listDatasets($rq)->getData();

        $datasets = !empty($res->datasets) ? $res->datasets : [];
        $count = !empty($res->total_records) ? $res->total_records : 0;

        $paginationData = $this->getPaginationData($datasets, $count, $getParams, $perPage);

        $datasetOrgs = [];
        $followed = [];

        if (!empty($paginationData['items'])) {
            // get organisation ids
            $orgIds = array_where(array_pluck($paginationData['items'], 'org_id'), function ($value, $key) {
                return !is_null($value);
            });

            // list organisations
            $params = [
                'criteria'  => [
                    'org_ids'  => array_unique($orgIds),
                    'locale'   => $locale
                ]
            ];
            $rq = Request::create('/api/listOrganisations', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listOrganisations($rq)->getData();
            $datasetOrgs = !empty($res->organisations) ? $res->organisations : [];

            $recordsLimit = 10;

            if (empty($getParams['user'])) {
                // check for organisation records limit
                $hasLimit = !($request->filled('org_limit') && $request->org_limit == 0);

                // list data organisations
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria,
                        'locale' => $locale
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataOrganisations', 'POST', $params);
                $api = new ApiOrganisation($rq);
                $res = $api->listDataOrganisations($rq)->getData();

                $organisations = !empty($res->organisations) ? $res->organisations : [];
                $getParams['org'] = array_intersect($getParams['org'], array_pluck($organisations, 'id'));

                $this->prepareDisplayParams(count($organisations), $hasLimit, $recordsLimit, 'org', $display);
            }

            if (empty($getParams['org'])) {
                // check for user records limit
                $hasLimit = !($request->filled('user_limit') && $request->user_limit == 0);

                // list data users
                $params = [
                    'criteria' => [
                        'dataset_criteria' => $criteria
                    ],
                ];
                if ($hasLimit) {
                    $params['criteria']['records_limit'] = $recordsLimit;
                }

                $rq = Request::create('/api/listDataUsers', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->listDataUsers($rq)->getData();

                $users = !empty($res->users) ? $res->users : [];
                $getParams['user'] = array_intersect($getParams['user'], array_pluck($users, 'id'));

                $this->prepareDisplayParams(count($users), $hasLimit, $recordsLimit, 'user', $display);
            }

            // check for group records limit
            $hasLimit = !($request->filled('group_limit') && $request->group_limit == 0);

            // list data groups
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria,
                    'locale' => $locale
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataGroups', 'POST', $params);
            $api = new ApiOrganisation($rq);
            $res = $api->listDataGroups($rq)->getData();

            $groups = !empty($res->groups) ? $res->groups : [];
            $getParams['group'] = array_intersect($getParams['group'], array_pluck($groups, 'id'));

            $this->prepareDisplayParams(count($groups), $hasLimit, $recordsLimit, 'group', $display);

            // check for category records limit
            $hasLimit = !($request->filled('category_limit') && $request->category_limit == 0);

            // check for category records limit
            $hasLimit = !($request->filled('category_limit') && $request->category_limit == 0);

            // list data categories
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria,
                    'locale' => $locale
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataCategories', 'POST', $params);
            $api = new ApiCategory($rq);
            $res = $api->listDataCategories($rq)->getData();

            $categories = !empty($res->categories) ? $res->categories : [];
            $getParams['category'] = array_intersect($getParams['category'], array_pluck($categories, 'id'));

            $this->prepareDisplayParams(count($categories), $hasLimit, $recordsLimit, 'category', $display);

            // check for tag records limit
            $hasLimit = !($request->filled('tag_limit') && $request->tag_limit == 0);

            // list data tags
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataTags', 'POST', $params);
            $api = new ApiTag($rq);
            $res = $api->listDataTags($rq)->getData();

            $tags = !empty($res->tags) ? $res->tags : [];
            $getParams['tag'] = array_intersect($getParams['tag'], array_pluck($tags, 'id'));

            $this->prepareDisplayParams(count($tags), $hasLimit, $recordsLimit, 'tag', $display);

            // check for format records limit
            $hasLimit = !($request->filled('format_limit') && $request->format_limit == 0);

            // list data formats
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria,
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataFormats', 'POST', $params);
            $api = new ApiResource($rq);
            $res = $api->listDataFormats($rq)->getData();

            $formats = !empty($res->data_formats) ? $res->data_formats : [];
            $getParams['format'] = array_intersect($getParams['format'], array_map('strtolower', array_pluck($formats, 'format')));

            $this->prepareDisplayParams(count($formats), $hasLimit, $recordsLimit, 'format', $display);

            // check for terms of use records limit
            $hasLimit = !($request->filled('license_limit') && $request->license_limit == 0);

            // list data terms of use
            $params = [
                'criteria' => [
                    'dataset_criteria' => $criteria,
                    'locale' => $locale
                ],
            ];
            if ($hasLimit) {
                $params['criteria']['records_limit'] = $recordsLimit;
            }

            $rq = Request::create('/api/listDataTermsOfUse', 'POST', $params);
            $api = new ApiTermsOfUse($rq);
            $res = $api->listDataTermsOfUse($rq)->getData();

            $termsOfUse = !empty($res->terms_of_use) ? $res->terms_of_use : [];
            $getParams['license'] = array_intersect($getParams['license'], array_pluck($termsOfUse, 'id'));

            $this->prepareDisplayParams(count($termsOfUse), $hasLimit, $recordsLimit, 'license', $display);

            // follow / unfollow dataset
            if ($user = \Auth::user()) {
                // get user follows
                $params = [
                    'api_key' => $user->api_key,
                    'id'      => $user->id
                ];
                $rq = Request::create('/api/getUserSettings', 'POST', $params);
                $api = new ApiUser($rq);
                $res = $api->getUserSettings($rq)->getData();
                if (!empty($res->user) && !empty($res->user->follows)) {
                    $followed = array_where(array_pluck($res->user->follows, 'dataset_id'), function ($value, $key) {
                        return !is_null($value);
                    });
                }

                $datasetIds = array_pluck($paginationData['items'], 'id');

                if ($request->has('follow')) {
                    // follow dataset
                    if (in_array($request->follow, $datasetIds) && !in_array($request->follow, $followed)) {
                        $followRq = Request::create('api/addFollow', 'POST', [
                            'api_key' => $user->api_key,
                            'user_id' => $user->id,
                            'data_set_id' => $request->follow,
                        ]);
                        $apiFollow = new ApiFollow($followRq);
                        $followResult = $apiFollow->addFollow($followRq)->getData();
                        if ($followResult->success) {
                            return back();
                        }
                    }
                } elseif ($request->has('unfollow')) {
                    // unfollow dataset
                    if (in_array($request->unfollow, $datasetIds) && in_array($request->unfollow, $followed)) {
                        $followRq = Request::create('api/unFollow', 'POST', [
                            'api_key' => $user->api_key,
                            'user_id' => $user->id,
                            'data_set_id' => $request->unfollow,
                        ]);
                        $apiFollow = new ApiFollow($followRq);
                        $followResult = $apiFollow->unFollow($followRq)->getData();
                        if ($followResult->success) {
                            return back();
                        }
                    }
                }
            }
        }

        return view(
            'data/list',
            [
                'class'              => 'data',
                'datasetOrgs'        => $datasetOrgs,
                'datasets'           => $paginationData['items'],
                'resultsCount'       => $count,
                'pagination'         => $paginationData['paginate'],
                'organisations'      => $organisations,
                'users'              => $users,
                'groups'             => $groups,
                'categories'         => $categories,
                'tags'               => $tags,
                'formats'            => $formats,
                'termsOfUse'         => $termsOfUse,
                'getParams'          => $getParams,
                'display'            => $display,
                'followed'           => $followed
            ]
        );
    }

    private function prepareDisplayParams($count, $hasLimit, $recordsLimit, $type, &$display)
    {
        if ($hasLimit && $count >= $recordsLimit) {
            $display['show_all'][$type] = true;
            $display['only_popular'][$type] = false;
        } elseif ($count > $recordsLimit) {
            $display['show_all'][$type] = false;
            $display['only_popular'][$type] = true;
        } else {
            $display['show_all'][$type] = false;
            $display['only_popular'][$type] = false;
        }
    }

    public function view(Request $request, $uri)
    {
        return view('data/view', [
            'class'     => 'data',
            'filter'    => 'healthcare',
            'mainCats'  => [
                'healthcare',
                'innovation',
                'education',
                'public_sector',
                'municipalities',
                'agriculture',
                'justice',
                'economy_business',
            ],
        ]);
    }

    public function resourceView(Request $request, $uri)
    {
        return view('data/view', [
            'class'     => 'data',
            'filter'    => 'healthcare',
            'mainCats'  => [
                'healthcare',
                'innovation',
                'education',
                'public_sector',
                'municipalities',
                'agriculture',
                'justice',
                'economy_business',
            ],
        ]);
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

    public function reportedList()
    {

    }

    public function reportedView()
    {

    }
}
