<?php

namespace Tests\Unit\Api;

use App\Page;
use App\Locale;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PageTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    public function testAddPage()
    {
        //test missing api key
        $this->post(url('api/addPage'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing data
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
                'locale'    => 'bg',
                'data'      => [
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

        // check that a record is made
        $this->assertTrue($count + 1 == Page::all()->count());
    }


    public function testEditPage()
    {
        $page = Page::create([
            'title'             => $this->faker->word(),
            'body'              => $this->faker->word(),
            'head_title'        => $this->faker->word(),
            'meta_descript'     => $this->faker->word(),
            'meta_key_words'    => $this->faker->word(),
            'forum_link'        => $this->faker->word,
            'active'            => $this->faker->boolean,
            'valid_from'        => $this->faker->date,
            'valid_to'          => $this->faker->date
        ]);

        //test missing api key
        $this->post(url('api/editPage'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing page id
        $this->post(
            url('api/editPage'),
            [
                'api_key'   => $this->getApiKey(),
                'page_id'   => null,
                'locale'    => 'en',
                'data'      => [
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

        //test missing data
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

        // test successful testEdit
        $this->post(
            url('api/editPage'),
            [
                'api_key'       => $this->getApiKey(),
                'page_id'       => $page->id,
                'locale'        => 'en',
                'data'          => [
                      'body'    => $this->faker->word(),
                      'active'  => 1
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testDeletePage()
    {
        $page = Page::create([
            'title'             => $this->faker->word(),
            'body'              => $this->faker->word(),
            'head_title'        => $this->faker->word(),
            'meta_descript'     => $this->faker->word(),
            'meta_key_words'    => $this->faker->word(),
            'forum_link'        => $this->faker->word,
            'active'            => $this->faker->boolean,
            'valid_from'        => $this->faker->date,
            'valid_to'          => $this->faker->date
        ]);

        //test missing api key
        $this->post(url('api/deletePage'),
            [
                'api_key' => null,
                'page_id' => $page->id
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing page id
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

        //test successful delete
        $this->post(
            url('api/deletePage'),
            [
                'api_key'   => $this->getApiKey(),
                'page_id'   => $page->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check that a record is missing
        $this->assertTrue($count - 1 == Page::all()->count());
    }

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
