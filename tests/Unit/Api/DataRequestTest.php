<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataRequestTest extends TestCase
{
   
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
