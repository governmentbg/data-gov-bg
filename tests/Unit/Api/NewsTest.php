<?php
namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class NewsTest extends TestCase
{
    public function testList()
    {
        $response = $this->post(
            url('api/listNews'),
            ['api_key' => $this->getApiKey()]
        );
        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}