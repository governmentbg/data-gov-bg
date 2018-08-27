<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\DocumentController as ApiDocument;

class DocumentController extends AdminController
{
     /**
     * Function for getting an array of translatable fields
     *
     * @return array of fields
     */
    public static function getTransFields()
    {
        return [
            [
                'label'    => 'custom.name',
                'name'     => 'name',
                'type'     => 'text',
                'view'     => 'translation',
                'required' => true,
            ],
            [
                'label'    => 'custom.description',
                'name'     => 'descript',
                'type'     => 'text',
                'view'     => 'translation_txt',
                'required' => true,
            ],
        ];
    }

    /**
     * Lists documents
     *
     * @param Request $request
     *
     * @return view with list of documents
     */
    public function list(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $params = [
                'api_key'          => \Auth::user()->api_key,
                'records_per_page' => $perPage,
                'page_number'      => !empty($request->page) ? $request->page : 1,
            ];

            if (isset($request->from)) {
                $params['criteria']['date_from'] = date_format(date_create($request->from), 'Y-m-d H:i:s');
            }

            if (isset($request->to)) {
                $params['criteria']['date_to'] = date_format(date_create($request->to .' 23:59'), 'Y-m-d H:i:s');
            }

            if (isset($request->dtype)) {
                $params['criteria']['date_type'] = $request->dtype;
            }

            if (isset($request->order)) {
                $params['criteria']['order']['field'] = $request->order;
                $params['criteria']['order']['type'] = 'asc';
            }

            $req = Request::create('/api/listDocuments', 'POST', $params);
            $api = new ApiDocument($req);
            $result = $api->listDocuments($req)->getData();
            $getParams = array_except(app('request')->input(), ['page', 'q']);

            $paginationData = $this->getPaginationData(
                $result->documents,
                $result->total_records,
                $getParams,
                $perPage
            );

            return view('/admin/documents', [
                'class'         => 'user',
                'documents'     => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'     => isset($request->q) ? $request->q : null,
                'range'      => [
                    'from' => isset($request->from) ? $request->from : null,
                    'to'   => isset($request->to) ? $request->to : null
                ],
            ]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Filters documents based on search string
     *
     * @param Request $request
     *
     * @return view with filtered document list
     */
    public function search(Request $request)
    {
        if (Role::isAdmin()) {
            $perPage = 10;
            $search = $request->offsetGet('q');

            if (empty($search)) {
                return redirect('admin/documents/list');
            }

            $params = [
                'records_per_page'  => $perPage,
                'criteria'          => [
                    'search' => $search,
                ]
            ];

            $searchRq = Request::create('/api/searchDocuments', 'POST', $params);
            $api = new ApiDocument($searchRq);
            $docData = $api->searchDocuments($searchRq)->getData();

            $documents = !empty($docData->documents) ? $docData->documents : [];
            $count = !empty($docData->total_records) ? $docData->total_records : 0;

            $getParams = [
                'q' => $search
            ];

            $paginationData = $this->getPaginationData($documents, $count, $getParams, $perPage);

            return view('admin/documents', [
                'class'         => 'user',
                'documents'     => $paginationData['items'],
                'pagination'    => $paginationData['paginate'],
                'search'        => $search,
            ]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            /*if ($request->has('create')) {

            }*/

            return view('admin/documentsAdd', ['class' => 'user', 'fields' => self::getTransFields()]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
