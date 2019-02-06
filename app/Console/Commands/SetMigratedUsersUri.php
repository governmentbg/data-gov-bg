<?php

namespace App\Console\Commands;

use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Http\Controllers\Api\UserController as ApiUser;

class SetMigratedUsersUri extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migratedUsers:setURI';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set migrated users uri';

    protected $migrationUserId;

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
            $this->init();
            $this->MapUsers();
        } catch (\Exception $ex) {
            $this->error('Mapping collection failed!');

            Log::error($ex->getMessage());
        }
    }

    private function init()
    {
        $this->info('Get all migrated users and associate them with their profiles in the old system..');

        $this->migrationUserId = DB::table('users')->where('username', 'migrate_data')->value('id');

        //Login
        \Auth::loginUsingId($this->migrationUserId);
    }

    private function MapUsers()
    {
        $migratedUsers = DB::table('users')->where('created_by', $this->migrationUserId)->count();
        $updatedUsers = $failedUsers = $userNotFound = 0;

        $params = [
            'all_fields' => true
        ];

        $response = request_url('user_list', $params);
        $oldSystemUsers = isset($response['result']) ? count($response['result']) : 0;

        if ($response['result']) {
            $this->line('');
            $bar = $this->output->createProgressBar(count($response['result']));

            foreach ($response['result'] as $result) {
                $savedUser = DB::table('users')
                    ->where('username', trim($result['name']))
                    ->where('uri', null)
                    ->first();

                if ($savedUser) {
                    $newData['id'] = $savedUser->id;
                    $newData['data']['uri'] = $result['id'];
                    $newData['data']['updated_by'] = $this->migrationUserId;

                    $request = Request::create('/api/editUser', 'POST', $newData);
                    $api = new ApiUser($request);
                    $result = $api->editUser($request)->getData();

                    if ($result->success) {
                        $updatedUsers++;
                    } else {
                        $failedUsers++;
                    }
                } else {
                    $userNotFound++;
                    $this->line('');
                    $this->line('Users with username: '. $result['name'] .' and email: '. $result['email'] .' were not found in the database.');
                }

                $bar->advance();
            }
        }

        if (isset($bar)) {
            $bar->finish();
        }

        $this->line('');
        $this->line('Migrated users: '. $migratedUsers);
        $this->line('Old system users: '. $oldSystemUsers);
        $this->line('Successfully updated users: '. $updatedUsers);
        $this->line('Users failed to update: '. $failedUsers);
        $this->line('Users that were not found: '. $userNotFound);
    }
}
