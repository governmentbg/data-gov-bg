<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
 
use Illuminate\Http\Request;
 

class SignalTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testList()
    {
        $response = $this->post(
            url('api/signals/listSignals'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
