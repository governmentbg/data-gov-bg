<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class ActionsHistoryTest extends TestCase
{
    public function testList()
    {
        $response = $this->post(
            url('api/actions/listActionHistory'),
            ['api_key' => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
