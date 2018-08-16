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

    private $locale = 'en';

    public function testAddNews()
    {
        //test missing api key
        $this->post(url('api/addNews'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing data
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
                    'locale'    => 'en',
                    'title'     => $this->faker->word(),
                    'abstract'  => $this->faker->word(),
                    'body'      => $this->faker->word(),
                    'active'    => 1,
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check that a record is made
        $this->assertTrue($count + 1 == Page::all()->count());
    }

    public function testEditNews()
    {
        $news = Page::create([
            'title'     => ApiController::trans($this->locale, $this->faker->word()),
            'active'    => true,
        ]);

        //test missing api key
        $this->post(url('api/addNews'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing news id
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

        //test missing data
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

        // test successful test edit
        $this->post(
            url('api/editNews'),
            [
                'api_key'       => $this->getApiKey(),
                'news_id'       => $news->id,
                'data'          => [
                    'locale'        => 'en',
                    'body'          => $this->faker->word(),
                    'abstract'      => $this->faker->word(),
                    'head_title'    => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testDeleteNews()
    {
        $news = Page::create([
            'title'     => ApiController::trans($this->locale, $this->faker->word()),
            'active'    => true,
        ]);

        //test missing api key
        $this->post(url('api/deleteNews'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing news id
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

        //test successful delete
        $this->post(
            url('api/deleteNews'),
            [
                'api_key'   => $this->getApiKey(),
                'news_id'   => $news->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check that a record is missing
        $this->assertTrue($count - 1 == Page::all()->count());
    }

    public function testListNews()
    {
        $this->post(
            url('api/listNews'),
            ['criteria' => []]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testSearchNews()
    {
        // test missing criteria
        $this->post(
            url('api/searchNews'),
            [
                'criteria' => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        //test successful search
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
