<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserOrgTest extends TestCase
{
    use WithFaker;

    public function testSearchUserOrgs()
    {
        $this->get(url('/user/organisations/search?q='. $this->faker->text(5)))
            ->assertStatus(200);

        $this->get(url('/user/organisations/search?q='. $this->faker->text(5) .'&page=2'))
            ->assertStatus(200);

        $this->get(url('/user/organisations/search?q='. $this->faker->text(5)))
            ->assertViewIs('user.organisations');

        $this->get(url('/user/organisations/search?q='. $this->faker->text(5)))
            ->assertViewHas('organisations');
    }
}
