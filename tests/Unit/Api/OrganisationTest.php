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

        $this->post(url('api/addOrganisation'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/addOrganisation'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/addOrganisation'),
                [
                    'api_key' => $this->getApiKey(),
                    'data'    => [
                        'name'          => $this->faker->name,
                        'description'   => $this->faker->name,
                        'locale'        => $locale,
                        'type'          => $type,
                        'active'        => $this->faker->boolean(),
                        'approved'      => $this->faker->boolean(),
                        'activity_info' => $this->faker->text(intval(8000)),
                        'contacts'      => $this->faker->text(intval(100)),
                    ]
                ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
