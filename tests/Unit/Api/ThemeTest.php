<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThemeTest extends TestCase
{
    /**
     * Test for SectionController@listSections
     */
    public function testlistThemes()
    {
        // test missing api_key
        $this->post(
            url('api/listThemes'),
            ['api_key' => null]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test successful theme list
        $this->post(
            url('api/listThemes'),
            ['api_key'   => $this->getApiKey()]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
