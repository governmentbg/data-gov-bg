<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\DocumentController as ApiDocuments;

class DocumentController extends Controller {

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

    public function viewDocument(Request $request, $id)
    {
        $params['doc_id'] = $id;
        $docRequest = Request::create('/api/listDocuments', 'POST', ['criteria' => $params]);
        $apiDocuments = new ApiDocuments($docRequest);
        $docList = $apiDocuments->listDocuments($docRequest)->getData();
        $documents = !empty($docList->documents[0]) ? $docList->documents[0] : null;

        if (!is_null($documents)) {
            if ($request->has('download')) {
                return response(utf8_decode($documents->data))
                    ->header('Cache-Control', 'no-cache private')
                    ->header('Content-Description', 'File Transfer')
                    ->header('Content-Type', $documents->mimetype)
                    ->header('Content-length', strlen(utf8_decode($documents->data)))
                    ->header('Content-Disposition', 'attachment; filename='. $documents->filename)
                    ->header('Content-Transfer-Encoding', 'binary');
            }

            return view(
                'document/view',
                [
                    'class'    => 'documents',
                    'document' => $documents
                ]
            );
        }
    }

    public function listDocuments(Request $request)
    {
        $perPage = 6;
        $pageNumber = !empty($request->page) ? $request->page : 1;

        $params = [
            'records_per_page' => $perPage,
            'page_number'      => $pageNumber
        ];

        $docRequest = Request::create('/api/listDocuments', 'POST', $params);
        $apiDocuments = new ApiDocuments($docRequest);
        $docList = $apiDocuments->listDocuments($docRequest)->getData();

        $paginationData = $this->getPaginationData(
            $docList->documents,
            $docList->total_records,
            [],
            $perPage
        );

        return view('document/list',
        [
            'class'            => 'documents',
            'documents'        => $paginationData['items'],
            'pagination'       => $paginationData['paginate'],
        ]);
    }

    public function searchDocuments(Request $request)
    {
        $perPage = 6;
        $search = $request->offsetGet('q');

        if (empty($search)) {
            return redirect('document/list');
        }

        $params = [
            'records_per_page'  => $perPage,
            'criteria'          => [
                'search' => $search,
            ]
        ];

        $searchRq = Request::create('/api/searchDocuments', 'POST', $params);
        $api = new ApiDocuments($searchRq);
        $docData = $api->searchDocuments($searchRq)->getData();

        $documents = !empty($docData->documents) ? $docData->documents : [];
        $count = !empty($docData->total_records) ? $docData->total_records : 0;

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($documents, $count, $getParams, $perPage);

        return view('document/list', [
            'class'         => 'documents',
            'documents'     => $paginationData['items'],
            'pagination'    => $paginationData['paginate'],
            'search'        => $search,
        ]);
    }
}
