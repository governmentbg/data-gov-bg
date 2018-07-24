<?php

namespace Tests\Unit\Api;

use App\Locale;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LocaleTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test locale creation
     *
     * @return void
     */
    public function testAddLocale()
    {
        // test missing api_key
        $this->post(url('api/addLocale'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test empty data
        $this->post(url('api/addLocale'),[
            'api_key'   => $this->getApiKey(),
            'data'      => [],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test successful locale create
        $this->post(url('api/addLocale'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'locale'    => 'yy',
                'active'    => $this->faker->boolean(),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test locale edit
     *
     * @return void
     */
    public function testEditLocale()
    {
        $locale = Locale::create([
            'locale'    => 'yy',
            'active'    => $this->faker->boolean(),
        ]);

        // test missing api_key
        $this->post(url('api/editLocale'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing locale
        $this->post(url('api/editLocale'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'active'    => $this->faker->boolean(),
            ]
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test missing active
        $this->post(url('api/editLocale'), [
            'api_key'   => $this->getApiKey(),
            'locale'    => 'yy',
            'data'      => [],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test successful edit
        $this->post(url('api/editLocale'), [
            'api_key'   => $this->getApiKey(),
            'locale'    => 'yy',
            'data'      => [
                'active'    => $this->faker->boolean(),
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test locale deletion
     *
     * @return void
     */
    public function testDeleteLocale()
    {
        $locale = Locale::create([
            'locale'    => 'yy',
            'active'    => $this->faker->boolean(),
        ]);

        // test missing api_key
        $this->post(url('api/deleteLocale'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test wrong locale uri
        $this->post(url('api/deleteLocale'), [
            'api_key'   => $this->getApiKey(),
            'locale'    => 'yyx',
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test locale deletion
        $this->post(url('api/deleteLocale'), [
            'api_key'   => $this->getApiKey(),
            'locale'    => 'yy',
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test locale list
     *
     * @return void
     */
    public function testListLocale()
    {
        // test missing api_key
        $this->post(url('api/listLocale'), ['api_key' => null])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // test empty criteria
        $this->post(url('api/listLocale'), [
            'api_key'    => $this->getApiKey(),
            'criteria'   => [],
        ])->assertStatus(200)->assertJson(['success' => true]);

        // test successful list
        $this->post(url('api/listLocale'), [
            'criteria'   => [
                'active'    => $this->faker->boolean(),
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testLocaleDetails()
    {
        $locale = Locale::create([
            'locale'    => 'yy',
            'active'    => $this->faker->boolean(),
        ]);

        // test missing locale
        $this->post(url('api/getLocaleDetails'), [
            'locale'    => 'yyx',
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test correct locale
        $this->post(url('api/getLocaleDetails'), [
            'api_key'   => $this->getApiKey(),
            'locale'    => 'yy',
        ])->assertStatus(200)->assertJson(['success' => true]);
    }
}
