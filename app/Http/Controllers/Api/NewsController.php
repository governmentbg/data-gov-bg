<?php

namespace App\Http\Controllers\Api;

use App\Page;
use \Validator;
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
     * @param string data[locale] - required
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
            'data.locale'           => 'required|string',
            'data.title'            => 'required|string',
            'data.abstract'         => 'required|string',
            'data.body'             => 'required|string',
            'data.head_title'       => 'nullable|string',
            'data.meta_description' => 'nullable|string',
            'data.meta_keywords'    => 'nullable|string',
            'data.forum_link'       => 'nullable|string',
            'data.active'           => 'required|integer',
            'data.valid_from'       => 'nullable|date',
            'data.valid_to'         => 'nullable|date',
        ]);

        if (!$validator->fails()) {
            try {
            DB::beginTransaction();
            $newNews = new Page;
            $locale = $newsData['data']['locale'];
            $newNews->title = $this->trans($locale, $newsData['data']['title']);
            $newNews->abstract = $this->trans($locale, $newsData['data']['abstract']);
            $newNews->body = $this->trans($locale, $newsData['data']['body']);

            if (isset($newsData['data']['head_title'])) {
                $newNews->head_title = $this->trans($locale, $newsData['data']['head_title']);
            }

            if (isset($newsData['data']['meta_description'])) {
                $newNews->meta_descript = $this->trans($locale, $newsData['data']['meta_description']);
            }

            if (isset($newsData['data']['meta_key_words'])) {
                $newNews->meta_key_words = $this->trans($locale, $newsData['data']['meta_key_words']);
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
                return $this->successResponse(['news_id' => $newNews->id], true);
            } catch (QueryException $e) {
                DB::rollback();
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse('Add news failure', $validator->errors()->messages());
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
            'news_id'               => 'required|integer|exists:pages,id',
            'data'                  => 'required|array',
            'data.locale'           => 'nullable|string',
            'data.title'            => 'nullable|string',
            'data.abstract'         => 'nullable|string',
            'data.body'             => 'nullable|string',
            'data.head_title'       => 'nullable|string',
            'data.meta_description' => 'nullable|string',
            'data.meta_key_words'   => 'nullable|string',
            'data.forum_link'       => 'nullable|string',
            'data.active'           => 'nullable|integer',
            'data.valid_from'       => 'nullable|date',
            'data.valid_to'         => 'nullable|date',
        ]);

        if (isset($editData['data']['title'])) {
            $validator->sometimes('data.locale', 'required', function ($editData) {
                return $editData['data']['title'] != '';
            });
        }

        if (isset($editData['data']['abstract'])) {
            $validator->sometimes('data.locale', 'required', function ($editData) {
                return $editData['data']['abstract'] != '';
            });
        }

        if (isset($editData['data']['body'])) {
            $validator->sometimes('data.locale', 'required', function ($editData) {
                return $editData['data']['body'] != '';
            });
        }

        if (isset($editData['data']['head_title'])) {
            $validator->sometimes('data.locale', 'required', function ($editData) {
                return $editData['data']['head_title'] != '';
            });
        }

        if (isset($editData['data']['meta_description'])) {
            $validator->sometimes('data.locale', 'required', function ($editData) {
                return $editData['data']['meta_description'] != '';
            });
        }

        if (isset($editData['data']['meta_key_words'])) {
            $validator->sometimes('data.locale', 'required', function ($editData) {
                return $editData['data']['meta_key_words'] != '';
            });
        }

        if (!$validator->fails()) {
            try {
                $locale = $editData['data']['locale'];
                $newsToEdit = Page::find($editData['news_id']);
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
                return $this->successResponse();
            } catch (QueryException $e) {
                DB::rollback();
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse('Edit news failure', $validator->errors()->messages());
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
            'news_id' => 'required|integer|exists:pages,id',
        ]);

        if (!$validator->fails()) {
            try {
                $deleteNews = Page::find($newsDeleteData['news_id']);

                $deleteNews->delete();

                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse('Delete news failure', $validator->errors()->messages());
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
            'locale'                => 'nullable|string',
            'criteria'              => 'nullable|array',
            'criteria.active'       => 'nullable|integer',
            'criteria.valid'        => 'nullable|integer',
            'criteria.date_from'    => 'nullable|date',
            'criteria.date_to'      => 'nullable|date',
            'criteria.date_type'    => 'nullable|string',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
        ]);

        if (!$validator->fails()) {
            $result = [];
            $criteria = $request->offsetGet('criteria');
            $locale = \LaravelLocalization::getCurrentLocale();
            $newsList = Page::select();

            $filterColumn = 'created_at';

            if (!empty($criteria['date_type'])) {
                if (strtolower($criteria['date_type']) == Page::DATE_TYPE_UPDATED) {
                    $filterColumn = 'updated_at';
                }
            }

            if (!empty($criteria['date_from'])) {
                if (strtolower($criteria['date_type']) == Page::DATE_TYPE_VALID) {
                    $filterColumn = 'valid_from';
                }

                $newsList->where($filterColumn, '>=', $criteria['date_from']);
            }

            if (!empty($criteria['date_to'])) {
                if (strtolower($criteria['date_type']) == Page::DATE_TYPE_VALID) {
                    $filterColumn = 'valid_to';
                }

                $newsList->where($filterColumn, '<=', $criteria['date_to']);
            }

            if (isset($criteria['active'])) {
                $newsList->where('active', $criteria['active']);
            }

            if (isset($criteria['valid'])) {
                if ($criteria['valid'] == 1) {
                    $newsList->where(function ($newsList) {
                        $newsList->where('valid_to', '>=', date(now()))
                            ->where('valid_from', '<=', date(now()))

                            ->orWhere('valid_from', null)
                            ->where('valid_to', '>=', date(now()))

                            ->orWhere('valid_to', null)
                            ->where('valid_from', '>=', date(now()));
                    });
                }

                if ($criteria['valid'] == 0) {
                    $newsList->where(function ($newsList) {
                        $newsList->where('valid_to', '<', date(now()))
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

            $count = $newsList->count();

            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $query->orderBy($criteria['order']['field'], $criteria['order']['type']);
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

            return $this->successResponse([
                'total_records' => $count,
                'news'          => $result,
            ], true);
        }

        return $this->errorResponse('List news failure', $validator->errors()->messages());
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
            'locale'                => 'nullable|string',
            'criteria'              => 'required|array',
            'criteria.keywords'     => 'required|string',
            'criteria.order'        => 'nullable|array',
            'criteria.order.type'   => 'nullable|string',
            'criteria.order.field'  => 'nullable|string',
            'records_per_page'      => 'nullable|integer',
            'page_number'           => 'nullable|integer',
        ]);

        $locale = \LaravelLocalization::getCurrentLocale();

        if (!$validator->fails()) {
            $result = [];
            $criteria = $request->offsetGet('criteria');
            $search = $criteria['keywords'];

            $ids = Page::search($search)->get()->pluck('id');
            $newsList = Page::whereIn('id', $ids);

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

        return $this->errorResponse('Search news failure', $validator->errors()->messages());
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
            'locale' => 'string',
            'news_id' => 'integer|required|exists:pages,id',
        ]);
        $locale = \LaravelLocalization::getCurrentLocale();
        if (!$validator->fails()) {
            $singleNews = Page::find($newsSearchData['news_id']);

            if ($singleNews) {
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

                return $this->successResponse([
                    'news' => $result,
                ], true);
            }
        }

        return $this->errorResponse('Search news failure', $validator->errors()->messages());
    }
}
