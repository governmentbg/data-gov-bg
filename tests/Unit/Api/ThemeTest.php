<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThemeTest extends TestCase
{
    /**
     * Test theme list
     */
    public function testListThemes()
    {
        // Test missing api_key
        $this->post(
            url('api/listThemes'),
            ['api_key' => null]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test successful theme list
        $this->post(
            url('api/listThemes'),
            ['api_key'   => $this->getApiKey()]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
