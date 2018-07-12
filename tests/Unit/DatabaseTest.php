<?php

namespace Tests\Unit\Api;

use App\Role;
use App\User;
use App\Locale;
use App\RoleRight;
use Tests\TestCase;
use App\Translation;
use App\Organisation;
use App\ActionsHistory;
use App\NewsletterDigestLog;
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
                'name'              => [$locale => $this->faker->name],
                'descript'          => [$locale => $this->faker->text(intval(8000))],
                'logo_file_name'    => $i != 1 ? $this->faker->imageUrl() : null,
                'logo_mime_type'    => $i != 1 ? $this->faker->mimeType() : null,
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
}
