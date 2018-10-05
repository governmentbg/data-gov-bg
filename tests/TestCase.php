<?php

namespace Tests;

use App\Page;
use App\Role;
use App\User;
use App\DataSet;
use App\Document;
use App\Resource;
use App\DataRequest;
use App\Organisation;
use App\UserToOrgRole;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $apiKey;
    protected $userId;
    protected $locale = 'en';

    protected function log($message)
    {
        echo PHP_EOL . PHP_EOL . print_r($message, true) . PHP_EOL;
    }

    protected function getApiKey()
    {
        if (isset($this->apiKey)) {
            return $this->apiKey;
        }

        $this->apiKey = User::where('username', 'system')->first()->api_key;

        return $this->apiKey;
    }

    protected function getUserId()
    {
        if (isset($this->systemUser)) {
            return $this->systemUser->id;
        }

        if (isset($this->userId)) {
            return $this->userId;
        }

        $this->userId = User::where('username', 'system')->first()->id;

        return $this->userId;
    }

    protected function getSystemUser()
    {
        if (isset($this->systemUser)) {
            return $this->systemUser;
        }

        $this->systemUser = User::where('username', 'system')->first();

        return $this->systemUser;
    }

    protected function getNewOrganisation($data = [])
    {
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);

        return Organisation::create(array_merge([
            'type'              => $type,
            'name'              => ApiController::trans($this->locale, $this->faker->name),
            'descript'          => ApiController::trans($this->locale, $this->faker->text(intval(8000))),
            'uri'               => $this->faker->uuid(),
            'logo_file_name'    => $this->faker->imageUrl(),
            'logo_mime_type'    => $this->faker->mimeType(),
            'logo_data'         => $this->faker->text(intval(8000)),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ], $data));
    }

    protected function getNewDataRequest($orgId)
    {
        return DataRequest::create([
            'org_id'           => $orgId,
            'descript'         => $this->faker->sentence(3),
            'published_url'    => $this->faker->url,
            'contact_name'     => $this->faker->name,
            'email'            => $this->faker->email,
            'notes'            => $this->faker->sentence(4),
            'status'           => $this->faker->boolean()
        ]);
    }

    protected function getNewDataSet()
    {
        return DataSet::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => 1,
            'status'        => $this->faker->numberBetween(1, 2),
        ]);
    }

    protected function getNewResource($dataSetId = null)
    {
        if (empty($dataSetId)) {
            $dataSetId = $this->getNewDataSet()->id;
        }

        return Resource::create([
            'data_set_id'       => $dataSetId,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => 1,
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);
    }

    protected function getNewPage($data = [])
    {
        return Page::create(array_merge([
            'title'     => ApiController::trans($this->locale, $this->faker->word()),
            'type'      => Page::TYPE_PAGE,
            'active'    => true,
        ], $data));
    }

    protected function getNewRole($data = [])
    {
        return Role::create(array_merge([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ], $data));
    }

    protected function getNewUser($data = [])
    {
        $username = $this->faker->unique()->name;

        return User::create(array_merge([
            'username'  => $username,
            'password'  => bcrypt($username),
            'email'     => $this->faker->unique()->email,
            'firstname' => $this->faker->firstName(),
            'lastname'  => $this->faker->lastName(),
            'is_admin'  => rand(0, 1),
            'active'    => rand(0, 1),
            'approved'  => rand(0, 1),
            'api_key'   => $this->faker->uuid(),
            'hash_id'   => $this->faker->md5(),
        ], $data));
    }

    protected function getNewUserToOrgRole($data = [])
    {
        $orgId = $this->getNewOrganisation()->id;
        $userId = $this->getNewUser()->id;
        $roleId = $this->getNewRole()->id;

        return UserToOrgRole::create(array_merge([
            'org_id'    => $orgId,
            'user_id'   => $userId,
            'role_id'   => $roleId,
        ], $data));
    }

    protected function getNewDocument()
    {
        $doc = Document::create([
            'name'         => ApiController::trans($this->locale, $this->faker->word()),
            'descript'     => ApiController::trans($this->locale, $this->faker->word()),
            'file_name'    => $this->faker->word(),
            'mime_type'    => $this->faker->word(),
        ]);

        file_put_contents('storage/docs/'. $doc->id, $this->faker->sentence());

        return $doc;
    }
}
