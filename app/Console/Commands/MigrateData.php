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
    protected $signature = 'migrate:data {direction} {--tags} {--users} {--orgs} {--groups} {--dsets} {--followers} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data migration';
    protected $migrationUserId;
    protected $apiKey;

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
        $this->migrationUserId = DB::table('users')->where('username', 'migrate_data')->value('id');
        $this->apiKey = config('app.MIGRATE_USER_API_KEY');

        try {
            $this->info('Data migration has started.');
            $this->line('');
            $this->info('For migrating tags enter --tags');
            $this->info('For migrating users enter --users');
            $this->info('For migrating organisations enter --orgs');
            $this->info('For migrating groups enter --groups');
            $this->info('For migrating datasets enter --dsets');
            $this->info('For migrating followers enter --followers');
            $this->info('For migrating all of above enter --all');
            $this->line('');
            $this->info('If none from above is selected it will migrate all');

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
        \Auth::loginUsingId($this->migrationUserId);

        ini_set('memory_limit', '8G');

        $optTags = $this->option('tags');
        $optUsers = $this->option('users');
        $optOrgs = $this->option('orgs');
        $optGroups = $this->option('groups');
        $optDsets = $this->option('dsets');
        $optFollowers = $this->option('followers');
        $optAll = $this->option('all');

        if (
            empty($optTags)
            && empty($optOrgs)
            && empty($optGroups)
            && empty($optUsers)
            && empty($optDsets)
            && empty($optFollowers))
        {
            $optAll = true;
        }

        if (!empty($optTags)) {
            $this->migrateTags();
        }

        if (!empty($optOrgs)) {
            $this->migrateOrganisations();
        }

        if (!empty($optGroups)) {
            $this->migrateGroups();
        }

        if (!empty($optUsers)) {
            $this->migrateUsers();
        }

        if (!empty($optDsets)) {
            $this->getUsersDatasets();
            $this->getOrgsDatasets();
        }

        if (!empty($optFollowers)) {
            $this->migrateFollowers();
        }

        if (!empty($optAll)) {
            $this->migrateTags();

            $this->line('Migrating users: ');
            $this->migrateUsers();

            $this->line('');
            $this->line('Migrating organisations: ');
            $this->migrateOrganisations();

            $this->line('');
            $this->line('Migrating groups: ');
            $this->migrateGroups();

            $this->line('');
            $this->line('Migrating users` datasets: ');
            $this->getUsersDatasets();

            $this->line('');
            $this->line('Migrating organisations` datasets: ');
            $this->getOrgsDatasets();

            $this->line('');
            $this->line('Migrating followers: ');
            $this->migrateFollowers();
        }
    }

    private function down()
    {
        $optTags = $this->option('tags');
        $optOrgs = $this->option('orgs');
        $optGroups = $this->option('groups');
        $optUsers = $this->option('users');
        $optDsets = $this->option('dsets');
        $optFollowers = $this->option('followers');
        $optAll = $this->option('all');

        if (
            empty($optTags)
            && empty($optOrgs)
            && empty($optGroups)
            && empty($optUsers)
            && empty($optDsets)
            && empty($optFollowers))
        {
            $optAll = true;
        }

        $users = User::where('created_by', $this->migrationUserId)->get()->pluck('id');
        $organisations = Organisation::where('created_by', $this->migrationUserId)->where('type', Organisation::TYPE_COUNTRY)->get()->pluck('id');
        $groups = Organisation::where('created_by', $this->migrationUserId)->where('type', Organisation::TYPE_GROUP)->get()->pluck('id');
        $dataSets = DataSet::where('is_migrated', true)->get()->pluck('id');
        $tags = Tags::where('created_by', $this->migrationUserId)->get()->pluck('id');
        $resources = Resource::whereIn('data_set_id', $dataSets)->get()->pluck('id');

        if (!empty($optTags)) {
            if (isset($tags)) {
                Tags::whereIn('id', $tags)->delete();
            }
        }

        if (!empty($optOrgs)) {
            if (isset($organisations)) {
                UserToOrgRole::whereIn('org_id', $organisations)->delete();
                UserFollow::whereIn('org_id', $organisations)->delete();
                Organisation::whereIn('id', $organisations)->forceDelete();
            }
        }

        if (!empty($optGroups)) {
            if (isset($groups)) {
                UserToOrgRole::whereIn('org_id', $groups)->delete();
                UserFollow::whereIn('org_id', $groups)->delete();
                Organisation::whereIn('id', $groups)->forceDelete();
            }
        }

        if (!empty($optUsers)) {
            if (isset($users)) {
                UserToOrgRole::whereIn('user_id', $users)->delete();
                UserFollow::whereIn('user_id', $users)->delete();
                UserSetting::whereIn('user_id', $users)->delete();
                DataSet::whereIn('created_by', $users)->forceDelete();
                User::whereIn('id', $users)->forceDelete();
            }
        }

        if (!empty($optDsets)) {
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
        }

        if (!empty($optAll)) {
            if (isset($resources)) {
                ElasticDataSet::whereIn('resource_id', $resources)->forceDelete();
            }

            if (isset($dataSets)) {
                Resource::whereIn('data_set_id', $dataSets)->forceDelete();
                DataSetTags::whereIn('data_set_id', $dataSets)->delete();
                UserFollow::whereIn('data_set_id', $dataSets)->delete();
                DataSet::whereIn('id', $dataSets)->forceDelete();

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

            if (isset($groups)) {
                UserToOrgRole::whereIn('org_id', $groups)->delete();
                UserFollow::whereIn('org_id', $groups)->delete();
                Organisation::whereIn('id', $groups)->forceDelete();
            }

            if (isset($tags)) {
                Tags::whereIn('id', $tags)->delete();
            }

            Artisan::call('cache:clear');
        }
    }

    private function migrateTags()
    {
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

                $newData['api_key'] = $this->apiKey;

                $newData['data']['migrated_data'] = true;
                $newData['data']['name'] = $res['display_name'];
                $newData['data']['created_by'] = $this->migrationUserId;

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
            $countSaved = Tags::where('created_by', $this->migrationUserId)->count();
            $this->line('Already saved tags: '. $countSaved);
        }

        $this->line('Tags total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Tags successful: '. (isset($tags['success']) ? count($tags['success']) : '0'));
        $this->error('Tags failed: '.(isset($tags['error']) ? count($tags['error']) : '0'));

        $this->line('');
    }

    private function migrateOrganisations()
    {
        $oldRecords = $success = $failed = 0;

        $params = [
            'all_fields'    => true,
            'include_users' => true,
        ];

        $response = request_url('organization_list', $params);

        if (!empty($response['result'])) {
            $bar = $this->output->createProgressBar(count($response['result']));

            foreach ($response['result'] as $res) {
                $type = 0;
                $error = $added = 0;
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

                $newData['api_key'] = $this->apiKey;

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
                $newData['data']['created_by'] = $this->migrationUserId;

                $request = Request::create('/api/addOrganisation', 'POST', $newData);
                $api = new ApiOrganisation($request);
                $result = $api->addOrganisation($request)->getData();

                if ($result->success) {
                    $newOrgId = $result->org_id;

                    if (isset($res['users'])) {
                        foreach ($res['users'] as $user) {
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

                            $userId = User::where('uri', $user['id'])->value('id');

                            if ($userId) {
                                $exists = UserToOrgRole::where('user_id', $userId)
                                    ->where('org_id', $newOrgId)
                                    ->where('role_id', $role)
                                    ->first();

                                if ($exists) {
                                    continue;
                                }

                                $userToOrgRole = new UserToOrgRole;
                                $userToOrgRole->user_id = $userId;
                                $userToOrgRole->org_id = $newOrgId;
                                $userToOrgRole->role_id = $role;

                                if ($userToOrgRole->save()) {
                                    $added ++;
                                } else {
                                    $error ++;
                                }
                            } else {
                                Log::error('User with uri "'. $user['id'] .'" was not found in saved users');
                            }
                        }

                        $this->line('');
                        $this->line('User to organisation role total: '. (isset($res['users']) ? count($res['users']) : '0')
                            .'; Successful: '. $added
                            .'; Failed: '. $error .';'
                        );
                    }

                    $success ++;
                    Log::info('Organisation "'. $res['display_name'] .'" added successfully!');
                } else {
                    $failed ++;
                    Log::error('Organisation "'. $res['display_name'] .'" with id: "'. $res['id'] .'" failed!');
                }

                $bar->advance();
            }

            $bar->finish();
        }

        $this->line('');

        if ($oldRecords > 0) {
            $this->line('Already saved organisation: '. $oldRecords);
        }

        $this->line('Organisations total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Organisations successful: '. $success);
        $this->error('Organisations failed: '. $failed);
    }

    private function migrateGroups()
    {
        $oldRecords = $success = $failed = 0;

        $params = [
            'all_fields' => true
        ];
        $response = request_url('group_list', $params);

        if (!empty($response['result'])) {
            $bar = $this->output->createProgressBar(count($response['result']));

            foreach ($response['result'] as $res) {
                $error = $added = 0;
                $alreadySaved = Organisation::where('uri', $res['id'])->first();

                if ($alreadySaved) {
                    $oldRecords++;

                    continue;
                }

                $newData['api_key'] = $this->apiKey;

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
                $newData['data']['created_by'] = $this->migrationUserId;

                $request = Request::create('/api/addGroup', 'POST', $newData);
                $api = new ApiOrganisation($request);
                $result = $api->addGroup($request)->getData();

                if ($result->success) {
                    $newGroupId = $result->id;

                    if (isset($res['users']) && count($res['users']) > 0) {
                        foreach ($res['users'] as $user) {
                            foreach ($res['users'] as $user) {
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

                                $userId = User::where('uri', $user['id'])->value('id');

                                if ($userId) {
                                    $exists = UserToOrgRole::where('user_id', $userId)
                                        ->where('org_id', $newGroupId)
                                        ->where('role_id', $role)
                                        ->first();

                                    if ($exists) {
                                        continue;
                                    }

                                    $userToOrgRole = new UserToOrgRole;
                                    $userToOrgRole->user_id = $userId;
                                    $userToOrgRole->org_id = $newGroupId;
                                    $userToOrgRole->role_id = $role;

                                    if ($userToOrgRole->save()) {
                                        $added ++;
                                    } else {
                                        $error ++;
                                    }
                                } else {
                                    Log::error('User with uri "'. $user['id'] .'" was not found in saved users');
                                }
                            }

                            $this->line('');
                            $this->line('User to organisation role total: '. (isset($res['users']) ? count($res['users']) : '0'));
                            $this->info('Successful: '. $added);
                            $this->error('Failed: '. $error);
                        }
                    }

                    $success ++;
                    Log::info('Group "'. $res['display_name'] .'" added successfully!');
                } else {
                    $failed ++;
                    Log::error('Group "'. $res['display_name'] .'" with id: "'. $res['id'] .'" failed!');
                }

                $bar->advance();
            }

            $bar->finish();
        }

        $this->line('');

        if ($oldRecords > 0) {
            $this->line('Already saved groups: '. $oldRecords);
        }

        $this->line('Groups total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Groups successful: '. $success);
        $this->error('Groups failed: '. $failed);
    }

    private function migrateUsers()
    {
        $params = [
            'all_fields' => true
        ];

        $oldRecords = 0;
        $success = $failed = 0;

        $response = request_url('user_list', $params);

        if (!empty($response['result'])) {
            $bar = $this->output->createProgressBar(count($response['result']));

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
                $newData['data']['uri'] = $res['id'];
                $newData['data']['created_at'] = $res['created'];
                $newData['data']['created_by'] = $this->migrationUserId;

                $request = Request::create('/api/addUser', 'POST', $newData);
                $api = new ApiUser($request);
                $result = $api->addUser($request)->getData();

                if ($result->success) {
                    $success ++;
                    Log::info('User "'. $res['name'] .'" added successfully!');
                } else {
                    $failed ++;
                    Log::error('User "'. $res['name'] .'" with id: "'. $res['id'] .'" failed!');
                }

                $bar->advance();
            }

            $bar->finish();
        }

        $this->line('');

        if ($oldRecords > 0) {
            $this->line('Already saved users: '. $oldRecords);
        }

        $this->line('Users total: '. (isset($response['result']) ? count($response['result']) : '0'));
        $this->info('Users successful: '. $success);
        $this->error('Users failed: '. $failed);
    }

    private function getUsersDatasets()
    {
        $totalOrgDatasets = 0;
        $totalSuccess = 0;
        $totalFailed = 0;

        $users = User::where('created_by', $this->migrationUserId)->pluck('uri')->toArray();
        $bar = $this->output->createProgressBar(count($users));

        if (is_array($users)) {
            foreach ($users as $user) {
                if (!empty($user)) {
                    $successPackages = 0;
                    $failedPacgakes = 0;

                    $params = [
                        'id'                => $user,
                        'include_datasets'  => true
                    ];
                    $response = request_url('user_show', $params);

                    if ($userData = $response['result']) {
                        $total = isset($userData['number_created_packages']) ? (int) $userData['number_created_packages'] : 0;

                        $this->line('');
                        $this->line('Current user has: '. $total .' datasets');

                        if ($total > 0) {
                            $this->line('Migrate datasets..');

                            foreach ($response['result']['datasets'] as $dataset) {
                                $result = [];
                                $alreadySavedDataset = DataSet::where('uri', $dataset['id'])->value('id');
                                $savedResCount = Resource::where('data_set_id', $alreadySavedDataset)->count();

                                if ($alreadySavedDataset && ($savedResCount = $dataset['num_resources'])) {
                                    Log::error('Dataset "'. $dataset['title'] .'" with all its resources are already saved. Dataset id: "'. $dataset['id'] .'"');
                                    continue;
                                }

                                $result = migrate_datasets($dataset['id'], true);

                                if (isset($result['success'])) {
                                    $successPackages++;

                                    $this->line('');
                                    $this->line('Resources total: '. $result['totalResources']);
                                    $this->info('Resources successful: '. $result['successResources']);
                                    $this->error('Resources failed: '. $result['failedResources']);
                                    $this->line('Unsupported resource format count for the current dataset: '. $result['unsuportedResources']);

                                    if (isset($result['followers']['success'])) {
                                        $this->line('Followers total: '. $result['followersInfo']['totalFollowers']);
                                        $this->info('Followers success: '. $result['followersInfo']['successFollowers']);
                                    } else {
                                        $this->info($result['followersInfo']['error_msg']);
                                    }
                                } else {
                                    $failedPacgakes++;

                                    if (isset($result['error'])) {
                                        $this->line('');
                                        $this->line($result['error_msg']);
                                    }
                                }

                                $totalSuccess += $successPackages;
                                $totalFailed += $failedPacgakes;
                                $this->line('');
                            }
                        }

                        $this->line('Users total datasets : '. $total);
                        $this->info('Dataset success: '. $successPackages);
                        $this->error('Dataset failed: '. $failedPacgakes);
                        $this->line('');
                        $bar->advance();
                    }
                }
            }
        }

        $this->line('Users Dataset Summary');
        $this->line('Total datasets: '. $totalOrgDatasets);
        $this->info('Total dataset success: '. $totalSuccess);
        $this->error('Total datasets failed: '. $totalFailed);
        $bar->finish();
    }

    private function getOrgsDatasets()
    {
        $totalOrgDatasets = $totalSuccess = $totalFailed = 0;

        $organisations = Organisation::where('created_by', $this->migrationUserId)->pluck('uri')->toArray();
        $bar = $this->output->createProgressBar(count($organisations));

        if (is_array($organisations)) {
            foreach ($organisations as $org) {
                $this->line('');
                $bar->advance();
                $this->line('');
                $failedPacgakes = $successPackages = 0;

                $params = [
                    'id'                => $org,
                    'include_datasets'  => true,
                ];

                $response = request_url('organization_show', $params);

                if ($orgData = $response['result']) {
                    $total = isset($orgData['package_count']) ? (int) $orgData['package_count'] : 0;

                    $this->line('Current organisation has: '. $total .' datasets');

                    if ($total > 0) {
                        $this->line('Migrate datasets..');

                        foreach ($orgData['packages'] as $dataset) {
                            $result = [];
                            $alreadySavedDataset = DataSet::where('uri', $dataset['id'])->value('id');
                            $savedResCount = Resource::where('data_set_id', $alreadySavedDataset)->count();

                            if ($alreadySavedDataset && ($savedResCount = $dataset['num_resources'])) {
                                Log::error('Dataset "'. $dataset['title'] .'" with all its resources are already saved. Dataset id: "'. $dataset['id'] .'"');
                                continue;
                            }

                            $result = migrate_datasets($dataset['id'], true);

                            if (isset($result['success'])) {
                                $successPackages++;

                                $this->line('');
                                $this->line('Resources total: '. $result['totalResources']);
                                $this->info('Resources successful: '. $result['successResources']);
                                $this->error('Resources failed: '. $result['failedResources']);
                                $this->line('Unsupported resource format count for the current dataset: '. $result['unsuportedResources']);

                                if (isset($result['followers']['success'])) {
                                    $this->line('Followers total: '. $result['followersInfo']['totalFollowers']);
                                    $this->info('Followers success: '. $result['followersInfo']['successFollowers']);
                                } else {
                                    $this->info($result['followersInfo']['error_msg']);
                                }
                                $this->line('');
                            } else {
                                $failedPacgakes++;

                                if (isset($result['error'])) {
                                    $this->line('');
                                    $this->line($result['error_msg']);
                                }
                            }

                            $totalSuccess += $successPackages;
                            $totalFailed += $failedPacgakes;
                            $totalOrgDatasets += $total;
                        }
                    }
                }

                $this->line('Organisation total datasets: '. $total);
                $this->info('Dataset success: '. $successPackages);
                $this->error('Dataset failed: '. $failedPacgakes);
            }
        }

        $this->line('');
        $this->line('Organisations Dataset Summary');
        $this->line('Total datasets: '. $totalOrgDatasets);
        $this->info('Total dataset success: '. $totalSuccess);
        $this->error('Total datasets failed: '. $totalFailed);
        $bar->finish();
    }

    private function migrateFollowers()
    {
        $countFollowers = 0;
        $addedFollowers = 0;

        $users = User::where('created_by', $this->migrationUserId)->pluck('uri', 'id');

        //Add user followers
        if ($users) {
            $this->line('');
            $this->line('Migrate user followers');
            $this->line('');
            $userBar = $this->output->createProgressBar(count($users));

            foreach ($users as $userId => $userUri) {
                $params = [
                    'id' => $userUri
                ];
                $response = request_url('user_follower_list', $params);

                if (isset($response['result']) && !empty($response['result'])) {
                    foreach ($response['result'] as $res) {
                        $userFollower = User::where('uri', $res['id'])->value('id');

                        if ($userFollower) {
                            $userFollowExists = UserFollow::where('user_id', $userFollower)
                                ->where('follow_user_id', $userId)
                                ->first();

                            if ($userFollowExists) {
                                continue;
                            }

                            $countFollowers++;
                            $newUserFollow['api_key'] = $this->apiKey;
                            $newUserFollow['user_id'] = $userFollower;
                            $newUserFollow['follow_user_id'] = $userId;

                            $userReq = Request::create('/api/addFollow', 'POST', $newUserFollow);
                            $api = new ApiFollow($userReq);
                            $api->addFollow($userReq)->getData();
                        }
                    }
                }

                $userBar->advance();
            }

            $userBar->finish();
        }

        //Add organisation followers
        $organisations = Organisation::where('created_by', $this->migrationUserId)->pluck('uri', 'id');

        if ($organisations) {
            $this->line('');
            $this->line('Migrate organisations followers');
            $this->line('');
            $orgBar = $this->output->createProgressBar(count($organisations));

            foreach ($organisations as $orgId => $orgUri) {
                $params = [
                    'id' => $orgUri
                ];
                $response = request_url('organization_follower_list', $params);

                if (isset($response['result']) && !empty($response['result'])) {
                    foreach ($response['result'] as $res) {
                        $orgFollower = User::where('uri', $res['id'])->value('id');

                        if ($orgFollower) {
                            $orgFollowExists = UserFollow::where('user_id', $orgFollower)
                                ->where('org_id', $orgId)
                                ->first();

                            if ($orgFollowExists) {
                                continue;
                            }

                            $countFollowers++;
                            $newOrgFollow['api_key'] = $this->apiKey;
                            $newOrgFollow['user_id'] = $orgFollower;
                            $newOrgFollow['org_id'] = $orgId;

                            $orgReq = Request::create('/api/addFollow', 'POST', $newOrgFollow);
                            $api = new ApiFollow($orgReq);
                            $api->addFollow($orgReq)->getData();
                        }

                        continue;
                    }
                }

                $orgBar->advance();
            }

            $orgBar->finish();
        }

        //Add data sets followers
        $dataSets = DataSet::where('is_migrated', true)->pluck('uri','id');

        if ($dataSets) {
            $this->line('');
            $this->line('Migrate datasets followers');
            $this->line('');
            $dsBar = $this->output->createProgressBar(count($dataSets));

            foreach ($dataSets as $dsId => $dsUri) {
                $params = [
                    'id' => $dsId
                ];
                $response = request_url('dataset_follower_list', $params);

                if (isset($response['result']) && !empty($response['result'])) {
                    foreach ($response['result'] as $res) {
                        $dsFollower = User::where('uri', $res['id'])->value('id');

                        if ($dsFollower) {
                            $dsFollowExists = UserFollow::where('user_id', $dsFollower)
                                ->where('data_set_id', $dsId)
                                ->first();

                            if ($dsFollowExists) {
                                continue;
                            }

                            $countFollowers++;
                            $newDataSetFollow['api_key'] = $this->apiKey;
                            $newDataSetFollow['user_id'] = $dsFollower;
                            $newDataSetFollow['data_set_id'] = $dsId;

                            $dataSetReq = Request::create('/api/addFollow', 'POST', $newDataSetFollow);
                            $api = new ApiFollow($dataSetReq);
                            $api->addFollow($dataSetReq)->getData();
                        }

                        continue;
                    }
                }

                $dsBar->advance();
            }

            $dsBar->finish();
        }

        $savedUsers = User::where('created_by', $this->migrationUserId)->pluck('id');

        $addedFollowers = UserFollow::whereIn('user_id', $savedUsers)->count();
        $this->line('');
        $this->line('Followers total: '. $countFollowers);
        $this->info('Followers success: '. $addedFollowers);
    }
}
