<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Tags;
use App\DataSet;
use App\Category;
use App\Resource;
use App\TermsOfUse;
use App\UserFollow;
use App\DataSetTags;
use App\UserSetting;
use App\Organisation;
use App\UserToOrgRole;
use App\Http\Controllers\Api\TagController as ApiTags;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\CategoryController as ApiCategories;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;
use App\Http\Controllers\Api\CustomSettingsController as ApiCustomSettings;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:data {direction} {source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            $this->info('Data migration has started.');
            $this->line('');

            if ($this->argument('direction') == 'up') {
                if ($this->argument('source') == null) {
                    $this->error('No source given.');
                } else {
                    $this->up();
                }
            } else if ($this->argument('direction') == 'down') {
                $this->down();
            } else {
                $this->error('No direction given.');
            }

            $this->info('Data migration finished successfully!');
        } catch (\Exception $ex) {
            $this->error('Data migration failed!');
            Log::error($ex->getMessage());
        }
    }

    private function up()
    {
        $migrateUserId = User::where('username', 'migrate_data')->value('id');
        \Auth::loginUsingId($migrateUserId);

        ini_set('memory_limit','4095M');

        $tagsData = $this->migrateTags();
        $termsData = $this->migrateLicense();
        $organisationData = $this->migrateOrganisations();

        $groupData = $this->migrateGroups();
        $userData = $this->migrateUsers();
        $dataSetsData = $this->migrateDatasets($userData, $organisationData['org_with_dataSets'], $termsData);

        $this->migrateUserToOrgRole($userData['users'], $organisationData['users_to_org_role']);
        $this->migrateFollowers($userData['users']);
    }

    private function down()
    {
        $migrateUser = User::where('username', 'migrate_data')->value('id');
        $users = User::where('created_by', $migrateUser)->get();

        foreach ($users as $user) {
            $userIds[] = $user->id;
        }

        $organisations = Organisation::where('created_by', $migrateUser)->get();

        foreach ($organisations as $org) {
            $orgIds[] = $org->id;
        }

        $dataSets = DataSet::whereIn('created_by', $userIds)->get();

        foreach ($dataSets as $dataSet) {
            $dataSetIds[] = $dataSet->id;
        }

        $tags = Tags::where('created_by', $migrateUser)->get();

        foreach ($tags as $tag) {
            $tagsIds[] = $tag->id;
        }

        $termsOfUse = TermsOfUse::where('created_by', $migrateUser)->get();

        foreach ($termsOfUse as $term) {
            $termsIds[] = $term->id;
        }

        if (isset($dataSetIds)) {
            Resource::whereIn('data_set_id', $dataSetIds)->forceDelete();
            DataSetTags::whereIn('data_set_id', $dataSetIds)->delete();
            UserFollow::whereIn('data_set_id', $dataSetIds)->delete();
        }

        if (isset($userIds)) {
            UserToOrgRole::whereIn('user_id', $userIds)->delete();
            UserFollow::whereIn('user_id', $userIds)->delete();
            UserSetting::whereIn('user_id', $userIds)->delete();
            DataSet::whereIn('created_by', $userIds)->forceDelete();
            User::whereIn('id', $userIds)->forceDelete();
        }

        if (isset($orgIds)) {
            UserToOrgRole::whereIn('org_id', $orgIds)->delete();
            UserFollow::whereIn('org_id', $orgIds)->delete();
            Organisation::whereIn('id', $orgIds)->forceDelete();
        }

        if (isset($tagsIds)) {
            Tags::whereIn('id', $tagsIds)->delete();
        }

        if (isset($termsIds)) {
            TermsOfUse::whereIn('id', $termsIds)->delete();
        }
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

    private function migrateTags()
    {
        $migrateUser = User::where('username', 'migrate_data')->get();
        $tags = [];

        $params = [
            'all_fields' => true
        ];
        $response = $this->requestUrl('tag_list', $params);

        if (!empty($response['result'])) {
            $tagsIds = [];

            foreach ($response['result'] as $res) {
                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                $newData['data']['migrated_data'] = true;
                $newData['data']['name'] = $res['display_name'];
                $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addGroup', 'POST', $newData);
                $api = new ApiTags($request);
                $result = $api->addTag($request)->getData();

                if ($result->success) {
                    $tagsIds['success'][$res['id']] = $result->id;
                } else {
                    $tagsIds['error'][$res['id']] = $res['display_name'];
                }
            }

            $tags = $tagsIds;
        }

        $this->line('Tags total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Tags successful: '. (isset($tags['success']) ? count($tags['success']) : '0'));
        $this->error('Tags failed: '.(isset($tags['error']) ? count($tags['error']) : '0'));
        $this->line('');

        return $tags;
    }

    private function migrateLicense()
    {
        $migrateUser = User::where('username', 'migrate_data')->get();
        $terms = [];

        $params = [
            'all_fields' => true
        ];
        $response = $this->requestUrl('license_list', $params);

        if (!empty($response['result'])) {
            $licensesIds = [];

            foreach ($response['result'] as $res) {
                $is_default = false;

                switch ($res['is_generic']) {
                    case 'True':
                        $is_default = true;

                        break;
                    default:
                        $is_default = false;
                }

                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                $newData['data']['migrated_data'] = true;
                $newData['data']['locale'] = "BG";
                $newData['data']['name'] = $res['title'];
                $newData['data']['description'] = !empty($res['url']) ? $res['url'] : $res['title'];
                $newData['data']['is_default'] = $is_default;
                $newData['data']['active'] = $res['status'] == 'active' ? true : false;
                $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addTermsOfUse', 'POST', $newData);
                $api = new ApiTermsOfUse($request);
                $result = $api->addTermsOfUse($request)->getData();

                if ($result->success) {
                    $licensesIds['success'][$res['id']] = $result->id;
                } else {
                    $licensesIds['error'][$res['id']] = $res['title'];
                }
            }

            $terms = $licensesIds;
        }

        $this->line('Terms of use total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Terms of use successful: '. (isset($terms['success']) ? count($terms['success']) : '0'));
        $this->error('Terms of use failed: '.(isset($terms['error']) ? count($terms['error']) : '0'));
        $this->line('');

        return $terms;
    }

    private function migrateOrganisations()
    {
        $migrateUser = User::where('username', 'migrate_data')->get();
        $organisationData = [];

        $params = [
            'all_fields'    => true,
            'include_users' => true,
        ];
        $response = $this->requestUrl('organization_list', $params);
        $numPackageFromOrgs = 0;

        if (!empty($response['result'])) {
            $orgIds = [];
            $usersToOrgRole = [];
            $orgWithDataSets = [];

            foreach ($response['result'] as $res) {
                $type = 0;

                switch ($res['type']) {
                    case 'organization':
                        $type = Organisation::TYPE_COUNTRY;

                        break;
                    case 'group':
                        $type = Organisation::TYPE_GROUP;

                        break;
                    default:
                        $type = Organisation::TYPE_CIVILIAN;
                }

                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                $newData['data']['migrated_data'] = true;
                $newData['data']['locale'] = 'BG';
                $newData['data']['type'] = $type;
                $newData['data']['logo'] = !empty($res['image_display_url']) ? $res['image_display_url'] : null;
                $newData['data']['name'] = $res['display_name'];
                $newData['data']['description'] = $res['description'];
                $newData['data']['uri'] = $res['id'];
                $newData['data']['active'] = $res['state'] == 'active' ? Organisation::ACTIVE_TRUE : Organisation::ACTIVE_FALSE;
                $newData['data']['approved'] = $res['approval_status'] == 'approved' ? Organisation::APPROVED_TRUE : Organisation::APPROVED_FALSE;
                $newData['data']['created_at'] = $res['created'];
                $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addOrganisation', 'POST', $newData);
                $api = new ApiOrganisation($request);
                $result = $api->addOrganisation($request)->getData();

                if ($result->success) {
                    $newOrgId = $result->org_id;

                    $numPackageFromOrgs += $res['package_count'];

                    if ($res['package_count'] > 0) {
                        $orgWithDataSets[$res['id']] = $newOrgId;
                    }

                    if (isset($res['users'])) {
                        foreach ($res['users'] as $user) {
                            $usersToOrgRole[$newOrgId][] = $user;
                        }
                    }

                    $orgIds['success'][$res['id']] = $newOrgId;
                } else {
                    $orgIds['error'][$res['id']] = $res['display_name'];
                }
            }

            $orgs = $orgIds;
        }

        $this->line('Organisations total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Organisations successful: '. (isset($orgs['success']) ? count($orgs['success']) : '0'));
        $this->error('Organisations failed: '.(isset($orgs['error']) ? count($orgs['error']) : '0'));
        $this->line('Organisations` dataset total: '. $numPackageFromOrgs);
        $this->line('User to org role: '. count($usersToOrgRole));
        $this->line('');

        $organisationData = [
            'organisations'     => $orgs,
            'users_to_org_role' => $usersToOrgRole,
            'org_with_dataSets' => $orgWithDataSets,
        ];

        return $organisationData;
    }

    private function migrateGroups()
    {
        $migrateUser = User::where('username', 'migrate_data')->get();
        $groups = [];
        $groupsWithDataSets = [];

        $params = [
            'all_fields' => true
        ];
        $response = $this->requestUrl('group_list', $params);

        if (!empty($response['result'])) {
            $groupIds = [];
            $usersToGroupRole = [];

            foreach ($response['result'] as $res) {
                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                $newData['data']['migrated_data'] = true;
                $newData['data']['locale'] = 'BG';
                $newData['data']['type'] = Organisation::TYPE_GROUP;
                $newData['data']['logo'] = !empty($res['image_display_url']) ? $res['image_display_url'] : null;

                if (!empty ($newData['data']['logo'])) {
                    $newData['data']['migrated_data'] = true;
                }

                $newData['data']['name'] = $res['display_name'];
                $newData['data']['descript'] = $res['description'];
                $newData['data']['uri'] = $res['id'];
                $newData['data']['active'] = $res['state'] == 'active' ? Organisation::ACTIVE_TRUE : Organisation::ACTIVE_FALSE;
                $newData['data']['approved'] = $res['approval_status'] == 'approved' ? Organisation::APPROVED_TRUE : Organisation::APPROVED_FALSE;
                $newData['data']['created_at'] = $res['created'];
                $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addGroup', 'POST', $newData);
                $api = new ApiOrganisation($request);
                $result = $api->addGroup($request)->getData();

                if ($result->success) {
                    $newGroupId = $result->id;

                    if ($res['package_count'] > 0) {
                        $groupsWithDataSets[$res['id']] = $newGroupId;
                    }

                    if (isset($res['users']) && count($res['users']) > 0) {
                        foreach ($res['users'] as $user) {
                            $usersToGroupRole[$newGroupId][] = $user;
                        }
                    }

                    $groupIds['success'][$res['id']] = $newGroupId;
                } else {
                    $groupIds['error'][$res['id']] = $res['display_name'];
                }
            }

            $groups = $groupIds;
        }

        $this->line('Groups total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Groups successful: '. (isset($groups['success']) ? count($groups['success']) : '0'));
        $this->error('Groups failed: '.(isset($groups['error']) ? count($groups['error']) : '0'));
        $this->line('');

        $groupsData = [
            'groups'                => $groups,
            'users_to_group_role'   => $usersToGroupRole,
            'groups_with_dataSets'  => $groupsWithDataSets,
        ];

        return $groupsData;
    }

    private function migrateUsers()
    {
        $userData = [];

        $migrateUser = User::where('username', 'migrate_data')->get();
        $header = array();
        $header[] = 'Authorization: '. User::where('username', 'migrate_data')->value('api_key');
        $params = [
            'all_fields' => true
        ];

        $numPackage = 0;
        $response = $this->requestUrl('user_list', $params, $header);

        if (!empty($response['result'])) {
            $userIds = [];
            $usersWithDataSets = [];

            foreach ($response['result'] as $res) {
                //manage names
                if (strpos($res['display_name'] , ' ') !== false) {
                    $names = explode(" ", $res['display_name']);
                    $fname = $names[0];

                    if (isset($names[2])) {
                        $lname = $names[2];
                    } else if ($names[1]) {
                        $lname = $names[1];
                    }
                } else if (strpos( $res['display_name'] , '_') !== false) {
                    $names = explode("_", $res['display_name']);
                    $fname = $names[0];

                    if (isset($names[2])) {
                        $lname = $names[2];
                    } else if ($names[1]) {
                        $lname = $names[1];
                    }
                } else {
                    $fname = $res['display_name'];
                    $lname = 'No Name';
                }

                $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                $password = substr(str_shuffle($chars),0,16);

                $newData['data']['migrated_data'] = true;
                $newData['data']['firstname'] = ucfirst($fname);
                $newData['data']['lastname'] = ucfirst($lname);
                $newData['data']['username'] = $res['name'];
                $newData['data']['email'] = $res['email'];
                $newData['data']['password'] = $password;
                $newData['data']['password_confirm'] = $password;
                $newData['data']['add_info'] = $res['about'];
                $newData['data']['is_admin'] = $res['sysadmin'];
                $newData['data']['active'] = ($res['state'] == 'active') ? true : false;
                $newData['data']['invite'] = 1;
                $newData['data']['created_at'] = $res['created'];
                $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addUser', 'POST', $newData);
                $api = new ApiUser($request);
                $result = $api->addUser($request)->getData();

                if ($result->success) {
                    $newUserId = User::where('api_key', $result->api_key)->value('id');
                    $numPackage += $res['number_created_packages'];

                    if ($res['number_created_packages'] > 0) {
                        $usersWithDataSets[$res['id']] = $newUserId;
                    }

                    $userIds['success'][$res['id']] = $newUserId;
                } else {
                    $userIds['error'][$res['id']] = $res['email'];
                }
            }

            $users = $userIds;
        }

        $this->line('Users total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Users successful: '. (isset($users['success']) ? count($users['success']) : '0'));
        $this->error('Users failed: '.(isset($users['error']) ? count($users['error']) : '0'));
        $this->line('Users` dataset total count: '. $numPackage);
        $this->line('Users with data sets '. count($usersWithDataSets));
        $this->line('');

        $userData = [
            'users'                 => $users,
            'users_with_dataSets'   => $usersWithDataSets,
        ];

        return $userData;
    }

    private function getUsersDatasets($usersWithDataSets)
    {
        if (is_array($usersWithDataSets)) {
            $userDataSets = [];

            foreach ($usersWithDataSets as $k => $v) {
                $params = [
                    'id'                => $k,
                    'include_datasets'  => true
                ];
                $response = $this->requestUrl('user_show', $params);

                foreach ($response['result']['datasets'] as $res) {
                    $userDataSets[] = $res;
                }
            }
        }

        return $userDataSets;
    }

    private function getOrgsDatasets($orgWithDataSets)
    {
        if (is_array($orgWithDataSets)) {
            $orgDataSets = [];

            foreach ($orgWithDataSets as $k => $v) {
                $params = [
                    'id' => $k,
                    'include_datasets' => true
                ];
                $response = $this->requestUrl('organization_show', $params);

                foreach ($response['result']['packages'] as $res) {
                    $orgDataSets[] = $res;
                }
            }
        }

        return $orgDataSets;
    }

    private function migrateDatasets($userData, $orgsWithDataSets, $termsData)
    {
        $datasetIds = [];
        $migrateUser = User::where('username', 'migrate_data')->get();

        $usersDataSets = $this->getUsersDatasets($userData['users_with_dataSets']);
        $orgDataSets = $this->getOrgsDatasets($orgsWithDataSets);
        $terms = $termsData;

        $addedUsers = $userData['users']['success'];

        $addedResources = 0;
        $failedResources = 0;
        $addedDatasets = 0;
        $failedDatasets = 0;

        $output = array_merge($usersDataSets, $orgDataSets);

        if (!empty($output)) {
            foreach ($output as $res) {
                $alreadySaved = DataSet::where('uri', $res['id'])->first();
                $defaultCategory = 14;

                if ($alreadySaved) {
                    continue;
                }

                $tags = [];
                $orgId = null;

                if (isset($res['owner_org'])) {
                    $orgId = Organisation::where('uri', $res['owner_org'])->value('id');
                }

                $termId = isset($res['license_id'])
                    ? $terms['success'][$res['license_id']]
                    : null;

                if (isset($res['tags']) && is_array($res['tags'])) {
                    foreach ($res['tags'] as $tag) {
                        array_push($tags, $tag['display_name']);
                    }

                    $category = $this->pickCategory($tags);
                }

                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                //TO DO check categories
                $newData['data']['category_id'] = $category;

                $newData['data']['migrated_data'] = true;
                $newData['data']['locale'] = "bg";
                $newData['data']['org_id'] = $orgId;
                $newData['data']['uri'] = $res['id'];
                $newData['data']['name'] = $res['title'];
                $newData['data']['description'] = $res['notes'];
                $newData['data']['terms_of_use_id'] = $termId;
                $newData['data']['visibility'] = $res['private'] ? DataSet::VISIBILITY_PRIVATE : DataSet::VISIBILITY_PUBLIC;
                $newData['data']['version'] = $res['version'];
                $newData['data']['status'] = ($res['state'] == 'active') ? DataSet::STATUS_PUBLISHED : DataSet::STATUS_DRAFT;
                $newData['data']['author_name'] = $res['author'];
                $newData['data']['author_email'] = $res['author_email'];
                $newData['data']['support_name'] = $res['maintainer'];
                $newData['data']['support_email'] = $res['maintainer_email'];
                $newData['data']['tags'] = $tags;
                $newData['data']['created_at'] = $res['metadata_created'];
                $newData['data']['updated_by'] = User::where('username', 'migrate_data')->value('id');
                $newData['data']['created_by'] = isset($addedUsers[$res['creator_user_id']])
                    ? $addedUsers[$res['creator_user_id']]
                    : User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addDataset', 'POST', $newData);
                $api = new ApiDataSet($request);
                $result = $api->addDataset($request)->getData();

                if ($result->success) {
                    $newDataSetId = DataSet::where('uri', $result->uri)->value('id');
                    $datasetIds['success'][$res['id']] = $newDataSetId;

                    if ($res['num_resources'] > 0) {
                        $fileFormats = Resource::getAllowedFormats();

                        // Add resources
                        foreach ($res['resources'] as $resource) {
                            if (in_array($resource['format'], $fileFormats)) {
                                if ($this->migrateDatasetsResources($newDataSetId, $resource)) {
                                    $addedResources++;
                                } else {
                                    $failedResources--;
                                }
                            }
                        }
                    }

                    $addedDatasets++;
                } else {
                    $failedDatasets++;
                    $datasetIds['error'][$res['id']] = $res['title'];
                }
            }
        }

        $datasets = $datasetIds;

        $this->info('Datasets successful: '. $addedDatasets);
        $this->error('Datasets failed: '. $failedDatasets);
        $this->info('Resources successful: '. $addedResources);
        $this->error('Resources failed: '. $failedResources);
        $this->line('');
    }

    private function migrateDatasetsResources($dataSetId, $resourceData)
    {
        $migrateUser = User::where('username', 'migrate_data')->get();

        $datasetUri = DataSet::where('id', $dataSetId)->value('uri');
        $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');
        $newData['dataset_uri'] = $datasetUri;

        $newData['data']['migrated_data'] = true;
        $newData['data']['locale'] = "bg";
        $newData['data']['data_set_id'] = $dataSetId;
        $newData['data']['name'] = isset($resourceData['name']) ? $resourceData['name'] : 'Без име';
        $newData['data']['uri'] = $resourceData['id'];
        $newData['data']['type'] = Resource::TYPE_FILE;
        $newData['data']['url'] =  $resourceData['url'];
        $newData['data']['description'] = $resourceData['description'];
        $newData['data']['resource_type'] = null;
        $newData['data']['file_format'] = $resourceData['format'];
        $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

        // get file
        $path = pathinfo($resourceData['url']);
        $url = $resourceData['url'];

        try {
            $newData['file']['file_content'] = @file_get_contents($url);
            $newData['file']['file_extension'] = $path['extension'];
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            $newData['file'] = null;
        }

        $request = Request::create('/api/addResourceMetadata', 'POST', $newData);
        $api = new ApiResource($request);
        $result = $api->addResourceMetadata($request)->getData();

        if ($result->success) {
            $newResourceId = Resource::where('uri', $result->data->uri)->value('id');

            $resourceIds['success'][$result->data->uri] = $newResourceId;

            if ($newData['file'] != null) {
                if ($this->manageMigratedFile($newData['file'], $result->data->uri)) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $resourceIds['error'] = $result->errors;
        }

        return false;
    }

    private function manageMigratedFile($fileData, $resourceURI)
    {
        $content = $fileData['file_content'];
        $extension = $fileData['file_extension'];

        if (!empty($extension)) {
            $convertData = [
                'api_key'   => User::where('username', 'migrate_data')->value('api_key'),
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
                    $elasticData = $resultConvert['data'];
                    $data['xmlData'] = $content;

                    if ($resultConvert['success']) {;
                        $elasticData = $resultConvert['data'];
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
                        $elasticData = $resultConvert['data'];
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
                        $elasticData = $resultConvert['data'];
                        $data['csvData'] = $resultConvert['data'];
                    }

                    break;
                case 'txt':
                    $elasticData = $convertData['data'];
                    $data['text'] = $convertData['data'];

                    break;
                default:
                    $method = 'img2json';
                    $convertData['data'] = base64_encode($convertData['data']);
                    $reqConvert = Request::create('/'. $method, 'POST', $convertData);
                    $api = new ApiConversion($reqConvert);
                    $resultConvert = $api->$method($reqConvert)->getData(true);

                    if ($resultConvert['success']) {
                        $elasticData = $resultConvert['data'];
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

                if ($resultElastic->success) {
                    return true;
                } else {
                    // Delete resource metadata record if there are errors
                    $resource = Resource::where('uri', $resourceURI)->first();

                    if ($resource) {
                        $resource->forceDelete();
                    }

                    return false;
                }
            }
            return false;
        }
    }

    private function migrateFollowers($addedUsers)
    {
        $migration_user = User::where('username', 'migrate_data')->first();

        $api_key = User::where('username', 'migrate_data')->value('api_key');
        $header = array();
        $header[] = 'Authorization: '.$api_key;
        $countFollowers = 0;
        $addedFollowers = 0;

        $users = $addedUsers;
        $userOldIds = isset($users['success']) ? $users['success'] : null;

        //Add user followers
        foreach ($userOldIds as $k => $v) {
            $params = [
                'id' => $k
            ];
            $response = $this->requestUrl('user_follower_list', $params, $header);

            if (isset($response['result'])) {
                foreach ($response['result'] as $res) {
                    if (isset($userOldIds[$res['id']])) {
                        $countFollowers++;
                        $newUserFollow['api_key'] = $api_key;
                        $newUserFollow['user_id'] = $userOldIds[$res['id']];
                        $newUserFollow['follow_user_id'] = $v;

                        $userReq = Request::create('/api/addFollow', 'POST', $newUserFollow);
                        $api = new ApiFollow($userReq);
                        $api->addFollow($userReq)->getData();
                    }

                    continue;
                }
            }
        }

        //Add organisation followers
        $organisations = Organisation::where('created_by', $migration_user->id)->get();

        foreach ($organisations as $org) {
            $params = [
                'id' => $org->uri
            ];
            $response = $this->requestUrl('organization_follower_list', $params, $header);

            if (isset($response['result'])) {
                foreach ($response['result'] as $res) {
                    if (isset($userOldIds[$res['id']])) {
                        $countFollowers++;
                        $newOrgFollow['api_key'] = $api_key;
                        $newOrgFollow['user_id'] = $userOldIds[$res['id']];
                        $newOrgFollow['org_id'] = $org->id;

                        $orgReq = Request::create('/api/addFollow', 'POST', $newOrgFollow);
                        $api = new ApiFollow($orgReq);
                        $api->addFollow($orgReq)->getData();
                    }

                    continue;
                }
            }
        }

        //Add data sets followers
        $dataSets = DataSet::where('created_by', $migration_user->id)->get();

        foreach ($dataSets as $dataSet) {
            $params = [
                'id' => $dataSet->uri
            ];
            $response = $this->requestUrl('dataset_follower_list', $params, $header);

            if (isset($response['result'])) {
                foreach ($response['result'] as $res) {
                    if (isset($userOldIds[$res['id']])) {
                        $countFollowers++;
                        $newDataSetFollow['api_key'] = $api_key;
                        $newDataSetFollow['user_id'] = $user[$res['id']];
                        $newDataSetFollow['data_set_id'] = $dataSet->id;

                        $dataSetReq = Request::create('/api/addFollow', 'POST', $newDataSetFollow);
                        $api = new ApiFollow($dataSetReq);
                        $api->addFollow($dataSetReq)->getData();
                    }

                    continue;
                }
            }
        }

        $migrateUser = User::where('username', 'migrate_data')->value('id');
        $users = User::where('created_by',User::where('username', 'migrate_data')->value('id'))->get();

        foreach ($users as $user) {
            $userIds[] = $user->id;
        }

        $addedFollowers = UserFollow::whereIn('user_id', $userIds)->count()->get();
        $this->line('Followers total: '. $countFollowers);
        $this->info('Followers success: '. $addedFollowers);

    }

    private function migrateUserToOrgRole($addedUsers, $userToOrgData)
    {
        $usersOldIds = isset($addedUsers['success']) ? $addedUsers['success'] : [];
        $userToOrgRole = $userToOrgData;

        $errors = 0;
        $success = 0;
        $total = 0;

        if (!empty($userToOrgRole) && $usersOldIds) {
            foreach ($userToOrgRole as $orgId => $orgUsers) {
                foreach ($orgUsers as $user) {
                    $total++;

                    if (isset($usersOldIds[$user['id']])) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $usersOldIds[$user['id']];
                        $userToOrgRole->org_id = $orgId;
                        $userToOrgRole->role_id = ($user['capacity'] == 'admin') ? 1 : 3;

                        if ($userToOrgRole->save()) {
                            $success++;
                        } else {
                            $errors++;
                        }
                    } else {
                        $errors++;
                    }
                }
            }
        }

        $this->line('User to role total: '. $total);
        $this->info('User to role successful: '. $success);
        $this->error('User to role failed: '. $errors);
    }

    private function pickCategory($tags)
    {
        $categories = [
            '1' => 0,  // 1 => 'Селско стопанство, риболов и аква култури, горско стопанство, храни',
            '2' => 0,  // 2 => 'Образование, култура и спорт',
            '3' => 0,  // 3 => 'Околна среда',
            '4' => 0,  // 4 => 'Енергетика',
            '5' => 0,  // 5 => 'Транспорт',
            '6' => 0,  // 6 => 'Наука и технологии',
            '7' => 0,  // 7 => 'Икономика и финанси',
            '8' => 0,  // 8 => 'Население и социални условия',
            '9' => 0,  // 9 => 'Правителство, публичен сектор',
            '10' => 0, // 10 => 'Здравеопазване',
            '11' => 0, // 11 => 'Региони, градове',
            '12' => 0, // 12 => 'Правосъдие, правна система, обществена безопасност',
            '13' => 0, // 13 => 'Международни въпроси'
            '14' => 0, // 14 => 'Некатегоризирани'
        ];

        foreach($tags as $tag) {
            $tag = strtolower($tag);

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
                    $categories['1']++; //Селско стопанство, риболов и аква култури, горско стопанство, храни

                    break;
                case strpos($tag, 'гимназии'):
                case strpos($tag, 'образование'):
                case strpos($tag, 'кандидатства'):
                case strpos($tag, 'училищ'):
                case strpos($tag, 'читалищ'):
                case strpos($tag, 'ученици'):
                case strpos($tag, 'ясли'):
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
                case strpos($tag, 'туризъм'):
                case 'академични длъжности':
                case 'военни':
                case 'план прием':
                case 'лека атлетика':
                case 'паметници на културата':
                    $categories['2']++; //Образование, култура и спорт

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
                case 'агенция по геодезия':
                case 'атмосферен вуздух':
                case 'площадки отпадъци':
                case 'битови отпадъци':
                case 'фини прахови частици':
                case 'фпч':
                case 'въглероден оксид':
                     $categories['3']++; //Околна среда

                     break;
                case strpos($tag, 'електромери'):
                case strpos($tag, 'белене'):
                    $categories['4']++; //Eнергетика

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
                case 'моторни-превозни средства':
                case 'автогара':
                case 'автомобил':
                    $categories['5']++; //Tранспорт

                    break;
                case strpos($tag, 'изследвания'):
                case strpos($tag, 'експерименти'):
                case strpos($tag, 'авторски'):
                case strpos($tag, 'информацион'):
                case strpos($tag, 'нау'):
                case 'Hackathon':
                case 'авторски права':
                    $categories['6']++; //Наука и технологии

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
                    $categories['7']++; //Икономика и финанси

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
                case 'census':
                case 'Агенция за хората с увреждания специализирани предприятия':
                case 'Агенция по заетостта':
                case 'Бюро по труда':
                    $categories['8']++; //Население и социални условия

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
                    $categories['9']++; //Правителство, публичен сектор

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
                    $categories['10']++; //Здравеопазване

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
                    $categories['11']++; //Региони, градове, общини

                    break;
                case strpos($tag, 'юрист'):
                case strpos($tag, 'юридически'):
                case strpos($tag, 'правни'):
                case strpos($tag, 'право'):
                case strpos($tag, 'закон'):
                case strpos($tag, 'убийств'):
                case strpos($tag, 'престъпления'):
                case 'prestupleniya_sreshtu_lichnostta _sobstvenostta_ikonomicheski':
                    $categories['12']++; //Правосъдие, правна система, обществена безопасност

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
                    $categories['13']++; //Международни въпроси

                    break;
            }
        }

        $categoriesIndex = array_flip($categories);
        $selectedCategory = $categoriesIndex[max($categories)];

        if (max($categories) == 0) {
            $selectedCategory = $categories['14'];
        }

        return $selectedCategory;

    }
}
