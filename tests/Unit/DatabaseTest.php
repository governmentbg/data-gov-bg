<?php

namespace Tests\Unit\Api;

use App\Role;
use App\User;
use App\Locale;
use App\Signal;
use App\DataSet;
use App\Category;
use App\Document;
use App\Resource;
use App\RoleRight;
use App\TermsOfUse;
use App\UserFollow;
use Tests\TestCase;
use App\UserSetting;
use App\DataSetGroup;
use App\Organisation;
use App\CustomSetting;
use App\UserToOrgRole;
use App\ActionsHistory;
use App\ElasticDataSet;
use App\TermsOfUseRequest;
use App\DataSetSubCategory;
use Faker\Factory as Faker;
use App\NewsletterDigestLog;
use App\Translator\Translation;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    const USER_RECORDS = 10;
    const PASSWORD_RESET_RECORDS = 10;
    const ROLES_RECORDS = 10;
    const ROLE_RIGHTS_RECORDS = 10;
    const NEWSLETTER_DIGEST_LOG_RECORDS = 10;
    const TRANSLATION_RECORDS = 10;
    const ORGANISATION_RECORDS = 10;
    const ACTIONS_HISTORY_RECORDS = 10;
    const CATEGORY_RECORDS = 10;
    const DATASET_RECORDS = 10;
    const RESOURCES_RECORDS = 10;
    const TERMS_RECORDS = 10;
    const SIGNALS_RECORDS = 10;
    const DOCUMENT_RECORDS = 10;
    const TERMS_OF_USE_REQUESTS_RECORDS = 10;
    const USER_FOLLOW_RECORDS = 10;
    const USER_SETTING_RECORDS = 10;
    const USER_TO_ORG_RECORDS = 10;
    const DATA_SET_GROUP_RECORDS = 10;
    const DATA_SET_SUB_CATEGORIES_RECORDS = 10;
    const ELASTIC_DATA_SET_RECORDS = 10;
    const CUSTOM_SETTING_RECORDS = 10;

    /**
     * Tests all tables structures and models
     *
     * @return void
     */
    public function testStructureAndModels()
    {
        $this->users();
        $this->passwordResets();
        $this->roles();
        $this->roleRights();
        $this->newsletterDigestLog();
        $this->locale();
        $this->translations();
        $this->organisations();
        $this->actionsHistory();
        $this->category();
        $this->dataset();
        $this->resources();
        $this->termsofuse();
        $this->signals();
        $this->documents();
        $this->termsofuserequests();
        $this->userfollows();
        $this->usersettings();
        $this->usertoorg();
        $this->datasetgroup();
        $this->datasetsubcategories();
        $this->elasticdatasets();
        $this->customsettings();
    }

    /**
     * Tests users table structure and models
     *
     * @return void
     */
    private function users()
    {
        // Test system user
        $this->assertDatabaseHas('users', ['username' => 'system']);

        // Test creation
        foreach (range(1, self::USER_RECORDS) as $i) {
            $user = null;
            $username = $this->faker->unique()->name;
            $userData = [
                'username'  => $username,
                'password'  => bcrypt($username),
                'email'     => $this->faker->unique()->email,
                'firstname' => $this->faker->firstName(),
                'lastname'  => $this->faker->lastName(),
                'add_info'  => $i != 1 ? $this->faker->text(intval(8000 / $i)) : null,
                'is_admin'  => rand(0, 1),
                'active'    => rand(0, 1),
                'approved'  => rand(0, 1),
                'api_key'   => $this->faker->uuid(),
                'hash_id'   => $this->faker->md5(),
            ];

            try {
                $user = User::create($userData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($user);
            $this->assertDatabaseHas('users', $userData);
        }

        if (!empty($user)) {
            // Test trigger
            $newUser = null;
            $newUserData = $userData;

            $newUserData['email'] = $this->faker->unique()->email;

            try {
                $newUser = User::create($newUserData);
            } catch (QueryException $ex) {}

            $this->assertNull($newUser);
            $this->assertDatabaseMissing('users', $newUserData);
        }

        if (!empty($user)) {
            // Test soft delete
            try {
                $user->delete();
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertSoftDeleted('users', $userData);
        }
    }

    /**
     * Tests password_resets table structure and models
     *
     * @return void
     */
    private function passwordResets()
    {
        // Test creation
        foreach (range(1, self::PASSWORD_RESET_RECORDS) as $i) {
            $record = null;
            $dbData = [
                'email'         => $this->faker->unique()->email,
                'token'         => $this->faker->uuid(),
                'created_at'    => $i != 1 ? $this->faker->dateTime() : null,
            ];

            try {
                $record = DB::table('password_resets')->insert($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);
            $this->assertDatabaseHas('password_resets', $dbData);
        }
    }

    /**
     * Tests roles table structure and models
     *
     * @return void
     */
    private function roles()
    {
        // Test creation
        foreach (range(1, self::ROLES_RECORDS) as $i) {
            $record = null;
            $dbData = [
                'name'      => $this->faker->word,
                'active'    => $this->faker->boolean(),
            ];

            try {
                $record = Role::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);
            $this->assertDatabaseHas('roles', $dbData);
        }
    }

    /**
     * Tests role_rights table structure and models
     *
     * @return void
     */
    private function roleRights()
    {
        $rights = array_keys(RoleRight::getRights());
        $roles = Role::limit(self::ROLES_RECORDS)->get()->toArray();

        // Test creation
        foreach ($roles as $role) {
            foreach ($rights as $right) {
                $record = null;
                $dbData = [
                    'role_id'           => $role['id'],
                    'module_name'       => $this->faker->unique()->word,
                    'right'             => $right,
                    'limit_to_own_data' => $this->faker->boolean(),
                    'api'               => $this->faker->boolean(),
                ];

                try {
                    $record = RoleRight::create($dbData);
                } catch (QueryException $ex) {
                    $this->log($ex->getMessage());
                }

                $this->assertNotNull($record);
                $this->assertDatabaseHas('role_rights', $dbData);
            }
        }

        if (!empty($dbData)) {
            // Test unique
            $newRecord = null;
            $newDbData = $dbData;

            try {
                $newRecord = RoleRight::create($newDbData);
            } catch (QueryException $ex) {}

            $this->assertNull($newRecord);
        }
    }

    /**
     * Tests newsletter_digest table structure and models
     *
     * @return void
     */
    private function newsletterDigestLog()
    {
        $types = array_keys(NewsletterDigestLog::getTypes());
        $users = User::limit(self::NEWSLETTER_DIGEST_LOG_RECORDS)->get()->toArray();

        // Test creation
        foreach ($users as $user) {
            $record = null;
            $dbData = [
                'user_id'   => $user['id'],
                'type'      => $this->faker->randomElement($types),
                'sent'      => $this->faker->dateTime(),
            ];

            try {
                $record = NewsletterDigestLog::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);
            $this->assertDatabaseHas('newsletter_digest_log', $dbData);
        }

        if (!empty($dbData)) {
            // Test unique
            $newRecord = null;
            $newDbData = $dbData;

            try {
                $newRecord = NewsletterDigestLog::create($newDbData);
            } catch (QueryException $ex) {}

            $this->assertNull($newRecord);
        }
    }

    /**
     * Tests locale table structure and models
     *
     * @return void
     */
    private function locale()
    {
        if (!Locale::all()->count()) {
            $languages = [
                'bg'    => true,
                'en'    => true,
                'ru'    => false,
                'xx'    => false,
            ];

            // Test creation
            foreach ($languages as $language => $active) {
                $record = null;
                $dbData = [
                    'locale'    => $language,
                    'active'    => $active,
                ];

                try {
                    $record = Locale::create($dbData);
                } catch (QueryException $ex) {
                    $this->log($ex->getMessage());
                }

                $this->assertNotNull($record);
                $this->assertDatabaseHas('locale', $dbData);
            }
        }
    }

    /**
     * Tests translations table structure and models
     *
     * @return void
     */
    private function translations()
    {
        $locales = Locale::limit(self::TRANSLATION_RECORDS)->get()->toArray();

        // Test creation
        $count = 0;

        while ($count != self::TRANSLATION_RECORDS) {
            foreach ($locales as $i => $locale) {
                $count++;

                if ($count == self::TRANSLATION_RECORDS) {
                    break;
                }

                $record = null;
                $dbData = [
                    'locale'    => $locale['locale'],
                    'group_id'  => $this->faker->unique()->numberBetween(99999, 9999999),
                    'text'      => $i % 2 == 0 ? $this->faker->text(intval(8000)) : null,
                    'label'     => $i % 2 == 1 ? $this->faker->text(intval(100)) : null,
                ];

                try {
                    $record = Translation::create($dbData);
                } catch (QueryException $ex) {
                    $this->log($ex->getMessage());
                }

                $this->assertNotNull($record);
                $this->assertDatabaseHas('translations', $dbData);
            }
        }

        if (!empty($dbData)) {
            // Test unique
            $newRecord = null;
            $newDbData = $dbData;

            try {
                $newRecord = Translation::create($newDbData);
            } catch (QueryException $ex) {}

            $this->assertNull($newRecord);
        }
    }

    /**
     * Tests organisations table structure and models
     *
     * @return void
     */
    private function organisations()
    {
        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $types = array_keys(Organisation::getTypes());

        // Test creation
        foreach (range(1, self::ORGANISATION_RECORDS) as $i) {
            $type = $this->faker->randomElement($types);
            $parentId = empty($parentId) && empty($record) || $type == Organisation::TYPE_GROUP ? null : $record->id;
            $locale = $this->faker->randomElement($locales)['locale'];
            $record = null;

            $dbData = [
                'type'              => $type,
                'uri'               => $this->faker->uuid(),
                'name'              => [$locale => $this->faker->name],
                'descript'          => [$locale => $this->faker->text(intval(8000))],
                'logo_file_name'    => $i != 1 ? $this->faker->imageUrl() : null,
                'logo_mime_type'    => $i != 1 ? $this->faker->mimeType() : null,
                'uri'               => $this->faker->url(),
                'logo_data'         => $i != 1 ? $this->faker->text(intval(8000)) : null,
                'parent_org_id'     => is_null($parentId) ? null : $parentId,
                'active'            => $this->faker->boolean(),
                'approved'          => $this->faker->boolean(),
            ];

            if ($i != 1) {
                $dbData = array_merge($dbData, [
                    'activity_info'     => [$locale => $this->faker->text(intval(8000))],
                    'contacts'          => [$locale => $this->faker->text(intval(100))],
                ]);
            }

            try {
                $record = Organisation::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('organisations', ['id' => $record->id]);
            }
        }

        if (!empty($record)) {
            // Test trigger
            $newRecord = null;
            $newDbData = $dbData;

            $newDbData['type'] = Organisation::TYPE_GROUP;
            $newDbData['parent_org_id'] = $record->id;

            try {
                $newRecord = User::create($newDbData);
            } catch (QueryException $ex) {}

            $this->assertNull($newRecord);
            $this->assertDatabaseMissing('organisations', $newDbData);
        }

        if (!empty($record)) {
            // Test soft delete
            try {
                $record->delete();
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertSoftDeleted('organisations', ['id' => $record->id]);
        }
    }

    /**
     * Tests actions_history table structure and models
     *
     * @return void
     */
    private function actionsHistory()
    {
        $users = User::select('id')->limit(self::ACTIONS_HISTORY_RECORDS)->get()->toArray();
        $types = array_keys(ActionsHistory::getTypes());
        $modules = ActionsHistory::MODULE_NAMES;

        // Test creation
        foreach (range(1, self::ACTIONS_HISTORY_RECORDS) as $i) {
            $user = $this->faker->randomElement($users)['id'];
            $type = $this->faker->randomElement($types);
            $module = $this->faker->randomElement($modules);
            $record = null;

            $dbData = [
                'user_id'       => $user,
                'occurrence'     => $this->faker->dateTime(),
                'module_name'   => $module,
                'action'        => $type,
                'action_object' => $this->faker->sentence(),
                'action_msg'    => $this->faker->sentence(),
                'ip_address'    => $this->faker->ipv4(),
                'user_agent'    => $this->faker->sentence(),
            ];

            try {
                $record = ActionsHistory::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

             $this->assertNotNull($record);
            if (!empty($record->id)) {
                $this->assertDatabaseHas('actions_history', ['id' => $record->id]);
            }
        }
    }

     /**
     * Tests category table structure and models
     *
     * @return void
     */
    private function category()
    {
        // Test creation
        foreach (range(1, self::CATEGORY_RECORDS) as $i) {
            $record = null;

            $dbData = [
                'name'              => $this->faker->name,
                'icon_file_name'    => $this->faker->name,
                'icon_mime_type'    => $this->faker->mimeType,
                'icon_data'         => $this->faker->name,
                'active'            => true,
                'ordering'          => Category::ORDERING_ASC,
            ];

            try {
                $record = Category::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('categories', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests data_sets table structure and models
     *
     * @return void
     */
    private function dataset()
    {
        $statuses = array_keys(DataSet::getStatus());
        $visibilities = array_keys(DataSet::getVisibility());
        // Test creation
        foreach (range(1, self::DATASET_RECORDS) as $i) {
            $record = null;
            $status = $this->faker->randomElement($statuses);
            $visibility = $this->faker->randomElement($visibilities);

            $dbData = [
                'uri'           => $this->faker->uuid(),
                'name'          => $this->faker->word(),
                'visibility'    => $visibility,
                'version'       => $this->faker->unique()->word,
                'status'        => $status,
            ];

            try {
                $record = DataSet::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('data_sets', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests resources table structure and models
     *
     * @return void
     */
    private function resources()
    {
        $types = array_keys(Resource::getTypes());
        $files = array_keys(Resource::getFormats());
        $httpTypes = array_keys(Resource::getRequestTypes());
        $dataSets = DataSet::orderBy('created_at', 'desc')->limit(self::RESOURCES_RECORDS)->get()->toArray();
        // Test creation
        foreach (range(1, self::RESOURCES_RECORDS) as $i) {
            $record = null;
            $dataSet = $this->faker->randomElement($dataSets)['id'];
            $type = $this->faker->randomElement($types);
            $fileType = $this->faker->randomElement($files);
            $httpType = $this->faker->randomElement($httpTypes);

            $dbData = [
                    'data_set_id'       => $dataSet,
                    'uri'               => $this->faker->uuid(),
                    'version'           => $this->faker->unique()->word,
                    'resource_type'     => $type,
                    'file_format'       => $fileType,
                    'resource_url'      => $this->faker->url(),
                    'http_rq_type'      => $httpType,
                    'authentication'    => $this->faker->name(),
                    'post_data'         => $this->faker->name(),
                    'http_headers'      => $this->faker->text(),
                    'name'              => [
                        'en' => $this->faker->name()
                    ],
                    'descript'          => [
                        'en' => $this->faker->text()
                    ],
                    'schema_descript'   => $this->faker->text(),
                    'schema_url'        => $this->faker->name(),
                    'is_reported'       => $this->faker->boolean(),
            ];

            try {
                $record = Resource::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('resources', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests termsofuse table structure and models
     *
     * @return void
     */
    private function termsofuse()
    {
        // Test creation
        foreach (range(1, self::TERMS_RECORDS) as $i) {
            $record = null;
            $dbData = [
                'name'          => $this->faker->name,
                'descript'      => $this->faker->text,
                'active'        => 1,
                'is_default'    => 1,
                'ordering'      => 1,
            ];

            try {
                $record = TermsOfUse::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('terms_of_use', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests signals table structure and models
     *
     * @return void
     */
    private function signals()
    {
        $resources = Resource::limit(self::SIGNALS_RECORDS)->get()->toArray();
        // Test creation
        foreach (range(1, self::SIGNALS_RECORDS) as $i) {
            $resource = $this->faker->randomElement($resources)['id'];
            $record = null;
            $dbData = [
                'resource_id' => $resource,
                'descript' =>$this->faker->sentence(4),
                'firstname' => $this->faker->firstName(),
                'lastname' => $this->faker->lastName(),
                'email'=> $this->faker->email(),
                'status' => $this->faker->boolean()
            ];

            try {
                $record = Signal::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('signals', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests documents table structure and models
     *
     * @return void
     */
    private function documents()
    {
        $locales = Locale::where('active', 1)->limit(self::DOCUMENT_RECORDS)->get()->toArray();
        // Test creation
        foreach (range(1, self::DOCUMENT_RECORDS) as $i) {
            $locale = $this->faker->randomElement($locales)['locale'];
            $record = null;
            $dbData = [
                'name'          => 1,
                'descript'      => 1,
                'file_name'     => $this->faker->word,
                'mime_type'     => $this->faker->word,
                'data'          => $this->faker->sentence(4)
            ];

            try {
                $record = Document::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('documents', ['id' => $record->id]);
            }
        }
    }

     /**
     * Tests terms_of_use_requests table structure and models
     *
     * @return void
     */
    private function termsofuserequests()
    {
        // Test creation
        foreach (range(1, self::TERMS_OF_USE_REQUESTS_RECORDS) as $i) {
            $record = null;
            $dbData = [
                'descript'      => $this->faker->sentence(),
                'firstname'     => $this->faker->firstName(),
                'lastname'      => $this->faker->lastName(),
                'email'         => $this->faker->email(),
                'status'        => $this->faker->boolean(),
            ];

            try {
                $record = TermsOfUseRequest::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('terms_of_use_requests', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests user_follows table structure and models
     *
     * @return void
     */
    private function userfollows()
    {
        $users = User::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $dataSets = DataSet::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        $categories = Category::select('id')->limit(self::USER_FOLLOW_RECORDS)->get()->toArray();
        // Test creation
        foreach (range(1, self::USER_FOLLOW_RECORDS) as $i) {
            $user = $this->faker->randomElement($users)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];
            $dataSet = $this->faker->randomElement($dataSets)['id'];
            $category = $this->faker->randomElement($categories)['id'];

            $record = null;
            $dbData = [
                'user_id'     => $user,
                'org_id'      => $organisation,
                'data_set_id' => $dataSet,
                'category_id' => $category,
                'news'        => $this->faker->numberBetween(10,20)
            ];

            try {
                $record = UserFollow::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('user_follows', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests user_settings table structure and models
     *
     * @return void
     */
    private function usersettings()
    {
        $users = User::select('id')->limit(self::USER_SETTING_RECORDS)->get()->toArray();
        $locales = Locale::where('active', 1)->limit(self::USER_SETTING_RECORDS)->get()->toArray();
        $newsLetters = NewsLetterDigestLog::select('id')->limit(self::USER_SETTING_RECORDS)->get()->toArray();

        // Test creation
        foreach (range(1, self::USER_SETTING_RECORDS) as $i) {
            $user = $this->faker->unique()->randomElement($users)['id'];
            $locale = $this->faker->randomElement($locales)['locale'];
            $newsLetter = $this->faker->randomElement($newsLetters)['id'];

            $record = null;
            $dbData = [
                'user_id'           => $user,
                'locale'            => $locale,
                'newsletter_digest' => $newsLetter
            ];

            try {
                $record = UserSetting::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('user_settings', ['id' => $record->id]);
            }
        }
    }

    /**
     * Tests user_to_org_role table structure and models
     *
     * @return void
     */
    private function usertoorg()
    {
        $users = User::select('id')->limit(self::USER_TO_ORG_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::USER_TO_ORG_RECORDS)->get()->toArray();
        $roles = Role::limit(self::USER_TO_ORG_RECORDS)->get()->toArray();
        $newFaker = Faker::create();

        // Test creation
        foreach (range(1, self::USER_TO_ORG_RECORDS) as $i) {
            $user =  $newFaker->unique()->randomElement($users)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];
            $role = $this->faker->randomElement($roles)['id'];

            $record = null;
            $dbData = [
                'user_id' => $user,
                'org_id'  => $organisation,
                'role_id' => $role
            ];

            try {
                $record = UserToOrgRole::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('user_to_org_role', ['id' => $record->user_id]);
            }
        }
    }

    /**
     * Tests data_set_groups table structure and models
     *
     * @return void
     */
    private function datasetgroup()
    {
        $dataSets = DataSet::select('id')->limit(self::DATA_SET_GROUP_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::DATA_SET_GROUP_RECORDS)->get()->toArray();

        $groupFaker = Faker::create(); //creating a new instance so the unique() works. Can be revised if solution is found.

        // Test creation
        foreach (range(1, self::DATA_SET_GROUP_RECORDS) as $i) {
            $dataSet = $groupFaker->unique()->randomElement($dataSets)['id'];
            $organisation = $this->faker->randomElement($organisations)['id'];

            $record = null;
            $dbData = [
                'data_set_id' => $dataSet,
                'group_id'  => $organisation
            ];

            try {
                $record = DataSetGroup::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('data_set_groups', ['id' => $record->data_set_id]);
            }
        }
    }

    /**
     * Tests data_set_groups table structure and models
     *
     * @return void
     */
    private function datasetsubcategories()
    {
        $dataSets = DataSet::select('id')->limit(self::DATA_SET_SUB_CATEGORIES_RECORDS)->get()->toArray();
        $categories = Category::select('id')->limit(self::DATA_SET_SUB_CATEGORIES_RECORDS)->get()->toArray();

        $subCatFaker = Faker::create();//creating a new instance so the unique() works. Can be revised if solution is found.
        // Test creation
        foreach (range(1, self::DATA_SET_GROUP_RECORDS) as $i) {
            $dataSet =  $subCatFaker->unique()->randomElement($dataSets)['id'];
            $category = $this->faker->randomElement($categories)['id'];

            $record = null;
            $dbData = [
                'data_set_id' => $dataSet,
                'sub_cat_id'  => $category
            ];

            try {
                $record = DataSetSubCategory::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('data_set_sub_category', ['id' => $record->data_set_id]);
            }
        }
    }


    private function elasticdatasets()
    {
        // Test creation
        foreach (range(1, self::DATA_SET_GROUP_RECORDS) as $i) {
            $record = null;
            $dbData = [
                'index' => $this->faker->word,
                'index_type'  => $this->faker->word,
                'doc' => $this->faker->randomDigit()
            ];

            try {
                $record = ElasticDataSet::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('elastic_data_set', ['id' => $record->id]);
            }
        }
    }

    private function customsettings()
    {
        $dataSets = DataSet::select('id')->limit(self::CUSTOM_SETTING_RECORDS)->get()->toArray();
        $organisations = Organisation::select('id')->limit(self::CUSTOM_SETTING_RECORDS)->get()->toArray();
        $resources = Resource::limit(self::CUSTOM_SETTING_RECORDS)->get()->toArray();

        // Test creation
        foreach (range(1, self::CUSTOM_SETTING_RECORDS) as $i) {

          $organisation =  $this->faker->randomElement($organisations)['id'];


            $record = null;
            $dbData = [
                'org_id' => $organisation,
                'data_set_id'  => null,
                'resource_id' => null,
                'key' => 1,
                'value' => 2
            ];

            try {
                $record = CustomSetting::create($dbData);
            } catch (QueryException $ex) {
                $this->log($ex->getMessage());
            }

            $this->assertNotNull($record);

            if (!empty($record->id)) {
                $this->assertDatabaseHas('custom_settings', ['id' => $record->id]);
            }
        }
    }
}
