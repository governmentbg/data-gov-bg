<?php

namespace Tests\Unit\Api;

use App\Role;
use App\Locale;
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
     * A basic test example.
     *
     * @return void
     */
    public function testAddRole()
    {
        $response = $this->post(url('api/addRole'), [
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

        $response = $this->post(url('api/editRole'), [
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

        $response = $this->post(url('api/deleteRole'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testListRole()
    {
        $response = $this->post(url('api/listRoles'), [
            'api_key'   => $this->getApiKey(),
            'criteria'  => [
                'active'    => 0,
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);

        $locales = Locale::where('active', 1)->get()->toArray();
        $locale = $this->faker->randomElement($locales)['locale'];
        $types = array_keys(Organisation::getPublicTypes());
        $type = $this->faker->randomElement($types);
        $name = $this->faker->name;
        
        $org = Organisation::create([
            'type'              => $type,
            'name'              => ApiController::trans($locale, $name),
            'uri'               => $this->faker->uuid(),
            'active'            => $this->faker->boolean(),
            'approved'          => $this->faker->boolean(),
        ]);

        $response = $this->post(url('api/listRoles'), [
            'api_key'   => $this->getApiKey(),
            'criteria'  => [
                'org_id'    => $org->id,
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

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

        $response = $this->post(url('api/getRoleRights'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $role->id,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
