<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\TermsOfUseRequest;

class TermsOfUseRequestTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Test for TermsOfUseRequestController@sendTermsOfUseRequest
     */
    public function testAddTermsOfUseRequest()
    {
        // Test missing api_key
        $this->post(url('api/sendTermsOfUseRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/sendTermsOfUseRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful section create
        $this->post(
            url('api/sendTermsOfUseRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'description'   => $this->faker->word(),
                    'firstname'     => $this->faker->name(),
                    'lastname'      => $this->faker->name(),
                    'email'         => $this->faker->email(),
                    'status'        => TermsOfUseRequest::STATUS_NEW,
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for TermsOfUseRequestController@editTermsOfUseRequest
     */
    public function testEditTermsOfUseRequest()
    {
        $section = TermsOfUseRequest::create([
            'descript'      => $this->faker->word(),
            'firstname'     => $this->faker->name(),
            'lastname'      => $this->faker->name(),
            'email'         => $this->faker->email(),
            'status'        => TermsOfUseRequest::STATUS_NEW,
        ]);

        // Test missing api_key
        $this->post(url('api/editTermsOfUseRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing record id
        $this->post(
            url('api/editTermsOfUseRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'          => $this->faker->word(),
                    'description'   => $this->faker->word(),
                    'locale'        => $this->locale,
                    'active'        => $this->faker->boolean(),
                    'is_default'    => $this->faker->boolean(),
                    'ordering'      => TermsOfUseRequest::STATUS_NEW,
                ]
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/editTermsOfUseRequest'),
            [
                'api_key'     => $this->getApiKey(),
                'request_id'  => $section->id,
                'data'        => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful section edit
        $this->post(
            url('api/editTermsOfUseRequest'),
            [
                'api_key'       => $this->getApiKey(),
                'request_id'    => $section->id,
                'data'          => [
                    'description'   => $this->faker->word(),
                    'firstname'     => $this->faker->name(),
                    'lastname'      => $this->faker->name(),
                    'email'         => $this->faker->email(),
                    'status'        => TermsOfUseRequest::STATUS_NEW,
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for TermsOfUseRequestController@deleteTermsOfUseRequest
     */
    public function testDeleteTermsOfUseRequest()
    {
        $section = TermsOfUseRequest::create([
            'descript'      => $this->faker->word(),
            'firstname'     => $this->faker->name(),
            'lastname'      => $this->faker->name(),
            'email'         => $this->faker->email(),
            'status'        => TermsOfUseRequest::STATUS_NEW,
        ]);

        // Test missing api_key
        $this->post(url('api/deleteTermsOfUseRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing record id
        $this->post(url('api/deleteTermsOfUseRequest'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful section delete
        $this->post(
            url('api/deleteTermsOfUseRequest'),
            [
                'api_key'       => $this->getApiKey(),
                'request_id'    => $section->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test for TermsOfUseRequestController@listTermsOfUseRequests
     */
    public function testListTermsOfUseRequest()
    {
        // Test missing api_key
        $this->post(url('api/listTermsOfUseRequests'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty criteria
        $this->post(
            url('api/listTermsOfUseRequests'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Test successful section list
        $this->post(
            url('api/listTermsOfUseRequests'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [
                    'status'    => $this->faker->numberBetween(1, 2),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
