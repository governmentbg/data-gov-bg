<?php

namespace App\Http\Controllers\Api;

use App\Document;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use \Validator;

class DocumentController extends ApiController
{
    /**
     * Add a document with provided data
     *
     * @param Request $request
     * @return json response
     */
    public function addDocument(Request $request)
    {
        $documentData = $request->all();
        $validator = Validator::make($documentData, [
            'data' => 'required|array',
            'data.name' => 'required|string',
            'data.description' => 'required|string',
            'data.locale' => 'required|string',
            'data.filename' => 'required|string',
            'data.mimetype' => 'required|string',
            'data.data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Add document failure');
        }

        $newDocument = new Document;
        $newDocument->name = [$documentData['data']['locale'] => $documentData['data']['name']];
        $newDocument->descript = [$documentData['data']['locale'] => $documentData['data']['description']];
        $newDocument->file_name = $documentData['data']['filename'];
        $newDocument->mime_type = $documentData['data']['mimetype'];
        $newDocument->data = $documentData['data']['data'];

        try {
            $newDocument->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Add document failure');
        }
        return $this->successResponse(['doc_id :' . $newDocument->id]);
    }

    /**
     * Edit document with provided data
     *
     * @param Request $request
     * @return json response
     */
    public function editDocument(Request $request)
    {
        $documentEditData = $request->all();

        $validator = Validator::make($documentEditData, [
            'doc_id' => 'required|integer',
            'data' => 'required|array',
            'data.name' => 'string',
            'data.description' => 'string',
            'data.locale' => 'string',
            'data.filename' => 'string',
            'data.mimetype' => 'string',
            'data.data' => 'string',
        ]);

        if (isset($documentEditData['data']['name'])) {
            $validator->sometimes('data.locale', 'required', function ($documentEditData) {
                return $documentEditData['data']['name'] != '';
            });
        }
        if (isset($documentEditData['data']['description'])) {
            $validator->sometimes('data.locale', 'required', function ($documentEditData) {
                return $documentEditData['data']['description'] != '';
            });
        }

        if ($validator->fails()) {
            return $this->errorResponse('Edit document failure');
        }

        $editDocument = Document::find($documentEditData['doc_id']);

        if (!$editDocument) {
            return $this->errorResponse('Edit document failure');
        }
        if (isset($documentEditData['data']['name'])) {
            $editDocument->name = [$documentEditData['data']['locale'] => $documentEditData['data']['name']];
        }

        if (isset($documentEditData['data']['description'])) {
            $editDocument->descript = [$documentEditData['data']['locale'] => $documentEditData['data']['description']];
        }

        if (isset($documentEditData['data']['filename'])) {
            $editDocument->file_name = $documentEditData['data']['filename'];
        }

        if (isset($documentEditData['data']['mimetype'])) {
            $editDocument->mime_type = $documentEditData['data']['mimetype'];
        }

        if (isset($documentEditData['data']['data'])) {
            $editDocument->data = $documentEditData['data']['data'];
        }

        try {
            $editDocument->save();
        } catch (QueryException $e) {
            return $this->errorResponse('Edit document failure');
        }
        return $this->successResponse();

    }

    /**
     * Delete a document based on ID
     *
     * @param Request $request
     * @return json response
     */
    public function deleteDocument(Request $request)
    {
        $documentDeleteData = $request->all();

        $validator = Validator::make($documentDeleteData, [
            'doc_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Delete document failure');
        }
        $deleteDocument = Document::find($documentDeleteData['doc_id']);

        if (!$deleteDocument) {
            return $this->errorResponse('Delete document failure');
        }

        try {
            $deleteDocument->delete();
        } catch (QueryException $e) {
            return $this->errorResponse('Delete document failure');
        }
        return $this->successResponse();

    }

