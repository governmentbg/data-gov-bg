<?php

namespace App\Http\Controllers\Api;

use \Validator;
use App\Image;
use App\Module;
use App\ActionsHistory;
use App\RoleRight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
use Illuminate\Database\QueryException;

class ImageController extends ApiController
{
    private $path;
    public function __construct()
    {
        $this->path = storage_path('images') .'/';
        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
    }

    /**`
     * Add an image with provided data
     *
     * @param array data - required
     * @param string|array data[name] - required
     * @param string|array data[comment] - optional
     * @param string data[img_url] - optional
     * @param string data[img_file] - optional
     * @param string data[img_data] - optional
     * @param string data[mime_type] - optional
     * @param string data[active] - optional
     *
     * @return json response with image id or error message
     */
    public function addImage(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'data' => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($post['data'], [
                'name'      => 'required|string|max:191',
                'comment'   => 'nullable|string|max:191',
                'img_url'   => 'required_without:img_file|string',
                'img_file'  => 'required_without:img_url|string|max:191',
                'img_data'  => 'required_without:img_url',
                'mime_type' => 'required_without:img_url|max:191',
                'active'    => 'nullable|integer|digits_between:0,1',
            ]);
        }

        $validator->after(function ($validator) use ($post) {
            if (isset($post['data']['img_data']) && !$this->checkImageSize($post['data']['img_data'])) {
                $validator->errors()->add('image', $this->getFileSizeError());
            }

            if ($validator->errors()->has('img_file')) {
                $validator->errors()->add('image', $validator->errors()->first('img_file'));
            }

            if ($validator->errors()->has('mime_type')) {
                $validator->errors()->add('image', $validator->errors()->first('mime_type'));
            }
        });

        if (!$validator->fails()) {
            $rightCheck = RoleRight::checkUserRight(
                Module::IMAGES,
                RoleRight::RIGHT_EDIT
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                DB::beginTransaction();

                $image = new Image;

                if (!empty($post['data']['active'])) {
                    $image->active = $post['data']['active'];
                }

                $image->size = 0;
                $image->width = 0;
                $image->height = 0;
                $image->name = $post['data']['name'];
                $image->comment = isset($post['data']['comment'])
                    ? $post['data']['comment']
                    : null;

                if (!empty($post['data']['img_data'])) {
                    $image->img_file = $post['data']['img_file'];
                    $image->mime_type = $post['data']['mime_type'];

                    $image->save();

                    file_put_contents($this->path . $image->id, $post['data']['img_data']);

                    try {
                        $uploadedImage = \Image::make($this->path . $image->id);
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());

                        if (file_exists($this->path . $image->id)) {
                            unlink($this->path . $image->id);
                        }

                        DB::rollback();

                        return $this->errorResponse(__('custom.add_error'), $validator->errors()->messages());
                    }

                    $image->update(
                        [
                            'width'  => $uploadedImage->width(),
                            'height' => $uploadedImage->height(),
                            'size'   => $uploadedImage->filesize(),
                        ]
                    );
                } elseif (!empty($post['data']['img_url'])) {
                    try {
                        $content = file_get_contents($post['data']['img_url']);
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());

                        DB::rollback();

                        return $this->errorResponse(__('custom.add_error'), $validator->errors()->messages());
                    }

                    $image->mime_type = '';
                    $image->img_file = basename($post['data']['img_url']);

                    $image->save();

                    file_put_contents($this->path . $image->id, $content);

                    try {
                        $uploadedImage = \Image::make($this->path . $image->id);
                    } catch (\Exception $e) {
                        Log::error($e->getMessage());

                        if (file_exists($this->path . $image->id)) {
                            unlink($this->path . $image->id);
                        }

                        DB::rollback();

                        return $this->errorResponse(__('custom.add_error'), $validator->errors()->messages());
                    }

                    $image->update(
                        [
                            'width'     => $uploadedImage->width(),
                            'height'    => $uploadedImage->height(),
                            'size'      => $uploadedImage->filesize(),
                            'mime_type' => $uploadedImage->mime()
                        ]
                    );
                }

                $logData = [
                    'module_name'   => Module::getModuleName(Module::IMAGES),
                    'action'        => ActionsHistory::TYPE_ADD,
                    'action_object' => $image->id,
                    'action_msg'    => 'Added new image',
                ];

