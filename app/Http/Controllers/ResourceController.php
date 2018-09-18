<?php

namespace App\Http\Controllers;

use App\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

class ResourceController extends Controller {
    public static function addMetadata($datasetUri, $resourceData, $file = null)
    {
        $data = [];
        $errors = [];
        $success = false;
        $uri = null;

        if (Auth::check()) {
            $apiKey = Auth::user()->api_key;
            $metadata = [
                'api_key'       => $apiKey,
                'dataset_uri'   => $datasetUri,
                'data'          => $resourceData,
            ];

            if (
                $metadata['data']['type'] == Resource::TYPE_FILE
                && isset($file)
                && $file->isValid()
            ) {
                $extension = $file->getClientOriginalExtension();

                if (!empty($extension)) {
                    $metadata['data']['file_format'] = $extension;
                    $content = file_get_contents($file->getRealPath());
                }
            } else if (
                $metadata['data']['type'] == Resource::TYPE_API
                && isset($data['resource_url'])
            ) {
                $reqHeaders = [];

                $ch = curl_init();

                curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

                if (isset($data['http_headers'])) {
                    $reqHeaders = preg_split('/\r\n|\r|\n/', $data['http_headers']);
                }

                // by default curl uses GET
                if ($data['http_rq_type'] == 'POST') {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

                    if (!empty($data['post_data'])) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data['post_data']);
                        $length = 'Content-Length: ' . strlen($data['post_data']);
                        array_push($reqHeaders, $length);
                    }
                }

                curl_setopt_array($ch, [
                    CURLOPT_HTTPHEADER      => $reqHeaders,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_TIMEOUT         => 60,
                    CURLOPT_URL             => $data['resource_url'],
                ]);

                $responseHeaders = [];
                // this function is called by curl for each header received
                curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
                    $length = strlen($header);
                    $header = explode(':', $header, 2);

                    // ignore invalid headers
                    if (count($header) < 2) {
                        return $length;
                    }

                    $name = strtolower(trim($header[0]));

                    if (!array_key_exists($name, $responseHeaders)) {
                        $responseHeaders[$name] = [trim($header[1])];
                    } else {
                        $responseHeaders[$name][] = trim($header[1]);
                    }

                    return $length;
                });

                $resp = curl_exec($ch);

                $extension = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

                curl_close($ch);

                if ($resp) {
                    $content = $resp;
                } else {
                    return compact('errors', 'success');
                }

                $extension = substr($extension, strpos($extension, '/') + 1);

                if (strpos($extension, ';')) {
                    $extension = substr($extension, 0, strpos($extension, ';'));
                }

                if (!empty($extension)) {
                    $metadata['data']['file_format'] = $extension;
                }
            }

            $savePost = Request::create('/api/addResourceMetadata', 'POST', $metadata);
            $api = new ApiResource($savePost);
            $result = $api->addResourceMetadata($savePost)->getData();

            if ($result->success) {
                $uri = $result->data->uri;

                if ($metadata['data']['type'] == Resource::TYPE_HYPERLINK) {
                    $success = true;
                } else if (!empty($extension)) {
                    $convertData = [
                        'api_key'   => $apiKey,
                        'data'      => $content,
                    ];

                    Session::forget('elasticData');

                    switch ($extension) {
                        case 'json':
                            Session::put('elasticData', json_decode($content, true));

                            break;
                        case 'csv':
                            $reqConvert = Request::create('/csv2json', 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->csv2json($reqConvert)->getData();

                            if ($resultConvert['success']) {
                                $elasticData = $resultConvert['data'];
                                Session::put('elasticData', $elasticData);
                                $data['csvData'] = $elasticData;
                            }

                            break;
                        case 'xml':
                            if (($pos = strpos($content, '?>')) !== false) {
                                $trimContent = substr($content, $pos + 2);
                                $convertData['data'] = trim($trimContent);
                            }

                            $reqConvert = Request::create('/xml2json', 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->xml2json($reqConvert)->getData(true);

                            if ($resultConvert['success']) {
                                $elasticData = $resultConvert['data'];
                                Session::put('elasticData', $elasticData);
                                $data['xmlData'] = $content;
                            }

                            break;
                        case 'kml':
                            $method = $extension .'2json';
                            $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->$method($reqConvert)->getData(true);

                            if ($resultConvert['success']) {
                                $elasticData = $resultConvert['data'];
                                Session::put('elasticData', $elasticData);
                            }

                            break;
                        case 'rdf':
                            $method = $extension .'2json';
                            $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->$method($reqConvert)->getData(true);

                            if ($resultConvert['success']) {
                                $elasticData = $resultConvert['data'];
                                Session::put('elasticData', $elasticData);
                                $data['xmlData'] = $content;
                            }

                            break;
                        case 'pdf':
                        case 'doc':
                            $method = $extension .'2json';
                            $convertData['data'] = base64_encode($convertData['data']);
                            $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->$method($reqConvert)->getData(true);

                            if ($resultConvert['success']) {
                                Session::put('elasticData', ['text' => $resultConvert['data']]);
                                $data['text'] = $resultConvert['data'];
                            }

                            break;
                        case 'xls':
                        case 'xlsx':
                            $method = 'xls2json';
                            $convertData['data'] = base64_encode($convertData['data']);
                            $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->$method($reqConvert)->getData(true);

                            if ($resultConvert['success']) {
                                Session::put('elasticData', $resultConvert['data']);
                                $data['csvData'] = $resultConvert['data'];
                            }

                            break;
                        case 'txt':
                            Session::put('elasticData', ['text' => $convertData['data']]);

                            $data['text'] = $convertData['data'];

                            break;
                        default:
                            $method = 'img2json';
                            $convertData['data'] = base64_encode($convertData['data']);
                            $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                            $api = new ApiConversion($reqConvert);
                            $resultConvert = $api->$method($reqConvert)->getData(true);

                            if ($resultConvert['success']) {
                                Session::put('elasticData', ['text' => $resultConvert['data']]);
                                $data['text'] = $resultConvert['data'];
                            }
                        }
                }

                if (Session::has('elasticData')) {
                    $success = true;
                }
            } else {
                $errors = $result->errors;
            }
        }

        return compact('errors', 'data', 'success', 'uri');
    }

    /**
     * Imports elastic search data for CSV format
     *
     * @param Request $request - resource uri
     *
     * @return redirect to resource view page
     */
    public function importCsvData(Request $request)
    {
        if ($request->has('ready_data') && $request->has('resource_uri')) {
            $admin = $request->offsetGet('admin');
            $root = empty($admin) ? 'user' : 'admin';
            $uri = $request->offsetGet('resource_uri');
            $elasticData = Session::get('elasticData');
            Session::forget('elasticData');
            $filtered = [];

            if ($request->has('keepcol')) {
                $keepColumns = $request->offsetGet('keepcol');

                if (empty($elasticData)) {
                    return redirect()->back()->withInput();
                } else {
                    foreach ($elasticData as $row) {
                        $filtered[] = array_intersect_key($row, $keepColumns);
                    }
                }
            }

            if (!empty($filtered)) {
                $saveData = [
                    'resource_uri'  => $uri,
                    'data'          => $filtered,
                ];
                $reqElastic = Request::create('/addResourceData', 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->addResourceData($reqElastic)->getData();

                if ($resultElastic->success) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    return redirect('/'. $root .'/resource/view/'. $uri);
                }

                $request->session()->flash('alert-danger', $resultElastic->error->message);

                // delete resource metadata record
                $resource = Resource::where('uri', $uri)->first();

                if ($resource) {
                    $resource->forceDelete();
                }

                return redirect()->back()->withInput()->withErrors($resultElastic->errors);
            }
        }

        $request->session()->flash('alert-danger', __('custom.add_error'));

        return redirect()->back()->withInput();
    }

    /**
     * Imports elastic search data for JSON, XML, RDF, KML formats
     *
     * @param Request $request - resource uri
     *
     * @return redirect to resource view page
     */
    public function importElasticData(Request $request)
    {
        if ($request->has('ready_data') && $request->has('resource_uri')) {
            $admin = $request->offsetGet('admin');
            $root = empty($admin) ? 'user' : 'admin';
            $uri = $request->offsetGet('resource_uri');
            $elasticData = Session::get('elasticData');
            Session::forget('elasticData');

            if (!empty($elasticData)) {
                $saveData = [
                    'resource_uri'  => $uri,
                    'data'          => $elasticData,
                ];
                $reqElastic = Request::create('/addResourceData', 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->addResourceData($reqElastic)->getData();

                if ($resultElastic->success) {
                    $request->session()->flash('alert-success', __('custom.changes_success_save'));

                    return redirect('/'. $root .'/resource/view/'. $uri);
                }

                $request->session()->flash('alert-danger', $resultElastic->error->message);
                // delete resource metadata record
                $resource = Resource::where('uri', $uri)->first();

                if ($resource) {
                    $resource->forceDelete();
                }

                return redirect()->back()->withInput()->withErrors($resultElastic->errors);
            }
        }

        $request->session()->flash('alert-danger', __('custom.add_error'));

        return redirect()->back()->withInput();
    }

    /**
     * Transforms resource data to downloadable file
     *
     * @param Request $request - file name, file format and id for resource elastic search data
     *
     * @return downlodable file
     */
    public function resourceDownload(Request $request)
    {
        $fileName = $request->offsetGet('name');
        $esid = $request->offsetGet('es_id');
        $format = $request->offsetGet('format');
        $method = 'to'. $format;
        $convertReq = Request::create('/api/'. $method, 'POST', ['es_id' => $esid]);
        $apiResources = new ApiConversion($convertReq);
        $resource = $apiResources->$method($convertReq)->getData();

        if (strtolower($format) == 'json') {
            $fileData = json_encode($resource->data);
        } else {
            $fileData = $resource->data;
        }

        if (!empty($resource->data)) {
            $handle = fopen('../storage/app/'. $fileName, 'w+');
            $path = stream_get_meta_data($handle)['uri'];

            fwrite($handle, $fileData);

            fclose($handle);

            $headers = array(
                'Content-Type' => 'text/'. strtolower($method),
            );

            return response()->download($path, $fileName .'.'. strtolower($format), $headers)->deleteFileAfterSend(true);
        }

        return back();
    }
}
