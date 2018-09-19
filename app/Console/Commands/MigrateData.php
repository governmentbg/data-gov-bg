<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use App\User;
use App\Tags;
use App\DataSet;
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
//    protected $signature = 'migrate:data {direction}';
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
        if ($this->argument('direction') == 'up') {
            if ($this->argument('source') == null) {
                $this->error('No source given');
            } else {
                $this->up();
            }
        } else if ($this->argument('direction') == 'down') {
            $this->down();
        }
        else {
            $this->error('No direction given');
        }
        $this->error($this->argument('direction'));
    }

    private function up ()
    {

        $migrate_user_id = User::where('username', 'migrate_data')->value('id');
        \Auth::loginUsingId($migrate_user_id);

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

    private function down ()
    {
        $migrate_user = User::where('username', 'migrate_data')->value('id');

        $users = User::where('created_by',User::where('username', 'migrate_data')->value('id'))->get();
        foreach ($users as $user) {
            $userIDs[] = $user->id;
        }

        $organisations = Organisation::where('created_by', User::where('username', 'migrate_data')->value('id'))->get();
        foreach ($organisations as $org) {
            $orgIDs[] = $org->id;
        }

        $dataSets = DataSet::where('updated_by', User::where('username', 'migrate_data')->value('id'))->get();
        foreach ($dataSets as $dataSet) {
            $dataSetIDs[] = $dataSet->id;
        }

        $tags = Tags::where('created_by', User::where('username', 'migrate_data')->value('id'))->get();
        foreach ($tags as $tag) {
            $tagsIDs[] = $tag->id;
        }

        $termsOfUse = TermsOfUse::where('created_by', User::where('username', 'migrate_data')->value('id'))->get();
        foreach ($termsOfUse as $term) {
            $termsIDs[] = $term->id;
        }

        $resources = Resource::whereIn('data_set_id', $dataSetIDs)->get();
        foreach ($resources as $res) {
            $resourcesIDs[] = $res->id;
        }

        $dataSetTags = DataSetTags::whereIn('data_set_id', $dataSetIDs)->get();
        foreach ($dataSetTags as $dataSetTag) {
            $dataSetTagsIDs[] = $dataSetTag->id;
        }

        $userToOrgRole = UserToOrgRole::whereIn('user_id', $userIDs)->get();
        foreach ($userToOrgRole as $usrToOrg) {
            $userToOrgIDs[] = $usrToOrg->id;
        }

        $userSettings = UserSetting::whereIn('user_id', $userIDs)->get();
        foreach ($userSettings as $setting) {
            $settingsIDs[] = $setting->id;
        }

        Resource::whereIn('data_set_id', $dataSetIDs)->forceDelete();
        DataSetTags::whereIn('data_set_id', $dataSetIDs)->delete();
        DataSet::whereIn('id', $dataSetIDs)->forceDelete();
        UserToOrgRole::whereIn('user_id', $userIDs)->delete();
        UserToOrgRole::whereIn('org_id', $orgIDs)->delete();
        UserFollow::whereIn('user_id', $userIDs)->delete();
        UserFollow::whereIn('org_id', $orgIDs)->delete();
        UserFollow::whereIn('data_set_id', $dataSetIDs)->delete();
        UserSetting::whereIn('user_id', $userIDs)->delete();
        User::whereIn('id', $userIDs)->forceDelete();
        Organisation::whereIn('id', $orgIDs)->forceDelete();
        Tags::whereIn('id', $tagsIDs)->delete();
        TermsOfUse::whereIn('id', $termsIDs)->delete();
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
        $migrate_user = User::where('username', 'migrate_data')->get();
        $tags = [];

        $params = [
            'all_fields' => true
        ];
        $response = $this->requestUrl('tag_list', $params);

        if (!empty($response['result'])) {
            $tagsIDs = [];
            foreach ($response['result'] as $res) {

                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                $newData['data']['migrated_data'] = true;
                $newData['data']['name'] = $res['display_name'];
                $newData['data']['created_by'] = User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addGroup', 'POST', $newData);
                $api = new ApiTags($request);
                $result = $api->addTag($request)->getData();

                if ($result->success) {
                    $tagsIDs['success'][$res['id']] = $result->id;
                } else {
                    $tagsIDs['error'][$res['id']] = $res['display_name'];
                }
            }

            $tags = $tagsIDs;
            error_log('Tags total: '. count($response['result']).' success: '. (isset($tags['success']) ? count($tags['success']) : '0')
                    .' errors ' . (isset($tags['error']) ? count($tags['error']) : '0'));
        }

        $this->line('Tags total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Tags successful: '. (isset($tags['success']) ? count($tags['success']) : '0'));
        $this->error('Tags failed: '.(isset($tags['error']) ? count($tags['error']) : '0'));
        $this->line('');

        return $tags;
    }

    private function migrateLicense()
    {
        $migrate_user = User::where('username', 'migrate_data')->get();
        $terms = [];

        $params = [
            'all_fields' => true
        ];
        $response = $this->requestUrl('license_list', $params);

        if (!empty($response['result'])) {
            $licensesIDs = [];
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
                    $licensesIDs['success'][$res['id']] = $result->id;
                } else {
                    $licensesIDs['error'][$res['id']] = $res['title'];
                }
            }

            $terms = $licensesIDs;
            error_log('terms count success '.(isset($terms['success']) ? count($terms['success']) : '0')
                    .' errors ' . (isset($terms['error']) ? count($terms['error']) : '0'));
        }

        $this->line('Terms of use total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Terms of use successful: '. (isset($terms['success']) ? count($terms['success']) : '0'));
        $this->error('Terms of use failed: '.(isset($terms['error']) ? count($terms['error']) : '0'));
        $this->line('');

        return $terms;
    }

    private function migrateOrganisations()
    {
        $migrate_user = User::where('username', 'migrate_data')->get();
        $organisationData = [];

        $params = [
            'all_fields' => true,
            'include_users' => true,
        ];
        $response = $this->requestUrl('organization_list', $params);
        $numPackageFromOrgs = 0;

        if (!empty($response['result'])) {
            $orgIDs = [];
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
                    $orgIDs['success'][$res['id']] = $newOrgId;
                } else {
                    $orgIDs['error'][$res['id']] = $res['display_name'];
                }
            }

