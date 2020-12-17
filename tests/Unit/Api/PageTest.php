<?php

namespace Tests\Unit\Api;

use App\Page;
use App\Locale;
use Tests\TestCase;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PageTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test page creation
     *
     * @return void
     */
    public function testAddPage()
    {
        // Test missing api key
        $this->post(url('api/addPage'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/addPage'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Page::all()->count();

        $this->post(
            url('api/addPage'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'locale'            => $this->locale,
                    'title'             => $this->faker->word(),
                    'body'              => $this->faker->word(),
                    'head_title'        => $this->faker->word(),
                    'meta_description'  => $this->faker->word(),
                    'meta_keywords'     => $this->faker->word(),
                    'forum_link'        => $this->faker->word(),
                    'active'            => 1
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is made
        $this->assertTrue($count + 1 == Page::all()->count());
    }

    /**
     * Test page modification
     *
     * @return void
     */
    public function testEditPage()
    {
        $page = $this->getNewPage();

        // Test missing api key
        $this->post(url('api/editPage'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing page id
        $this->post(
            url('api/editPage'),
            [
                'api_key'   => $this->getApiKey(),
                'page_id'   => null,
                'data'      => [
                    'locale'    => $this->locale,
                    'title'               => $this->faker->word(),
                    'body'                => $this->faker->word(),
                    'head_title'          => $this->faker->word(),
                    'meta_description'    => $this->faker->word(),
                    'meta_keywords'       => $this->faker->word(),
                    'forum_link'          => $this->faker->word(),
                    'active'              => 1,
                    'valid_from'          => $this->faker->date,
                    'valid_to'            => $this->faker->date
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editPage'),
            [
                'api_key'     => $this->getApiKey(),
                'page_id'     => $page->id,
                'data'        => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful testEdit
        $this->post(
            url('api/editPage'),
            [
                'api_key'       => $this->getApiKey(),
                'page_id'       => $page->id,
                'data'          => [
                    'locale'        => $this->locale,
                    'title'         => $this->faker->word(),
                    'body'          => $this->faker->word(),
                    'abstract'      => $this->faker->word(),
                    'head_title'    => $this->faker->word(),
                    'active'        => 1
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test page deletion
     *
     * @return void
     */
    public function testDeletePage()
    {
        $page = $this->getNewPage();

        // Test missing api key
        $this->post(url('api/deletePage'),
            [
                'api_key' => null,
                'page_id' => $page->id
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing page id
        $this->post(
            url('api/deletePage'),
            [
                'api_key'    => $this->getApiKey(),
                'page_id'    => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Page::all()->count();

        // Test successful delete
        $this->post(
            url('api/deletePage'),
            [
                'api_key'   => $this->getApiKey(),
                'page_id'   => $page->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is missing
        $this->assertTrue($count - 1 == Page::all()->count());
    }

    /**
     * Test page list
     *
     * @return void
     */
    public function testList()
    {
        $response = $this->post(
            url('api/listPages'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
