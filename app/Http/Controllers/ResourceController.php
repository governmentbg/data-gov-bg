<?php

namespace App\Http\Controllers;

use App\Page;
use App\Role;
use App\Resource;
use App\ElasticDataSet;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    public static function addMetadata($recordUri, $resourceData, $file = null, $isUpdate = false, $changeMeta = true)
    {
        $data = [];
        $errors = [];
        $success = false;
        $uri = null;

        if (Auth::check()) {
            $apiKey = Auth::user()->api_key;
            $metadata = [
                'api_key'   => $apiKey,
                'data'      => $resourceData,
            ];

            if ($isUpdate) {
                $metadata['resource_uri'] = $recordUri;
            } else {
                $metadata['dataset_uri'] = $recordUri;
            }

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
            } elseif (
                $metadata['data']['type'] == Resource::TYPE_API
                && isset($resourceData['resource_url'])
            ) {
                $reqHeaders = [];

                $ch = curl_init();

                curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

                if (isset($resourceData['http_headers'])) {
                    $reqHeaders = preg_split('/\r\n|\r|\n/', $resourceData['http_headers']);
                }

                // By default curl uses GET
                if ($resourceData['http_rq_type'] == 'POST') {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

                    if (!empty($resourceData['post_data'])) {
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $resourceData['post_data']);
                        $length = 'Content-Length: ' . strlen($resourceData['post_data']);
                        array_push($reqHeaders, $length);
                    }
                }

                curl_setopt_array($ch, [
                    CURLOPT_HTTPHEADER      => $reqHeaders,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_TIMEOUT         => 60,
                    CURLOPT_URL             => $resourceData['resource_url'],
                ]);

                $responseHeaders = [];
                // This function is called by curl for each header received
                curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$responseHeaders) {
                    $length = strlen($header);
                    $header = explode(':', $header, 2);

                    // Ignore invalid headers
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

            if ($changeMeta) {
                $apiFunction = $isUpdate ? 'editResourceMetadata' : 'addResourceMetadata';
                $savePost = Request::create('/api/'. $apiFunction, 'POST', $metadata);
                $api = new ApiResource($savePost);
                $result = $api->$apiFunction($savePost)->getData();
            }

            if ((isset($result) && $result->success) || !$changeMeta) {
                $uri = $isUpdate ? $recordUri : $result->data->uri;

                if (in_array($metadata['data']['type'], [Resource::TYPE_HYPERLINK, Resource::TYPE_AUTO])) {
                    $success = true;
                } else if (!empty($extension)) {
                    $data = self::callConversions($apiKey, $extension, $content, $uri);
                }

                if (Session::has('elasticData.'. $uri)) {
                    $success = true;
                }
            } else {
                $errors = $result->errors;
            }
        }

        return compact('errors', 'data', 'success', 'uri', 'extension');
    }

    public static function callConversions($apiKey, $extension, $content, $resourceUri)
    {
        $data = [];

        $convertData = [
            'api_key'   => $apiKey,
            'data'      => $content,
        ];

        $extension = strtolower($extension);

        switch ($extension) {
            case 'json':
                Session::put('elasticData.'. $resourceUri, json_decode($content, true));

                if (is_array(json_decode($content, true))) {
                    $data = json_decode($content, true);
                }

                break;
            case 'tsv':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/tsv2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->tsv2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                    $data['tsvData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'xsd':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/xsd2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->xsd2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                    $data['xsdData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'ods':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/ods2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->ods2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                    $data['odsData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'slk':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/slk2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->slk2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                    $data['slkData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'rtf':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/rtf2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->rtf2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, [$elasticData]);
                    $data['rtfData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'odt':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/odt2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->odt2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, [$elasticData]);
                    $data['odtData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'csv':
                $reqConvert = Request::create('/csv2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->csv2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                    $data['csvData'] = $elasticData;
                } else {
                    $data['error'] = $resultConvert->error->message;
                }

                break;
            case 'xml':
                if (($pos = strpos($content, '?>')) !== false) {
                    $trimContent = substr($content, $pos + 2);
                    $convertData['data'] = trim($trimContent);
                }

                $convertData['parse_large'] = true;

                $reqConvert = Request::create('/xml2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->xml2json($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    $elasticData = $resultConvert['data'];
                    Session::put('elasticData.'. $resourceUri, $elasticData);

                    if (is_xml_excel_exported($content)) {
                        $metadata = [
                            'api_key'       => $apiKey,
                            'resource_uri'  => $resourceUri,
                            'data'          => [
                                'file_format'   => 'CSV',
                            ],
                        ];
                        $editRequest = Request::create('/api/editResourceMetadata', 'POST', $metadata);
                        $api = new ApiResource($editRequest);
                        $resultEdit = $api->editResourceMetadata($editRequest)->getData();

                        if ($resultEdit->success) {
                            $data['csvData'] = $elasticData;
                        } else {
                            $data['error'] = $resultEdit->error->message;
                        }
                    } else {
                        $data['xmlData'] = $content;
                    }
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }

                break;
            case 'kml':
                $method = $extension .'2json';
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    $elasticData = $resultConvert['data'];
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }

                break;
            case 'rdf':
                $method = $extension .'2json';
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    $elasticData = $resultConvert['data'];
                    Session::put('elasticData.'. $resourceUri, $elasticData);
                    $data['xmlData'] = $content;
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }

                break;
            case 'pdf':
                $method = $extension .'2json';
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    Session::put('elasticData.'. $resourceUri, ['text' => $resultConvert['data']]);
                    $data['text'] = $resultConvert['data'];
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }

                break;
            case 'doc':
            case 'docx':
                $method = 'doc2json';
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    Session::put('elasticData.'. $resourceUri, ['text' => $resultConvert['data']]);
                    $data['text'] = $resultConvert['data'];
                } else {
                    $data['error'] = $resultConvert['error']['message'];
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
                    Session::put('elasticData.'. $resourceUri, $resultConvert['data']);
                    $data['csvData'] = $resultConvert['data'];
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }

                break;
            case 'txt':
                Session::put('elasticData.'. $resourceUri, ['text' => $convertData['data']]);

                $data['text'] = $convertData['data'];

                break;
            case 'html':
                $method = 'html2json';
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    Session::put('elasticData.'. $resourceUri, ['text' => $resultConvert['data']]);
                    $data['text'] = $resultConvert['data'];
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }

                break;
            default:
                $method = 'img2json';
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    Session::put('elasticData.'. $resourceUri, ['text' => $resultConvert['data']]);
                    $data['text'] = $resultConvert['data'];
                } else {
                    $data['error'] = $resultConvert['error']['message'];
                }
        }

        return $data;
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
            $root = Role::isAdmin() ? 'admin' : 'user';
            $uri = $request->offsetGet('resource_uri');
            $action = $request->offsetGet('action');
            $elasticData = Session::get('elasticData.'. $uri);
            Session::forget('elasticData.'. $uri);
            $filtered = [];
            $extensionFormat = $request->extensionFormat;

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
                    'resource_uri'     => $uri,
                    'data'             => $filtered,
                    'extension_format' => $extensionFormat
                ];

                $apiFunction = $action == 'create' ? 'addResourceData' : 'updateResourceData';
                $reqElastic = Request::create('/'. $apiFunction, 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->$apiFunction($reqElastic)->getData();

                if ($resultElastic->success) {
                    $request->session()->flash(
                        'alert-success',
                        empty($resultElastic->message) ? __('custom.changes_success_save') : $resultElastic->message
                    );

                    if ($request->has('org_uri')) {
                        $orgUri = $request->offsetGet('org_uri');

                        return redirect('/'. $root .'/organisations/'. $orgUri .'/resource/'. $uri);
                    }

                    if ($request->has('group_uri')) {
                        $groupUri = $request->offsetGet('group_uri');

                        return redirect('/'. $root .'/groups/'. $groupUri .'/resource/'. $uri);
                    }

                    return redirect('/'. $root .'/resource/view/'. $uri);
                }

                $request->session()->flash('alert-danger', $resultElastic->error->message);

                if ($action == 'create') {
                    // Delete resource metadata record
                    $resource = Resource::where('uri', $uri)->first();

                    if ($resource) {
                        $resource->forceDelete();
                    }
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
            $root = Role::isAdmin() ? 'admin' : 'user';
            $uri = $request->offsetGet('resource_uri');
            $action = $request->offsetGet('action');
            $elasticData = Session::get('elasticData.'. $uri);
            Session::forget('elasticData.'. $uri);
            $extensionFormat = $request->extensionFormat;

            if (!empty($elasticData)) {
                $saveData = [
                    'resource_uri'     => $uri,
                    'data'             => $elasticData,
                    'extension_format' => $extensionFormat
                ];

                $apiFunction = $action == 'create' ? 'addResourceData' : 'updateResourceData';
                $reqElastic = Request::create('/'. $apiFunction, 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->$apiFunction($reqElastic)->getData();

                if ($resultElastic->success) {
                    $request->session()->flash(
                        'alert-success',
                        empty($resultElastic->message) ? __('custom.changes_success_save') : $resultElastic->message
                    );

                    if ($request->has('org_uri')) {
                        $orgUri = $request->offsetGet('org_uri');

                        return redirect('/'. $root .'/organisations/'. $orgUri .'/resource/'. $uri);
                    }

                    if ($request->has('group_uri')) {
                        $groupUri = $request->offsetGet('group_uri');

                        return redirect('/'. $root .'/groups/'. $groupUri .'/resource/'. $uri);
                    }

                    return redirect('/'. $root .'/resource/view/'. $uri);
                }

                $request->session()->flash('alert-danger', $resultElastic->error->message);

                if ($action == 'create') {
                    // Delete resource metadata record
                    $resource = Resource::where('uri', $uri)->first();

                    if ($resource) {
                        $resource->forceDelete();
                    }
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
        $fileName = str_replace(['\\', '/'], '_', $fileName);

        $resourceId = (int) $request->offsetGet('resource');
        $version = (int) $request->offsetGet('version');
        $format = $request->offsetGet('format');
        $data = ElasticDataSet::getElasticData($resourceId, $version);

        if (strtolower($format) != 'json') {
            $method = 'json2'. strtolower($format);
            $convertReq = Request::create('/api/'. $method, 'POST', ['data' => $data]);
            $apiResources = new ApiConversion($convertReq);
            $resource = $apiResources->$method($convertReq)->getData();

            if (!$resource->success) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.converse_unavailable')));
            }

            $fileData = $resource->data;
        } else {
            $fileData = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($fileData)) {
            $tmpFileName = str_random(32);
            $handle = fopen('../storage/app/'. $tmpFileName, 'w+');
            $path = stream_get_meta_data($handle)['uri'];

            fwrite($handle, $fileData);

            fclose($handle);

            $headers = ['Content-Type' => 'text/'. strtolower($format)];

            return response()->download($path, $fileName .'.'. strtolower($format), $headers)->deleteFileAfterSend(true);
        }

        return back();
    }

    /**
     * Transforms resource data to downloadable file
     *
     * @param Request $request - file format, uri of the resource
     *
     * @return downlodable file
     */
    public function resourceDirectDownload(Request $request)
    {
        $format = $request->offsetGet('format');
        $uri = $request->offsetGet('uri');
        $locale = \LaravelLocalization::getCurrentLocale();

        $params = [
          'resource_uri' => $uri,
          'locale'  => $locale
        ];
        $rq = Request::create('/api/getResourceMetadata', 'POST', $params);
        $api = new ApiResource($rq);
        $res = $api->getResourceMetadata($rq)->getData();
        $resource = !empty($res->resource) ? $res->resource : [];

        $resourceId = $resource->id;
        $fileName = str_replace(['\\', '/'], '_', $resource->name);
        $version = $resource->version;

        $data = ElasticDataSet::getElasticData($resourceId, $version);

        if (strtolower($format) != 'json') {
            $method = 'json2'. strtolower($format);
            $convertReq = Request::create('/api/'. $method, 'POST', ['data' => $data]);
            $apiResources = new ApiConversion($convertReq);
            $resource = $apiResources->$method($convertReq)->getData();

            if (!$resource->success) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.converse_unavailable')));
            }

            $fileData = $resource->data;
        } else {
            $fileData = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        if (!empty($fileData)) {
            $tmpFileName = str_random(32);
            $handle = fopen('../storage/app/'. $tmpFileName, 'w+');
            $path = stream_get_meta_data($handle)['uri'];

            fwrite($handle, $fileData);

            fclose($handle);

            $headers = ['Content-Type' => 'text/'. strtolower($format)];

            return response()->download($path, $fileName .'.'. strtolower($format), $headers)->deleteFileAfterSend(true);
        }

        return back();
    }

    /**
     * Make zip file from dataset resources data
     *
     * @param Request $request - files format, uri of the dataset
     *
     * @return string $uri to download zip file
     */
    public function resourcesPrepareZipForDownload(Request $request)
    {
        $uri = $request->offsetGet('uri');
        $format = $request->offsetGet('format');

        $zipName = "$uri.zip";
        $zipDir = "../storage/app/$uri";
        if(!file_exists($zipDir) && !is_dir($zipDir)) {
          \Log::info("Create folder $zipDir");
          mkdir($zipDir, 0777);
        }

        $params = [
          'criteria' => [
            'dataset_uri' => $uri
          ]
        ];
        $params['records_per_page'] = 100;
        $params['resource_type'] = [1,3]; // TYPE_FILE, TYPE_API

        $rq = Request::create('/api/listResources', 'POST', $params);
        $apiResources = new ApiResource($rq);
        $res = $apiResources->listResources($rq)->getData();
        $resources = !empty($res->resources) ? $res->resources : [];
        //$resources = [];

        if(!empty($resources)) {
          //dd($resources);

          foreach ($resources as $key => $resource) {

            //if($key > 5) continue;

            $resourceId = $resource->id;
            $checkName = (mb_strlen($resource->name) > 130) ? mb_substr($resource->name, 0, 130, "utf-8") : $resource->name;
            $fileName = str_replace(['\\', '/'], '_', $checkName);
            if(file_exists($zipDir.DIRECTORY_SEPARATOR.$fileName)) {
              continue;
            }
            $version = $resource->version;

            $data = ElasticDataSet::getElasticData($resourceId, $version);
            if(empty($data)) {
              continue;
            }

            if (strtolower($format) != 'json') {
              $method = 'json2'. strtolower($format);
              $convertReq = Request::create('/api/'. $method, 'POST', ['data' => $data]);
              $apiResources = new ApiConversion($convertReq);
              $resource = $apiResources->$method($convertReq)->getData();

              if (!$resource->success) {
                return redirect()->back()->withErrors(session()->flash('alert-danger', __('custom.converse_unavailable')));
              }

              $fileData = $resource->data;
            } else {
              $fileData = json_encode($data, JSON_UNESCAPED_UNICODE);
            }

            if (!empty($fileData)) {

              $handle = fopen($zipDir.DIRECTORY_SEPARATOR.$fileName, 'w+');

              fwrite($handle, $fileData);

              fclose($handle);
            }
          }

          if(!file_exists($zipDir.DIRECTORY_SEPARATOR.$zipName)) {

            $files = glob($zipDir.DIRECTORY_SEPARATOR."*");
            $zip = new \ZipArchive;
            if ($zip->open($zipDir.DIRECTORY_SEPARATOR.$zipName, \ZipArchive::CREATE) === TRUE) {
              foreach ($files as $file) {
                $exploded = explode("/", $file);
                $fileName = end($exploded);
                $zip->addFile($file, $fileName.".".strtolower($format));
              }
              $zip->close();
            }
          }

        }

        return $uri;
    }

    /**
     * Download zip file
     *
     * @param Request $request - uri of the dataset
     *
     * @return downlodable zip file
     */
    public function downloadAndDeleteZip(Request $request)
    {
      $uri = $request->offsetGet('uri');
      $zipName = "$uri.zip";
      $zipDir = "../storage/app/$uri";

      if(file_exists($zipDir.DIRECTORY_SEPARATOR.$zipName)) {
        //dd($zipDir.DIRECTORY_SEPARATOR.$zipName ."<br>".basename($zipName)."<br>".filesize($zipDir.DIRECTORY_SEPARATOR.$zipName));
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.basename($zipName));
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        @$filesize = filesize($zipDir.DIRECTORY_SEPARATOR.$zipName);
        if($filesize) header('Content-Length: ' . $filesize);

        if(ob_get_level()){
          ob_end_clean();
        }

        readfile($zipDir.DIRECTORY_SEPARATOR.$zipName);

        $this->deleteZipFolder($uri);
      }

      return redirect()->route('orgViewDataset', [$uri]);
    }

    public function deleteZipFolder($uri)
    {
      $zipDir = "../storage/app/$uri";
      \Log::info("Delete $zipDir");

      if(file_exists($zipDir) && is_dir($zipDir)) {
        if (is_dir($zipDir)) {
          $objects = scandir($zipDir);
          foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
              if (is_dir($zipDir. DIRECTORY_SEPARATOR .$object) && !is_link($zipDir."/".$object))
                rrmdir($zipDir. DIRECTORY_SEPARATOR .$object);
              else
                unlink($zipDir. DIRECTORY_SEPARATOR .$object);
            }
          }
          rmdir($zipDir);
        }
      }
    }

    public function resourceCancelImport(Request $request, $uri, $action)
    {
        if ($action == 'create') {
            // Delete resource metadata record
            $resource = Resource::where('uri', $uri)->first();

            if ($resource) {
                $resource->forceDelete();
            }
        }

        $request->session()->flash('alert-danger', uctrans('custom.cancel_resource_import'));

        return back();
    }

    public function execResourceQueryScript(Request $request)
    {
        $format = $request->format;
        $resourceParams = ['resource_uri' => $request->uri, 'version' => $request->version];

        $rq = Request::create('/api/getResourceData', 'POST', $resourceParams);
        $api = new ApiResource($rq);
        $res = $api->getResourceData($rq)->getData();

        if ($res->success) {
            $data = isset($res->data) ? $res->data : [];

            if ($format == Page::RESOURCE_RESPONSE_CSV) {
                $convertData = ['data' => json_decode(json_encode($data), true)];
                $reqConvert = Request::create('/json2csv', 'POST', $convertData);
                $apiConvert = new ApiConversion($reqConvert);
                $resultConvert = $apiConvert->json2csv($reqConvert)->getData();
                $data = isset($resultConvert->data) ? $resultConvert->data : [];
                $res->success = $resultConvert->success;
            }
        } else {
            $data = isset($res->errors) ? $res->errors : [];
        }

        return [
            'success' => $res->success,
            'data'    => json_encode($data)
        ];
    }
}
