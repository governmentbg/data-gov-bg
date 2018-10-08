<?php

namespace Tests\Unit\Api;

use App\Signal;
use App\Resource;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SignalTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test signal creation
     *
     * @return void
     */
    public function testSendSignal()
    {
        // Test missing data
        $this->post(
            url('api/sendSignal'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Signal::all()->count();
        $resource = $this->getNewResource();

        $this->post(
            url('api/sendSignal'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'resource_id'   => $resource->id,
                    'description'   => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => 1,
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is made
        $this->assertTrue($count + 1 == Signal::all()->count());
    }

    /**
     * Test signal modification
     *
     * @return void
     */
    public function testEditSignal()
    {
        $resource = $this->getNewResource();
        $signal = Signal::create([
            'resource_id'   => $resource->id,
            'descript'      => $this->faker->sentence(4),
            'firstname'     => $this->faker->firstname(),
            'lastname'      => $this->faker->lastname(),
            'email'         => $this->faker->email(),
            'status'        => 1,
        ]);

        // Test missing api key
        $this->post(url('api/editSignal'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing news id
        $this->post(
            url('api/editSignal'),
            [
                'api_key'       => $this->getApiKey(),
                'signal_id'     => null,
                'data'          => [
                    'resource_id'   => $resource->id,
                    'descript'      => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => 1
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editSignal'),
            [
                'api_key'     => $this->getApiKey(),
                'signal_id'   => $signal->id,
                'data'        => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful test еdit
        $this->post(
            url('api/editSignal'),
            [
                'api_key'       => $this->getApiKey(),
                'signal_id'     => $signal->id,
                'data'          => [
                    'resource_id'   => $resource->id,
                    'descript'      => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => 1,
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test signal deletion
     *
     * @return void
     */
    public function testDeleteSignal()
    {
        $resource = $this->getNewResource();
        $signal = Signal::create([
            'resource_id'   => $resource->id,
                    'descript'      => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
            'status'        => 1,
        ]);

        // Test missing api key
        $this->post(url('api/deleteSignal'),
            [
                'api_key'   => null,
                'signal_id' => $signal->id
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing signal id
        $this->post(
            url('api/deleteSignal'),
            [
                'api_key'     => $this->getApiKey(),
                'signal_id'   => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Signal::all()->count();

        // Test successful delete
        $this->post(
            url('api/deleteSignal'),
            [
                'api_key'     => $this->getApiKey(),
                'signal_id'   => $signal->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is missing
        $this->assertTrue($count - 1 == Signal::all()->count());
    }

    /**
     * Test signal list
     *
     * @return void
     */
    public function testListSignals()
    {
        $response = $this->post(
            url('api/listSignals'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
