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
    protected function getImageData($data, $mime)
    {
        return 'data:'. $mime .';base64,'. base64_encode($data);
    }
}
