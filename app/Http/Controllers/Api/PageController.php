<?php

namespace App\Http\Controllers\Api;

use \App\Page;
use \Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class PageController extends ApiController
{
    /**
     * Add a new page
     *
     * Requires a json $request
     *
     * @param array data - required
     * @param string data[locale] - required
     * @param integer data[section_id] - optional
     * @param string data[title] - required
     * @param string data[body] - optional
     * @param string data[head_title] - optional
     * @param string data[meta_description] - optional
     * @param string data[forum_link] - optional
     * @param integer data[active] - required
     *
     * @return json response with new page id on success and error on fail
     */
    public function addPage(Request $request)
    {
        $pageData = $request->all();

        $validator = Validator::make($pageData, [
            'data'      => 'required|array',
            'locale'    => 'required|string',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($pageData['data'], [
                'section_id'       => 'nullable|integer',
                'title'            => 'required|string|max:191',
                'body'             => 'required|string|max:8000',
                'head_title'       => 'nullable|string|max:191',
                'meta_description' => 'nullable|string|max:8000',
                'meta_keywords'    => 'nullable|string|max:191',
                'forum_link'       => 'nullable|string|max:191',
                'active'           => 'required|boolean',
                'valid_from'       => 'nullable|date',
                'valid_to'         => 'nullable|date',
            ]);
        }

        if (!$validator->fails()) {
            try {
                DB::beginTransaction();
                $locale = $pageData['locale'];

                $newPage = new Page;
                $newPage->title = $this->trans($locale, $pageData['data']['title']);

                if (isset($pageData['data']['section_id'])) {
                    $newPage->section_id = $pageData['data']['section_id'];
                }

                if (isset($pageData['data']['body'])) {
                    $newPage->body = $this->trans($locale, $pageData['data']['body']);
                }

                if (isset($pageData['data']['head_title'])) {
                    $newPage->head_title = $this->trans($locale, $pageData['data']['head_title']);
                }

                if (isset($pageData['data']['meta_description'])) {
                    $newPage->meta_descript = $this->trans($locale, $pageData['data']['meta_description']);
                }

                if (isset($pageData['data']['meta_keywords'])) {
                    $newPage->meta_key_words = $this->trans($locale, $pageData['data']['meta_keywords']);
                }

                if (isset($pageData['data']['forum_link'])) {
                    $newPage->forum_link = $pageData['data']['forum_link'];
                }

                $newPage->active = $pageData['data']['active'];

                $newPage->save();
                DB::commit();
                return $this->successResponse(['page_id' => $newPage->id]);

            } catch (QueryException $e) {
                DB::rollback();
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_page_fail'), $validator->errors()->messages());
    }

    /**
     * Edits a page based on the provided pageId and edit data
     *
     * @param integer page_id - required
     * @param array data - required
     * @param string data[locale] - required
     * @param integer data[section_id] - optional
     * @param string data[title] - required
     * @param string data[body] - optional
     * @param string data[head_title] - optional
     * @param string data[meta_description] - optional
     * @param string data[forum_link] - optional
     * @param integer data[active] - required
     *
     * @return json response with success or error
     */
    public function editPage(Request $request)
    {
        $editData = $request->all();

        if (sizeof($editData['data']) < 1) {
            return $this->errorResponse(__('custom.edit_page_fail'));
        }

        $validator = Validator::make($editData, [
            'page_id'    => 'required|integer|exists:pages,id|max:10',
            'data'       => 'required|array',
            'locale'     => 'required|string|max:5',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($editData['data'], [
                'section_id'       => 'nullable|integer|max:10',
                'title'            => 'nullable|string|max:191',
                'body'             => 'nullable|string|max:8000',
                'head_title'       => 'nullable|string|max:191',
                'meta_description' => 'nullable|string|max:8000',
                'meta_keywords'    => 'nullable|string|max:191',
                'forum_link'       => 'nullable|string|max:191',
                'active'           => 'required|boolean',
                'valid_from'       => 'nullable|date',
                'valid_to'         => 'nullable|date',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $pageToEdit = Page::find($editData['page_id']);

                DB::beginTransaction();

                $locale = $editData['locale'];

                if (isset($editData['data']['title'])) {
                    $pageToEdit->title = $this->trans($locale, $editData['data']['title']);
                }

                if (isset($editData['data']['section_id'])) {
                    $pageToEdit->section_id = $editData['data']['section_id'];
                }

                if (isset($editData['data']['body'])) {
                    $pageToEdit->body = $this->trans($locale, $editData['data']['body']);
                }

                if (isset($editData['data']['head_title'])) {
                    $pageToEdit->head_title = $this->trans($locale, $editData['data']['head_title']);
                }

                if (isset($editData['data']['meta_description'])) {
                    $pageToEdit->meta_descript = $this->trans($locale, $editData['data']['meta_description']);
                }

                if (isset($editData['data']['meta_keywords'])) {
                    $pageToEdit->meta_key_words = $this->trans($locale, $editData['data']['meta_keywords']);
                }

                if (isset($editData['data']['forum_link'])) {
                    $pageToEdit->forum_link = $editData['data']['forum_link'];
                }

                if (isset($editData['data']['active'])) {
                    $pageToEdit->active = $editData['data']['active'];
                }

                $pageToEdit->save();
                DB::commit();
                return $this->successResponse();
            } catch (QueryException $e) {
                DB::rollback();
                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_page_fail'), $validator->errors()->messages());
    }

    /**
     * Deletes a page based on id
     *
     * @param Request $request
     *
     * @param integer page_id - required
     *
     * @return json response with success or error
     */
    public function deletePage(Request $request)
    {
        $deleteData = $request->all();

        $validator = Validator::make($deleteData, [
            'page_id' => 'required|integer|exists:pages,id|max:10',
        ]);

        if (!$validator->fails()) {
            try {
                $pageToBeDeleted = Page::find($deleteData['page_id']);

                $pageToBeDeleted->delete();
                return $this->successResponse();
            } catch (QueryException $e) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.delete_page_fail'), $validator->errors()->messages());
    }

    /**
     * Lists pages based on request input
     *
     * @param Request $request
     *
     * @param array criteria - optional
     * @param string locale - optional
     * @param integer criteria[page_id] - optional
     * @param integer criteria[active] - optional
     * @param integer criteria[section_id] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with success or list of pages
     */
    public function listPages(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'criteria'                => 'nullable|array',
            'locale'                  => 'nullable|string|max:5',
            'records_per_page'        => 'nullable|integer|max:10',
            'page_number'             => 'nullable|integer|max:10',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'page_id'        => 'nullable|integer|max:10',
                'active'         => 'nullable|boolean',
                'section_id '    => 'nullable|integer|max:10',
                'order'          => 'nullable|array',
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'     => 'nullable|string|max:191',
                'field'    => 'nullable|string|max:191',
            ]);
        }

        if (!$validator->fails()) {
            $result = [];

            $locale = \LaravelLocalization::getCurrentLocale();

            $pageList = '';
            $columns = [
                'id',
                'section_id',
                'title',
                'body',
                'head_title',
                'meta_descript',
                'meta_key_words',
                'forum_link',
                'active',
                'created_at',
                'updated_at',
                'created_by',
                'updated_by',
            ];

            $pageList = Page::select($columns);

            if (isset($criteria['order'])) {
                if (is_array($criteria['order'])) {
                    if (!in_array($criteria['order']['field'], $columns)) {
                        unset($criteria['order']['field']);
                    }
                }
            }

            if (isset($criteria['page_id'])) {
                $pageList->where('id', $criteria['page_id']);
            }

            if (isset($criteria['active'])) {
                $pageList->where('active', $criteria['active']);
            }

            if (isset($criteria['section_id'])) {
                $pageList->where('section_id', $criteria['section_id']);
            }

            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $pageList->orderBy($criteria['order']['field'],
                    $criteria['order']['type'] == 'asc' ? 'asc' : 'desc');
            }

            $total_records = $pageList->count();

            if (isset($request['records_per_page']) || isset($request['page_number'])) {
                $pageList->forPage($request->input('page_number'), $request->input('records_per_page'));
            }

            $pageList = $pageList->get();

            if (!empty($pageList)) {

                foreach ($pageList as $singlePage) {
                    $result[] = [
                        'id'                  => $singlePage->id,
                        'locale'              => $locale,
                        'section_id'          => $singlePage->section_id,
                        'title'               => $singlePage->title,
                        'body'                => $singlePage->body,
                        'head_title'          => $singlePage->head_title,
                        'meta_description'    => $singlePage->meta_descript,
                        'meta_keywords'       => $singlePage->meta_key_words,
                        'forum_link'          => $singlePage->forum_link,
                        'active'              => $singlePage->active,
                        'created_at'          => date($singlePage->created_at),
                        'updated_at'          => date($singlePage->updated_at),
                        'created_by'          => $singlePage->created_by,
                        'updated_by'          => $singlePage->updated_by,
                    ];
                }
            }

            return $this->successResponse([
                'total_records' => $total_records,
                'pages'         => $result,
            ], true);
        }

        return $this->errorResponse(__('custom.list_pages_fail'), $validator->errors()->messages());
    }
}
