<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\TermsOfUse;

class TermsOfUseTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Test for TermsOfUseController@addTermsOfUse
     */
    public function testAddTermsOfUse()
    {
        // test missing api_key
        $this->post(url('api/addTermsOfUse'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test empty data
        $this->post(
            url('api/addTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful section create
        $this->post(
            url('api/addTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'          => $this->faker->word(),
                    'description'   => $this->faker->word(),
                    'locale'        => 'en',
                    'active'        => $this->faker->boolean(),
                    'is_default'    => $this->faker->boolean(),
                    'ordering'      => $this->faker->numberBetween(0,10),
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
    /**
     * Test for TermsOfUseController@editTermsOfUse
     */
    public function testEditTermsOfUse()
    {
        $section = TermsOfUse::create([
            'name'          => ['en' => $this->faker->word()],
            'descript'      => ['en' => $this->faker->word()],
            'active'        => $this->faker->boolean(),
            'is_default'    => $this->faker->boolean(),
            'ordering'      => $this->faker->numberBetween(0,10),
            'created_by'    => 1,
        ]);

        // test missing api_key
        $this->post(url('api/editTermsOfUse'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing record id
        $this->post(
            url('api/editTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'          => $this->faker->word(),
                    'description'   => $this->faker->word(),
                    'locale'        => 'en',
                    'active'        => $this->faker->boolean(),
                    'is_default'    => $this->faker->boolean(),
                    'ordering'      => $this->faker->numberBetween(0,10),
                ]
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test empty data
        $this->post(
            url('api/editTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'terms_id'  => $section->id,
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful section edit
        $this->post(
            url('api/editTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'terms_id'  => $section->id,
                'data'      => [
                    'name'          => $this->faker->word(),
                    'description'   => $this->faker->word(),
                    'locale'        => 'en',
                    'active'        => $this->faker->boolean(),
                    'is_default'    => $this->faker->boolean(),
                    'ordering'      => $this->faker->numberBetween(0,10),
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for TermsOfUseController@deleteTermsOfUse
     */
    public function testDeleteTermsOfUse()
    {
        $section = TermsOfUse::create([
            'name'          => ['en' => $this->faker->word()],
            'descript'      => ['en' => $this->faker->word()],
            'active'        => $this->faker->boolean(),
            'is_default'    => $this->faker->boolean(),
            'ordering'      => $this->faker->numberBetween(0,10),
            'created_by'    => 1,
        ]);

        // test missing api_key
        $this->post(url('api/deleteTermsOfUse'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing record id
        $this->post(url('api/deleteTermsOfUse'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful section delete
        $this->post(
            url('api/deleteTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'terms_id'  => $section->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for TermsOfUseController@listTermsOfUse
     */
    public function testListTermsOfUse()
    {
        // test missing api_key
        $this->post(url('api/listTermsOfUse'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test empty criteria
        $this->post(
            url('api/listTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // test successful section list
        $this->post(
            url('api/listTermsOfUse'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [
                    'active' => $this->faker->boolean(),
                    'locale' => 'en'
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for TermsOfUseController@getTermsOfUseDetails
     */
    public function testGetTermsOfUseDetails()
    {
        $section = TermsOfUse::create([
            'name'          => ['en' => $this->faker->word()],
            'descript'      => $this->faker->word(),
            'active'        => $this->faker->boolean(),
            'is_default'    => $this->faker->boolean(),
            'ordering'      => $this->faker->numberBetween(0,10),
            'created_by'    => 1,
        ]);
        // test missing api_key
        $this->post(url('api/getTermsOfUseDetails'), [
                'api_key'   => null,
                'terms_id'  => $section->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // test missing record id
        $this->post(url('api/getTermsOfUseDetails'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful section delete
        $this->post(
            url('api/getTermsOfUseDetails'),
            [
                'api_key'   => $this->getApiKey(),
                'terms_id'  => $section->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
