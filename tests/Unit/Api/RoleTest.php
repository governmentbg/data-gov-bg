<?php

namespace Tests\Unit\Api;

use App\Role;
use App\Locale;
use App\Module;
use Tests\TestCase;
use App\Organisation;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * Test role creation
     *
     * @return void
     */
    public function testAddRole()
    {
        $response = $this->post(url('api/addRole'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'name'      => 'addedFromTest',
                'active'    => 1,
            ],
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test role modification
     *
     * @return void
     */
    public function testEditRole()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/editRole'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
            'data'      => [
                'name'      => 'addedFromTest2',
                'active'    => 0,
            ],
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test role deletion
     *
     * @return void
     */
    public function testDeleteRole()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/deleteRole'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test role list
     *
     * @return void
     */
    public function testListRole()
    {
        $response = $this->post(url('api/listRoles'), [
            'api_key'   => $this->getApiKey(),
            'criteria'  => [
                'active'    => 0,
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);

        $org = $this->getNewOrganisation();

        $response = $this->post(url('api/listRoles'), [
            'api_key'   => $this->getApiKey(),
            'criteria'  => [
                'org_id'    => $org->id,
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test role rights modification
     *
     * @return void
     */
    public function testModifyRoleRights()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/modifyRoleRights'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
            'data'      => [
                [
                    'module_name'       => Module::getModules()[1],
                    'right'             => 2,
                    'limit_to_own_data' => 0,
                    'api'               => 1,
                ],
                [
                    'module_name'       => Module::getModules()[2],
                    'right'             => 2,
                    'limit_to_own_data' => 0,
                    'api'               => 1,
                ],
                [
                    'module_name'       => Module::getModules()[3],
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

    /**
     * Test role rights list
     *
     * @return void
     */
    public function testListRoleRights()
    {
        $role = Role::create([
            'name'      => $this->faker->firstName(),
            'active'    => 1,
        ]);

        $response = $this->post(url('api/getRoleRights'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
