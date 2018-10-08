<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Section;
use App\Http\Controllers\ApiController;

class SectionTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Test for SectionController@addSection
     */
    public function testAddSection()
    {
        // Test missing api_key
        $this->post(url('api/addSection'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/addSection'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful section create
        $this->post(
            url('api/addSection'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'          => $this->faker->word(),
                    'locale'        => $this->locale,
                    'active'        => $this->faker->boolean(),
                    'read_only'     => $this->faker->boolean(),
                    'forum_link'    => $this->faker->url(),
                    'ordering'      => $this->faker->numberBetween(0, 10),
                    'theme'         => $this->faker->numberBetween(0, 10),
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
    /**
     * Test for SectionController@editSection
     */
    public function testEditSection()
    {
        $section = Section::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'active'        => $this->faker->boolean(),
            'read_only'     => $this->faker->boolean(),
            'forum_link'    => $this->faker->url(),
            'ordering'      => $this->faker->numberBetween(0, 10),
            'theme'         => $this->faker->numberBetween(0, 10),
            'created_by'    => 1,
        ]);

        // Test missing api_key
        $this->post(url('api/editSection'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing record id
        $this->post(
            url('api/editSection'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'          => $this->faker->word(),
                    'locale'        => $this->locale,
                    'active'        => $this->faker->boolean(),
                    'read_only'     => $this->faker->boolean(),
                    'forum_link'    => $this->faker->url(),
                    'ordering'      => $this->faker->numberBetween(0, 10),
                    'theme'         => $this->faker->numberBetween(0, 10),
                ]
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/addSection'),
            [
                'api_key'   => $this->getApiKey(),
                'id'        => $section->id,
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful section edit
        $this->post(
            url('api/editSection'),
            [
                'api_key'   => $this->getApiKey(),
                'id'        => $section->id,
                'data'      => [
                    'name'          => $this->faker->word(),
                    'locale'        => $this->locale,
                    'active'        => $this->faker->boolean(),
                    'read_only'     => $this->faker->boolean(),
                    'forum_link'    => $this->faker->url(),
                    'ordering'      => $this->faker->numberBetween(0, 10),
                    'theme'         => $this->faker->numberBetween(0, 10),
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for SectionController@deleteSection
     */
    public function testDeleteSection()
    {
        $section = Section::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'active'        => $this->faker->boolean(),
            'read_only'     => $this->faker->boolean(),
            'forum_link'    => $this->faker->url(),
            'ordering'      => $this->faker->numberBetween(0, 10),
            'theme'         => $this->faker->numberBetween(0, 10),
            'created_by'    => 1,
        ]);

        // Test missing api_key
        $this->post(url('api/deleteSection'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing record id
        $this->post(url('api/deleteSection'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful section delete
        $this->post(
            url('api/deleteSection'),
            [
                'api_key'   => $this->getApiKey(),
                'id'        => $section->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for SectionController@listSections
     */
    public function testListSections()
    {
        // Test empty criteria
        $this->post(
            url('api/listSections'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Test successful section list
        $this->post(
            url('api/listSections'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [
                    'active' => $this->faker->boolean(),
                    'locale' => $this->locale,
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for SectionController@listSubsections
     */
    public function testListSubsections()
    {
        // Test empty criteria
        $this->post(
            url('api/listSubsections'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Test successful subsection list
        $this->post(
            url('api/listSubsections'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [
                    'active' => $this->faker->boolean(),
                    'locale' => $this->locale,
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
