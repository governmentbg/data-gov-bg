<?php

namespace Tests\Unit\Api;

use App\Role;
use App\User;
use App\Locale;
use Tests\TestCase;
use App\Organisation;
use App\UserToOrgRole;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrganisationTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    const ORGANISATION_RECORDS = 10;

    public function testAddOrganisation()
    {
        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $locale = $this->faker->randomElement($locales)['locale'];
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);
        $apiKey = $this->getApiKey();

        $this->post(url('api/addOrganisation'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/addOrganisation'), ['api_key' => $apiKey])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(url('api/addOrganisation'), [
            'api_key'       => $apiKey,
            'data'          => [
                'name'          => $this->faker->name,
                'description'   => $this->faker->name,
                'locale'        => $locale,
                'type'          => $type,
                'active'        => $this->faker->boolean(),
                'approved'      => $this->faker->boolean(),
                'activity_info' => $this->faker->text(8000),
                'contacts'      => $this->faker->text(100),
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testEditOrganisation()
    {
        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $locale = $this->faker->randomElement($locales)['locale'];
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);
        $apiKey = $this->getApiKey();

        $org = Organisation::create([
            'type'              => $type,
            'name'              => [$locale => $this->faker->name],
            'descript'          => [$locale => $this->faker->text(intval(8000))],
            'uri'               => $this->faker->uuid(),
            'logo_file_name'    => $this->faker->imageUrl(),
            'logo_mime_type'    => $this->faker->mimeType(),
            'logo_data'         => $this->faker->text(intval(8000)),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ]);

        $org->searchable();

        $this->post(url('api/editOrganisation'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $locale = $this->faker->randomElement($locales)['locale'];
        $type = $this->faker->randomElement($types);

        $this->post(url('api/editOrganisation'), ['api_key' => $apiKey])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(url('api/editOrganisation'), [
            'api_key'           => $apiKey,
            'org_id'            => $org->id,
            'data'              => [
                'type'              => $type,
                'locale'            => $locale,
                'name'              => $this->faker->name,
                'descript'          => $this->faker->text(intval(8000)),
                'logo_file_name'    => $this->faker->imageUrl(),
                'logo_mime_type'    => $this->faker->mimeType(),
                'logo_data'         => $this->faker->text(intval(8000)),
                'active'            => $this->faker->boolean(),
                'approved'          => $this->faker->boolean(),
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testDeleteOrganisation()
    {
        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $locale = $this->faker->randomElement($locales)['locale'];
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);
        $apiKey = $this->getApiKey();

        $org = Organisation::create([
            'type'              => $type,
            'name'              => [$locale => $this->faker->name],
            'descript'          => [$locale => $this->faker->text(intval(8000))],
            'uri'               => $this->faker->uuid(),
            'logo_file_name'    => $this->faker->imageUrl(),
            'logo_mime_type'    => $this->faker->mimeType(),
            'logo_data'         => $this->faker->text(intval(8000)),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ]);

        $this->post(url('api/deleteOrganisation'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/deleteOrganisation'), ['api_key' => $apiKey])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(url('api/deleteOrganisation'), [
            'api_key'           => $apiKey,
            'org_id'            => $org->id,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testListOrganisations()
    {
        // test missing api_key
        $this->post(url('api/listOrganisations'), ['api_key' => null])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // test empty criteria
        $this->post(url('api/listOrganisations'), [
            'api_key'    => $this->getApiKey(),
            'criteria'   => [],
        ])->assertStatus(200)->assertJson(['success' => true]);

        // test successful list
        $this->post(url('api/listOrganisations'), [
            'criteria'  => [
                'active'    => $this->faker->numberBetween(0, 1),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testSearchOrganisations()
    {
        // test empty criteria
        $this->post(url('api/searchOrganisations'), ['api_key' => null])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test search criteria
        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $locale = $this->faker->randomElement($locales)['locale'];
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);
        $name = $this->faker->name;

        $org = Organisation::create([
            'type'              => $type,
            'name'              => ApiController::trans($locale, $name),
            'descript'          => ApiController::trans($locale, $this->faker->text(intval(8000))),
            'uri'               => $this->faker->uuid(),
            'logo_file_name'    => $this->faker->imageUrl(),
            'logo_mime_type'    => $this->faker->mimeType(),
            'logo_data'         => $this->faker->text(intval(8000)),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ]);

        $this->post(url('api/searchOrganisations'), [
            'criteria'  => [
                'locale'    => 'bg',
                'keywords'  => $name,
            ],
        ])->assertStatus(200)->assertJson(['success' => true, 'organisations' => [['name' => $name]]]);
    }

    public function testOrganisationDetails()
    {
        // test empty criteria
        $this->post(url('api/getOrganisationDetails'), ['api_key' => null])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test search criteria
        $locales = Locale::where('active', 1)->limit(self::ORGANISATION_RECORDS)->get()->toArray();
        $locale = $this->faker->randomElement($locales)['locale'];
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);
        $name = $this->faker->name;

        $org = Organisation::create([
            'type'              => $type,
            'name'              => ApiController::trans($locale, $name),
            'descript'          => ApiController::trans($locale, $this->faker->text(intval(8000))),
            'uri'               => $this->faker->uuid(),
            'logo_file_name'    => $this->faker->imageUrl(),
            'logo_mime_type'    => $this->faker->mimeType(),
            'logo_data'         => $this->faker->text(intval(8000)),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ]);

        $this->post(url('api/getOrganisationDetails'), [
            'org_id' => $org->id,
        ])->assertStatus(200)->assertJson(['success' => true, 'data' => ['name' => $name]]);
    }

    public function testMembers()
    {
        // test empty criteria
        $this->post(url('api/getMembers'))
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test search criteria
        $org = Organisation::select('id')->inRandomOrder()->first()->toArray();
        $role = Role::select('id')->inRandomOrder()->first()->toArray();

        if (!empty($org)) {
            $this->post(url('api/getMembers'), [
                'org_id'        => $org['id'],
                'role_id'       => $role['id'],
                'for_approval'  => $this->faker->boolean(),
                'keywords'      => $this->faker->word,
            ])->assertStatus(200)->assertJson(['success' => true]);
        }
    }

    public function testDelMember()
    {
        // test no api key
        $this->post(url('api/delMember'))
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test empty criteria
        $this->post(url('api/delMember'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $userToOrgRole = UserToOrgRole::inRandomOrder()->first()->toArray();

        if (!empty($userToOrgRole)) {
            $this->post(url('api/delMember'), [
                'api_key'       => $this->getApiKey(),
                'org_id'        => $userToOrgRole['org_id'],
                'user_id'       => $userToOrgRole['user_id'],
            ])->assertStatus(200)->assertJson(['success' => true]);
        }
    }

    public function testEditMember()
    {
        // test no api key
        $this->post(url('api/editMember'))
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test empty criteria
        $this->post(url('api/editMember'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $userToOrgRole = UserToOrgRole::inRandomOrder()->first()->toArray();

        if (!empty($userToOrgRole)) {
            $this->post(url('api/delMember'), [
                'api_key'       => $this->getApiKey(),
                'org_id'        => $userToOrgRole['org_id'],
                'user_id'       => $userToOrgRole['user_id'],
                'role_id'       => $userToOrgRole['role_id'],
            ])->assertStatus(200)->assertJson(['success' => true]);
        }
    }
}
