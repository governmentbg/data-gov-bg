<?php

namespace Tests\Unit\Api;

use App\DataSet;
use Tests\TestCase;
use App\DataSetGroup;
use App\Organisation;
use App\Http\Controllers\ApiController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DataSetTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    private $locale = 'en';
    /**
     * Test DataSet creation
     *
     * @return void
     */
    public function testAddDataSet()
    {
        //  test missing api_key
        $this->post(url('api/addDataset'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test empty data
        $this->post(url('api/addDataset'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test successful DataSet create
        $this->post(url('api/addDataset'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'name'          => $this->faker->word(),
                'locale'        => 'en',
                'category_id'   => $this->faker->numberBetween(1, 3),
                'visibility'    => $this->faker->numberBetween(1, 2),
                'version'       => $this->faker->randomDigit(),
                'status'        => $this->faker->numberBetween(1, 2),
            ]
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testEditDataSet()
    {
        $dataSet = DataSet::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->randomDigit(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        // test missing api_key
        $this->post(url('api/editDataset'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing DataSet uri
        $this->post(url('api/editDataset'), [
            'api_key'   => $this->getApiKey(),
            'data'      => [
                'name'          => $this->faker->word(),
                'locale'        => 'en',
                'category_id'   => $this->faker->numberBetween(1, 3),
                'visibility'    => $this->faker->numberBetween(1, 2),
                'version'       => $this->faker->randomDigit(),
                'status'        => $this->faker->numberBetween(1, 2),
            ]
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test empty data
        $this->post(url('api/editDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
            'data'          => [],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test successful edit
        $this->post(url('api/editDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
            'data'          => [
                'name'          => $this->faker->uuid(),
                'locale'        => 'en',
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

    public function testDeleteDataSet()
    {
        $dataSet = DataSet::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->randomDigit(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        // test missing api_key
        $this->post(url('api/deleteDataset'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test wrong DataSet uri
        $this->post(url('api/deleteDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $this->faker->uuid(),
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test DataSet deletion
        $this->post(url('api/deleteDataset'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testListDataSets()
    {
        // test missing api_key
        $this->post(url('api/listDatasets'), [
            'api_key'    => $this->getApiKey(),
            'criteria'   => [],
        ])->assertStatus(200)->assertJson(['success' => true]);

        // test empty criteria
        $this->post(url('api/listDatasets'), [
            'api_key'    => $this->getApiKey(),
            'criteria'   => [],
        ])->assertStatus(200)->assertJson(['success' => true]);

        // test successful list
        $this->post(url('api/listDatasets'), [
            'criteria'   => [
                'locale'    => 'en',
                'reported'  => $this->faker->numberBetween(0, 1),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testSearchDataSets()
    {
        // test missing api_key
        $this->post(url('api/listDatasets'), [
            'api_key'   => null,
            'criteria'  => [
                'locale'    => 'en',
            ],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test empty criteria
        $this->post(url('api/listDatasets'), ['criteria' => []])
             ->assertStatus(500)
             ->assertJson(['success' => false]);

        // test successfull search
        $this->post(url('api/listDatasets'), [
            'criteria'   => [
                'locale'    => 'en',
                'keywords'  => $this->faker->word(),
            ],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testGetDataSetDetails()
    {
        $dataSet = DataSet::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1,3),
            'visibility'    => $this->faker->numberBetween(1,2),
            'version'       => $this->faker->randomDigit(),
            'status'        => $this->faker->numberBetween(1,2),
        ]);

        // test mising api key
        $this->post(url('api/getDatasetDetails'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing Data Set uri
        $this->post(url('api/getDatasetDetails'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => null,
        ])->assertStatus(500)->assertJson(['success' => false]);

        $this->post(url('api/getDatasetDetails'), [
            'api_key'       => $this->getApiKey(),
            'dataset_uri'   => $dataSet->uri,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testAddDataSetToGroup()
    {
        $dataSet = DataSet::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->randomDigit(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        $organisation = Organisation::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'type'          => Organisation::TYPE_GROUP,
            'descript'      => $this->faker->text(),
            'active'        => $this->faker->numberBetween(0, 1),
            'approved'      => $this->faker->numberBetween(0, 1),
        ]);

        // test mising api key
        $this->post(url('api/addDatasetToGroup'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing group_id
        $this->post(url('api/addDatasetToGroup'), [
            'api_key'        => $this->getApiKey(),
            'data_set_uri'   => $dataSet->uri,
            'group_id'       => null,
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test missing dataset_uri
        $this->post(url('api/addDatasetToGroup'), [
            'api_key'        => $this->getApiKey(),
            'data_set_uri'   => null,
            'group_id'       => [$organisation->id],
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test successful request
        $this->post(url('api/addDatasetToGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => $dataSet->uri,
            'group_id'      => [$organisation->id],
        ])->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testRemoveDataSetFromGroup()
    {
        $dataSet = DataSet::create([
            'name'          => ApiController::trans($this->locale, $this->faker->word()),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1,3),
            'visibility'    => $this->faker->numberBetween(1,2),
            'version'       => $this->faker->randomDigit(),
            'status'        => $this->faker->numberBetween(1,2),
        ]);

        $dataSetGroup = DataSetGroup::create([
            'group_id'      => $this->faker->numberBetween(1,3),
            'data_set_id'   => $dataSet->id,
        ]);

        // test mising api key
        $this->post(url('api/removeDatasetFromGroup'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing group_id
        $this->post(url('api/removeDatasetFromGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => $dataSet->uri,
            'group_id'      => null,
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test missing dataset_uri
        $this->post(url('api/removeDatasetFromGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => null,
            'group_id'      => $dataSetGroup->group_id,
        ])->assertStatus(500)->assertJson(['success' => false]);

        // test successful request
        $this->post(url('api/removeDatasetFromGroup'), [
            'api_key'       => $this->getApiKey(),
            'data_set_uri'  => $dataSet->uri,
            'group_id'      => $dataSetGroup->group_id,
        ])->assertStatus(200)->assertJson(['success' => true]);
    }
}
