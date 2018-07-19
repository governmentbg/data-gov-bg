<?php

namespace Tests\Unit\Api;

use App\Locale;
use Tests\TestCase;
use App\Organisation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganisationTest extends TestCase
{
    use WithFaker;

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
            'logo_file_name'    => $this->faker->imageUrl(),
            'logo_mime_type'    => $this->faker->mimeType(),
            'logo_data'         => $this->faker->text(intval(8000)),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ]);

        $this->post(url('api/editOrganisation'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $locale = $this->faker->randomElement($locales)['locale'];
        $type = $this->faker->randomElement($types);

        $this->post(
            url('api/editOrganisation'),
            ['api_key' => $apiKey]
        )->assertStatus(500)->assertJson(['success' => false]);

        $this->post(
            url('api/editOrganisation'),
            [
                'api_key'           => $apiKey,
                'org_id'            => $org->id,
                'data'              => [
                    'type'              => $type,
                    'name'              => $this->faker->name,
                    'descript'          => $this->faker->text(intval(8000)),
                    'logo_file_name'    => $this->faker->imageUrl(),
                    'logo_mime_type'    => $this->faker->mimeType(),
                    'logo_data'         => $this->faker->text(intval(8000)),
                    'active'            => $this->faker->boolean(),
                    'approved'          => $this->faker->boolean(),
                ]
            ]
        )->assertStatus(200)->assertJson(['success' => true]);
    }
}
