<?php

namespace App\Http\Controllers\Api;

use App\Page;
use \Validator;
use App\Module;
use App\RoleRight;
use App\ActionsHistory;
use App\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class NewsController extends ApiController
{
    /**
     * Add a new piece of news
     *
     * Requires a json $request
     *
     * @param string api_key - required
     * @param array data - required
     * @param string data[locale] - optional
     * @param string data[title] - required
     * @param string data[abstract] -required
     * @param string data[body] - required
     * @param string data[head_title] - optional
     * @param string data[meta_description] - optional
     * @param string data[forum_link] - optional
     * @param integer data[active] - required
     * @param date data[valid_from] - optional
     * @param date data[valid_to] - optional
     *
     * @return json response with new news_id on success and error on fail
     */
    public function addNews(Request $request)
    {
        $newsData = $request->all();

        $validator = Validator::make($newsData, [
            'data'                  => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($newsData['data'], [
                'locale'              => 'nullable|string|max:5',
                'title'               => 'required_with:locale|max:191',
                'title.bg'            => 'required_without:locale|string|max:191',
                'title.*'             => 'max:191',
                'abstract'            => 'required_with:locale|max:8000',
                'abstract.bg'         => 'required_without:locale|string|max:8000',
                'abstract.*'          => 'max:8000',
                'body'                => 'required_with:locale|max:8000',
                'body.bg'             => 'required_without:locale|string|max:8000',
                'body.*'              => 'max:8000',
                'head_title'          => 'nullable|max:191',
                'head_title.*'        => 'max:191',
                'meta_description'    => 'nullable|max:191',
                'meta_description.*'  => 'max:191',
                'meta_keywords'       => 'nullable|max:191',
                'meta_keywords.*'     => 'max:191',
                'forum_link'          => 'nullable|string|max:191',
                'active'              => 'required|boolean',
                'valid_from'          => 'nullable|date',
                'valid_to'            => 'nullable|date',
            ]);
        }

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::NEWS,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            $locale = isset($newsData['data']['locale']) ? $newsData['data']['locale'] : null;

            try {
                DB::beginTransaction();
                $newNews = new Page;
                $newNews->type = Page::TYPE_NEWS;
                $newNews->title = $this->trans($locale, $newsData['data']['title']);
                $newNews->abstract = $this->trans($locale, $newsData['data']['abstract']);
                $newNews->body = $this->trans($locale, $newsData['data']['body']);

                if (isset($newsData['data']['head_title'])) {
                    $newNews->head_title = $this->trans($locale, $newsData['data']['head_title']);
                }

                if (isset($newsData['data']['meta_description'])) {
                    $newNews->meta_descript = $this->trans($locale, $newsData['data']['meta_description']);
                }

                if (isset($newsData['data']['meta_keywords'])) {
                    $newNews->meta_key_words = $this->trans($locale, $newsData['data']['meta_keywords']);
                }

                if (isset($newsData['data']['forum_link'])) {
                    $newNews->forum_link = $newsData['data']['forum_link'];
                }

                if (isset($newsData['data']['valid_from'])) {
                    $newNews->valid_from = $newsData['data']['valid_from'];
                }

                if (isset($newsData['data']['valid_to'])) {
                    $newNews->valid_to = $newsData['data']['valid_to'];
                }

                $newNews->active = $newsData['data']['active'];

                $newNews->save();
                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::NEWS),
                    'action'           => ActionsHistory::TYPE_ADD,
                    'action_object'    => $newNews->id,
                    'action_msg'       => 'Added news',
                ];

                Module::add($logData);

                return $this->successResponse(['news_id' => $newNews->id], true);
            } catch (QueryException $e) {
                DB::rollback();
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_news_fail'), $validator->errors()->messages());
    }

    /**
     * Edit an existing piece of news based on its id
     *
     * Requires a json $request
     *
     * @param string api_key - required
     * @param integer news_id - required
     * @param array data - required
     * @param string data[locale] - optional
     * @param string data[title] - optional
     * @param string data[abstract] -optional
     * @param string data[body] - optional
     * @param string data[head_title] - optional
     * @param string data[meta_description] - optional
     * @param string data[forum_link] - optional
     * @param integer data[active] - optional
     * @param date data[valid_from] - optional
     * @param date data[valid_to] - optional
     *
     * @return json response with status and error on fail
     */
    public function editNews(Request $request)
    {
        $editData = $request->all();

        $validator = Validator::make($editData, [
            'news_id'   => 'required|integer|exists:pages,id|digits_between:1,10',
            'data'      => 'required|array',
            'locale'    => 'nullable|string|max:5',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($editData['data'], [
                'title'               => 'required_with:locale|max:191',
                'title.bg'            => 'required_without:locale|string|max:191',
                'title.*'             => 'max:191',
                'abstract'            => 'required_with:locale|max:8000',
                'abstract.bg'         => 'required_without:locale|string|max:8000',
                'abstract.*'          => 'max:8000',
                'body'                => 'required_with:locale|max:8000',
                'body.bg'             => 'required_without:locale|string|max:8000',
                'body.*'              => 'max:8000',
                'head_title'          => 'nullable|max:191',
                'head_title.*'        => 'max:191',
                'meta_description'    => 'nullable|max:191',
                'meta_description.*'  => 'max:191',
                'meta_keywords'       => 'nullable|max:191',
                'meta_keywords.*'     => 'max:191',
                'forum_link'          => 'nullable|string|max:191',
                'active'              => 'nullable|boolean',
                'valid_from'          => 'nullable|date',
                'valid_to'            => 'nullable|date',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $locale = isset($newsData['locale']) ? $newsData['locale'] : null;
                $newsToEdit = Page::find($editData['news_id']);
                $rightCheck = RoleRight::checkUserRight(
                    Module::NEWS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $newsToEdit->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                DB::beginTransaction();

                if (isset($editData['data']['title'])) {
                    $newsToEdit->title = $this->trans($locale, $editData['data']['title']);
                }

                if (isset($editData['data']['abstract'])) {
                    $newsToEdit->abstract = $this->trans($locale, $editData['data']['abstract']);
                }

                if (isset($editData['data']['body'])) {
                    $newsToEdit->body = $this->trans($locale, $editData['data']['body']);
                }

                if (isset($editData['data']['head_title'])) {
                    $newsToEdit->head_title = $this->trans($locale, $editData['data']['head_title']);
                }

                if (isset($editData['data']['meta_description'])) {
                    $newsToEdit->meta_descript = $this->trans($locale, $editData['data']['meta_description']);
                }

                if (isset($editData['data']['meta_keywords'])) {
                    $newsToEdit->meta_key_words = $this->trans($locale, $editData['data']['meta_keywords']);
                }

                if (isset($editData['data']['forum_link'])) {
                    $newsToEdit->forum_link = $editData['data']['forum_link'];
                }

                if (isset($editData['data']['active'])) {
                    $newsToEdit->active = $editData['data']['active'];
                }

                if (isset($editData['data']['valid_from'])) {
                    $newsToEdit->valid_from = $editData['data']['valid_from'];
                }

                if (isset($editData['data']['valid_to'])) {
                    $newsToEdit->valid_to = $editData['data']['valid_to'];
                }

                $newsToEdit->save();
                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::NEWS),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $newsToEdit->id,
                    'action_msg'       => 'Edited news',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                DB::rollback();
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_news_fail'), $validator->errors()->messages());
    }

    /**
     * Fucntion for deleting an existing piece of news
     *
     * Requires json $request
     *
     * @param string api_key - required
     * @param integer news_id - required
     *
     * @return json with status and error on failure
     */
    public function deleteNews(Request $request)
    {
        $newsDeleteData = $request->all();
        $validator = Validator::make($newsDeleteData, [
            'news_id' => 'required|integer|exists:pages,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            try {
                $deleteNews = Page::find($newsDeleteData['news_id']);
                $rightCheck = RoleRight::checkUserRight(
                    Module::NEWS,
                    RoleRight::RIGHT_ALL,
                    [],
                    [
                        'created_by' => $deleteNews->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                $deleteNews->delete();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::NEWS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $newsDeleteData['news_id'],
                    'action_msg'       => 'Deleted news',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_news_fail'), $validator->errors()->messages());
    }

    /**
     * Function for listing existing news by criteria
     *
     * @param string locale - optional
     * @param array criteria - optional
     * @param integer criteria[active] - optional
     * @param integer criteria[valid] - optional
     * @param date criteria[date_from] - optional
     * @param date criteria[date_to] - optional
     * @param string criteria[date_type] - optional
     * @param integer criteria[active] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json list of news or error on failure
     */
    public function listNews(Request $request)
    {
        $newsListData = $request->all();

        $validator = Validator::make($newsListData, [
            'api_key'               => 'nullable|string|exists:users,api_key',
            'locale'                => 'nullable|string|max:5',
            'criteria'              => 'nullable|array',
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
        ]);

        $criteria = isset($newsListData['criteria']) ? $newsListData['criteria'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'active'       => 'nullable|boolean',
                'valid'        => 'nullable|integer|digits_between:1,10',
                'date_from'    => 'nullable|date',
                'date_to'      => 'nullable|date',
                'date_type'    => 'nullable|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $result = [];
            $criteria = $request->offsetGet('criteria');
            $locale = \LaravelLocalization::getCurrentLocale();
            $newsList = Page::select()->where('type', Page::TYPE_NEWS);

            $filterColumn = 'created_at';

            if (!empty($criteria['date_type'])) {
                if (isset($criteria['date_type']) && strtolower($criteria['date_type']) == Page::DATE_TYPE_UPDATED) {
                    $filterColumn = 'updated_at';
                }
            }

            if (!empty($criteria['date_from'])) {
                if (isset($criteria['date_type']) && strtolower($criteria['date_type']) == Page::DATE_TYPE_VALID) {
                    $filterColumn = 'valid_from';
                }

                $newsList->where($filterColumn, '>=', $criteria['date_from']);
            }

            if (!empty($criteria['date_to'])) {
                if (isset($criteria['date_type']) && strtolower($criteria['date_type']) == Page::DATE_TYPE_VALID) {
                    $filterColumn = 'valid_to';
                }

                $newsList->where($filterColumn, '<=', $criteria['date_to']);
            }

            if (isset($newsListData['api_key'])) {
                if (!\Auth::user()->is_admin) {
                    if (isset($criteria['active'])) {
                        $newsList->where('active', $criteria['active']);
                    }

                    if (isset($criteria['valid'])) {
                        if ($criteria['valid'] == 1) {
                            $newsList->where(function ($m) {
                                $m->where('valid_to', '>=', date(now()))
                                    ->where('valid_from', '<=', date(now()))

                                    ->orWhere('valid_from', null)
                                    ->where('valid_to', '>=', date(now()))

                                    ->orWhere('valid_to', null)
                                    ->where('valid_from', '>=', date(now()))

                                    ->orWhere('valid_to', null)
                                    ->where('valid_from', null);
                            });
                        }

                        if ($criteria['valid'] == 0) {
                            $newsList->where(function ($m) {
                                $m->where('valid_to', '<', date(now()))
                                    ->where('valid_from', '>', date(now()))

                                    ->orWhere('valid_from', null)
                                    ->where('valid_to', '<', date(now()))

                                    ->orWhere('valid_from', '>', date(now()))
                                    ->where('valid_to', null)

                                    ->orWhere('valid_from', '>', date(now()))
                                    ->where('valid_to', '>', date(now()))

                                    ->orWhere('valid_from', '<', date(now()))
                                    ->where('valid_to', '<', date(now()));
                            });
                        }
                    }
                } else {
                    if (isset($criteria['active'])) {
                        $newsList->where('active', $criteria['active']);
                    }
                }
            } else if (!\Auth::check()) {
                $newsList->where('active', 1);
                $newsList->where(function ($m){
                        $m->where('valid_from', null)
                            ->where('valid_to', null)
                            ->orWhere('valid_from', '<=', date(now()))
                            ->where('valid_to', '>=', date(now()));
                });
            }

            $count = $newsList->count();

            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $newsList->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }

            $newsList->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $newsList = $newsList->get();

            if ($newsList) {
                foreach ($newsList as $singleNews) {
                    $result[] = [
                        'id'                => $singleNews->id,
                        'locale'            => $locale,
                        'title'             => $singleNews->title,
                        'abstract'          => $singleNews->abstract,
                        'body'              => $singleNews->body,
                        'head_title'        => $singleNews->head_title,
                        'meta_description'  => $singleNews->meta_descript,
                        'meta_keywords'     => $singleNews->meta_key_words,
                        'forum_link'        => $singleNews->forum_link,
                        'active'            => $singleNews->active,
                        'created_at'        => date($singleNews->created_at),
                        'updated_at'        => date($singleNews->updated_at),
                        'created_by'        => $singleNews->created_by,
                        'updated_by'        => $singleNews->updated_by,
                        'valid_from'        => date($singleNews->valid_from),
                        'valid_to'          => date($singleNews->valid_to),
                    ];
                }
            }

            $transFields = [
                'title',
                'abstract',
                'body',
                'head_title',
                'meta_descript',
                'meta_key_words',
            ];

            if (isset($criteria['order'])) {
                if ($criteria['order'] && in_array($criteria['order']['field'], $transFields)) {
                    usort($results, function($a, $b) use ($criteria) {
                        return strtolower($criteria['order']['type']) == 'asc'
                            ? strcmp($a[$criteria['order']['field']], $b[$criteria['order']['field']])
                            : strcmp($b[$criteria['order']['field']], $a[$criteria['order']['field']]);
                    });
                }
            }

            return $this->successResponse([
                'total_records' => $count,
                'news'          => $result,
            ], true);
        }

        return $this->errorResponse(__('custom.list_news_fail'), $validator->errors()->messages());
    }


    /**
     * Search news articles based on input criteria
     *
     * @param string locale - optional
     * @param array criteria - required
     * @param string criteria[keywords] - required
     * @param array criteria[order] - optional
     * @param string order['type'] - optional
     * @param string order['field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with status and results or error on failure
     */
    public function searchNews(Request $request)
    {
        $newsSearchData = $request->all();

        $validator = Validator::make($newsSearchData, [
            'locale'                => 'nullable|string|max:5',
            'criteria'              => 'required|array',
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
        ]);

        $criteria = isset($newsSearchData['criteria']) ? $newsSearchData['criteria'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'keywords'     => 'required|string|max:191',
                'order'        => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            $result = [];
            $criteria = $request->offsetGet('criteria');
            $search = $criteria['keywords'];

            $ids = Page::search($search)->where('type', Page::TYPE_NEWS)->get()->pluck('id');
            $newsList = Page::whereIn('id', $ids);

            if(!\Auth::check() || !Role::isAdmin()) {
                $newsList
                    ->where('active', 1)
                    ->where(function ($m){
                        $m->where('valid_from', null)
                            ->where('valid_to', null)
                            ->orWhere('valid_from', '<=', date(now()))
                            ->where('valid_to', '>=', date(now()));
                });
            }

            $total_records = $newsList->count();

            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $newsList->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }

            $newsList->forPage(
                $request->offsetGet('page_number'),
                $this->getRecordsPerPage($request->offsetGet('records_per_page'))
            );

            $newsList = $newsList->get();

            if (!empty($newsList)) {
                foreach ($newsList as $singleNews) {
                    $result[] = [
                        'id'                => $singleNews->id,
                        'locale'            => $locale,
                        'title'             => $singleNews->title,
                        'abstract'          => $singleNews->abstract,
                        'body'              => $singleNews->body,
                        'head_title'        => $singleNews->head_title,
                        'meta_description'  => $singleNews->meta_descript,
                        'meta_keywords'     => $singleNews->meta_key_words,
                        'forum_link'        => $singleNews->forum_link,
                        'active'            => $singleNews->active,
                        'created_at'        => date($singleNews->created_at),
                        'updated_at'        => date($singleNews->updated_at),
                        'created_by'        => $singleNews->created_by,
                        'updated_by'        => $singleNews->updated_by,
                        'valid_from'        => date($singleNews->valid_from),
                        'valid_to'          => date($singleNews->valid_to),
                    ];
                }
            }

            return $this->successResponse([
                'total_records' => $total_records,
                'news' => $result,
            ], true);
        }

        return $this->errorResponse(__('custom.search_news_fail'), $validator->errors()->messages());
    }

    /**
     * Get details on a specific news piece via news_id
     *
     * @param string locale - optional
     * @param integer news_id - required
     *
     * @return json response with status and news details or error on failure
     */
    public function getNewsDetails(Request $request)
    {
        $newsSearchData = $request->all();

        $validator = Validator::make($newsSearchData, [
            'locale'  => 'string|max:5',
            'news_id' => 'integer|required|exists:pages,id|digits_between:1,10',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();
        if (!$validator->fails()) {
            $singleNews = Page::select()->where('type', 1)->where('id', $newsSearchData['news_id']);

            if(!\Auth::check() || !Role::isAdmin()) {
                $singleNews
                    ->where('active', 1)
                    ->where(function ($m){
                        $m->where('valid_from', null)
                            ->where('valid_to', null)
                            ->orWhere('valid_from', '<=', date(now()))
                            ->where('valid_to', '>=', date(now()));
                });
            }

            $singleNews = $singleNews->first();

            if ($singleNews) {
                $result = [
                    'id'                => $singleNews->id,
                    'locale'            => $locale,
                    'title'             => $singleNews->title,
                    'abstract'          => $singleNews->abstract,
                    'body'              => $singleNews->body,
                    'head_title'        => $singleNews->head_title,
                    'meta_description'  => $singleNews->meta_descript,
                    'meta_keywords'     => $singleNews->meta_key_words,
                    'forum_link'        => $singleNews->forum_link,
                    'active'            => $singleNews->active,
                    'created_at'        => date($singleNews->created_at),
                    'updated_at'        => date($singleNews->updated_at),
                    'created_by'        => $singleNews->created_by,
                    'updated_by'        => $singleNews->updated_by,
                    'valid_from'        => date($singleNews->valid_from),
                    'valid_to'          => date($singleNews->valid_to),
                ];

                $logData = [
                    'module_name'      => Module::getModuleName(Module::NEWS),
                    'action'           => ActionsHistory::TYPE_SEE,
                    'action_object'    => $singleNews->id,
                    'action_msg'       => 'Got news details',
                ];

                Module::add($logData);

                return $this->successResponse([
                    'news' => $result,
                ], true);
            }
        }

        return $this->errorResponse(__('custom.get_news_fail'), $validator->errors()->messages());
    }
}
