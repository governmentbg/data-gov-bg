<?php

namespace Tests\Unit\Api;

use Tests\TestCase;
use App\DataSetGroup;
use App\Organisation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DataSetTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test dataset creation
     *
     * @return void
     */
    public function testAddDataSet()
    {
        // Тest missing api_key
        $this->post(url('api/addDataset'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test empty data
        $this->post(url('api/addDataset'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test successful DataSet create
        $this->post(url('api/addDataset'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'name'          => $this->faker->word(),
                'locale'        => $this->locale,
                'category_id'   => $this->faker->numberBetween(1, 3),
                'visibility'    => $this->faker->numberBetween(1, 2),
                'version'       => $this->faker->randomDigit(),
                'status'        => $this->faker->numberBetween(1, 2),
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset modification
     *
     * @return void
     */
    public function testEditDataSet()
    {
        $dataSet = $this->getNewDataSet();

        // Test missing api_key
        $this->post(url('api/editDataset'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing DataSet uri
        $this->post(url('api/editDataset'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'name'          => $this->faker->word(),
                'locale'        => $this->locale,
                'category_id'   => $this->faker->numberBetween(1, 3),
                'visibility'    => $this->faker->numberBetween(1, 2),
                'version'       => $this->faker->randomDigit(),
                'status'        => $this->faker->numberBetween(1, 2),
            ]
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test empty data
        $this->post(url('api/editDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
            'data'          => [],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test successful edit
        $this->post(url('api/editDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
            'data'          => [
                'name'          => $this->faker->uuid(),
                'locale'        => $this->locale,
                'category_id'   => $this->faker->numberBetween(1, 3),
                'visibility'    => $this->faker->numberBetween(1, 2),
                'version'       => $this->faker->randomDigit(),
                'status'        => $this->faker->numberBetween(1, 2),
                'tags'          => [
                    $this->faker->word(),
                    $this->faker->word(),
                ],
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset deletion
     *
     * @return void
     */
    public function testDeleteDataSet()
    {
        $dataSet = $this->getNewDataSet();

        // Test missing api_key
        $this->post(url('api/deleteDataset'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test wrong DataSet uri
        $this->post(url('api/deleteDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $this->faker->uuid(),
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test DataSet deletion
        $this->post(url('api/deleteDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset list
     *
     * @return void
     */
    public function testListDataSets()
    {
        // Test missing api_key
        $this->post(url('api/listDatasets'), [
            'api_key'    => $this->getApiKey(),
            'criteria'   => [],
        ])->assertStatus(200)->assertJson(['success' => true]);

        // Test empty criteria
        $this->post(url('api/listDatasets'), [
            'api_key'    => $this->getApiKey(),
            'criteria'   => [],
        ])->assertStatus(200)->assertJson(['success' => true]);

        // Test successful list
        $this->post(url('api/listDatasets'), [
            'criteria'   => [
                'locale'    => $this->locale,
                'reported'  => $this->faker->numberBetween(0, 1),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset search
     *
     * @return void
     */
    public function testSearchDataSets()
    {
        $this->post(url('api/listDatasets'), [
            'criteria'   => [
                'locale'    => $this->locale,
                'keywords'  => $this->faker->word(),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->post(url('api/listDatasets'), [
            'criteria'   => [
                'keywords'  => $this->faker->word(),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset details
     *
     * @return void
     */
    public function testGetDataSetDetails()
    {
        $dataSet = $this->getNewDataSet();

        // Test mising api key
        $this->post(url('api/getDatasetDetails'), ['api_key' => null])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing Data Set uri
        $this->post(url('api/getDatasetDetails'), [
            'dataset_uri'   => null,
        ])->assertStatus(500)->assertJson(['success' => false]);

        $this->post(url('api/getDatasetDetails'), [
            'dataset_uri'   => $dataSet->uri,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset group add
     *
     * @return void
     */
    public function testAddDataSetToGroup()
    {
        $dataSet = $this->getNewDataSet();
        $organisation = $this->getNewOrganisation(['type' => Organisation::TYPE_GROUP]);

        // Test mising api key
        $this->post(url('api/addDatasetToGroup'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing group_id
        $this->post(url('api/addDatasetToGroup'), [
            'api_key'        => $this->getApiKey(),
            'data_set_uri'   => $dataSet->uri,
            'group_id'       => null,
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test missing dataset_uri
        $this->post(url('api/addDatasetToGroup'), [
            'api_key'        => $this->getApiKey(),
            'data_set_uri'   => null,
            'group_id'       => [$organisation->id],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test successful request
        $this->post(url('api/addDatasetToGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => $dataSet->uri,
            'group_id'      => [$organisation->id],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test dataset group remove
     *
     * @return void
     */
    public function testRemoveDataSetFromGroup()
    {
        $dataSet = $this->getNewDataSet();
        $organisation = $this->getNewOrganisation(['type' => Organisation::TYPE_GROUP]);

        $dataSetGroup = DataSetGroup::create([
            'group_id'      => $organisation->id,
            'data_set_id'   => $dataSet->id,
        ]);

        // Test mising api key
        $this->post(url('api/removeDatasetFromGroup'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing group_id
        $this->post(url('api/removeDatasetFromGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => $dataSet->uri,
            'group_id'      => null,
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test missing dataset_uri
        $this->post(url('api/removeDatasetFromGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => null,
            'group_id'      => $dataSetGroup->group_id,
        ])->assertStatus(500)->assertJson(['success' => false]);

        // Test successful request
        $this->post(url('api/removeDatasetFromGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => $dataSet->uri,
            'group_id'      => $dataSetGroup->group_id,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }
}
