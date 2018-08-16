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

    public function testSendSignal()
    {
        //test missing api key
        $this->post(url('api/sendSignal'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing data
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
        $resources = Resource::limit(2)->get()->toArray();
        $this->post(
            url('api/sendSignal'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'resource_id'   => $this->faker->randomElement($resources)['id'],
                    'description'   => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => $this->faker->randomDigit()
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check that a record is made
        $this->assertTrue($count + 1 == Signal::all()->count());
    }

    public function testEditSignal()
    {
        $resources = Resource::limit(2)->get()->toArray();
        $signal = Signal::create([
            'resource_id'   => $this->faker->randomElement($resources)['id'],
            'descript'      => $this->faker->sentence(4),
            'firstname'     => $this->faker->firstname(),
            'lastname'      => $this->faker->lastname(),
            'email'         => $this->faker->email(),
            'status'        => $this->faker->randomDigit()
        ]);

        //test missing api key
        $this->post(url('api/sendSignal'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing news id
        $this->post(
            url('api/editSignal'),
            [
                'api_key'     => $this->getApiKey(),
                'signal_id'   => null,
                'data'        => [
                    'resource_id'   => $this->faker->randomElement($resources)['id'],
                    'descript'      => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => $this->faker->randomDigit()
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        //test missing data
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

        // test successful test еdit
        $this->post(
            url('api/editSignal'),
            [
                'api_key'       => $this->getApiKey(),
                'signal_id'     => $signal->id,
                'data'          => [
                    'resource_id'   => $this->faker->randomElement($resources)['id'],
                    'descript'      => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => $this->faker->randomDigit()
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testDeleteSignal()
    {
        $resources = Resource::limit(2)->get()->toArray();
        $signal = Signal::create([
                    'resource_id'   => $this->faker->randomElement($resources)['id'],
                    'descript'      => $this->faker->sentence(4),
                    'firstname'     => $this->faker->firstname(),
                    'lastname'      => $this->faker->lastname(),
                    'email'         => $this->faker->email(),
                    'status'        => $this->faker->randomDigit()
        ]);

        //test missing api key
        $this->post(url('api/deleteSignal'),
            [
                'api_key'   => null,
                'signal_id' => $signal->id
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing signal id
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

        //test successful delete
        $this->post(
            url('api/deleteSignal'),
            [
                'api_key'     => $this->getApiKey(),
                'signal_id'   => $signal->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check that a record is missing
        $this->assertTrue($count - 1 == Signal::all()->count());
    }

    /**
     * A basic test example.
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
