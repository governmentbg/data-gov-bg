<?php

namespace App\Http\Controllers;

use App\Image;
use App\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\DocumentController as ApiDocuments;

class DocumentController extends Controller {
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function viewDocument(Request $request, $id)
    {
        $params['doc_id'] = $id;
        $docRequest = Request::create('/api/listDocuments', 'POST', ['criteria' => $params]);
        $apiDocuments = new ApiDocuments($docRequest);
        $docList = $apiDocuments->listDocuments($docRequest)->getData();
        $document = !empty($docList->documents[0]) ? $docList->documents[0] : null;

        if (!is_null($document)) {
            $discussion = $this->getForumDiscussion($document->forum_link);

            $viewParams =  [
                'class'          => 'documents',
                'document'       => $document,
            ];

            return view (
                'document/view',
                !empty($discussion)
                    ? array_merge($viewParams, $discussion)
                    : $viewParams
            );
        }
    }

    public function downloadDocument(Request $request, $id, $fileName)
    {
        $doc = Document::find(base64_decode($id));

        if (!empty('doc')) {
            return response($doc->data, 200, [
                'Content-Type'          => $doc->mime_type,
                'Content-Disposition'   => 'attachment; filename="'. $fileName .'"',
            ]);
        }

        return response()->json(['message' => 'Not Found.'], 404);
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
            return redirect('document');
        }

        $params = [
            'records_per_page'  => $perPage,
            'criteria'          => [
                'keywords' => $search,
            ]
        ];

        $searchRq = Request::create('/api/listDocuments', 'POST', $params);
        $api = new ApiDocuments($searchRq);
        $docData = $api->listDocuments($searchRq)->getData();

        $documents = !empty($docData->documents) ? $docData->documents : [];
        $count = !empty($docData->total_records) ? $docData->total_records : 0;

        $getParams = [
            'q' => $search
        ];

        $paginationData = $this->getPaginationData($documents, $count, $getParams, $perPage);

        return view('document/list', [
            'class'          => 'documents',
            'documents'      => $paginationData['items'],
            'pagination'     => $paginationData['paginate'],
            'search'         => $search,
        ]);
    }

    /**
     * Displays an image
     *
     * @param Request $request
     * @param integer $id
     *
     * @return image on success
     */
    public function viewImage(Request $request, $id)
    {
        $image = Image::find($id);

        if (!empty($image) && $image->active) {
            try {
                $image = \Image::make($image->data);

                if ($request->segment(2) == Image::TYPE_THUMBNAIL) {
                    $image->resize(160, 108);
                }

                return $image->response();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return __('custom.non_existing_image');
    }
}
