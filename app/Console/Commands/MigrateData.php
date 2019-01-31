<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
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
use App\ElasticDataSet;
use App\Http\Controllers\Api\TagController as ApiTags;
use App\Http\Controllers\Api\UserController as ApiUser;
use App\Http\Controllers\Api\DataSetController as ApiDataSet;
use App\Http\Controllers\Api\ResourceController as ApiResource;
use App\Http\Controllers\Api\UserFollowController as ApiFollow;
use App\Http\Controllers\Api\ConversionController as ApiConversion;
use App\Http\Controllers\Api\TermsOfUseController as ApiTermsOfUse;
use App\Http\Controllers\Api\OrganisationController as ApiOrganisation;

class MigrateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:data {direction}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data migration';

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
                $this->up();
                $this->info('Data migration finished successfully!');
            } else if ($this->argument('direction') == 'down') {
                $this->down();
                die();
            } else {
                $this->error('No direction given.');
            }
        } catch (\Exception $ex) {
            $this->error('Data migration failed!');
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

        $this->migrateTags();

        $this->migrateOrganisations();
        $this->migrateGroups();
        $this->migrateUsers();

        $this->getUsersDatasets();
        $this->getOrgsDatasets();

        $this->migrateUserToOrgRole();
        $this->migrateFollowers();
    }

    private function down()
    {
        $migrateUser = User::where('username', 'migrate_data')->value('id');
        $users = User::where('created_by', $migrateUser)->get()->pluck('id');

        $organisations = Organisation::where('created_by', $migrateUser)->get()->pluck('id');

        $dataSets = DataSet::whereIn('created_by', $users)->get()->pluck('id');

        $tags = Tags::where('created_by', $migrateUser)->get()->pluck('id');

        $termsOfUse = TermsOfUse::where('created_by', $migrateUser)->get()->pluck('id');

        $resources = Resource::whereIn('data_set_id', $dataSets)->get()->pluck('id');

        if (isset($resources)) {
            ElasticDataSet::whereIn('resource_id', $resources)->forceDelete();
        }

        if (isset($dataSets)) {
            Resource::whereIn('data_set_id', $dataSets)->forceDelete();
            DataSetTags::whereIn('data_set_id', $dataSets)->delete();
            UserFollow::whereIn('data_set_id', $dataSets)->delete();

            foreach ($dataSets as $id) {
                $indexParams['index'] = $id;
                if (\Elasticsearch::indices()->exists($indexParams)) {
                    \Elasticsearch::indices()->delete(['index' => $id]);
                }
            }
        }

        if (isset($users)) {
            UserToOrgRole::whereIn('user_id', $users)->delete();
            UserFollow::whereIn('user_id', $users)->delete();
            UserSetting::whereIn('user_id', $users)->delete();
            DataSet::whereIn('created_by', $users)->forceDelete();
            User::whereIn('id', $users)->forceDelete();
        }

        if (isset($organisations)) {
            UserToOrgRole::whereIn('org_id', $organisations)->delete();
            UserFollow::whereIn('org_id', $organisations)->delete();
            Organisation::whereIn('id', $organisations)->forceDelete();
        }

        if (isset($tags)) {
            Tags::whereIn('id', $tags)->delete();
        }

        if (isset($termsOfUse)) {
            TermsOfUse::whereIn('id', $termsOfUse)->delete();
        }

        Artisan::call('cache:clear');
    }

    private function migrateTags()
    {
        $migrateUser = User::where('username', 'migrate_data')->value('id');
        $tags = [];
        $oldRecords = 0;

        $params = [
            'all_fields' => true
        ];
        $response = request_url('tag_list', $params);

        if (!empty($response['result'])) {
            $tagsIds = [];

            foreach ($response['result'] as $res) {
                $alreadySaved = Tags::where('name', $res['display_name'])->first();

                if ($alreadySaved) {
                    $oldRecords++;

                    continue;
                }

                $newData['api_key'] = config('app.MIGRATE_USER_API_KEY');

                $newData['data']['migrated_data'] = true;
                $newData['data']['name'] = $res['display_name'];
                $newData['data']['created_by'] = $migrateUser;

                $request = Request::create('/api/addGroup', 'POST', $newData);
                $api = new ApiTags($request);
                $result = $api->addTag($request)->getData();

                if ($result->success) {
                    $tagsIds['success'][$res['id']] = $result->id;
                    Log::info('Tag "'. $res['display_name'] .'" added successfully!');
                } else {
                    $tagsIds['error'][$res['id']] = $res['display_name'];
                    Log::error('Tag "'. $res['display_name'] .'" failed!');
                }
            }

            $tags = $tagsIds;
        }

        if ($oldRecords > 336) {
            $countSaved = Tags::where('created_by', $migrateUser)->count();
            $this->line('Already saved tags: '. $countSaved);
        } else {
            $this->line('Tags total: '. (isset($response['result']) ? count($response['result']) : '0'));
            $this->info('Tags successful: '. (isset($tags['success']) ? count($tags['success']) : '0'));
            $this->error('Tags failed: '.(isset($tags['error']) ? count($tags['error']) : '0'));
        }
        $this->line('');

        return $tags;
    }

    private function migrateOrganisations()
    {
        $organisationData = [];
        $usersToOrgRole = [];
        $orgs = [];
        $orgWithDataSets = [];
        $oldRecords = 0;

        $params = [
            'all_fields'    => true,
            'include_users' => true,
        ];
        $response = request_url('organization_list', $params);
        $numPackageFromOrgs = 0;

        if (!empty($response['result'])) {
            $orgIds = [];

            foreach ($response['result'] as $res) {
                $type = 0;
                $alreadySaved = Organisation::where('uri', $res['id'])->first();

                if ($alreadySaved) {
                    $oldRecords++;

                    continue;
                }

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

                $newData['api_key'] = config('app.MIGRATE_USER_API_KEY');

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
                    Log::info('Organisation "'. $res['display_name'] .'" added successfully!');
                } else {
                    $orgIds['error'][$res['id']] = $res['display_name'];
                    Log::error('Organisation "'. $res['display_name'] .'" with id: "'. $res['id'] .'" failed!');
                }
            }

            $orgs = $orgIds;
        }

        if ($oldRecords > 0) {
            $this->line('Already saved organisation: '. $oldRecords);
        } else {
            $this->line('Organisations total: '. (isset($response['result']) ? count($response['result']) : '0'));
            $this->info('Organisations successful: '. (isset($orgs['success']) ? count($orgs['success']) : '0'));
            $this->error('Organisations failed: '.(isset($orgs['error']) ? count($orgs['error']) : '0'));
            $this->line('User to org role: '. count($usersToOrgRole));
        }
        $this->line('');

        $organisationData = [
            'organisations'     => $orgs,
            'users_to_org_role' => $usersToOrgRole,
            'org_with_dataSets' => $orgWithDataSets,
        ];

        if (Cache::has('organisationData')) {
            Cache::add('organisationData', $organisationData, 86400);
        } else {
            Cache::put('organisationData', $organisationData, 86400);
        }

        return $organisationData;
    }

    private function migrateGroups()
    {
        $groups = [];
        $groupsWithDataSets = [];
        $usersToGroupRole = [];
        $oldRecords = 0;

        $params = [
            'all_fields' => true
        ];
        $response = request_url('group_list', $params);

        if (!empty($response['result'])) {
            $groupIds = [];
            $usersToGroupRole = [];

            foreach ($response['result'] as $res) {
                $alreadySaved = Organisation::where('uri', $res['id'])->first();

                if ($alreadySaved) {
                    $oldRecords++;

                    continue;
                }

                $newData['api_key'] = config('app.MIGRATE_USER_API_KEY');

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
                    Log::info('Group "'. $res['display_name'] .'" added successfully!');
                } else {
                    $groupIds['error'][$res['id']] = $res['display_name'];
                    Log::error('Group "'. $res['display_name'] .'" with id: "'. $res['id'] .'" failed!');
                }
            }

            $groups = $groupIds;
        }

        if ($oldRecords > 0) {
            $this->line('Already saved groups: '. $oldRecords);
        } else {
            $this->line('Groups total: '. (isset($response['result']) ? count($response['result']) : '0'));
            $this->info('Groups successful: '. (isset($groups['success']) ? count($groups['success']) : '0'));
            $this->error('Groups failed: '.(isset($groups['error']) ? count($groups['error']) : '0'));
        }
        $this->line('');

        $groupsData = [
            'groups'                => $groups,
            'users_to_group_role'   => $usersToGroupRole,
            'groups_with_dataSets'  => $groupsWithDataSets,
        ];

        if (Cache::has('groupsData')) {
            Cache::add('groupsData', $groupsData, 86400);
        } else {
            Cache::put('groupsData', $groupsData, 86400);
        }

        return $groupsData;
    }

    private function migrateUsers()
    {
        $userData = [];
        $users = [];
        $usersWithDataSets = [];
        $userIds = [];
        $header = array();
        $header[] = 'Authorization: '. config('app.MIGRATE_USER_API_KEY');
        $params = [
            'all_fields' => true
        ];

        $numPackage = 0;
        $oldRecords = 0;
        $response = request_url('user_list', $params, $header);

        if (!empty($response['result'])) {

            foreach ($response['result'] as $res) {
                $alreadySaved = User::where('username', $res['name'])->first();

                if ($alreadySaved) {
                    $oldRecords++;

                    continue;
                }

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

                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $password = substr(str_shuffle($chars), 0, 16);

                $newData['data']['migrated_data'] = true;
                $newData['data']['firstname'] = ucfirst($fname);
                $newData['data']['lastname'] = ucfirst($lname);
                $newData['data']['username'] = $res['name'];
                $newData['data']['email'] = empty($res['email'])
                    ? 'no_mail'. rand(1, 9999999) .'@mail.com'
                    : trim($res['email']);
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
                    Log::info('User "'. $res['name'] .'" added successfully!');
                } else {
                    $userIds['error'][$res['id']] = $res['email'];
                    Log::error('User "'. $res['name'] .'" with id: "'. $res['id'] .'" failed!');
                }
            }

            $users = $userIds;
        }

        if ($oldRecords > 0) {
            $this->line('Already saved users: '. $oldRecords);
        } else {
            $this->line('Users total: '. (isset($response['result']) ? count($response['result']) : '0'));
            $this->info('Users successful: '. (isset($users['success']) ? count($users['success']) : '0'));
            $this->error('Users failed: '.(isset($users['error']) ? count($users['error']) : '0'));
            $this->line('Users` dataset total count: '. $numPackage);
            $this->line('Users with data sets '. count($usersWithDataSets));
        }
        $this->line('');

        $userData = [
            'users'                 => $users,
            'users_with_dataSets'   => $usersWithDataSets,
        ];

        if (Cache::has('userData')) {
            Cache::add('userData', $userData, 86400);
        } else {
            Cache::put('userData', $userData, 86400);
        }

        return $userData;
    }

    private function getUsersDatasets()
    {
        $totalOrgDatasets = 0;
        $totalSuccess = 0;
        $totalFailed = 0;

        $userData = Cache::get('userData');
        $usersWithDataSets = $userData['users_with_dataSets'];

        $bar = $this->output->createProgressBar(count($usersWithDataSets));

        if (is_array($usersWithDataSets)) {
            $userDataSets = [];

            foreach ($usersWithDataSets as $k => $v) {
                $successPackages = 0;
                $failedPacgakes = 0;
                $params = [
                    'id'                => $k,
                    'include_datasets'  => true
                ];
                $response = request_url('user_show', $params);

                if (isset ($response['result']['datasets'])) {
                    foreach ($response['result']['datasets'] as $res) {
                        if ($this->migrateDatasets($res)) {
                            $successPackages++;
                        } else {
                            $failedPacgakes++;
                        }

                        $totalSuccess += $successPackages;
                        $totalFailed += $failedPacgakes;
                    }

                    $this->line('Users total datasets : '. $response['result']['number_created_packages']);
                    $this->info('Dataset success: '. $successPackages);
                    $this->error('Dataset failed: '. $failedPacgakes);
                    $this->line('');
                    $bar->advance();
                    $this->line('');
                }
            }
        }

        $this->line('Users Dataset Summary');
        $this->line('Total datasets: '. $totalOrgDatasets);
        $this->info('Total dataset success: '. $totalSuccess);
        $this->error('Total datasets failed: '. $totalFailed);
        $bar->finish();

        return $userDataSets;
    }

    private function getOrgsDatasets()
    {
        $totalOrgDatasets = 0;
        $totalSuccess = 0;
        $totalFailed = 0;
        $orgDataSets = [];
        $organisationData = Cache::get('organisationData');
        $orgWithDataSets = $organisationData['org_with_dataSets'];

        $bar = $this->output->createProgressBar(count($orgWithDataSets));

        if (is_array($orgWithDataSets)) {
            foreach ($orgWithDataSets as $k => $v) {
                $failedPacgakes = 0;
                $successPackages = 0;
                $params = [
                    'id'                => $k,
                    'include_datasets'  => true,
                ];
                $response = request_url('organization_show', $params);
                $total = isset($response['result']['package_count']) ? (int) $response['result']['package_count'] : 0;

                if (isset($response['result']['packages'])) {
                    foreach ($response['result']['packages'] as $res) {
                        if ($this->migrateDatasets($res)) {
                            $successPackages++;
                        } else {
                            $failedPacgakes++;
                        }

                        $totalSuccess += $successPackages;
                        $totalFailed += $failedPacgakes;
                        $totalOrgDatasets += $total;
                    }
                }

                $this->line('Organisation total datasets: '. $total);
                $this->info('Dataset success: '. $successPackages);
                $this->error('Dataset failed: '. $failedPacgakes);
                $this->line('');
                $bar->advance();
                $this->line('');
            }
        }

        $this->line('');
        $this->line('Organisations Dataset Summary');
        $this->line('Total datasets: '. $totalOrgDatasets);
        $this->info('Total dataset success: '. $totalSuccess);
        $this->error('Total datasets failed: '. $totalFailed);
        $bar->finish();

        return $orgDataSets;
    }

    private function mapTermsOfUse($oldLicense)
    {
        $licenses = [
            'cc-zero'   => 1,  // Условия за предоставяне на информация без защитени авторски права.
            'cc-by'     => 2,  // Условия за предоставяне на произведение за повторно използване. Признаване на авторските права.
            'cc-by-sa'  => 3,  // Условия за предоставяне на произведение за повторно използване. Признаване на авторските права. Споделяне на споделеното.
        ];

        $newTerm = isset($licenses[$oldLicense]) ? $licenses[$oldLicense] : null;

        return $newTerm;
    }

    private function migrateDatasets($dataSet)
    {
        $addedResources = 0;
        $failedResources = 0;
        $addedDatasets = 0;
        $failedDatasets = 0;
        $unsuporrtedFormat = 0;

        $terms = Cache::get('termsData');

        $userData = Cache::get('userData');
        $addedUsers = $userData['users']['success'];

        if (!empty($dataSet)) {
            $alreadySaved = DataSet::where('uri', $dataSet['id'])->first();
            $category = 14;

            if (!$alreadySaved) {
                $tags = [];
                $orgId = null;

                if (isset($dataSet['owner_org'])) {
                    $orgId = Organisation::where('uri', $dataSet['owner_org'])->value('id');
                }

                $termId = isset($dataSet['license_id'])
                    ? $this->mapTermsOfUse($dataSet['license_id'])
                    : null;

                if (isset($dataSet['tags']) && !empty($dataSet['tags'])) {
                    foreach ($dataSet['tags'] as $tag) {
                        array_push($tags, $tag['display_name']);
                    }

                    $category = $this->pickCategory($tags);
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
                $newData['data']['uri'] = $dataSet['id'];
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
                $newData['data']['created_at'] = $dataSet['metadata_created'];
                $newData['data']['updated_by'] = User::where('username', 'migrate_data')->value('id');
                $newData['data']['created_by'] = isset($addedUsers[$dataSet['creator_user_id']])
                    ? $addedUsers[$dataSet['creator_user_id']]
                    : User::where('username', 'migrate_data')->value('id');

                $request = Request::create('/api/addDataset', 'POST', $newData);
                $api = new ApiDataSet($request);
                $result = $api->addDataset($request)->getData();

                $resCreatedBy = $newData['data']['created_by'];

                if ($result->success) {
                    $newDataSetId = DataSet::where('uri', $result->uri)->value('id');
                    $datasetIds['success'][$dataSet['id']] = $newDataSetId;

                    if ($dataSet['num_resources'] > 0) {
                        $fileFormats = Resource::getAllowedFormats();
                        Log::info('Dataset "'. $dataSet['title'] .'" added successfully!');

                        // Add resources
                        if (isset($dataSet['resources'])) {
                            foreach ($dataSet['resources'] as $resource) {
                                $savedResource = Resource::where('uri', $resource['id'])->first();
                                $fileFormat = strtoupper(str_replace('.', '', $resource['format']));
                                $resource['created_by'] = $resCreatedBy;

                                if ($savedResource) {
                                    continue;
                                }

                                if (in_array($fileFormat, $fileFormats)) {
                                    if ($this->migrateDatasetsResources($newDataSetId, $resource)) {
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
                    $addedDatasets++;

                    $this->line('Resources total: '. $dataSet['num_resources']);
                    $this->info('Resources successful: '. $addedResources);
                    $this->error('Resources failed: '. $failedResources);
                    $this->line('Unsuported resource format count for the current dataset: '. $unsuporrtedFormat);

                    $this->line('');
                    return true;
                } else {
                    $failedDatasets++;
                    $datasetIds['error'][$dataSet['id']] = $dataSet['title'];
                    Log::error('Dataset "'. $dataSet['title'] .'" with id: "'. $dataSet['id'] .'" failed!');

                    return false;
                }
            } else {
                Log::error('Dataset with id(uri): "'. $dataSet['id'] .'" already exists!');
            }

            unset($dataSet);
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
        $extension = null;

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

                        if ($resultConvert['success'] ) {
                            $elasticData['text'] = isset($resultConvert['data']) ? $resultConvert['data'] : [];
                            $data['text'] = $resultConvert['data'];
                        } else {
                            Log::error(print_r($resultConvert, true));
                        }
                    } catch (Exception $ex) {
                        Log::error(print_r($ex->getMessage(),true));
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

    private function migrateFollowers()
    {
        $migrationUser = User::where('username', 'migrate_data')->first();

        $apiKey = config('app.MIGRATE_USER_API_KEY');
        $header = array();
        $header[] = 'Authorization: '.$apiKey;
        $countFollowers = 0;
        $addedFollowers = 0;

        $userData = Cache::get('userData');
        $users = $userData['users'];
        $usersOldIds = isset($users['success']) ? $users['success'] : null;

        //Add user followers
        if ($usersOldIds) {
            foreach ($usersOldIds as $k => $v) {
                $params = [
                    'id' => $k
                ];
                $response = request_url('user_follower_list', $params, $header);

                if (isset($response['result']) && !empty($response['result'])) {
                    foreach ($response['result'] as $res) {
                        if (isset($usersOldIds[$res['id']])) {
                            $userFollowExists = UserFollow::where('user_id', $usersOldIds[$res['id']])
                                ->where('follow_user_id', $v)
                                ->first();

                            if ($userFollowExists) {
                                continue;
                            }

                            $countFollowers++;
                            $newUserFollow['api_key'] = $apiKey;
                            $newUserFollow['user_id'] = $usersOldIds[$res['id']];
                            $newUserFollow['follow_user_id'] = $v;

                            $userReq = Request::create('/api/addFollow', 'POST', $newUserFollow);
                            $api = new ApiFollow($userReq);
                            $api->addFollow($userReq)->getData();
                        }

                        continue;
                    }
                }
            }
        }

        //Add organisation followers
        $organisations = Organisation::where('created_by', $migrationUser->id)->get();

        foreach ($organisations as $org) {
            $params = [
                'id' => $org->uri
            ];
            $response = request_url('organization_follower_list', $params, $header);

            if (isset($response['result']) && !empty($response['result'])) {
                foreach ($response['result'] as $res) {
                    if (isset($usersOldIds[$res['id']])) {
                        $orgFollowExists = UserFollow::where('user_id', $usersOldIds[$res['id']])
                            ->where('org_id', $org->id)
                            ->first();

                        if ($orgFollowExists) {
                            continue;
                        }

                        $countFollowers++;
                        $newOrgFollow['api_key'] = $apiKey;
                        $newOrgFollow['user_id'] = $usersOldIds[$res['id']];
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
        $savedUsers = User::where('created_by', $migrationUser->id)->get()->pluck('id');
        $dataSets = DataSet::whereIn('created_by', $savedUsers)->get();

        foreach ($dataSets as $dataSet) {
            $params = [
                'id' => $dataSet->uri
            ];
            $response = request_url('dataset_follower_list', $params, $header);

            if (isset($response['result']) && !empty($response['result'])) {
                foreach ($response['result'] as $res) {
                    if (isset($usersOldIds[$res['id']])) {
                        $dsFollowExists = UserFollow::where('user_id', $usersOldIds[$res['id']])
                            ->where('data_set_id', $dataSet->id)
                            ->first();

                        if ($dsFollowExists) {
                            continue;
                        }

                        $countFollowers++;
                        $newDataSetFollow['api_key'] = $apiKey;
                        $newDataSetFollow['user_id'] = $usersOldIds[$res['id']];
                        $newDataSetFollow['data_set_id'] = $dataSet->id;

                        $dataSetReq = Request::create('/api/addFollow', 'POST', $newDataSetFollow);
                        $api = new ApiFollow($dataSetReq);
                        $api->addFollow($dataSetReq)->getData();
                    }

                    continue;
                }
            }
        }

        $addedFollowers = UserFollow::whereIn('user_id', $savedUsers)->count();
        $this->line('Followers total: '. $countFollowers);
        $this->info('Followers success: '. $addedFollowers);
        $this->line('');

    }

    private function migrateUserToOrgRole()
    {
        $userData = Cache::get('userData');
        $users = $userData['users'];
        $usersOldIds = isset($users['success']) ? $users['success'] : [];

        $organisationData = Cache::get('organisationData');
        $userToOrgRole = $organisationData['users_to_org_role'];

        $errors = 0;
        $success = 0;
        $total = 0;

        if (!empty($userToOrgRole) && !empty($usersOldIds)) {
            foreach ($userToOrgRole as $orgId => $orgUsers) {
                foreach ($orgUsers as $user) {
                    $total++;
                    $role = 3;

                    switch ($user['capacity']) {
                        case 'admin':
                            $role = 1;

                            break;
                        case 'member':
                            $role = 5;

                            break;
                        case 'editor':
                            $role = 4;

                            break;
                    }

                    if (isset($usersOldIds[$user['id']])) {
                        $exists = UserToOrgRole::where('user_id', $usersOldIds[$user['id']])
                                ->where('org_id', $orgId)
                                ->where('role_id', $role)
                                ->first();

                        if ($exists) {
                            continue;
                        }

                        $userToOrgRole = new UserToOrgRole;
                        $userToOrgRole->user_id = $usersOldIds[$user['id']];
                        $userToOrgRole->org_id = $orgId;
                        $userToOrgRole->role_id = $role;

                        if ($userToOrgRole->save()) {
                            $success++;
                        } else {
                            $errors++;
                        }
                    } else {
                        $errors++;
                        Log::error('User with id "'. $user['id'] .'" was not found in saved users');
                    }
                }
            }
        }

        $this->line('User to role total: '. $total);
        $this->info('User to role successful: '. $success);
        $this->error('User to role failed: '. $errors);
        $this->line('');
    }

    private function pickCategory($tags)
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
}
