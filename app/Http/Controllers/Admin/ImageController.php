<?php

namespace App\Http\Controllers\Admin;

use App\Role;
use App\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\ImageController as ApiImage;

class ImageController extends AdminController
{
    /**
     * Lists images
     *
     * @param Request $request
     *
     * @return view with list of images
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

            $req = Request::create('/api/listImages', 'POST', $params);
            $api = new ApiImage($req);
            $result = $api->listImages($req)->getData();

            $paginationData = $this->getPaginationData(
                isset($result->images) ? $result->images : [],
                $result->total_records,
                [],
                $perPage
            );

            return view('/admin/images', [
                'class'      => 'user',
                'images'     => $paginationData['items'],
                'pagination' => $paginationData['paginate'],
            ]);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Add image
     *
     * @param Request $request
     *
     * @return view on success on failure redirect to homepage
     */
    public function add(Request $request)
    {
        if (Role::isAdmin()) {
            if ($request->has('create')) {
                $params = [];

                if (!empty($request->image)) {
                    $params['filename'] = $request->image->getClientOriginalName();
                    $path = $request->image->getPathName();
                    $params['data'] = \File::get($path);
                    $params['mimetype'] = $request->image->getMimeType();
                }

                $rq = Request::create('/api/addImage', 'POST', [
                    'data' => [
                        'name'       => $request->offsetGet('name'),
                        'comment'    => $request->offsetGet('comment'),
                        'img_file'   => isset($params['filename']) ? $params['filename'] : null,
                        'mime_type'  => isset($params['mimetype']) ? $params['mimetype'] : null,
                        'img_data'   => isset($params['data']) ? $params['data'] : null,
                        'active'     => $request->offsetGet('active'),
                    ]
                ]);
                $api = new ApiImage($rq);
                $result = $api->addImage($rq)->getData();

                if (!empty($result->success)) {
                    $request->session()->flash('alert-success', __('custom.add_success'));

                    return redirect('/admin/images/view/'. $result->img_id);
                } else {
                    $request->session()->flash('alert-danger', __('custom.add_error'));

                    return back()->withErrors($result->errors)->withInput(Input::all());
                }
            }

            return view('admin/ImagesAdd', ['class' => 'user']);
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Displays information for an image
     *
     * @param Request $request
     * @param integer $id
     *
     * @return view on success on failure redirect to listing page
     */
    public function view(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $req = Request::create('/api/getImageDetails', 'POST', ['img_id' => $id]);
            $api = new ApiImage($req);
            $result = $api->getImageDetails($req)->getData();

            if ($result->success) {
                $result->image->img_data = $this->getImageData(
                    utf8_decode($result->image->img_data),
                    $result->image->mime_type
                );
                $result->image = $this->appendPublicURI($result->image);
            } else {
                $request->session()->flash('alert-danger', __('custom.non_existing_image'));
            }

            return view(
                'admin/imagesView',
                [
                    'class' => 'user',
                    'image' => isset($result->image) ? $this->getModelUsernames($result->image) : null
                ]
            );
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Edit an image based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function edit(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';
            $model = Image::where('id', $id)->first();

            if (isset($model->id)) {
                $model = $this->appendPublicURI($model);
                $model = $this->getModelUsernames($model);

                if ($request->has('edit')) {
                    $rq = Request::create('/api/editImage', 'POST', [
                        'img_id' => $id,
                        'data' => [
                            'name'    => $request->offsetGet('name'),
                            'comment' => $request->offsetGet('comment'),
                            'active'  => $request->offsetGet('active'),
                        ]
                    ]);

                    $api = new ApiImage($rq);
                    $result = $api->editImage($rq)->getData();

                    if ($result->success) {
                        $request->session()->flash('alert-success', __('custom.edit_success'));

                        return back();
                    } else {
                        $request->session()->flash('alert-danger', __('custom.edit_error'));

                        return back()->withErrors(isset($result->errors) ? $result->errors : []);
                    }
                }
            } else {
                $request->session()->flash('alert-danger', __('custom.non_existing_image'));
            }

            return view('admin/imagesEdit', compact('model', 'class'));
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
    }

    /**
     * Delete an image based on id
     *
     * @param Request $request
     * @param integer $id
     * @return view on success with messages
     */
    public function delete(Request $request, $id)
    {
        if (Role::isAdmin()) {
            $class = 'user';

            $rq = Request::create('/api/deleteImage', 'POST', [
                'img_id' => $id,
            ]);

            $api = new ApiImage($rq);
            $result = $api->deleteImage($rq)->getData();

            if ($result->success) {
                $request->session()->flash('alert-success', __('custom.delete_success'));

                return redirect('/admin/images/list');
            } else {
                $request->session()->flash('alert-danger', __('custom.delete_error'));

                return redirect('/admin/images/list')->withErrors(isset($result->errors) ? $result->errors : []);
            }
        }

        return redirect()->back()->with('alert-danger', __('custom.access_denied_page'));
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

    /**
     * Add public URIs to an image object
     *
     * @param object $image
     *
     * @return object with public URIs added
     */
    public function appendPublicURI($image)
    {
        if (is_object($image)) {
            $image->thumb = url('/images/') .'/'. Image::TYPE_THUMBNAIL .'/'. $image->id;
            $image->item = url('/images/') .'/'. Image::TYPE_IMAGE .'/'. $image->id;
        }

        return $image;
    }
}