                Module::add($logData);

                DB::commit();

                return $this->successResponse(['img_id' => $image->id], true);
            } catch (QueryException $ex) {
                DB::rollback();

                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.add_error'), $validator->errors()->messages());
    }

    /**
     * Get image details
     *
     * @param int img_id - required without name
     * @param string name - required without img_id
     *
     * @return json with image details or error
     */
    public function getImageDetails(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'img_id' => 'required_without:name|integer|exists:images,id|digits_between:1,10',
            'name'   => 'required_without:img_id|string',
        ]);

        if (!$validator->fails()) {
            try {
                $query = Image::select();

                if (isset($post['img_id'])) {
                    $query->where('id', $post['img_id']);
                } else {
                    $query->where('name', $post['name']);
                }

                $image = $query->first();

                $rightCheck = RoleRight::checkUserRight(
                    Module::IMAGES,
                    RoleRight::RIGHT_VIEW,
                    [],
                    [
                        'created_by' => $image->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                if (isset($image->id)) {
                    try {
                        $imageContent = file_get_contents($this->path .'/'. $image->id);
                        $image->img_data = utf8_encode($imageContent);
                    } catch (\Exception $e) {
                        $image->img_data = null;
                    }
                }

                return $this->successResponse(['image' => $image], true);
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
        }

        return $this->errorResponse(__('custom.get_image_details_error'), $validator->errors()->messages());
    }

    /**
     * Edit image with provided data
     *
     * @param int img_id - required
     * @param array data - required
     * @param string data[name] - optional
     * @param string data[name] - optional
     * @param string data[comment] - optional
     * @param string data[img_url] - optional
     * @param string data[img_file] - optional
     * @param string data[img_data] - optional
     * @param string data[mime_type] - optional
     * @param string data[active] - optional
     *
     * @return json response with success or error message
     */
    public function editImage(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'img_id' => 'required|integer|exists:images,id|digits_between:1,10',
            'data'   => 'required|array',
        ]);

        if (!$validator->fails()) {
            $validator = Validator::make($post['data'], [
                'name'      => 'required|string|max:191',
                'comment'   => 'nullable|string|max:191',
                'img_url'   => 'nullable|string',
                'img_file'  => 'required_with:img_data|required_with:mime_type|string|max:191',
                'img_data'  => 'required_with:img_file|required_with:mime_type',
                'mime_type' => 'required_with:img_file|required_with:img_data|string|max:191',
                'active'    => 'nullable|integer|digits_between:0,1',
            ]);
        }

        if (!$validator->fails()) {
            try {
                $editImage = Image::find($post['img_id']);

                $rightCheck = RoleRight::checkUserRight(
                    Module::IMAGES,
                    RoleRight::RIGHT_EDIT,
                    [],
                    [
                        'created_by' => $editImage->created_by
                    ]
                );

                if (!$rightCheck) {
                    return $this->errorResponse(__('custom.access_denied'));
                }

                DB::beginTransaction();

                if (!empty($post['data']['name'])) {
                    $editImage->name = $post['data']['name'];
                }

                if (!empty($post['data']['comment'])) {
                    $editImage->comment = $post['data']['comment'];
                } else {
                    $editImage->comment = null;
                }

                if (!empty($post['data']['active'])) {
                    $editImage->active = $post['data']['active'];
                } else {
                    $editImage->active = Image::INACTIVE_IMAGE;
                }

                if (!empty($post['data']['img_url'])) {
                    try {
                        $content = file_get_contents($post['data']['img_url']);
                    } catch (\Exception $e) {
                        DB::rollback();

                        return $this->errorResponse(__('custom.edit_error'), $validator->errors()->messages());
                    }

                    $editImage->img_file = basename($post['data']['img_url']);

                    file_put_contents($this->path . $editImage->id, $content);
                    try {
                        $uploadedImage = \Image::make($this->path . $editImage->id);
                    } catch (\Exception $e) {
                        DB::rollback();

                        return $this->errorResponse(__('custom.edit_error'), $validator->errors()->messages());
                    }

                    $editImage->update(
                        [
                            'width'     => $uploadedImage->width(),
                            'height'    => $uploadedImage->height(),
                            'size'      => $uploadedImage->filesize(),
                            'mime_type' => $uploadedImage->mime()
                        ]
                    );
                } elseif (!empty($post['data']['img_data'])) {
                    $editImage->img_file = $post['data']['img_file'];
                    $editImage->mime_type = $post['data']['mime_type'];

                    file_put_contents($this->path . $editImage->id, $post['data']['img_data']);
                    try {
                        $uploadedImage = \Image::make($this->path . $editImage->id);
                    } catch (\Exception $e) {
                        DB::rollback();

                        return $this->errorResponse(__('custom.edit_error'), $validator->errors()->messages());
                    }

                    $editImage->update(
                        [
                            'width'  => $uploadedImage->width(),
                            'height' => $uploadedImage->height(),
                            'size'   => $uploadedImage->filesize(),
                        ]
                    );
                }

                $editImage->save();

                DB::commit();

                $logData = [
                    'module_name'      => Module::getModuleName(Module::IMAGES),
                    'action'           => ActionsHistory::TYPE_MOD,
                    'action_object'    => $editImage->id,
                    'action_msg'       => 'Edited an image',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $e) {
                DB::rollback();

                Log::error($e->getMessage());
            }
        }

        return $this->errorResponse(__('custom.edit_image_fail'), $validator->errors()->messages());
    }

    /**
     * Delete an image based on ID
     *
     * @param int img_id - required
     *
     * @return json response with success or error message
     */
    public function deleteImage(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'img_id' => 'required|integer|exists:images,id|digits_between:1,10',
        ]);

        if (!$validator->fails()) {
            $deleteImage = Image::find($post['img_id']);

            $rightCheck = RoleRight::checkUserRight(
                Module::IMAGES,
                RoleRight::RIGHT_ALL,
                [],
                [
                    'created_by' => $deleteImage->created_by
                ]
            );

            if (!$rightCheck) {
                return $this->errorResponse(__('custom.access_denied'));
            }

            try {
                $deleteImage->delete();

                if (file_exists($this->path . $deleteImage->id)) {
                    unlink($this->path . $deleteImage->id);
                }

                $logData = [
                    'module_name'      => Module::getModuleName(Module::IMAGES),
                    'action'           => ActionsHistory::TYPE_DEL,
                    'action_object'    => $post['img_id'],
                    'action_msg'       => 'Deleted image',
                ];

                Module::add($logData);

                return $this->successResponse();
            } catch (QueryException $ex) {
                Log::error($ex->getMessage());
            }
            return $this->errorResponse(__('custom.delete_image_fail'));
        }

        return $this->errorResponse(__('custom.delete_image_fail'), $validator->errors()->messages());
    }

    /**
     * List images
     *
     * @param integer records_per_page - optional
     * @param integer page_number - optional
     *
     * @return json response with images list or error message
     */
    public function listImages(Request $request)
    {
        $post = $request->all();

        $validator = Validator::make($post, [
            'records_per_page'      => 'nullable|integer|digits_between:1,10',
            'page_number'           => 'nullable|integer|digits_between:1,10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(__('custom.list_images_fail'), $validator->errors()->messages());
        }

        $rightCheck = RoleRight::checkUserRight(
            Module::IMAGES,
            RoleRight::RIGHT_VIEW
        );

        if (!$rightCheck) {
            return $this->errorResponse(__('custom.access_denied'));
        }

        $result = [];

        $query = Image::select();
        $count = $query->count();
        $query->forPage(
            $request->offsetGet('page_number'),
            $this->getRecordsPerPage($request->offsetGet('records_per_page'))
        );

        $result = [];
        $images = $query->get();

        if (count($images)) {
            foreach ($images as $key => $img) {
                try {
                    $imageContent = file_get_contents($this->path .'/'. $images[$key]->id);
                    $images[$key]->img_data = utf8_encode($imageContent);
                } catch (\Exception $e) {
                    $images[$key]->img_data = null;
                }
            }
        }

        $logData = [
            'module_name'      => Module::getModuleName(Module::IMAGES),
            'action'           => ActionsHistory::TYPE_SEE,
            'action_msg'       => 'Listed images',
        ];

        Module::add($logData);

        return $this->successResponse(
            [
                'total_records' => $count,
                'images'        => $images
            ],
            true
        );
    }
}