//            if (count($orgWithDataSets) > 0) {
//                error_log(count($orgWithDataSets));
//            }

            $orgs = $orgIDs;
            error_log('Organisations count: '.count($response['result']).' success: '. (isset($orgs['success']) ? count($orgs['success']) : '0')
                    .', errors: ' . (isset($orgs['error']) ? count($orgs['error']) : '0'));
        }

        $this->line('Organisations total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Organisations successful: '. (isset($orgs['success']) ? count($orgs['success']) : '0'));
        $this->error('Organisations failed: '.(isset($orgs['error']) ? count($orgs['error']) : '0'));
        $this->line('Organisations` dataset total: '. $numPackageFromOrgs);
        $this->line('User to org role: '. count($usersToOrgRole));
        $this->line('');

        $organisationData = [
            'organisations' => $orgs,
            'users_to_org_role' => $usersToOrgRole,
            'org_with_dataSets' => $orgWithDataSets,
        ];

        return $organisationData;
    }

    private function migrateGroups()
    {
        $migrate_user = User::where('username', 'migrate_data')->get();
        $groups = [];
        $groupsWithDataSets = [];

        $params = [
            'all_fields' => true
        ];
        $response = $this->requestUrl('group_list', $params);

        if (!empty($response['result'])) {
            $groupIDs = [];
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
                    $groupIDs['success'][$res['id']] = $newGroupId;
                } else {
                    $groupIDs['error'][$res['id']] = $res['display_name'];
                }
            }

            $groups = $groupIDs;
            error_log('Groups count: '.count($response['result']).' success: '.(isset($groups['success']) ? count($groups['success']) : '0')
                    .', errors: ' . (isset($groups['error']) ? count($groups['error']) : '0'));
        }

        $this->line('Groups total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Groups successful: '. (isset($groups['success']) ? count($groups['success']) : '0'));
        $this->error('Groups failed: '.(isset($groups['error']) ? count($groups['error']) : '0'));
        $this->line('');

        $groupsData = [
            'groups' => $groups,
            'users_to_group_role' => $usersToGroupRole,
            'groups_with_dataSets' => $groupsWithDataSets,
        ];

        return $groupsData;
    }

    private function migrateUsers()
    {
        $userData = [];

        $migrate_user = User::where('username', 'migrate_data')->get();
        $header = array();
        $header[] = 'Authorization: '. User::where('username', 'migrate_data')->value('api_key');
        $params = [
            'all_fields' => true
        ];

        $numPackage = 0;

        $response = $this->requestUrl('user_list', $params, $header);

        if (!empty($response['result'])) {
            $userIDs = [];
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
                    $userIDs['success'][$res['id']] = $newUserId;
                } else {
                    $userIDs['error'][$res['id']] = $res['email'];
                }
            }

            $users = $userIDs;
            error_log('Users count: '.count($response['result']).' success '. (isset($users['success']) ? count($users['success']) : '0')
                    .' errors ' . (isset($users['error']) ? count($users['error']) : '0'));
        } else {
            error_log('empty result users');
        }
        $this->line('Users total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Users successful: '. (isset($users['success']) ? count($users['success']) : '0'));
        $this->error('Users failed: '.(isset($users['error']) ? count($users['error']) : '0'));
        $this->line('Users` dataset total: '. $numPackage);
        $this->line('');

        $userData = [
            'users' => $users,
            'users_with_dataSets' => $usersWithDataSets,
        ];

        return $userData;
    }

    private function getUsersDatasets($usersWithDataSets)
    {
        if (is_array($usersWithDataSets)) {
            $userDataSets = [];
            foreach ($usersWithDataSets as $k => $v) {
                $params = [
                    'id' => $k,
                    'include_datasets' => true
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

    // give response
    private function migrateDatasets($userData, $orgsWithDataSets, $termsData)
    {
        $datasetIDs = [];
        $migrate_user = User::where('username', 'migrate_data')->get();

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

                if ($alreadySaved) {
                    continue;
                }
                $tags = [];
                $org_id = null;

                if (isset($res['owner_org'])) {
                    $org_id = Organisation::where('uri', $res['owner_org'])->value('id');
                }

//                $org_id = !isset($res['owner_org'])
//                        ? null
//                        : Organisation::where('uri', $res['owner_org'])->value('id');

                $term_id = isset($res['license_id'])
                        ? $terms['success'][$res['license_id']]
                        : null;

                if (isset($res['tags']) && is_array($res['tags'])) {
                    foreach ($res['tags'] as $tag) {
                        array_push($tags, $tag['display_name']);
                    }
                }

                $newData['api_key'] = User::where('username', 'migrate_data')->value('api_key');

                if (isset($res['num_followers']) && $res['num_followers'] > 0) {
                    $userWithFollowers[] = $res['id'];
                }

                //TO DO check categories
                $newData['data']['category_id'] = 1;

                $newData['data']['migrated_data'] = true;
                $newData['data']['locale'] = "bg";
                $newData['data']['org_id'] = $org_id;
                $newData['data']['uri'] = $res['id'];
                $newData['data']['name'] = $res['title'];
                $newData['data']['description'] = $res['notes'];
                $newData['data']['terms_of_use_id'] = $term_id;
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
                    $datasetIDs['success'][$res['id']] = $newDataSetId;

                    if ($res['num_resources'] > 0) {
                        $fileFormats = Resource::getAllowedFormats();

//                       Add resources
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
                    $datasetIDs['error'][$res['id']] = $res['title'];
                }
            }
        }

        $datasets = $datasetIDs;
        error_log('Dateset count: '.count($output).'  success '.(isset($datasets['success']) ? count($datasets['success']) : '0')
            .' errors ' . (isset($datasets['error']) ? count($datasets['error']) : '0'));

        $this->info('Datasets successful: '. $addedDatasets);
        $this->error('Datasets failed: '. $failedDatasets);
        $this->info('Resources successful: '. $addedResources);
        $this->error('Resources failed: '. $failedResources);
        $this->line('');
    }

    private function migrateDatasetsResources($dataSetId, $resourceData)
    {
        $migrate_user = User::where('username', 'migrate_data')->get();

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
        error_log('Resource resources count success '.(isset($resourceIds['success']) ? count($resourceIds['success']) : '0')
            .' errors ' . (isset($resourceIds['error']) ? count($resourceIds['error']) : '0'));
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
                    // delete resource metadata record if errors
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

        //Add org followers
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
                }
            }
        }

        $migrate_user = User::where('username', 'migrate_data')->value('id');
        $users = User::where('created_by',User::where('username', 'migrate_data')->value('id'))->get();

        foreach ($users as $user) {
            $userIDs[] = $user->id;
        }

        $addedFollowers = UserFollow::whereIn('user_id', $userIDs)->count()->get();
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
            foreach ($userToOrgRole as $orgID => $orgUsers) {
                foreach ($orgUsers as $user) {
                    $total++;
                    if (isset($usersOldIds[$user['id']])) {
                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $usersOldIds[$user['id']];
                        $userToOrgRole->org_id = $orgID;
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
}
