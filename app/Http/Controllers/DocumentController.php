<?php

namespace App\Http\Controllers;

use App\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DocumentController as ApiDocuments;

class DocumentController extends Controller {
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
            $discussion = $this->getForumDiscussion($documents->forum_link);

            $viewParams =  [
                'class'          => 'documents',
                'document'       => $documents,
            ];

            return view (
                'document/view',
                !empty($discussion)
                    ? array_merge($viewParams, $discussion)
                    : $viewParams
            );
        }
    }

    public function downloadDocument(Request $request, $path, $fileName)
    {
        return response()->download(base64_decode($path), $fileName);
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
        $isActive = Image::where('id', $id)->value('active');

        if ($isActive) {
            try {
                $image = \Image::make(storage_path('images') .'/'. $id);

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
