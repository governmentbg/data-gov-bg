<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\RightController as Right;

class RightTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testList()
    {
        $response = $this->post(
            url('api/rights/listRights'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
