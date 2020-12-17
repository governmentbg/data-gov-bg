<?php

namespace Tests\Unit\Api;

use App\Category;
use Tests\TestCase;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CategoryTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test category creation
     *
     * @return void
     */
    public function testAddMainCategory()
    {
        // Test missing api_key
        $this->post(url('api/addMainCategory'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/addMainCategory'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful MainCategory create
        $this->post(
            url('api/addMainCategory'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'          => $this->faker->word(),
                    'locale'        => $this->locale,
                ]
            ]
        )
          ->assertStatus(200)
          ->assertJson(['success' => true]);

        // Test successful MainCategory create
        $this->post(
            url('api/addMainCategory'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'name'      => ApiController::trans($this->locale, $this->faker->name()),
                ]
            ]
        )
          ->assertStatus(200)
          ->assertJson(['success' => true]);
    }

    /**
     * Test category modification
     *
     * @return void
     */
    public function testEditMainCategory() {
        $category = Category::create([
            'name'              => ApiController::trans($this->locale, $this->faker->name()),
            'icon_file_name'    => $this->faker->name(),
            'icon_mime_type'    => $this->faker->mimeType(),
            'icon_data'         => $this->faker->name(),
            'active'            => true,
            'ordering'          => Category::ORDERING_ASC,
        ]);

        //  test missing api_key
        $this->post(url('api/editMainCategory'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(
            url('api/editMainCategory'),
            [
                'api_key'       => $this->getApiKey(),
                'category_id'   => $category->id,
                'data'          => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test empty category id
        $this->post(
            url('api/editMainCategory'),
            [
                'api_key'       => $this->getApiKey(),
                'category_id'   => null,
                'data'          => [
                    'name'          => $this->faker->word(),
                    'locale'        => $this->locale,
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful MainCategory create
        $this->post(
            url('api/editMainCategory'),
            [
                'api_key'       => $this->getApiKey(),
                'category_id'   => $category->id,
                'data'          => [
                    'name'          => $this->faker->word(),
                    'locale'        => $this->locale,
                ]
            ]
        )
          ->assertStatus(200)
          ->assertJson(['success' => true]);

        $this->post(
            url('api/editMainCategory'),
            [
                'api_key'       => $this->getApiKey(),
                'category_id'   => $category->id,
                'data'          => [
                    'name'          => ApiController::trans($this->locale, $this->faker->name()),
                ]
            ]
        )
          ->assertStatus(200)
          ->assertJson(['success' => true]);
    }

    /**
     * Test category deletion
     *
     * @return void
     */
    public function testDeleteMainCategory()
    {
        $category = Category::create([
            'name'              => ApiController::trans($this->locale, $this->faker->name()),
            'icon_file_name'    => $this->faker->name(),
            'icon_mime_type'    => $this->faker->mimeType(),
            'icon_data'         => $this->faker->name(),
            'active'            => true,
            'ordering'          => Category::ORDERING_ASC,
        ]);

        //  test missing api_key
        $this->post(url('api/deleteMainCategory'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty category id
        $this->post(
            url('api/deleteMainCategory'),
            [
                'api_key'       => $this->getApiKey(),
                'category_id'   => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful MainCategory delete
        $this->post(
            url('api/deleteMainCategory'),
            [
                'api_key'       => $this->getApiKey(),
                'category_id'   => $category->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test category list
     *
     * @return void
     */
    public function testListMainCategories()
    {
        // Test missing api_key
        $this->post(url('api/listMainCategories'), ['api_key' => null])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Test with criteria
        $this->post(url('api/listMainCategories'), [
            'criteria' => [
                'active' => 1,
            ],
        ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test category details
     *
     * @return void
     */
    public function testGetMainCategoryDetails()
    {
        $category = Category::create([
            'name'              => ApiController::trans($this->locale, $this->faker->name()),
            'icon_file_name'    => $this->faker->name(),
            'icon_mime_type'    => $this->faker->mimeType(),
            'icon_data'         => $this->faker->name(),
            'active'            => true,
            'ordering'          => Category::ORDERING_ASC,
        ]);

        // Test missing category id
        $this->post(url('api/getMainCategoryDetails'), [
            'category_id'   => null,
            'locale'        => $this->locale,
        ])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful request
        $this->post(url('api/getMainCategoryDetails'), [
            'category_id'   => $category->id,
            'locale'        => $this->locale,
        ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
