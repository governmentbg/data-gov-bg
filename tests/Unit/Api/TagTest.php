<?php

namespace Tests\Unit\Api;

use App\Tags;
use App\Category;
use Tests\TestCase;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TagTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test tag creation
     *
     * @return void
     */
    public function testAddTag()
    {
        // Test missing api_key
        $this->post(url('api/addTag'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/addTag'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful Tag create
        $this->post(
            url('api/addTag'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'      => $this->faker->word(),
                ]
            ]
        )
        ->assertStatus(200)
        ->assertJson(['success' => true]);
    }

    /**
     * Test tag modification
     *
     * @return void
     */
    public function testEditTag()
    {
        $tag = Tags::create(['name' => 'a new tag for edit test']);

        // Test missing api_key
        $this->post(url('api/editTag'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing tag_id
        $this->post(
            url('api/editTag'),
            [
                'api_key'   => $this->getApiKey(),
                'tag_id'    => null,
                'data'      => [
                    'name'      => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editTag'),
            [
                'api_key'   => $this->getApiKey(),
                'tag_id'    => null,
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful edit
        $this->post(
            url('api/editTag'),
            [
                'api_key'   => $this->getApiKey(),
                'tag_id'    => $tag->id,
                'data'      => [
                    'name'      => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test tag deletion
     *
     * @return void
     */
    public function testDeleteTag()
    {
        $tag = Tags::create(['name' => 'a new tag for delete test']);

        // Test missing api_key
        $this->post(url('api/deleteTag'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing tag_id
        $this->post(
            url('api/deleteTag'),
            [
                'api_key'   => $this->getApiKey(),
                'tag_id'    => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful delete
        $this->post(
            url('api/deleteTag'),
            [
                'api_key'   => $this->getApiKey(),
                'tag_id'    => $tag->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test tag list
     *
     * @return void
     */
    public function testListTags()
    {
        // Test missing api_key
        $this->post(url('api/listTags'), ['api_key' => null])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test tag details
     *
     * @return void
     */
    public function testGetTagDetails()
    {
        $tag = Tags::create([
            'name'      => $this->faker->name(),
        ]);

        // Test missing tag_id
        $this->post(url('api/getTagDetails'))
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test for success
        $this->post(url('api/getTagDetails'), [
            'tag_id' => $tag->id,
        ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test tag search
     *
     * @return void
     */
    public function testSearchTag()
    {
        $name = $this->faker->name();
        $tag = Tags::create([
            'name'  => $name,
        ]);

        // Test missing tag_id
        $this->post(url('api/searchTag'))
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test for success
        $this->post(url('api/searchTag'), [
            'name' => $name,
        ])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'tag' => ['name' => $name]]);
    }
}
