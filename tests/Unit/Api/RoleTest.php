<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use App\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAddRole()
    {
        $response = $this->post(url('api/roles/addRole'), [
            'api_key' => $this->getApiKey(),
            'name'    => 'addedFromTest',
            'active'  => 1,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testEditRole()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/roles/editRole'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
            'name'      => $this->faker->firstName(),
            'active'    => 0,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testDeleteRole()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/roles/deleteRole'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testListeRole()
    {
        $response = $this->post(url('api/roles/listRoles'), [
            'api_key'   => $this->getApiKey(),
            'active'    => 0
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testModifyRoleRights()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/roles/modifyRoleRights'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
            'data'      => [
                [
                    'module_name'       => 'testModule',
                    'right'             => 2,
                    'limit_to_own_data' => 0,
                    'api'               => 1,
                ],
                [
                    'module_name'       => 'testModule2',
                    'right'             => 2,
                    'limit_to_own_data' => 0,
                    'api'               => 1,
                ],
                [
                    'module_name'       => 'testModule3',
                    'right'             => 2,
                    'limit_to_own_data' => 0,
                    'api'               => 1,
                ],
            ],
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testListRoleRights()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/roles/getRoleRights'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }


}
