<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use App\DataRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DataRequestTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test data request send
     *
     * @return void
     */
    public function testSendDataRequest()
    {
        // Test missing api key
        $this->post(url('api/sendDataRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/sendDataRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = DataRequest::all()->count();
        $org = $this->getNewOrganisation();

        $this->post(
            url('api/sendDataRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'org_id'           => $org->id,
                    'description'      => $this->faker->sentence(3),
                    'published_url'    => $this->faker->url,
                    'contact_name'     => $this->faker->name,
                    'email'            => $this->faker->email,
                    'notes'            => $this->faker->sentence(4),
                    'status'           => 1
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is made
        $this->assertTrue($count + 1 == DataRequest::all()->count());
    }

    /**
     * Test data request edit
     *
     * @return void
     */
    public function testEditDataRequest()
    {
        $org = $this->getNewOrganisation();
        $request = $this->getNewDataRequest($org->id);

        // Test missing api key
        $this->post(url('api/editDataRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing request id
        $this->post(
            url('api/editDataRequest'),
            [
                'api_key'           => $this->getApiKey(),
                'request_id'        => null,
                'data'              => [
                    'org_id'            => $org->id,
                    'description'       => $this->faker->sentence(3),
                    'published_url'     => $this->faker->url,
                    'contact_name'      => $this->faker->name,
                    'email'             => $this->faker->email,
                    'notes'             => $this->faker->sentence(4),
                    'status'            => $this->faker->boolean()
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editDataRequest'),
            [
                'api_key'      => $this->getApiKey(),
                'request_id'   => $request->id,
                'data'         => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful testEdit
        $this->post(
            url('api/editDataRequest'),
            [
                'api_key'           => $this->getApiKey(),
                'request_id'        => $request->id,
                'data'              => [
                    'org_id'            => $org->id,
                    'description'       => $this->faker->sentence(3),
                    'published_url'     => $this->faker->url,
                    'contact_name'      => $this->faker->name,
                    'email'             => $this->faker->email,
                    'notes'             => $this->faker->sentence(4),
                    'status'            => 1
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test data request delete
     *
     * @return void
     */
    public function testDeleteDataRequest()
    {
        $org = $this->getNewOrganisation();
        $request = $this->getNewDataRequest($org->id);

        // Test missing api key
        $this->post(url('api/deleteDataRequest'),
            [
                'api_key'       => null,
                'request_id'    => $request->id
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing request id
        $this->post(
            url('api/deleteDataRequest'),
            [
                'api_key'      => $this->getApiKey(),
                'request_id'   => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = DataRequest::all()->count();

        // Test successful delete
        $this->post(
            url('api/deleteDataRequest'),
            [
                'api_key'      => $this->getApiKey(),
                'request_id'   => $request->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is missing
        $this->assertTrue($count - 1 == DataRequest::all()->count());
    }

    /**
     * Test data request list
     *
     * @return void
     */
    public function testListDataRequests()
    {
        $org = $this->getNewOrganisation();
        $request = $this->getNewDataRequest($org->id);

        $response = $this->post(
            url('api/listDataRequests'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
