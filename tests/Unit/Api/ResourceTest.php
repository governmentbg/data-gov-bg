<?php

namespace Tests\Unit\Api;

use App\DataSet;
use App\Resource;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResourceTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test resource creation
     *
     * @return void
     */
    public function testAddResourceMetadata()
    {
        $dataSet = $this->getNewDataSet();

        // Test missing api_key
        $this->post(url('api/addResourceMetadata'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing dataset_uri
        $this->post(
            url('api/addResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'data'              => [
                    'name'              => $this->faker->word(),
                    'description'       => $this->faker->text(),
                    'locale'            => $this->locale,
                    'version'           => $this->faker->numberBetween(1, 999),
                    'schema_descript'   => $this->faker->word(),
                    'schema_url'        => $this->faker->url(),
                    'resource_type'     => $this->faker->numberBetween(1, 3),
                    'resource_url'      => $this->faker->url(),
                    'http_rq_type'      => $this->faker->randomElement(['post', 'get']),
                    'post_data'         => $this->faker->text(),
                    'authentication'    => $this->faker->word(),
                    'http_headers'      => $this->faker->text(),
                ]
            ]
        )->assertStatus(500)->assertJson(['success' => false]);

        // Test successfull creation
        $this->post(
            url('api/addResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'dataset_uri'       => $dataSet->uri,
                'data'              => [
                    'type'              => $this->faker->numberBetween(1, 3),
                    'name'              => $this->faker->word(),
                    'descript'          => $this->faker->text(),
                    'locale'            => $this->locale,
                    'version'           => $this->faker->word(),
                    'schema_descript'   => $this->faker->word(),
                    'schema_url'        => $this->faker->url(),
                    'resource_type'     => $this->faker->numberBetween(1, 3),
                    'resource_url'      => $this->faker->url(),
                    'post_data'         => $this->faker->text(),
                    'http_rq_type'      => $this->faker->randomElement(['post', 'get']),
                    'authentication'    => $this->faker->word(),
                    'http_headers'      => $this->faker->text(),
                ]
            ]
        )->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test resource data creation
     *
     * @return void
     */
    public function testAddResourceData()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test missing api_key
        $this->post(url('api/addResourceData'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing resource uri
        $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => null,
                'data'          => $this->faker->text(),
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data uri
        $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => $resource->uri,
                'data'          => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successfull edit
        $data = [
            [
                'album'                 => 'The White Stripes',
                'year'                  => '1999',
                'US_peak_chart_post'    => '-',
            ],
            [
                'album'                 => 'De Stijl',
                'year'                  => '2000',
                'US_peak_chart_post'    => '-',
            ],
            [
                'album'                 => 'White Blood Cells',
                'year'                  => '2001',
                'US_peak_chart_post'    => '61',
            ],
            [
                'album'                 => 'Elephant',
                'year'                  => '2003',
                'US_peak_chart_post'    => '6',
            ],
            [
                'album'                 => 'Get Behind Me Satan',
                'year'                  => '2005',
                'US_peak_chart_post'    => '3',
            ],
            [
                'album'                 => 'Icky Thump',
                'year'                  => '2007',
                'US_peak_chart_post'    => '2',
            ],
            [
                'album'                 => 'Under Great White Northern Lights',
                'year'                  => '2010',
                'US_peak_chart_post'    => '11',
            ],
            [
                'album'                 => 'Live in Mississippi',
                'year'                  => '2011',
                'US_peak_chart_post'    => '-',
            ],
            [
                'album'                 => 'Live at the Gold Dollar',
                'year'                  => '2012',
                'US_peak_chart_post'    => '-',
            ],
            [
                'album'                 => 'Nine Miles from the White City',
                'year'                  => '2013',
                'US_peak_chart_post'    => '-',
            ],
        ];

        // Alternative format
        $data2 = [
            [
                'album',
                'year',
                'US_peak_chart_post',
            ],
            [
                'De Stijl',
                '2000',
                '-',
            ],
            [
                'White Blood Cells',
                '2001',
                '61',
            ],
            [
                'Elephant',
                '2003',
                '6',
            ],
            [
                'Get Behind Me Satan',
                '2005',
                '3',
            ],
            [
                'Icky Thump',
                '2007',
                '2',
            ],
            [
                'Under Great White Northern Lights',
                '2010',
                '11',
            ],
            [
                'Live in Mississippi',
                '2011',
                '-',
            ],
            [
                'Live at the Gold Dollar',
                '2012',
                '-',
            ],
            [
                'Nine Miles from the White City',
                '2013',
                '-',
            ],
        ];

        $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => $resource->uri,
                'data'          => $data2,
            ]
        )->assertStatus(200)->assertJson(['success' => true]);

        sleep(1);

        // Test successful request
        $this->post(
            url('api/getResourceData'),
            ['resource_uri' => $resource->uri]
        )->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test resource upload
     *
     * @return void
     */
    public function testCsvUpload()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        $result = $this->post(url('api/csv2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => file_get_contents(storage_path('tests/sample.csv')),
        ])->assertStatus(200)->assertJson(['success' => true]);

        $data = $result->original['data'];

        $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => $resource->uri,
                'data'          => $data,
            ]
        )->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test resource upload
     *
     * @return void
     */
    public function testPdfAndCsvUpload()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id, [
            'file_format'   => Resource::FORMAT_CSV,
            'resource_type' => Resource::TYPE_FILE,
        ]);

        $result = $this->post(url('api/csv2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => file_get_contents(storage_path('tests/sample.csv')),
        ])->assertStatus(200)->assertJson(['success' => true]);

        $data = $result->original['data'];

        $result = $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => $resource->uri,
                'data'          => $data,
            ]
        )->assertStatus(200)->assertJson(['success' => true]);

        $resource = $this->getNewResource($dataSet->id, [
            'file_format'   => Resource::FORMAT_JSON,
            'resource_type' => Resource::TYPE_FILE,
        ]);

        $result = $this->post(url('api/pdf2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => base64_encode(file_get_contents(storage_path('tests/sample.pdf'))),
        ])->assertStatus(200)->assertJson(['success' => true]);

        $data = $result->original['data'];

        $result = $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => $resource->uri,
                'data'          => ['text' => $data],
            ]
        )->assertStatus(200)->assertJson(['success' => true]);

        $resource = $this->getNewResource($dataSet->id, [
            'file_format'   => Resource::FORMAT_CSV,
            'resource_type' => Resource::TYPE_FILE,
        ]);

        $result = $this->post(url('api/csv2json'), [
            'api_key'   => $this->getApiKey(),
            'data'      => file_get_contents(storage_path('tests/sample.csv')),
        ])->assertStatus(200)->assertJson(['success' => true]);

        $data = $result->original['data'];

        $result = $this->post(
            url('api/addResourceData'),
            [
                'api_key'       => $this->getApiKey(),
                'resource_uri'  => $resource->uri,
                'data'          => $data,
            ]
        )->assertStatus(200)->assertJson(['success' => true]);
    }

    /**
     * Test resource modification
     *
     * @return void
     */
    public function testEditResourceMetadata()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test missing api_key
        $this->post(url('api/editResourceMetadata'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing resource uri
        $this->post(
            url('api/editResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => null,
                'data'              => [
                    'name'              => $this->faker->word(),
                    'descript'          => $this->faker->text(),
                    'version'           => 2,
                    'schema_descript'   => $this->faker->word(),
                    'file_format'       => $this->faker->numberBetween(1, 3),
                    'post_data'         => $this->faker->text(),
                    'schema_url'        => $this->faker->url(),
                    'resource_type'     => $this->faker->numberBetween(1, 3),
                    'resource_url'      => $this->faker->url(),
                    'http_rq_type'      => $this->faker->randomElement(['post', 'get']),
                    'authentication'    => $this->faker->word(),
                    'http_headers'      => $this->faker->text(),
                    'is_reported'       => false,
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test sucessfull metadata edit
        $this->post(
            url('api/editResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => [
                    'locale'            => $this->locale,
                    'name'              => $this->faker->word(),
                    'descript'          => $this->faker->text(),
                    'version'           => 2,
                    'schema_descript'   => $this->faker->word(),
                    'post_data'         => $this->faker->text(),
                    'schema_url'        => $this->faker->url(),
                    'resource_type'     => $this->faker->numberBetween(1, 3),
                    'resource_url'      => $this->faker->url(),
                    'http_rq_type'      => $this->faker->randomElement(['post', 'get']),
                    'authentication'    => $this->faker->word(),
                    'http_headers'      => $this->faker->text(),
                    'type'              => $this->faker->numberBetween(1, 3),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource data modification
     *
     * @return void
     */
    public function testUpdateResourceData()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test missing api_key
        $this->post(url('api/updateResourceData'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing resource uri
        $this->post(
            url('api/updateResourceData'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => null,
                'data'              => $this->faker->text(),
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing resource uri
        $this->post(
            url('api/updateResourceData'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successfull data update
        $this->post(
            url('api/updateResourceData'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => ['test elastic' => 'data array 2'],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource deletion
     *
     * @return void
     */
    public function testDeleteResource()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test missing api_key
        $this->post(url('api/deleteResource'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing resource uri
        $this->post(
            url('api/deleteResource'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successfull delete
        $this->post(
            url('api/deleteResource'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource list
     *
     * @return void
     */
    public function testListResources()
    {
        // Test mising api key
        $this->post(url('api/listResources'), ['api_key' => null])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

         // Test ok list
         $this->post(url('api/listResources'),
         [
            'api_key'   => $this->getApiKey(),
            'criteria'  => [
                'locale'    => 'en'
            ]

         ])
         ->assertStatus(200)
         ->assertJson(['success' => true]);
    }

    /**
     * Test resource metadata
     *
     * @return void
     */
    public function testGetResourceMetadata()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test mising resource uri
        $this->post(
            url('api/getResourceMetadata'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful request
        $this->post(
            url('api/getResourceMetadata'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource schema
     *
     * @return void
     */
    public function testGetResourceSchema()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test mising resource uri
        $this->post(
            url('api/getResourceSchema'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);


        // Test successful request
        $this->post(
            url('api/getResourceSchema'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource view
     *
     * @return void
     */
    public function testGetResourceView()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test mising resource uri
        $this->post(
            url('api/getResourceView'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful request
        $this->post(
            url('api/getResourceView'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource data
     *
     * @return void
     */
    public function testGetResourceData()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test mising resource uri
        $this->post(
            url('api/getResourceData'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful request
        $this->post(
            url('api/getResourceData'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resource search
     *
     * @return void
     */
    public function testSearchResourceData()
    {
        $dataSet = $this->getNewDataSet();
        $resource = $this->getNewResource($dataSet->id);

        // Test mising criteria
        $this->post(
            url('api/searchResourceData'),
            [
                'criteria' => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test mising with criteria
        $this->post(
            url('api/searchResourceData'),
            [
                'criteria'  => [
                    'keywords'  => 'cool',
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
