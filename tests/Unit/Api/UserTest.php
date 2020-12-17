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

    /**
     * Test users list
     *
     * @return void
     */
    public function testListUsers()
    {
        $this->post(url('api/listUsers'), ['api_key' => $this->getApiKey()])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users search
     *
     * @return void
     */
    public function testSearchUsers()
    {
        $criteria = ['locale' => $this->locale, 'keywords' => 'search'];

        $this->post(url('api/listUsers'), ['api_key' => $this->getApiKey(), 'criteria' => $criteria])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users roles
     *
     * @return void
     */
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

    /**
     * Test users settings
     *
     * @return void
     */
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

    /**
     * Test users creation
     *
     * @return void
     */
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
                    'email'            => 'dimitar@finite-soft.com',
                    'password'         => $password,
                    'password_confirm' => $password,
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users modification
     *
     * @return void
     */
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
                'api_key'   => $this->getApiKey(),
                'id'        => $this->getUserId(),
                'data'      => [
                    'firstname' => $this->faker->name(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users deletion
     *
     * @return void
     */
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
                'api_key'   => $this->getApiKey(),
                'id'        => $this->getUserId(),
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users api key generation
     *
     * @return void
     */
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
                'api_key'   => $this->getApiKey(),
                'id'        => $this->getUserId(),
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users register
     *
     * @return void
     */
    public function testRegister()
    {
        $password = str_random(10);

        $this->post(url('api/register'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/register'),
            [
                'api_key'           => $this->getApiKey(),
                'data'              => [
                    'firstname'         => $this->faker->name(),
                    'lastname'          => $this->faker->name(),
                    'email'             => 'dimitar@finite-soft.com',
                    'password'          => $password,
                    'password_confirm'  => $password,
                ]
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(
            url('api/register'),
            [
                'api_key'           => $this->getApiKey(),
                'data'              => [
                    'username'          => $this->faker->name(),
                    'firstname'         => $this->faker->name(),
                    'lastname'          => $this->faker->name(),
                    'email'             => 'dimitar@finite-soft.com',
                    'password'          => $password,
                    'password_confirm'  => $password,
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test users invite
     *
     * @return void
     */
    public function testInviteUser()
    {
        $this->post(url('api/inviteUser'), ['api_key' => $this->getApiKey()])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $this->post(url('api/inviteUser'), [
            'api_key'   => $this->getApiKey(),
            'id'        => $this->getUserId(),
            'data'      => [
                'email'     => 'dimitar@finite-soft.com',
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->post(url('api/inviteUser'), [
            'data'      => [
                'email'     => 'dimitar@finite-soft.com',
            ]
        ])->assertStatus(403)->assertJson(['success' => false]);
    }
}
