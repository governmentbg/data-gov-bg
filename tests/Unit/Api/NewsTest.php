<?php
namespace Tests\Unit\Api;

use App\Page;
use Tests\TestCase;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NewsTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test news creation
     *
     * @return void
     */
    public function testAddNews()
    {
        // Test missing api key
        $this->post(url('api/addNews'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/addNews'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Page::all()->count();

        $this->post(
            url('api/addNews'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'locale'    => $this->locale,
                    'title'     => $this->faker->word(),
                    'abstract'  => $this->faker->word(),
                    'body'      => $this->faker->word(),
                    'active'    => 1,
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is made
        $this->assertTrue($count + 1 == Page::all()->count());
    }

    /**
     * Test news modification
     *
     * @return void
     */
    public function testEditNews()
    {
        $news = $this->getNewPage(['type' => Page::TYPE_NEWS]);

        // Test missing api key
        $this->post(url('api/addNews'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing news id
        $this->post(
            url('api/editNews'),
            [
                'api_key'   => $this->getApiKey(),
                'news_id'   => null,
                'data'      => [
                    'body'      => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editNews'),
            [
                'api_key'   => $this->getApiKey(),
                'news_id'   => $news->id,
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful test edit
        $this->post(
            url('api/editNews'),
            [
                'api_key'       => $this->getApiKey(),
                'news_id'       => $news->id,
                'data'          => [
                    'locale'        => $this->locale,
                    'title'         => $this->faker->word(),
                    'body'          => $this->faker->word(),
                    'abstract'      => $this->faker->word(),
                    'head_title'    => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test news deletion
     *
     * @return void
     */
    public function testDeleteNews()
    {
        $news = $this->getNewPage(['type' => Page::TYPE_NEWS]);

        // Test missing api key
        $this->post(url('api/deleteNews'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing news id
        $this->post(
            url('api/deleteNews'),
            [
                'api_key'   => $this->getApiKey(),
                'news_id'   => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Page::all()->count();

        // Test successful delete
        $this->post(
            url('api/deleteNews'),
            [
                'api_key'   => $this->getApiKey(),
                'news_id'   => $news->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is missing
        $this->assertTrue($count - 1 == Page::all()->count());
    }

    /**
     * Test news list
     *
     * @return void
     */
    public function testListNews()
    {
        $this->post(
            url('api/listNews'),
            ['criteria' => []]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test news search
     *
     * @return void
     */
    public function testSearchNews()
    {
        // Test missing criteria
        $this->post(
            url('api/searchNews'),
            [
                'criteria' => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful search
        $this->post(
            url('api/searchNews'),
            [
                'criteria' => [
                    'keywords'  => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
