<?php

namespace App\Http\Controllers\Api;

use \Validator;
use App\Document;
use App\Module;
use App\RoleRight;
use App\ActionsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class DocumentController extends ApiController
{
    private $path;

    public function __construct()
    {
        $this->path = storage_path('docs') .'/';

        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
    }

    /**
     * Add a document with provided data
     *
     * @param array data - required
     * @param string|array data[name] - required
     * @param string|array data[description] - required
     * @param string data[locale] - optional
     * @param string data[filename] - required
     * @param string data[mimetype] - required
     * @param string data[data] - required
     *
     * @return json response with doc id or error message
     */
    public function addDocument(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'data'  => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($post['data'], [
                'name'           => 'required_with:locale|max:191',
                'name.bg'        => 'required_without:locale|string|max:191',
                'name.*'         => 'max:191',
                'description'    => 'required_with:locale|max:8000',
                'description.bg' => 'required_without:locale|string|max:8000',
                'locale'         => 'nullable|string|max:5',
                'filename'       => 'required|string|max:191',
                'mimetype'       => 'required|string|max:191',
                'data'           => 'required|string|max:4294967295',
                'forum_link'     => 'nullable|string|max:191'
            ]);
        }

        $validator->after(function ($validator) {
            if ($validator->errors()->has('filename')) {
                $validator->errors()->add('document', $validator->errors()->first('filename'));
            }

            if ($validator->errors()->has('data')) {
                $validator->errors()->add('document', $validator->errors()->first('data'));
            }
        });

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::DOCUMENTS,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                DB::beginTransaction();

                $doc = new Document;
                $doc->name = $this->trans($post['data']['locale'], $post['data']['name']);
                $doc->descript = $this->trans($post['data']['locale'], $post['data']['description']);
                $doc->file_name = $post['data']['filename'];
                $doc->mime_type = $post['data']['mimetype'];

                $doc->forum_link = isset($post['data']['forum_link']) ? $post['data']['forum_link'] : null;
                $doc->save();

                if ($this->checkFileSize($post['data']['data'])) {
                    $doc->save();

                    file_put_contents($this->path . $doc->id, $post['data']['data']);

                    DB::commit();

                    $logData = [
                        'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                        'action'           => ActionsHistory::TYPE_ADD,
                        'action_object'    => $doc->id,
                        'action_msg'       => 'Added new document',
                    ];

                    Module::add($logData);

                    return $this->successResponse(['doc_id' => $doc->id]);
                }

                $validator->errors()->add('logo', $this->getFileSizeError());
            } catch (\Exception $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_document_fail'), $validator->errors()->messages());
    }

    /**
     * Append provided data to a document
     *
     * @param array data - required
     * @param int data[doc_id] - required
     * @param string data[data] - required
     *
     * @return json response with doc id or error message
     */
    public function appendDocumentData(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'doc_id'    => 'required|int|exists:documents,id',
            'data'      => 'required|string',
        ]);

        if (!$validator->fails()) {
            try {
                file_put_contents($this->path . $data['doc_id'], $data['data'], FILE_APPEND);

                return $this->successResponse();
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.append_document_fail'), $validator->errors()->messages());
    }

    /**
     * Edit document with provided data
     *
     * @param int doc_id - required
     * @param array data - required
     * @param string|array data[name] - optional
     * @param string|array data[description] - optional
     * @param string data[locale] - optional
     * @param string data[filename] - optional
     * @param string data[mimetype] - optional
     * @param string data[data] - optional
     *
     * @return json response with success or error message
     */
    public function editDocument(Request $request)
    {
        $post = $request->all();
        $errors = [];

        $validator = Validator::make($post, [
            'doc_id'    => 'required|integer|exists:documents,id|digits_between:1,10',
            'data'      => 'required|array',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->messages();
        } else {
            $data = $post['data'];

            $validator = \Validator::make($data, [
                'name'         => 'nullable',
                'description'  => 'nullable',
                'locale'       => 'nullable|string|max:5',
                'filename'     => 'nullable|string',
                'mimetype'     => 'nullable|string',
                'data'         => 'nullable|string',
            ]);
        }

        if (!$validator->fails()) {
            $validator = Validator::make($post['data'], [
                'name'           => 'required_with:locale|max:191',
                'name.bg'        => 'required_without:locale|string|max:191',
                'name.*'         => 'max:191',
                'description'    => 'required_with:locale|max:8000',
                'description.bg' => 'required_without:locale|string|max:8000',
                'locale'         => 'nullable|string|max:5',
                'filename'       => 'nullable|string|max:191',
                'mimetype'       => 'nullable|string|max:191',
                'data'           => 'nullable|string|max:4294967295',
                'forum_link'     => 'nullable|string|max:191'
            ]);
        }

        $validator->after(function ($validator) {
            if ($validator->errors()->has('description.bg')) {
                $validator->errors()->add('descript.bg', $validator->errors()->first('description.bg'));
            }

            if ($validator->errors()->has('description')) {
                $validator->errors()->add('descript', $validator->errors()->first('description'));
            }

            if ($validator->errors()->has('filename')) {
                $validator->errors()->add('document', $validator->errors()->first('filename'));
            }

            if ($validator->errors()->has('data')) {
                $validator->errors()->add('document', $validator->errors()->first('data'));
            }
        });

        if (!$validator->fails()) {
            try {
                $doc = Document::find($post['doc_id']);

                $rightCheck = RoleRight::checkUserRight(
                    Module::DOCUMENTS,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $doc->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                DB::beginTransaction();

                if (isset($data['name'])) {
                    $doc->name = $this->trans($data['locale'], $data['name'], true);
                }

                if (isset($data['description'])) {
                    $doc->descript = $this->trans($data['locale'], $data['description'], true);
                }

                if (isset($data['filename'])) {
                    $doc->file_name = $data['filename'];
                }

                if (isset($data['mimetype'])) {
                    $doc->mime_type = $data['mimetype'];
                }

                if (isset($data['forum_link'])) {
                    $doc->mime_type = $data['forum_link'];
                }

                if (isset($data['data'])) {
                    file_put_contents($this->path . $doc->id, $data['data']);
                }

                $doc->save();

                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $doc->id,
                    'action_msg'       => 'Edited a document',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (\Exception $e) {
                DB::rollback();

                Log::error($e->getMessage());
            }
        } else {
            $errors = $validator->errors()->messages();
        }

        return $this->errorResponse(__('custom.edit_document_fail'), $errors);
    }

    /**
     * Delete a document based on ID
     *
     * @param int doc_id - required
     *
     * @return json response with success or error message
     */
    public function deleteDocument(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'doc_id' => 'required|integer|exists:documents,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $deleteDocument = Document::find($post['doc_id']);
            $rightCheck = RoleRight::checkUserRight(
                Module::DOCUMENTS,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $deleteDocument->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                $deleteDocument->delete();

                unlink($this->path . $post['doc_id']);

                $logData = [
                    'module_name'      => Module::getModuleName(Module::DOCUMENTS),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $post['doc_id'],
                    'action_msg'       => 'Deleted document',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (\Exception $ex) {
                Log::error($ex->getMessage());
            }
            return $this->errorResponse(__('custom.delete_document_fail'));
        }

        return $this->errorResponse(__('custom.delete_document_fail'), $validator->errors()->messages());
    }

    /**
     * List documents based on search criteria
     *
     * @param array criteria - optional
     * @param integer criteria[doc_id] - optional
     * @param date criteria[date_from] - optional
     * @param date criteria[date_to] - optional
     * @param string criteria[locale] - optional
     * @param string criteria[date_type] - optional
     * @param array criteria[order] - optional
     * @param string criteria[order][type] - optional
     * @param string criteria[order][field] - optional
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with doc list or error message
     */
    public function listDocuments(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'criteria'          => 'nullable|array',
            'records_per_page'  => 'nullable|integer|digits_between:1,10',
            'page_number'       => 'nullable|integer|digits_between:1,10',
        ]);

        $criteria = isset($post['criteria']) ? $post['criteria'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($criteria, [
                'doc_id'       => 'nullable|integer|digits_between:1,10',
                'keywords'     => 'nullable|string',
                'date_from'    => 'nullable|date',
                'date_to'      => 'nullable|date',
                'locale'       => 'nullable|string|max:5',
                'date_type'    => 'nullable|string|max:191',
                'order'        => 'nullable|array',
                'forum_link'   => 'nullable|string|max:191'
            ]);
        }

        $order = isset($criteria['order']) ? $criteria['order'] : [];

        if (!$validator->fails()) {
            $validator = Validator::make($order, [
                'type'   => 'nullable|string|max:191',
                'field'  => 'nullable|string|max:191',
            ]);
        }

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_document_fail'), $validator->errors()->messages());
        }

        $locale = \LaravelLocalization::getCurrentLocale();

        $columns = [
            'id',
            'name',
            'descript',
            'file_name',
            'mime_type',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        $query = Document::select($columns);

        if (isset($criteria['keywords'])) {
            $ids = Document::search($criteria['keywords'])->get()->pluck('id');
            $query = Document::whereIn('id', $ids);
        }

        if (isset($criteria['order']['field'])) {
            if (!in_array($criteria['order']['field'], $columns)) {
                return $this->errorResponse(__('custom.invalid_sort_field'));
            }
        }

        if (isset($criteria['doc_id'])) {
            $query->where('id', $criteria['doc_id']);
        }

        $filterColumn = 'created_at';

        if (isset($criteria['date_type'])) {
            if (strtolower($criteria['date_type']) == Document::DATE_TYPE_UPDATED) {
                $filterColumn = 'updated_at';
            }
        }

        if (isset($criteria['date_from'])) {
            $query->where($filterColumn, '>=', $criteria['date_from']);
        }

        if (isset($criteria['forum_link'])) {
            $query->where('forum_link', $criteria['forum_link']);
        }

        if (isset($criteria['date_to'])) {
            $query->where($filterColumn, '<=', $criteria['date_to']);
        }

        $count = $query->count();

        $transFields = ['descript', 'name'];

        $transCols = Document::getTransFields();

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if (in_array($criteria['order']['field'], $transFields)) {
                $col = $transCols[$criteria['order']['field']];
                $query->select('translations.label', 'translations.group_id', 'translations.text', 'documents.*')
                    ->leftJoin('translations', 'translations.group_id', '=', 'documents.' . $criteria['order']['field'])
                    ->where('translations.locale', $locale)
                    ->orderBy('translations.' . $col, $criteria['order']['type']);
            } else {
                $query->orderBy($criteria['order']['field'], $criteria['order']['type']);
            }
        }

        $query->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $results = [];

        foreach ($query->get() as $result) {
            $path = base64_encode($this->path . $result->id);

            $itemData = [
                'id'            => $result->id,
                'locale'        => $locale,
                'name'          => $result->name,
                'description'   => $result->descript,
                'filename'      => $result->file_name,
                'mimetype'      => $result->mime_type,
                'data'          => 'document/download/'. $path .'/'. $result->file_name,
                'forum_link'    => $result->forum_link,
                'created_at'    => isset($result->created_at) ? $result->created_at->toDateTimeString() : null,
                'updated_at'    => isset($result->updated_at) ? $result->updated_at->toDateTimeString() : null,
                'created_by'    => $result->created_by,
                'updated_by'    => $result->updated_by,
            ];

            $results[] = $itemData;
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::DOCUMENTS),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed documents',
        ];

        Module::add($logData);

        return $this->successResponse(
            [
                'total_records' => $count,
                'documents'     => $results
            ],
            true
        );
    }
}
