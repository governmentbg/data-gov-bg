<?php

use App\User;
use App\DataSet;
use App\Resource;
use App\ElasticDataSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

function is_xml_excel_exported($xml)
{
    return strpos($xml, '<?mso-application progid="Excel.Sheet"?>') !== false;
}

function request_url($uri, $params = null, $header = null)
{
    $source = config('app.OLD_OD_API_SOURCE');

    $header = ['Authorization: '. config('app.MIGRATE_USER_API_KEY')];

    $requestUrl = $source . $uri;
    $ch = curl_init($requestUrl);

    if ($header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // grab URL and pass it to the browser
    $response = curl_exec($ch);
    $response = json_decode($response,true);
    curl_close($ch);

    return $response;
}

function migrate_datasets($dataSetUri, $convert)
{
    $addedResources = 0;
    $failedResources = 0;
    $failedDatasets = 0;
    $unsuporrtedFormat = 0;

    $params = [
        'id' => $dataSetUri
    ];

    $response = request_url('package_show', $params);

    if ($dataSet = $response['result']) {
        $alreadySaved = DB::table('data_sets')->where('uri', $dataSet['id'])->first();
        $category = 14;

        $tags = [];
        $orgId = null;

        if (isset($dataSet['owner_org'])) {
            $orgId = DB::table('organisations')->where('uri', $dataSet['owner_org'])->value('id');
        }

        $termId = isset($dataSet['license_id'])
            ? map_terms_of_use($dataSet['license_id'])
            : null;

        if (isset($dataSet['tags']) && !empty($dataSet['tags'])) {
            foreach ($dataSet['tags'] as $tag) {
                array_push($tags, $tag['display_name']);
            }

            $category = pick_category($tags);
        }

        if (
            isset($dataSet['author_email'])
            && !filter_var($dataSet['author_email'], FILTER_VALIDATE_EMAIL)
        ) {
            $dataSet['author_email'] = '';
        }

        if (
            isset($dataSet['maintainer_email'])
            && !filter_var($dataSet['maintainer_email'], FILTER_VALIDATE_EMAIL)
        ) {
            $dataSet['maintainer_email'] = '';
        }

        $newData['api_key'] = config('app.MIGRATE_USER_API_KEY');

        $newData['data']['category_id'] = $category;
        $newData['data']['migrated_data'] = true;
        $newData['data']['locale'] = "bg";
        $newData['data']['org_id'] = $orgId;
        $newData['data']['name'] = $dataSet['title'];
        $newData['data']['description'] = $dataSet['notes'];
        $newData['data']['terms_of_use_id'] = $termId;
        $newData['data']['visibility'] = $dataSet['private'] ? DataSet::VISIBILITY_PRIVATE : DataSet::VISIBILITY_PUBLIC;
        $newData['data']['version'] = $dataSet['version'];
        $newData['data']['status'] = ($dataSet['state'] == 'active') ? DataSet::STATUS_PUBLISHED : DataSet::STATUS_DRAFT;
        $newData['data']['author_name'] = $dataSet['author'];
        $newData['data']['author_email'] = $dataSet['author_email'];
        $newData['data']['support_name'] = $dataSet['maintainer'];
        $newData['data']['support_email'] = $dataSet['maintainer_email'];
        $newData['data']['tags'] = $tags;
        $newData['data']['is_migrated'] = true;
        $newData['data']['created_at'] = $dataSet['metadata_created'];
        $newData['data']['updated_by'] = DB::table('users')->where('username', 'migrate_data')->value('id');

        $createdBy = DB::table('users')->where('uri', $dataSet['creator_user_id'])->value('id');
        $newData['data']['created_by'] = isset($createdBy)
            ? $createdBy
            : User::where('username', 'migrate_data')->value('id');

        if ($alreadySaved) {
            $newData['dataset_uri'] = $dataSet['id'];
            $request = Request::create('/api/editDataset', 'POST', $newData);
            $api = new ApiDataSet($request);
            $result = $api->editDataset($request)->getData();
        } else {
            $newData['data']['uri'] = $dataSet['id'];
            $request = Request::create('/api/addDataset', 'POST', $newData);
            $api = new ApiDataSet($request);
            $result = $api->addDataset($request)->getData();
        }

        $resCreatedBy = $newData['data']['created_by'];

        if ($result->success) {
            $datasetInfo = [];

            $newDataSetId = DB::table('data_sets')->where('uri', $dataSet['id'])->value('id');

            //Migrate dataset followers
            $followersInfo = dataset_followers($dataSet['id']);

            if ($dataSet['num_resources'] > 0) {
                $fileFormats = Resource::getAllowedFormats();
                Log::info('Dataset "'. $dataSet['title'] .'" added successfully!');

                // Add resources
                if (isset($dataSet['resources'])) {
                    foreach ($dataSet['resources'] as $resource) {
                        $alternativeFileFormat = '';
                        $alternativeFileFormat = explode('/', $resource['url']);
                        $alternativeFileFormat = substr(strrchr(array_pop($alternativeFileFormat), '.'), 1);

                        $fileFormat = !empty($resource['format'])
                            ? $resource['format']
                            : $alternativeFileFormat;

                        $fileFormat = strtoupper(str_replace('.', '', $fileFormat));

                        if ($fileFormat == 'WORD') {
                            $fileFormat = 'DOC';
                        }

                        $resource['format'] = $fileFormat;
                        $resource['created_by'] = $resCreatedBy;

                        if (in_array($fileFormat, $fileFormats)) {
                            if (migrate_datasets_resources($newDataSetId, $resource, $convert)) {
                                $addedResources++;
                            } else {
                                $failedResources++;
                            }
                        } else {
                            $unsuporrtedFormat++;

                            Log::error('Resource format "'. $fileFormat .'" unsupported.');
                        }

                        unset($resource);
                    }
                }

                $datasetInfo = [
                    'success'               => true,
                    'totalResources'        => $dataSet['num_resources'],
                    'successResources'      => $addedResources,
                    'failedResources'       => $failedResources,
                    'unsuportedResources'   => $unsuporrtedFormat,
                    'followersInfo'         => $followersInfo,
                ];
            }

            return $datasetInfo;
        } else {
            $failedDatasets++;
            Log::error('Dataset "'. $dataSet['title'] .'" with uri(id): "'. $dataSet['id'] .'" failed!');

            return false;
        }

        unset($dataSet);
    }
}

function migrate_datasets_resources($dataSetId, $resourceData, $convert)
{
    $dataSet = DataSet::where('uri', $resourceData['package_id'])->get()->first();
    $alreadySaved = DB::table('resources')
        ->where('data_set_id', $dataSetId)
        ->where('created_by', $dataSet->created_by)
        ->where('created_at', date('Y-m-d H:i:s', strtotime($resourceData['created'])))
        ->get()
        ->first();

    $datasetUri = DB::table('data_sets')->where('id', $dataSetId)->value('uri');
    $newData['api_key'] = config('app.MIGRATE_USER_API_KEY');

    $newData['data']['migrated_data'] = true;
    $newData['data']['locale'] = 'bg';
    $newData['data']['data_set_id'] = $dataSetId;
    $newData['data']['name'] = !empty($resourceData['name']) ? $resourceData['name'] : 'Без име';
    $newData['data']['uri'] = $resourceData['id'];
    $newData['data']['type'] = Resource::TYPE_FILE;
    $newData['data']['url'] = $resourceData['url'];
    $newData['data']['description'] = $resourceData['description'];
    $newData['data']['resource_type'] = null;
    $newData['data']['file_format'] = $resourceData['format'];
    $newData['data']['created_by'] = $resourceData['created_by'];
    $newData['data']['created_at'] = $resourceData['created'];
    $newData['data']['updated_by'] = DB::table('users')->where('username', 'migrate_data')->value('id');

    // get file
    $url = $resourceData['url'];
    $url = explode ('/', $url);
    $url[2] = config('app.OLD_PORTAL_DOMAIN');
    $url = implode('/', $url);

    $path = pathinfo($url);

    if ($path['filename'] == '') {
        $filename = rand() . $path['basename'];
        $url = $path['dirname']. '/' .$filename;
    }

    try {
        $newData['file']['file_content'] = @file_get_contents($url);
        $newData['file']['file_extension'] = isset($path['extension']) ? $path['extension'] : '';
    } catch (Exception $ex) {
        Log::error('Resource get content error: '. $ex->getMessage());

        $newData['file'] = null;
    }

    if (isset($newData['file'])) {
        if ($alreadySaved) {
            // Change uri
            $newResourceURI = $alreadySaved->uri;

            if ($alreadySaved->uri != $resourceData['id']) {
                $changeData['resource_uri'] = $alreadySaved->uri;
                $changeData['data']['uri'] = $resourceData['id'];
                $changeData['data']['migrated_data'] = true;
                $changeRequest = Request::create('/api/editResourceMetadata', 'POST', $changeData);
                $apiEdit = new ApiResource($changeRequest);
                $changeResult = $apiEdit->editResourceMetadata($changeRequest)->getData();

                $newResourceURI = $resourceData['id'];
            }

            $newData['resource_uri'] = $resourceData['id'];
            $request = Request::create('/api/editResourceMetadata', 'POST', $newData);
            $api = new ApiResource($request);
            $result = $api->editResourceMetadata($request)->getData();
        } else {
            $newData['dataset_uri'] = $datasetUri;
            $request = Request::create('/api/addResourceMetadata', 'POST', $newData);
            $api = new ApiResource($request);
            $result = $api->addResourceMetadata($request)->getData();
            $newResourceURI = $resourceData['id'];
        }

        if ($result->success) {
            if (manage_migrated_file($newData['file'], $newResourceURI, $convert)) {
                Log::info('Resource metadata "'. $newData['data']['name'] .'" added successfully!');
                return true;
            } else {
                return false;
            }
        } else {
            Log::error('Resource metadata "'. $newData['data']['name']
                .'" with uri(id): "'. $resourceData['id']
                .'" failed! Parent Dataset id: "'. $dataSetId .'".');
        }
    }

    return false;
}

function manage_migrated_file($fileData, $resourceURI, $convertClosedFormats)
{
    $content = $fileData['file_content'];
    $extension = $fileData['file_extension'];

    if (!empty($extension)) {
        $convertData = [
            'api_key'   => config('app.MIGRATE_USER_API_KEY'),
            'data'      => $content,
        ];

        $extension = strtolower($extension);

        switch ($extension) {
            case 'json':
                $elasticData = $content;

                break;
            case 'tsv':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/tsv2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->tsv2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    $data['tsvData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert->error->message, true));
                }

                break;
            case 'xsd':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/xsd2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->xsd2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    $data['xsdData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert->error->message, true));
                }

                break;
            case 'ods':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/ods2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->ods2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    $data['odsData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert->error->message, true));
                }

                break;
            case 'slk':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/slk2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->slk2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    $data['slkData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert->error->message, true));
                }

                break;
            case 'rtf':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/rtf2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->rtf2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = [$resultConvert->data];
                    $data['rtfData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert->error->message, true));
                }

                break;
            case 'odt':
                $convertData['data'] = base64_encode($convertData['data']);
                $reqConvert = Request::create('/odt2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->odt2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = [$resultConvert->data];
                    $data['odtData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert->error->message, true));
                }

                break;
            case 'csv':
                $reqConvert = Request::create('/csv2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->csv2json($reqConvert)->getData();

                if ($resultConvert->success) {
                    $elasticData = $resultConvert->data;
                    $data['csvData'] = $elasticData;
                } else {
                    Log::error(print_r($resultConvert, true));
                }

                break;
            case 'xml':
                if (($pos = strpos($content, '?>')) !== false) {
                    $trimContent = substr($content, $pos + 2);
                    $convertData['data'] = trim($trimContent);
                } else {
                    Log::error(print_r($resultConvert, true));
                }

                $reqConvert = Request::create('/xml2json', 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->xml2json($reqConvert)->getData(true);
                $elasticData = $resultConvert['data'];
                $data['xmlData'] = $content;

                if ($resultConvert['success']) {;
                    $elasticData = $resultConvert['data'];
                    $data['xmlData'] = $content;
                } else {
                    Log::error(print_r($resultConvert, true));
                }

                break;
            case 'kml':
                $method = $extension .'2json';
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    $elasticData = $resultConvert['data'];
                } else {
                    Log::error(print_r($resultConvert, true));
                }

                break;
            case 'rdf':
                $method = $extension .'2json';
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    $elasticData = $resultConvert['data'];
                    $data['xmlData'] = $content;
                } else {
                    Log::error(print_r($resultConvert, true));
                }

                break;
            case 'xls':
            case 'xlsx':
                try {
                    $method = 'xls2json';
                    $convertData['data'] = base64_encode($convertData['data']);
                    $convertData['data'] = mb_convert_encoding($convertData['data'], 'UTF-8', 'UTF-8');
                    $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                    $api = new ApiConversion($reqConvert);
                    $resultConvert = $api->$method($reqConvert)->getData(true);

                    if ($resultConvert['success']) {
                        $elasticData = $resultConvert['data'];
                        $data['csvData'] = $resultConvert['data'];
                    } else {
                        Log::error(print_r($resultConvert, true));
                    }
                } catch (Exception $ex) {
                    Log::error(print_r($ex->getMessage(),true));
                }

                break;
            case 'doc':
            case 'docx':
                if ($convertClosedFormats) {
                    try {
                        $method = 'doc2json';
                        $convertData['data'] = base64_encode($convertData['data']);
                        $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                        $api = new ApiConversion($reqConvert);
                        $resultConvert = $api->$method($reqConvert)->getData(true);

                        if ($resultConvert['success']) {
                                $elasticData['text'] = $resultConvert['data'];
                                $data['text'] = $resultConvert['data'];
                        } else {
                            Log::error(print_r($resultConvert, true));
                        }
                    } catch (Exception $ex) {
                        Log::error(print_r($ex->getMessage(),true));
                    }
                } else {
                    Log::error(strtoupper($extension) .' is not an open format.');
                }

                break;
            case 'pdf':
                if ($convertClosedFormats) {
                    try {
                        $method = $extension .'2json';
                        $convertData['data'] = base64_encode($convertData['data']);
                        $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                        $api = new ApiConversion($reqConvert);
                        $resultConvert = $api->$method($reqConvert)->getData(true);

                        if ($resultConvert['success']) {
                            $elasticData['text'] = $resultConvert['data'];
                            $data['text'] = $resultConvert['data'];
                        } else {
                            $data['error'] = $resultConvert['error']['message'];
                        }
                    } catch (Exception $ex) {
                        Log::error(print_r($ex->getMessage(),true));
                    }
                } else {
                    Log::error(strtoupper($extension) .' is not an open format.');
                }

                break;
            case 'txt':
                $elasticData['text'] = $convertData['data'];
                $data['text'] = $convertData['data'];

                break;
            default:
                $method = 'img2json';
                $convertData['data'] = base64_encode($convertData['data']);
                $convertData['data'] = mb_convert_encoding($convertData['data'], 'UTF-8', 'UTF-8');
                $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                $api = new ApiConversion($reqConvert);
                $resultConvert = $api->$method($reqConvert)->getData(true);

                if ($resultConvert['success']) {
                    $elasticData['text'] = $resultConvert['data'];
                    $data['text'] = $resultConvert['data'];
                }
        }

        $resource = Resource::where('uri',$resourceURI)->first();
        $lastVersionElds = ElasticDataSet::where('resource_id', $resource->id)->max('version');

        $alreadyExists = ElasticDataSet::getElasticData($resource->id, $lastVersionElds);

        if (!empty($elasticData)) {
            $saveData = [
                'resource_uri'  => $resourceURI,
                'data'          => $elasticData,
            ];

            if ($alreadyExists) {
                $reqElastic = Request::create('/updateResourceData', 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->updateResourceData($reqElastic)->getData();
            } else {
                $reqElastic = Request::create('/addResourceData', 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->addResourceData($reqElastic)->getData();
            }

            unset($elasticData, $saveData);
            gc_collect_cycles();

            if ($resultElastic->success) {
                Log::info('Resource with uri(id): "'. $resourceURI .'" added successfully to elastic!');

                return true;
            } else {
                // Delete resource metadata record if there are errors
                Log::error('Resource with uri(id): "'. $resourceURI .'" failed on adding in elastic!');

                if ($resource && !$alreadyExists) {
                    $resource->forceDelete();
                    Log::warning('Remove resource metadata with uri(id): "'. $resourceURI .'"');
                }

                return false;
            }
        } else {
            // Delete resource metadata record if there are errors
            Log::error('Resource with uri(id): "'. $resourceURI .'" failed on adding in elastic!');

            if ($resource && !$alreadyExists) {
                $resource->forceDelete();
                Log::warning('Remove resource metadata with uri(id): "'. $resourceURI .'"');
            }
        }

        return false;
    }
}

function pick_category($tags)
{
    $categories = [
        '1'     => 0,  // 1  => 'Селско стопанство, риболов и аква култури, горско стопанство, храни',
        '2'     => 0,  // 2  => 'Образование, култура и спорт',
        '3'     => 0,  // 3  => 'Околна среда',
        '4'     => 0,  // 4  => 'Енергетика',
        '5'     => 0,  // 5  => 'Транспорт',
        '6'     => 0,  // 6  => 'Наука и технологии',
        '7'     => 0,  // 7  => 'Икономика и финанси',
        '8'     => 0,  // 8  => 'Население и социални условия',
        '9'     => 0,  // 9  => 'Правителство, публичен сектор',
        '10'    => 0,  // 10 => 'Здравеопазване',
        '11'    => 0,  // 11 => 'Региони, градове',
        '12'    => 0,  // 12 => 'Правосъдие, правна система, обществена безопасност',
        '13'    => 0,  // 13 => 'Международни въпроси',
        '14'    => 0,  // 14 => 'Некатегоризирани'
    ];

    foreach($tags as $tag) {
        $tag = mb_strtolower($tag, 'UTF-8');

        switch ($tag) {
            case strpos($tag, 'животн'):
            case strpos($tag, 'храни'):
            case strpos($tag, 'горс'):
            case strpos($tag, 'горa'):
            case strpos($tag, 'стопанс'):
            case strpos($tag, 'аренд'):
            case strpos($tag, 'земедел'):
            case strpos($tag, 'аренд'):
            case strpos($tag, 'извор'):
            case strpos($tag, 'селско'):
            case strpos($tag, 'кладен'):
            case strpos($tag, 'рент'):
            case strpos($tag, 'комбайн'):
            case strpos($tag, 'стопанск'):
            case strpos($tag, 'язовир'):
            case strpos($tag, 'язовирите'):
            case strpos($tag, 'нив'):
            case strpos($tag, 'лесоустройство'):
            case strpos($tag, 'лов'):
            case strpos($tag, 'мери'):
            case strpos($tag, 'минералн'):
            case strpos($tag, 'паша'):
            case strpos($tag, 'пасища'):
            case strpos($tag, 'пчел'):
            case 'renta':
            case 'agriculture':
            case 'мери и пасища':
            case 'zemedelska tehnika':
            case 'агростатистика':
            case 'земеделска и горска техника':
                $categories['1']++; // Селско стопанство, риболов и аква култури, горско стопанство, храни

                break;
            case strpos($tag, 'гимназии'):
            case strpos($tag, 'образование'):
            case strpos($tag, 'кандидатства'):
            case strpos($tag, 'училищ'):
            case strpos($tag, 'читалищ'):
            case strpos($tag, 'ученици'):
            case strpos($tag, 'ясли'):
            case strpos($tag, 'детски'):
            case strpos($tag, 'ученик'):
            case strpos($tag, 'стипенд'):
            case strpos($tag, 'оцен'):
            case strpos($tag, 'клас'):
            case strpos($tag, 'изпит'):
            case strpos($tag, 'градини'):
            case strpos($tag, 'култур'):
            case strpos($tag, 'спорт'):
            case strpos($tag, 'футбол'):
            case strpos($tag, 'карате'):
            case strpos($tag, 'атлетика'):
            case strpos($tag, 'паметни'):
            case strpos($tag, 'резултат'):
            case strpos($tag, 'турист'):
            case strpos($tag, 'turizum'):
            case strpos($tag, 'ministerstvo na turizma'):
            case strpos($tag, 'tourism'):
            case strpos($tag, 'училищата'):
            case strpos($tag, 'туризъм'):
            case strpos($tag, 'нво'):
            case strpos($tag, 'дзи'):
            case 'академични длъжности':
            case 'военни':
            case 'средно образование':
            case 'план прием':
            case 'лека атлетика':
            case 'паметници на културата':
                $categories['2']++; // Образование, култура и спорт

                break;
            case strpos($tag, 'вуздух'):
            case strpos($tag, 'въглероден'):
            case strpos($tag, 'диоксид'):
            case strpos($tag, 'дървета'):
            case strpos($tag, 'отпадъци'):
            case strpos($tag, 'прахови'):
            case strpos($tag, 'отпадъ'):
            case strpos($tag, 'битови'):
            case strpos($tag, 'замърсяване'):
            case strpos($tag, 'води'):
            case strpos($tag, 'метролог'):
            case strpos($tag, 'еколог'):
            case strpos($tag, 'околна'):
            case strpos($tag, 'пречиств'):
            case strpos($tag, 'разделно'):
            case strpos($tag, 'хартия'):
            case strpos($tag, 'картон'):
            case strpos($tag, 'пластмас'):
            case strpos($tag, 'пунктове'):
            case strpos($tag, 'риосв'):
            case strpos($tag, 'атмосферен'):
            case 'агенция по геодезия':
            case 'атмосферен въздух':
            case 'площадки отпадъци':
            case 'битови отпадъци':
            case 'фини прахови частици':
            case 'фпч':
            case 'въглероден оксид':
                 $categories['3']++; // Околна среда

                 break;
            case strpos($tag, 'електромери'):
            case strpos($tag, 'белене'):
                $categories['4']++; // Eнергетика

                break;
            case strpos($tag, 'автобус'):
            case strpos($tag, 'транспорт'):
            case strpos($tag, 'инфраструктур'):
            case strpos($tag, 'летищ'):
            case strpos($tag, 'маршрут'):
            case strpos($tag, 'линии'):
            case strpos($tag, 'мпс'):
            case strpos($tag, 'път'):
            case strpos($tag, 'превоз'):
            case strpos($tag, 'железопътен'):
            case strpos($tag, 'влак'):
            case strpos($tag, 'такси'):
            case strpos($tag, 'разписан'):
            case strpos($tag, 'маршрутната'):
            case 'моторни-превозни средства':
            case 'автогара':
            case 'автомобил':
                $categories['5']++; // Tранспорт

                break;
            case strpos($tag, 'изследвания'):
            case strpos($tag, 'експерименти'):
            case strpos($tag, 'авторски'):
            case strpos($tag, 'информацион'):
            case strpos($tag, 'нау'):
            case 'Hackathon':
            case 'авторски права':
                $categories['6']++; // Наука и технологии

                break;
            case strpos($tag, 'икономическ'):
            case strpos($tag, 'финанс'):
            case strpos($tag, 'кредит'):
            case strpos($tag, 'данъчн'):
            case strpos($tag, 'потребление'):
            case strpos($tag, 'търговия'):
            case strpos($tag, 'данъчн'):
            case strpos($tag, 'икономи'):
            case strpos($tag, 'бюджет'):
            case strpos($tag, 'себра'):
            case strpos($tag, 'дарен'):
            case 'SEBRA':
                $categories['7']++; // Икономика и финанси

                break;
            case strpos($tag, 'пенсии'):
            case strpos($tag, 'осигуряване'):
            case strpos($tag, 'обезщетения'):
            case strpos($tag, 'безработица'):
            case strpos($tag, 'настаняване'):
            case strpos($tag, 'заетост'):
            case strpos($tag, 'социал'):
            case strpos($tag, 'инвалид'):
            case strpos($tag, 'Професионална'):
            case strpos($tag, 'увреждан'):
            case strpos($tag, 'увреждания'):
            case 'census':
            case 'хора с увреждания':
            case 'Агенция за хората с увреждания специализирани предприятия':
            case 'Агенция по заетостта':
            case 'Бюро по труда':
                $categories['8']++; // Население и социални условия

                break;
            case strpos($tag, 'избори'):
            case strpos($tag, 'elections'):
            case strpos($tag, 'законодателство'):
            case strpos($tag, 'гласуване'):
            case strpos($tag, 'изборни'):
            case strpos($tag, 'политически'):
            case strpos($tag, 'народ'):
            case strpos($tag, 'референдум'):
            case strpos($tag, 'президент'):
            case strpos($tag, 'разрешител'):
            case strpos($tag, 'цик'):
            case 'proekti':
                $categories['9']++; // Правителство, публичен сектор

                break;
            case strpos($tag, 'зъбо'):
            case strpos($tag, 'аптеки'):
            case strpos($tag, 'лекар'):
            case strpos($tag, 'дрогерии'):
            case strpos($tag, 'помощ'):
            case strpos($tag, 'дентал'):
            case strpos($tag, 'медицин'):
            case strpos($tag, 'фармацевтич'):
            case strpos($tag, 'хоспис'):
            case strpos($tag, 'болести'):
            case strpos($tag, 'боличн'):
            case strpos($tag, 'здрав'):
            case strpos($tag, 'лечеб'):
            case strpos($tag, 'медико'):
            case 'здравеопазване':
            case 'магистър-фармацевти':
                $categories['10']++; // Здравеопазване

                break;
            case strpos($tag, 'общин'):
            case strpos($tag, 'област'):
            case strpos($tag, 'домашни'):
            case strpos($tag, 'градоустройств'):
            case strpos($tag, 'бездомни'):
            case strpos($tag, 'регион'):
            case strpos($tag, 'география'):
            case strpos($tag, 'безстопанствени'):
            case strpos($tag, 'кадаст'):
            case strpos($tag, 'населени'):
            case strpos($tag, 'общинска'):
            case strpos($tag, 'кмет'):
            case strpos($tag, 'столич'):
            case strpos($tag, 'общ.'):
            case strpos($tag, 'обл.'):
            case strpos($tag, 'обс'):
            case strpos($tag, 'община'):
                $categories['11']++; // Региони, градове, общини

                break;
            case strpos($tag, 'юрист'):
            case strpos($tag, 'юридически'):
            case strpos($tag, 'правни'):
            case strpos($tag, 'право'):
            case strpos($tag, 'закон'):
            case strpos($tag, 'убийств'):
            case strpos($tag, 'престъпления'):
            case 'prestupleniya_sreshtu_lichnostta _sobstvenostta_ikonomicheski':
                $categories['12']++; // Правосъдие, правна система, обществена безопасност

                break;
            case strpos($tag, 'европ'):
            case strpos($tag, 'ЕС'):
            case strpos($tag, 'досиета'):
            case strpos($tag, 'нато'):
            case strpos($tag, 'война'):
            case strpos($tag, 'военно оборудване'):
            case strpos($tag, 'международни споразумения'):
            case strpos($tag, 'външна политика'):
            case strpos($tag, 'международ'):
            case 'european elections':
                $categories['13']++; // Международни въпроси

                break;
        }
    }

    $categoriesIndex = array_flip($categories);
    $selectedCategory = $categoriesIndex[max($categories)];

    if (max($categories) == 0) {
        $selectedCategory = 14;
    }

    return $selectedCategory;
}

function map_terms_of_use($oldLicense)
{
    $licenses = [
        'cc-zero'   => 1,  // Условия за предоставяне на информация без защитени авторски права.
        'cc-by'     => 2,  // Условия за предоставяне на произведение за повторно използване. Признаване на авторските права.
        'cc-by-sa'  => 3,  // Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Споделяне на споделеното.
    ];

    $newTerm = isset($licenses[$oldLicense]) ? $licenses[$oldLicense] : null;

    return $newTerm;
}

function dataset_followers($datasetURI)
{
    $apiKey = config('app.MIGRATE_USER_API_KEY');
    $followers = [];

    $params = [
        'id' => $datasetURI
    ];
    $response = request_url('dataset_follower_list', $params);
    $datasetId = DB::table('data_sets')->where('uri', $datasetURI)->value('id');

    if (isset($response['result']) && !empty($response['result'])) {
        foreach ($response['result'] as $res) {
            $user = DB::table('users')->where('uri', $res['id'])->value('id');

            if ($user) {
                $dsFollowExists = DB::table('user_follows')->where('user_id', $user)
                    ->where('data_set_id', $datasetId)
                    ->first();

                if ($dsFollowExists) {
                    continue;
                }

                $newDataSetFollow['api_key'] = $apiKey;
                $newDataSetFollow['user_id'] = $user;
                $newDataSetFollow['data_set_id'] = $datasetId;

                $dataSetReq = Request::create('/api/addFollow', 'POST', $newDataSetFollow);
                $api = new ApiFollow($dataSetReq);
                $api->addFollow($dataSetReq)->getData();
            }

            continue;
        }
        $addedFollowers = DB::table('user_follows')->where('data_set_id', $datasetId)->count();

        $followers = [
            'totalFollowers' => count($response['result']),
            'successFollowers' => $addedFollowers
        ];
    } else {
        $followers = [
            'error_msg' => 'No followers for this dataset were found.',
        ];
    }

    return $followers;
}
