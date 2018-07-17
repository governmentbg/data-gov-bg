<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Page;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use \Validator;

class NewsController extends ApiController
{
    /**
     * Add a new piece of news
     *
     * @param Request $request
     * @return json response
     */
    public function addNews(Request $request)
    {
        $newsData = $request->all();
        $validator = Validator::make($newsData, [
            'data' => 'required|array',
            'data.locale' => 'required|string',
            'data.title' => 'required|string',
            'data.abstract' => 'required|string',
            'data.body' => 'required|string',
            'data.head_title' => 'nullable|string',
            'data.meta_description' => 'nullable|string',
            'data.meta_keywords' => 'nullable|string',
            'data.forum_link' => 'nullable|string',
            'data.active' => 'required|integer',
            'data.valid_from' => 'date',
            'data.valid_to' => 'date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Add news failure');
        }

        $locale = $newsData['data']['locale'];

        $newNews = new Page;
        $newNews->title = [$locale => $newsData['data']['title']];
        $newNews->abstract = [$locale => $newsData['data']['abstract']];
        $newNews->body = [$locale => $newsData['data']['body']];

        if (isset($newsData['data']['head_title'])) {
            $newNews->head_title = [$locale => $newsData['data']['head_title']];
        }

        if (isset($newsData['data']['meta_description'])) {
            $newNews->meta_desctript = [$locale => $newsData['data']['meta_description']];
        }

        if (isset($newsData['data']['meta_key_words'])) {
            $newNews->meta_key_words = [$locale => $newsData['data']['meta_key_words']];
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

        try {
            $newNews->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Add news failure');
        }
        return $this->successResponse("news_id: " . $newNews->id);
    }

    /**
     * Edit an existing piece of news based on its id
     *
     * @param Request $request
     * @return json response
     */
    public function editNews(Request $request)
    {
        $editData = $request->all();
        if (sizeof($editData['data']) < 1) {
            return $this->errorResponse('Edit failure');
        }
        $validator = Validator::make($editData, [
            'news_id' => 'required|integer',
            'data' => ' array',
            'data.locale' => 'string',
            'data.title' => 'string',
            'data.abstract' => 'string',
            'data.body' => 'string',
            'data.head_title' => 'nullable|string',
            'data.meta_description' => 'nullable|string',
            'data.meta_key_words' => 'nullable|string',
            'data.forum_link' => 'nullable|string',
            'data.active' => 'integer',
            'data.valid_from' => 'date',
            'data.valid_to' => 'date',
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

        if ($validator->fails()) {
            return $this->errorResponse('Edit news failure');
        }

        $newsToEdit = Page::find($editData['news_id']);

        if (!$newsToEdit) {
            return $this->errorResponse('Edit news failure');
        }

        if (isset($editData['data']['title'])) {
            $newsToEdit->title = [$editData['data']['locale'] => $editData['data']['title']];
        }

        if (isset($editData['data']['abstract'])) {
            $newsToEdit->abstract = [$editData['data']['locale'] => $editData['data']['abstract']];
        }

        if (isset($editData['data']['body'])) {
            $newsToEdit->body = [$editData['data']['locale'] => $editData['data']['body']];
        }

        if (isset($editData['data']['head_title'])) {
            $newsToEdit->head_title = [$editData['data']['locale'] => $editData['data']['head_title']];
        }

        if (isset($editData['data']['meta_description'])) {
            $newsToEdit->meta_desctript = [$editData['data']['locale'] => $editData['data']['meta_description']];
        }

        if (isset($editData['data']['meta_key_words'])) {
            $newsToEdit->meta_key_words = [$editData['data']['locale'] => $editData['data']['meta_key_words']];
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

        try {
            $newsToEdit->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Edit news failure');
        }
        return $this->successResponse();

    }

    public function deleteNews(Request $request)
    {
        $newsDeleteData = $request->all();
        $validator = Validator::make($newsDeleteData, [
            'news_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete news failure');
        }

        $deleteNews = Page::find($newsDeleteData['news_id']);

        if (!$deleteNews) {
            return $this->errorResponse('Delete news failure');
        }

        try {
            $deleteNews->delete();
        } catch (QueryException $e) {
            return $this->errorResponse('Delete news failure');
        }
        return $this->successResponse();

    }

    public function listNews(Request $request)
    {
        $newsListData = $request->all();
        $validator = Validator::make($newsListData, [
            'locale' => 'string',
            'criteria' => 'array',
            'citeria.active' => 'integer',
            'criteria.valid' => 'integer',
            'criteria.date_from' => 'date',
            'criteria.date_to' => 'date',
            'criteria.date_type' => 'string',
            'criteria.order' => 'array',
            'criteria.order.type' => 'string',
            'criteria.order.field' => 'string',
            'records_per_page' => 'integer',
            'page_number' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List news failure');
        }

        $result = [];
        $criteria = $request->json('criteria');

        $newsList = Page::select('*');

        $orderColumns = [
            'id',
            'title',
            'abstract',
            'body',
            'head_title',
            'meta_desctript',
            'meta_key_words',
            'forum_link',
            'active',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'valid_from',
            'valid_to',
        ];

        if (isset($criteria['order'])) {
            if (is_array($criteria['order'])) {
                if (!in_array($criteria['order']['field'], $orderColumns)) {
                    unset($criteria['order']['field']);
                }
            }
        }

        if (isset($criteria['locale'])) {
            $locale = \LaravelLocalization::setLocale($criteria['locale']);
        } else {
            $locale = config('app.locale');
        }

        if (is_null($criteria)) {
            $newsList = $newsList;
        }

        $filterColumn = 'created_at';
        if (isset($criteria['date_type'])) {
            if (strtolower($criteria['date_type']) == Page::DATE_TYPE_UPDATED) {
                $filterColumn = 'updated_at';
            }
        }

        if (isset($criteria['date_from'])) {
            if (strtolower($criteria['date_type']) == Page::DATE_TYPE_VALID) {
                $filterColumn = 'valid_from';
            }
            $newsList = $newsList->where($filterColumn, '>=', $criteria['date_from']);

        }
        if (isset($criteria['date_to'])) {
            if (strtolower($criteria['date_type']) == Page::DATE_TYPE_VALID) {
                $filterColumn = 'valid_to';
            }
            $newsList = $newsList->where($filterColumn, '<=', $criteria['date_to']);
        }

        if (isset($criteria['active'])) {
            $newsList = $newsList->where('active', $criteria['active']);
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if ($criteria['order']['type'] == 'desc') {
                $newsList = $newsList->orderBy($criteria['order']['field'], 'desc');
            }

            if ($criteria['order']['type'] == 'asc') {
                $newsList = $newsList->orderBy($criteria['order']['field'], 'asc');
            }
        }

        if (isset($request['records_per_page']) && isset($request['page_number'])) {
            $newsList = $newsList->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        if (isset($criteria['valid'])) {
            if ($criteria['valid'] == 1) {
                $newsList = $newsList->where(function ($newsList) {
                    $newsList->where('valid_to', '>=', date(now()))
                        ->where('valid_from', '<=', date(now()))

                        ->orWhere('valid_from', null)
                        ->where('valid_to', '>=', date(now()))

                        ->orWhere('valid_to', null)
                        ->where('valid_from', '>=', date(now()));
                });
            }
            if ($criteria['valid'] == 0) {
                $newsList = $newsList->where(function ($newsList) {
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

        $newsList = $newsList->get();
        if (!empty($newsList)) {
            $total_records = $newsList->count();
            foreach ($newsList as $singleNews) {
                $result[] = [
                    'id' => $singleNews->id,
                    'locale' => $locale,
                    'title' => [$locale => $singleNews->title],
                    'abstract' => [$locale => $singleNews->abstract],
                    'body' => [$locale => $singleNews->body],
                    'head_title' => [$locale => $singleNews->head_title],
                    'meta_description' => [$locale => $singleNews->meta_desctript],
                    'meta_keywords' => [$locale => $singleNews->meta_key_words],
                    'forum_link' => $singleNews->forum_link,
                    'active' => $singleNews->active,
                    'created_at' => date($singleNews->created_at),
                    'updated_at' => date($singleNews->updated_at),
                    'created_by' => $singleNews->created_by,
                    'updated_by' => $singleNews->updated_by,
                    'valid_from' => date($singleNews->valid_from),
                    'valid_to' => date($singleNews->valid_to),
                ];
            }
        }
        return $this->successResponse([
            'total_records' => $total_records,
            'news' => $result,
        ], true);

    }
    /**
     * Search news articles based on input criteria
     *
     * @param Request $request
     * @return json response
     */
    public function searchNews(Request $request)
    {
        $newsSearchData = $request->all();
        $validator = Validator::make($newsSearchData, [
            'locale' => 'string',
            'criteria' => 'array',
            'criteria.keywords' => 'required|string',
            'criteria.order' => 'array',
            'criteria.order.type' => 'string',
            'criteria.order.field' => 'string',
            'records_per_page' => 'integer',
            'page_number' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Search news failure');
        }

        $result = [];
        $criteria = $request->json('criteria');
        $search = $criteria['keywords'];

        if (isset($criteria['locale'])) {
            $locale = \LaravelLocalization::setLocale($criteria['locale']);
        } else {
            $locale = config('app.locale');
        }

        $newsList = Page::select('*');

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if ($criteria['order']['type'] == 'desc') {
                $newsList = $newsList->orderBy($criteria['order']['field'], 'desc');
            }

            if ($criteria['order']['type'] == 'asc') {
                $newsList = $newsList->orderBy($criteria['order']['field'], 'asc');
            }
        }

        if (isset($request['records_per_page']) && isset($request['page_number'])) {
            $newsList = $newsList->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        $finalList = Page::search($search)->constrain($newsList);
        $newsList = $finalList->get();

        if (!empty($newsList)) {
            $total_records = $newsList->count();
            foreach ($newsList as $singleNews) {
                $result[] = [
                    'id' => $singleNews->id,
                    'locale' => $locale,
                    'title' => $singleNews->title,
                    'abstract' => $singleNews->abstract,
                    'body' => $singleNews->body,
                    'head_title' => $singleNews->head_title,
                    'meta_description' => $singleNews->meta_desctript,
                    'meta_keywords' => $singleNews->meta_key_words,
                    'forum_link' => $singleNews->forum_link,
                    'active' => $singleNews->active,
                    'created_at' => date($singleNews->created_at),
                    'updated_at' => date($singleNews->updated_at),
                    'created_by' => $singleNews->created_by,
                    'updated_by' => $singleNews->updated_by,
                    'valid_from' => date($singleNews->valid_from),
                    'valid_to' => date($singleNews->valid_to),
                ];
            }
        }
        
        return $this->successResponse([
            'total_records' => $total_records,
            'news' => $result,
        ], true);

    }
    /**
     * Get details on a specific news piece via news_id
     *
     * @param Request $request
     * @return json response
     */
    public function getNewsDetails(Request $request)
    {
        $newsSearchData = $request->all();
        $validator = Validator::make($newsSearchData, [
            'locale' => 'string',
            'news_id' => 'integer|required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Search news failure');
        }

        $singleNews = Page::find($newsSearchData['news_id']);

        if (!$singleNews) {
            return $this->errorResponse('Search news failure');
        }

        if (isset($newsSearchData['locale'])) {
            $locale = \LaravelLocalization::setLocale($newsSearchData['locale']);
        } else {
            $locale = config('app.locale');
        }

        if (!empty($singleNews)) {
            $result[] = [
                'id' => $singleNews->id,
                'locale' => $locale,
                'title' => $singleNews->title,
                'abstract' => $singleNews->abstract,
                'body' => $singleNews->body,
                'head_title' => $singleNews->head_title,
                'meta_description' => $singleNews->meta_desctript,
                'meta_keywords' => $singleNews->meta_key_words,
                'forum_link' => $singleNews->forum_link,
                'active' => $singleNews->active,
                'created_at' => date($singleNews->created_at),
                'updated_at' => date($singleNews->updated_at),
                'created_by' => $singleNews->created_by,
                'updated_by' => $singleNews->updated_by,
                'valid_from' => date($singleNews->valid_from),
                'valid_to' => date($singleNews->valid_to),
            ];
        }

        return $this->successResponse([
            'news' => $result,
        ], true);

    }
}
