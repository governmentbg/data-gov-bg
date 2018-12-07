<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\User;
use App\DataSet;
use App\Resource;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\ConversionController as ApiConversion;

class DataRepair extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repair:migratedData {direction} {source?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair migrated data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->info('Data migration repair has started.');
            $this->line('');

            if ($this->argument('direction') == 'up') {
                if ($this->argument('source') == null) {
                    $this->error('No source given.');
                    $this->error('Repair migrated data failed!');
                } else {
                    $this->up();
                    $this->info('Repair migrated data finished successfully!');
                }
            } else {
                $this->error('No direction given.');
            }
        } catch (\Exception $ex) {
            $this->error('Repair migrated data failed!');
            Log::error(print_r($ex->getMessage(), true));

            $this->up();
        }
    }

    private function up()
    {
        gc_enable();
        $migrateUserId = User::where('username', 'migrate_data')->value('id');
        \Auth::loginUsingId($migrateUserId);

        ini_set('memory_limit', '8G');

        $this->repairBrokenDatasets();
    }

    private function requestUrl($uri, $params = null, $header = null)
    {
        $requestUrl = $this->argument('source'). $uri;
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

    private function repairBrokenDatasets()
    {
        $brokenDatasets = DB::table('data_sets')
            ->select('data_sets.uri')
            ->whereNotIn('data_sets.id',
                DB::table('resources')
                ->rightJoin('elastic_data_set', 'resources.id', '=', 'elastic_data_set.resource_id')
                ->get()
                ->pluck('data_set_id')
            )->get();

        $brokenResources = DB::table('resources')
            ->whereNotIn('id', DB::table('elastic_data_set')->get()->pluck('resource_id'))
            ->get()
            ->pluck('id');

        $this->line('Datasets that do not have resource or have resources without indexes in elastic table are '. count($brokenDatasets));
        $this->line('Broken resource are '. count($brokenResources));

        if ($this->confirm('You are going to delete all resources for which there are no indexes in elastic. Are you sure?')) {
            error_log('confirmed');
            if (isset($brokenResources)) {
                Resource::whereIn('id', $brokenResources)->forceDelete();
                $fileFormats = Resource::getAllowedFormats();

                foreach ($brokenDatasets as $dataset) {
                    $addedResources = 0;
                    $failedResources = 0;
                    $unsuporrtedFormat = 0;
                    $dataSetInfo = DataSet::where('uri', $dataset->uri)->first();

                    $params = [
                        'id' => $dataset->uri
                    ];

                    $response = $this->requestUrl('package_show', $params);
                    error_log(var_export($dataset->uri,true));

                    if ($response['success'] && $response['result']['num_resources'] > 0) {
                        $dataSetResources = isset($response['result']['resources'])
                            ? $response['result']['resources']
                            : [];

                        foreach ($dataSetResources as $resource) {
                            $savedResource = Resource::where('uri', $resource['id'])->first();
                            $fileFormat = strtoupper(str_replace('.', '', $resource['format']));
                            $resource['created_by'] = $dataSetInfo->created_by;

                            if ($savedResource) {
                                continue;
                            }

                            if (in_array($fileFormat, $fileFormats)) {
                                Log::info('Resource format "'. $fileFormat);
                                if ($this->migrateDatasetsResources($dataSetInfo->id, $resource)) {
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
                }
            }

        } else {
            $this->line('Command is aborted!');
        }
    }
    private function migrateDatasetsResources($dataSetId, $resourceData)
    {
        $datasetUri = DataSet::where('id', $dataSetId)->value('uri');
        $newData['api_key'] = config('app.MIGRATE_USER_API_KEY');
        $newData['dataset_uri'] = $datasetUri;

        $newData['data']['migrated_data'] = true;
        $newData['data']['locale'] = "bg";
        $newData['data']['data_set_id'] = $dataSetId;
        $newData['data']['name'] = !empty($resourceData['name']) ? $resourceData['name'] : 'Без име';
        $newData['data']['uri'] = $resourceData['id'];
        $newData['data']['type'] = Resource::TYPE_FILE;
        $newData['data']['url'] =  $resourceData['url'];
        $newData['data']['description'] = $resourceData['description'];
        $newData['data']['resource_type'] = null;
        $newData['data']['file_format'] = $resourceData['format'];
        $newData['data']['created_by'] = $resourceData['created_by'];
        $newData['data']['created_at'] = $resourceData['created'];
        $newData['data']['updated_by'] = User::where('username', 'migrate_data')->value('id');

        // get file
        $path = pathinfo($resourceData['url']);
        $url = $resourceData['url'];

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
            $request = Request::create('/api/addResourceMetadata', 'POST', $newData);
            $api = new ApiResource($request);
            $result = $api->addResourceMetadata($request)->getData();

            if ($result->success) {
                $newResourceId = Resource::where('uri', $result->data->uri)->value('id');

                $resourceIds['success'][$result->data->uri] = $newResourceId;

                if ($this->manageMigratedFile($newData['file'], $result->data->uri)) {
                    Log::info('Resource metadata "'. $newData['data']['name'] .'" added successfully!');
                    return true;
                } else {
                    return false;
                }
            } else {
                $resourceIds['error'] = $result->errors;
                Log::error('Resource metadata "'. $newData['data']['name']
                        .'" with id: "'. $resourceData['id']
                        .'" failed! Parent Dataset id: "'. $dataSetId .'".');
            }
        }

        return false;
    }

    private function manageMigratedFile($fileData, $resourceURI)
    {
        $content = $fileData['file_content'];
        $extension = $fileData['file_extension'];

        if (!empty($extension)) {
            $convertData = [
                'api_key'   => config('app.MIGRATE_USER_API_KEY'),
                'data'      => $content,
            ];

            switch ($extension) {
                case 'json':
                    $elasticData = $content;

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

                    break;
                case 'pdf':
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

                    break;
                case 'txt':
                    $elasticData['text'] = $resultConvert['data'];
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

            if (!empty($elasticData)) {
                $saveData = [
                    'resource_uri'  => $resourceURI,
                    'data'          => $elasticData,
                ];

                $reqElastic = Request::create('/addResourceData', 'POST', $saveData);
                $api = new ApiResource($reqElastic);
                $resultElastic = $api->addResourceData($reqElastic)->getData();

                unset($elasticData, $saveData);
                gc_collect_cycles();

                if ($resultElastic->success) {
                    Log::info('Resource with id: "'. $resourceURI .'" added successfully to elastic!');

                    return true;
                } else {
                    // Delete resource metadata record if there are errors
                    $resource = Resource::where('uri', $resourceURI)->first();
                    Log::error('Resource with id: "'. $resourceURI.'" failed on adding in elastic!');

                    if ($resource) {
                        $resource->forceDelete();
                        Log::warning('Remove resource metadata with id: "'. $resourceURI.'"');
                    }

                    return false;
                }
            }

            return false;
        }
    }
}
