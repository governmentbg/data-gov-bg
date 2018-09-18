<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

class VisualisationController extends Controller {

    /**
     * Return view for iframe embedding
     *
     * @param Request $request
     * @param string $uri - resource uri
     * @return view
     */
    public function resourceEmbed(Request $request, $uri)
    {
        $reqMetadata = Request::create('/api/getResourceMetadata', 'POST', ['resource_uri' => $uri]);
        $apiMetadata = new ApiResource($reqMetadata);
        $result = $apiMetadata->getResourceMetadata($reqMetadata)->getData();
        $resource = !empty($result->resource) ? $result->resource : null;

        if (!empty($resource)) {
                        $data = [];

            if (!empty($resource)) {
                $resource->format_code = Resource::getFormatsCode($resource->file_format);
                $resource = $this->getModelUsernames($resource);

                $reqEsData = Request::create('/api/getResourceData', 'POST', ['resource_uri' => $uri]);
                $apiEsData = new ApiResource($reqEsData);
                $response = $apiEsData->getResourceData($reqEsData)->getData();

                $data = !empty($response->data) ? $response->data : [];

                if ($resource->format_code == Resource::FORMAT_XML) {
                    $convertData = [
                        'api_key'   => \Auth::user()->api_key,
                        'data'      => $data,
                    ];
                    $reqConvert = Request::create('/json2xml', 'POST', $convertData);
                    $apiConvert = new ApiConversion($reqConvert);
                    $resultConvert = $apiConvert->json2xml($reqConvert)->getData();
                    $data = $resultConvert->data;
                }

                return view('visualisation/visualisation', [
                    'class'         => 'user',
                    'resource'      => $resource,
                    'data'          => $data,
                ]);
            }
        }

        return abort(404);
    }
}
