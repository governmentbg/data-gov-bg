<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
//use App\Translator\LaravelLocalization;
use \App\Page;
use \App\User;
use \Validator;

//needs to be revised. When BG is supplied still returns EN translations!!! row 280
class PageController extends ApiController
{
    /**
     * Adds a new page based on the provided data
     *
     * @param Request $request
     * @return json response
     */
    public function addPage(Request $request)
    {
        $pageData = $request->all();

        $validator = Validator::make($pageData, [
            'data' => 'required|array',
            'data.locale' => 'required|string',
            'data.section_id' => 'nullable|integer',
            'data.title' => 'required|string',
            'data.body' => 'nullable|string',
            'data.head_title' => 'nullable|string',
            'data.meta_description' => 'nullable|string',
            'data.meta_keywords' => 'nullable|string',
            'data.forum_link' => 'nullable|string',
            'data.active' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Add page failure');
        }

        $locale = $pageData['data']['locale'];

        $newPage = new Page;

        $newPage->title = [$locale => $pageData['data']['title']];

        if (isset($pageData['data']['section_id'])) {
            $newPage->section_id = $pageData['data']['section_id'];
        }

        if (isset($pageData['data']['abstract'])) {
            $newPage->abstract = [$locale => $pageData['data']['abstract']];
        }

        if (isset($pageData['data']['body'])) {
            $newPage->body = [$locale => $pageData['data']['body']];
        }

        if (isset($pageData['data']['head_title'])) {
            $newPage->head_title = [$locale => $pageData['data']['head_title']];
        }

        if (isset($pageData['data']['meta_desctript'])) {
            $newPage->meta_desctript = [$locale => $pageData['data']['meta_desctript']];
        }

        if (isset($pageData['data']['meta_key_words'])) {
            $newPage->meta_key_words = [$locale => $pageData['data']['meta_key_words']];
        }

        if (isset($pageData['data']['forum_link'])) {
            $newPage->forum_link = $pageData['data']['forum_link'];
        }

        $newPage->active = $pageData['data']['active'];

        try {
            $newPage->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Page add failure');
        }

        return $this->successResponse(['page_id :' . $newPage->id]);
    }

    /**
     * Edits a page based on the provided pageId and edit data
     *
     * @param Request $request
     * @return json response
     */
    public function editPage(Request $request)
    {
        $editData = $request->all();

        if (sizeof($editData['data']) < 1) {
            return $this->errorResponse('Edit failure');
        }

        $validator = Validator::make($editData, [
            'page_id' => 'required|integer',
            'data' => 'required|array',
            'data.locale' => 'required|string',
            'data.section_id' => 'nullable|integer',
            'data.title' => 'nullable|string',
            'data.body' => 'nullable|string',
            'data.head_title' => 'nullable|string',
            'data.meta_description' => 'nullable|string',
            'data.meta_keywords' => 'nullable|string',
            'data.forum_link' => 'nullable|string',
            'data.active' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Edit page failure');
        }

        $pageToEdit = Page::find($editData['page_id']);

        if ($pageToEdit) {
            $locale = $editData['data']['locale'];

            if (isset($editData['data']['title'])) {
                $pageToEdit->title = [$locale => $editData['data']['title']];
            }
            if (isset($editData['data']['section_id'])) {
                $pageToEdit->section_id = $editData['data']['section_id'];
            }

            if (isset($editData['data']['abstract'])) {
                $pageToEdit->abstract = [$locale => $editData['data']['abstract']];
            }

            if (isset($editData['data']['body'])) {
                $pageToEdit->body = [$locale => $editData['data']['body']];
            }

            if (isset($editData['data']['head_title'])) {
                $pageToEdit->head_title = [$locale => $editData['data']['head_title']];
            }

            if (isset($editData['data']['meta_desctript'])) {
                $pageToEdit->meta_desctript = [$locale => $editData['data']['meta_desctript']];
            }

            if (isset($editData['data']['meta_key_words'])) {
                $pageToEdit->meta_key_words = [$locale => $editData['data']['meta_key_words']];
            }

            if (isset($editData['data']['forum_link'])) {
                $pageToEdit->forum_link = $editData['data']['forum_link'];
            }

            if (isset($editData['data']['active'])) {
                $pageToEdit->active = $editData['data']['active'];
            }

            $pageToEdit->updated_by = \Auth::user()->id;

            try {
                $pageToEdit->save();
            } catch (QueryException $e) {
                return $this->errorResponse('Page edit failure');
            }
        } else {
            return $this->errorResponse('Page edit failure');
        }
        return $this->successResponse();
    }

    public function deletePage(Request $request)
    {
        $deleteData = $request->all();

        $validator = Validator::make($deleteData, [
            'page_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete page failure');
        }

        $pageToBeDeleted = Page::find($deleteData['page_id']);
        if ($pageToBeDeleted) {
            try {
                $pageToBeDeleted->delete();
            } catch (QueryException $e) {
                return $this->errorResponse('Delete page failure');
            }
        } else {
            return $this->errorResponse('Delete page failure');
        }
        return $this->successResponse();
    }
    /**
     * Lists pages based on request input
     *
     * @param Request $request
     * @return json response
     */
    public function listPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'criteria' => 'array',
            'criteria.page_id' => 'integer',
            'criteria.locale' => 'string',
            'criteria.active' => 'integer',
            'criteria.section_id ' => 'integer',
            'criteria.order' => 'array',
            'criteria.order.type' => 'string',
            'criteria.order.field' => 'string',
            'records_per_page' => 'integer',
            'page_number' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List pages failure');
        }

        $result = [];
        $criteria = $request->json('criteria');

        if (isset($criteria['locale'])) {
            $locale = \LaravelLocalization::setLocale($criteria['locale']);
        } else {
            $locale = config('app.locale');
        }
        $pageList = '';
        $pageList = Page::select(
            'id',
            'section_id',
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
            'updated_by');
             
        if (is_null($criteria)) {
            $pageList = $pageList;
        }

        if (isset($criteria['page_id'])) {
            $pageList = $pageList->where('id', $criteria['page_id']);
        }

        if (isset($criteria['active'])) {
            $pageList = $pageList->where('active', $criteria['active']);
        }

        if (isset($criteria['section_id'])) {
            $pageList = $pageList->where('section_id', $criteria['section_id']);
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if ($criteria['order']['type'] == 'desc') {
                $pageList = $pageList->orderBy($criteria['order']['field'], 'desc');
            }
        } else {
            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $pageList = $pageList->orderBy($criteria['order']['field'], 'asc');
            }
        }

