<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ModulesTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testListModules()
    {
        $response = $this->post(
            url('api/listModules'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
