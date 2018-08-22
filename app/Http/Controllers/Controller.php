<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get array with results for current page and paginator
     *
     * @param array result
     * @param integer totalCount
     * @param array params - array with GET parameters
     * @param integer perPage
     *
     * @return array with results for the current page and paginator object
     */
    public function getPaginationData($result = [], $totalCount = 0, $params = [], $perPage = 1)
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $path = !empty($params)
            ? LengthAwarePaginator::resolveCurrentPath() .'?'. http_build_query($params)
            : LengthAwarePaginator::resolveCurrentPath();

        $paginator = new LengthAwarePaginator(
            $result,
            $totalCount,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => $path]
        );

        return [
            'items'    => $result,
            'paginate' => $paginator
        ];
    }

    /**
     * Get image data
     *
     * @param binary $data
     * @param string $mime
     *
     * @return string
     */
    protected function getImageData($data, $mime, $type = 'org')
    {
        if (empty($data) || empty($mime)) {
            return asset('img/default-'. $type .'.svg');
        }

        return 'data:'. $mime .';base64,'. base64_encode($data);
    }

    /**
     * Returns model with usernames instead of user ids for record signatures
     *
     * @param Model $model
     *
     * @return view
     */
    public function getModelUsernames($model) {
        if (isset($model)) {
            if (is_object($model)) {
                if (
                    $model->updated_by == $model->created_by
                    && !is_null($model->created_by)
                ) {
                    $username = User::find($model->created_by)->username;
                    $model->updated_by = $username;
                    $model->created_by = $username;
                } else {
                    $model->updated_by = is_null($model->updated_by) ? null : \App\User::find($model->updated_by)->username;
                    $model->created_by = is_null($model->created_by) ? null : \App\User::find($model->created_by)->username;
                }
            } elseif (is_array($model)) {
                $storage = [];

                foreach ($model as $key => $item) {
                    $createdId = $item->created_by;
                    $updatedId = $item->updated_by;

                    if (
                        $item->updated_by == $item->created_by
                        && !is_null($item->created_by)
                    ) {
                        if (!empty($storage[$item->created_by])) {
                            $model[$key]->updated_by = $storage[$item->created_by];
                            $model[$key]->created_by = $storage[$item->created_by];
                        } else {
                            $username = \App\User::find($item->created_by)->username;
                            $model[$key]->updated_by = $username;
                            $model[$key]->created_by = $username;
                            $storage[$createdId] = $username;
                        }

                    } else {
                        if (!empty($storage[$item->created_by])) {
                            $model[$key]->created_by = $storage[$item->created_by];
                        } else {
                            $username = is_null($item->created_by) ? null : \App\User::find($item->created_by)->username;
                            $model[$key]->created_by = $username;

                            if (!is_null($username)) {
                                $storage[$createdId] = $username;
                            }
                        }

                        if (!empty($storage[$item->updated_by])) {
                            $model[$key]->updated_by = $storage[$item->updated_by];
                        } else {
                            $username = is_null($item->updated_by) ? null : \App\User::find($item->updated_by)->username;
                            $model[$key]->updated_by = $username;

                            if (!is_null($username)) {
                                $storage[$updatedId] = $username;
                            }
                        }
                    }
                }
            }
        }

        return $model;
    }
}