        if (isset($request['records_per_page']) || isset($request['page_number'])) {
            $pageList = $pageList->forPage($request->input('page_number'), $request->input('records_per_page'));
        }

        $pageList = $pageList->get();

        if (!empty($pageList)) {
            $total_records = $pageList->count();

            foreach ($pageList as $singlePage) {
                $result[] = [
                    'id' => $singlePage->id,
                    'locale' => $locale, //needs to be revised. When BG is supplied still returns EN translations!!!
                    'section_id' => $singlePage->section_id,
                    'title' => [$locale => $singlePage->title],
                    'abstract' => [$locale => $singlePage->abstract],
                    'body' => [$locale => $singlePage->body],
                    'head_title' => [$locale => $singlePage->head_title],
                    'meta_description' => [$locale => $singlePage->meta_description],
                    'meta_keywords' => [$locale => $singlePage->meta_keywords],
                    'forum_link' => $singlePage->forum_link,
                    'active' => $singlePage->active,
                    'created_at' => date($singlePage->created_at),
                    'updated_at' => date($singlePage->updated_at),
                    'created_by' => $singlePage->created_by,
                    'updated_by' => $singlePage->updated_by,
                ];
            }

        }
        return $this->successResponse([
            'total_records' => $total_records,
            'pages' => $result
        ], true);

    }
}