    /**
     * List documents based on search criteria
     *
     * @param Request $request
     * @return json response
     */
    public function listDocuments(Request $request)
    {
        $documentListData = $request->all();

        $validator = Validator::make($documentListData, [
            'criteria' => 'array',
            'criteria.doc_id' => 'integer',
            'criteria.date_from' => 'date',
            'criteria.date_to' => 'date',
            'criteria.locale' => 'string',
            'criteria.date_type' => 'string',
            'criteria.order' => 'array',
            'criteria.order.type' => 'string',
            'criteria.order.field' => 'string',
            'records_per_page' => 'integer',
            'page_number' => 'integer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('List document failure');
        }

        $result = [];
        $criteria = $request->json('criteria');

        $documentList = Document::select(
            'id',
            'name',
            'descript',
            'file_name',
            'mime_type',
            'data',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by'
        );

        $orderColumns = [
            'id',
            'name',
            'descript',
            'file_name',
            'mime_type',
            'data',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
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
            $documentList = $documentList;
        }

        if (isset($criteria['doc_id'])) {
            $documentList = $documentList->where('id', $criteria['doc_id']);
        }

        $filterColumn = 'created_at';

        if (isset($criteria['date_type'])) {
            if (strtolower($criteria['date_type']) == Document::DATE_TYPE_UPDATED) {
                $filterColumn = 'updated_at';
            }
        }

        if (isset($criteria['date_from'])) {
            $documentList = $documentList->where($filterColumn, '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to'])) {
            $documentList = $documentList->where($filterColumn, '<=', $criteria['date_to']);
        }

        if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
            if ($criteria['order']['type'] == 'desc') {
                $documentList = $documentList->orderBy($criteria['order']['field'], 'desc');
            }
        } else {
            if (isset($criteria['order']['type']) && isset($criteria['order']['field'])) {
                $documentList = $documentList->orderBy($criteria['order']['field'], 'asc');
            }
        }

        if (isset($request['records_per_page']) || isset($request['page_number'])) {
            $documentList = $documentList->forPage($request['page_number'], $request['records_per_page']);
        }

        $documentList = $documentList->get();

        if (!empty($documentList)) {
            $total_records = $documentList->count();
            foreach ($documentList as $singleDocument) {
                $result[] = [
                    'id' => $singleDocument->id,
                    'locale' => $locale, //needs to be revised. When BG is supplied still returns EN translations!!!
                    'name' => [$locale => $singleDocument->name],
                    'description' => [$locale => $singleDocument->desript],
                    'filename' => $singleDocument->file_name,
                    'mimetype' => $singleDocument->mime_type,
                    'data' => $singleDocument->data,
                    'created_at' => date($singleDocument->created_at),
                    'updated_at' => date($singleDocument->updated_at),
                    'created_by' => $singleDocument->created_by,
                    'updated_by' => $singleDocument->updated_by,
                ];
            }
        }

        return $this->successResponse([
            'total_records' => $total_records,
            'documents' => $result,
        ], true);

    }

    /**
     * Get a list of documents based on criteria
     *
     * @param Request $request
     * @return json response
     * TODO does not display translations of NAME AND DESCRIPTION !!!
     */
    public function searchDocuments(Request $request)
    {
        $post = $request->all();
        $criteria = isset($post['criteria']) ? $post['criteria'] : false;
        
        if (!empty($criteria)) {
            $validator = Validator::make($post, [
                'criteria' => 'array',
                'criteria.search' => 'string',
                'criteria.order.type' => 'string',
                'criteria.order.field' => 'string',
                'records_per_page' => 'integer',
                'page_number' => 'integer',
            ]);

            if (!$validator->fails()) {
                $data = [];
                $order = [];
                $order['type'] = !empty($criteria['order']['type']) ? $criteria['order']['type'] : 'asc';
                $order['field'] = !empty($criteria['order']['field']) ? $criteria['order']['field'] : 'id';
                $pagination = !empty($post['records_per_page']) ? $post['records_per_page'] : null;
                $page = !empty($post['page_number']) ? $post['page_number'] : null;
                $search = !empty($criteria['search']) ? $criteria['search'] : null;

                $orderColumns = [
                    'id',
                    'name',
                    'descript',
                    'file_name',
                    'mime_type',
                    'data',
                    'created_at',
                    'updated_at',
                    'created_by',
                    'updated_by',
                ];

                if (isset($order['field'])) {
                    if (!in_array($order['field'], $orderColumns)) {
                        unset($order['field']);
                    }
                }

                try {
                    $query = Document::select('*');
                    if (isset($order['field']) && isset($order['type'])) {
                        $query = $query->orderBy($order['field'], $order['type']);
                    }
                    if ($pagination && $page) {
                        $query = $query->forPage($pagination, $page);
                    }
                    if ($search) {
                        $data = Document::search($search)->constrain($query)->get();
                    } else {
                        $data = $query->get();
                    }
                    //TODO does not display translations of NAME AND DESCRIPTION !!!
                    foreach ($data as $set) {
                        $set['name'] = $set->name;
                        $set['descript'] = $set->descript;

                    }
                    return $this->successResponse([
                        'documents' => $data,
                        'total_records' => $data->count(),
                    ], true);
                } catch (QueryException $ex) {
                    return $this->errorResponse($ex->getMessage());
                }
            }
        }
        return $this->errorResponse('Search document failure');
    }
}
