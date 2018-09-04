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
    public static function getDocTransFields($edit = false)
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
                'name'     => $edit ? 'descript' : 'description',
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
            if ($request->has('create')) {
                $params = [];

                if (!empty($request->document)) {
                    $params['filename'] = $request->document->getClientOriginalName();
                    $path = $request->document->getPathName();
                    $params['data'] = \File::get($path);
                    $params['mimetype'] = $request->document->getMimeType();
                }

                $rq = Request::create('/api/addDocument', 'POST', [
                    'data' => [
                        'name'        => $request->offsetGet('name'),
                        'description' => $request->offsetGet('description'),
                        'filename'    => isset($params['filename']) ? $params['filename'] : null,
                        'mimetype'    => isset($params['mimetype']) ? $params['mimetype'] : null,
                        'data'        => isset($params['data']) ? $params['data'] : null,
                        'forum_link'  => $request->offsetGet('forum_link'),
                    ]
                ]);
                $api = new ApiDocument($rq);
                $result = $api->addDocument($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/documents/view/'. $result->data->doc_id);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view('admin/documentsAdd', ['class' => 'user', 'fields' => self::getDocTransFields()]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Displays information for a given document
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to homepage
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $req = Request::create('/api/listDocuments', 'POST', ['criteria' => ['doc_id' => $id]]);
            $api = new ApiDocument($req);
            $result = $api->listDocuments($req)->getData();
            $doc = isset($result->documents[0]) ? $result->documents[0] : null;

            if (!is_null($doc)) {
                if ($request->has('download')) {
                    return response(utf8_decode($doc->data))
                        ->header('Cache-Control', 'no-cache private')
                        ->header('Content-Description', 'File Transfer')
                        ->header('Content-Type', $doc->mimetype)
                        ->header('Content-length', strlen(utf8_decode($doc->data)))
                        ->header('Content-Disposition', 'attachment; filename='. $doc->filename)
                        ->header('Content-Transfer-Encoding', 'binary');

                }

                return view(
                    'admin/documentsView',
                    [
                        'class'    => 'user',
                        'document' => $doc
                    ]
                );
            }

            return redirect('/admin/documents/list');
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit a document based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $fields = self::getDocTransFields(true);

            $model = Document::find($id)->loadTranslations();

            if ($request->has('edit')) {
                if (!empty($request->document)) {
                    $params['filename'] = $request->document->getClientOriginalName();
                    $path = $request->document->getPathName();
                    $params['data'] = \File::get($path);
                    $params['mimetype'] = $request->document->getMimeType();
                }

                $rq = Request::create('/api/editDocument', 'POST', [
                    'doc_id' => $id,
                    'data' => [
                        'name'        => $request->offsetGet('name'),
                        'description' => $request->offsetGet('descript'),
                        'filename'    => isset($params['filename']) ? $params['filename'] : null,
                        'mimetype'    => isset($params['mimetype']) ? $params['mimetype'] : null,
                        'data'        => isset($params['data']) ? $params['data'] : null,
                        'forum_link'  => $request->offsetGet('forum_link'),
                    ]
                ]);

                $api = new ApiDocument($rq);
                $result = $api->editDocument($rq)->getData();

                if ($result->success) {
                    $request->session()->flash('alert-success', __('custom.edit_success'));

                    return back();
                } else {
                    $request->session()->flash('alert-danger', __('custom.edit_error'));

                    return back()->withErrors(isset($result->errors) ? $result->errors : []);
                }
            }

            return view('admin/documentsEdit', compact('class', 'fields', 'model'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Delete a document based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';

            $rq = Request::create('/api/deleteDocument', 'POST', [
                'doc_id' => $id,
            ]);

            $api = new ApiDocument($rq);
            $result = $api->deleteDocument($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect('/admin/documents/list');
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));

                return redirect('/admin/documents/list')->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }
}
