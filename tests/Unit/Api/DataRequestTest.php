<?php

namespace Tests\Unit\Api;

use App\DataRequest;
use Tests\TestCase;
use App\Organisation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DataRequestTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    public function testSendDataRequest()
    {
        //test missing api key
        $this->post(url('api/sendDataRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing data
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
        $orgs = Organisation::orderBy('created_at', 'desc')->limit(10)->get()->toArray();
        $org = $this->faker->randomElement($orgs)['id'];

        $this->post(
            url('api/sendDataRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'org_id'           => $org,
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
        // check that a record is made
        $this->assertTrue($count + 1 == DataRequest::all()->count());
    }

    public function testEditDataRequest()
    {
        $orgs = Organisation::orderBy('created_at', 'desc')->limit(10)->get()->toArray();
        $org = $this->faker->randomElement($orgs)['id'];

        $request = DataRequest::create([
                    'org_id'           => $org,
                    'descript'         => $this->faker->sentence(3),
                    'published_url'    => $this->faker->url,
                    'contact_name'     => $this->faker->name,
                    'email'            => $this->faker->email,
                    'notes'            => $this->faker->sentence(4),
                    'status'           => $this->faker->boolean()
        ]);

        //test missing api key
        $this->post(url('api/editDataRequest'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing request id
        $this->post(
            url('api/editDataRequest'),
            [
                'api_key'   => $this->getApiKey(),
                'request_id'   => null,
                'data'      => [
                    'org_id'           => $org,
                    'description'      => $this->faker->sentence(3),
                    'published_url'    => $this->faker->url,
                    'contact_name'     => $this->faker->name,
                    'email'            => $this->faker->email,
                    'notes'            => $this->faker->sentence(4),
                    'status'           => $this->faker->boolean()
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        //test missing data
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

        // test successful testEdit
        $this->post(
            url('api/editDataRequest'),
            [
                'api_key'        => $this->getApiKey(),
                'request_id'     => $request->id,
                'data'           => [
                    'org_id'           => $org,
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
    }

    public function testDeleteDataRequest()
    {
        $orgs = Organisation::orderBy('created_at', 'desc')->limit(10)->get()->toArray();
        $org = $this->faker->randomElement($orgs)['id'];

        $request = DataRequest::create([
                    'org_id'           => $org,
                    'descript'         => $this->faker->sentence(3),
                    'published_url'    => $this->faker->url,
                    'contact_name'     => $this->faker->name,
                    'email'            => $this->faker->email,
                    'notes'            => $this->faker->sentence(4),
                    'status'           => 1
        ]);

        //test missing api key
        $this->post(url('api/deleteDataRequest'),
            [
                'api_key'       => null,
                'request_id'    => $request->id
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing request id
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

        //test successful delete
        $this->post(
            url('api/deleteDataRequest'),
            [
                'api_key'      => $this->getApiKey(),
                'request_id'   => $request->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
        // check that a record is missing
        $this->assertTrue($count - 1 == DataRequest::all()->count());
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testListDataRequests()
    {
        $response = $this->post(
            url('api/listDataRequests'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
