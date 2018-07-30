<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function testListUsers()
    {
        $this->post(url('api/listUsers'), ['api_key' => $this->getApiKey()])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->post(url('api/listUsers'), ['api_key' => ''])
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function testSearchUsers()
    {
        $criteria = ['locale' => app()->getLocale()];

        $this->post(url('api/searchUsers'), ['api_key' => $this->getApiKey()])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->post(url('api/searchUsers'), ['api_key' => $this->getApiKey(), 'criteria' => $criteria])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->post(url('api/searchUsers'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function testGetUserRoles()
    {
        $this->post(url('api/getUserRoles'), ['api_key' => $this->getApiKey(), 'id' => $this->getUserId()])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->post(url('api/getUserRoles'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(url('api/getUserRoles'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    public function testGetUserSettings()
    {
        $this->post(url('api/getUserSettings'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/getUserSettings'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(url('api/getUserSettings'), ['api_key' => $this->getApiKey(), 'id' => $this->getUserId()])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testAddUser()
    {
        $password = bcrypt(str_random(10));

        $this->post(url('api/addUser'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/addUser'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/addUser'),
            [
                'api_key' => $this->getApiKey(),
                'data'    => [
                    'firstname'        => $this->faker->name(),
                    'lastname'         => $this->faker->name(),
                    'email'            => $this->faker->safeEmail(),
                    'password'         => $password,
                    'password_confirm' => $password,
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testEditUser()
    {
        $this->post(url('api/editUser'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/editUser'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/editUser'),
            [
                'api_key' => $this->getApiKey(),
                'data'    => [
                    'firstname' => $this->faker->name(),
                ],
                'id' => $this->getUserId()
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testDeleteUser()
    {
        $this->post(url('api/deleteUser'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/deleteUser'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/deleteUser'),
            [
                'api_key' => $this->getApiKey(),
                'id' => $this->getUserId()
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testGenerateAPIKey()
    {
        $this->post(url('api/generateAPIKey'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->post(url('api/generateAPIKey'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/generateAPIKey'),
            [
                'api_key' => $this->getApiKey(),
                'id' => $this->getUserId()
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testRegister()
    {
        $password = bcrypt(str_random(10));

        $this->post(url('api/register'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/register'),
            [
                'api_key' => $this->getApiKey(),
                'data'    => [
                    'firstname'        => $this->faker->name(),
                    'lastname'         => $this->faker->name(),
                    'email'            => $this->faker->safeEmail(),
                    'password'         => bcrypt(str_random(10)),
                    'password_confirm' => bcrypt(str_random(10)),
                ]
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/register'),
            [
                'api_key' => $this->getApiKey(),
                'data'    => [
                    'firstname'        => $this->faker->name(),
                    'lastname'         => $this->faker->name(),
                    'email'            => $this->faker->safeEmail(),
                    'password'         => $password,
                    'password_confirm' => $password,
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testInviteUser()
    {
        $this->post(url('api/inviteUser'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/inviteUser'),
            [
                'api_key' => $this->getApiKey(),
                'data'    => [
                    'email' => $this->faker->safeEmail(),
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->post(
            url('api/inviteUser'),
            [
                'data'    => [
                    'email' => $this->faker->safeEmail(),
                ]
            ]
        )
            ->assertStatus(403)
            ->assertJson(['success' => false]);
    }
}
