<?php

namespace Tests\Unit\Api;

use App\DataSet;
use App\Resource;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ResourceTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testAddResourceMetadata()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        // test missing api_key
        $this->post(url('api/addResourceMetadata'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing dataset_uri
        $this->post(
            url('api/addResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'data'              => [
                    'name'              => $this->faker->word(),
                    'description'       => $this->faker->text(),
                    'locale'            => 'en',
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

        // test successfull creation
        $this->post(
            url('api/addResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'dataset_uri'       => $dataSet->uri,
                'data'              => [
                    'type'              => $this->faker->numberBetween(1, 3),
                    'name'              => $this->faker->word(),
                    'descript'          => $this->faker->text(),
                    'locale'            => 'en',
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

    public function testAddResourceData()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test missing api_key
        $this->post(url('api/addResourceData'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing resource uri
        $this->post(
            url('api/addResourceData'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => null,
                'data'              => $this->faker->text(),
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test missing data uri
        $this->post(
            url('api/addResourceData'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successfull edit
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

        // alternative format
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
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => $data2,
            ]
        )->assertStatus(200)->assertJson(['success' => true]);

        sleep(1);

        // test successful request
        $this->post(
            url('api/getResourceData'),
            ['resource_uri' => $resource->uri]
        )->assertStatus(200)->assertJson(['success' => true]);

        // test successfull data update
        $this->post(
            url('api/updateResourceData'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => $data2,
            ]
        )->assertStatus(200)->assertJson(['success' => true]);

        // test successful request
        $this->post(
            url('api/getResourceData'),
            ['resource_uri' => $resource->uri]
        )->assertStatus(200)->assertJson(['success' => true]);
    }

    public function testEditResourceMetadata()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test missing api_key
        $this->post(url('api/editResourceMetadata'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing resource uri
        $this->post(
            url('api/editResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => null,
                'data'              => [
                    'name'              => $this->faker->word(),
                    'descript'          => $this->faker->text(),
                    'version'           => $this->faker->word(),
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

        // test missing data
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

        // test sucessfull metadata edit
        $this->post(
            url('api/editResourceMetadata'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => $resource->uri,
                'data'              => [
                    'locale'            => 'en',
                    'name'              => $this->faker->word(),
                    'descript'          => $this->faker->text(),
                    'version'           => $this->faker->word(),
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

    public function testUpdateResourceData()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1,3),
            'visibility'    => $this->faker->numberBetween(1,2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1,2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test missing api_key
        $this->post(url('api/updateResourceData'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing resource uri
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

        // test missing resource uri
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

        // test successfull data update
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

    public function testDeleteResource()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test missing api_key
        $this->post(url('api/deleteResource'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // test missing resource uri
        $this->post(
            url('api/deleteResource'),
            [
                'api_key'           => $this->getApiKey(),
                'resource_uri'      => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successfull delete
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

    public function testListResources()
    {
        // test mising api key
        $this->post(url('api/listResources'), ['api_key' => null])
            ->assertStatus(500)
            ->assertJson(['success' => false]);

         // test ok list
         $this->post(url('api/listResources'),
         [
            'api_key' => $this->getApiKey(),
            'criteria' => [
                "locale" => "en"
            ]

         ])
         ->assertStatus(200)
         ->assertJson(['success' => true]);
    }

    public function testGetResourceMetadata()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1,3),
            'visibility'    => $this->faker->numberBetween(1,2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1,2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test mising resource uri
        $this->post(
            url('api/getResourceMetadata'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful request
        $this->post(
            url('api/getResourceMetadata'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testGetResourceSchema()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1,2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test mising resource uri
        $this->post(
            url('api/getResourceSchema'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);


        // test successful request
        $this->post(
            url('api/getResourceSchema'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testGetResourceView()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1,3),
            'visibility'    => $this->faker->numberBetween(1,2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1,2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test mising resource uri
        $this->post(
            url('api/getResourceView'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful request
        $this->post(
            url('api/getResourceView'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testGetResourceData()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test mising resource uri
        $this->post(
            url('api/getResourceData'),
            [
                'resource_uri' => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test successful request
        $this->post(
            url('api/getResourceData'),
            [
                'resource_uri' => $resource->uri,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function testSearchResourceData()
    {
        $dataSet = DataSet::create([
            'name'          => $this->faker->word(),
            'uri'           => $this->faker->uuid(),
            'category_id'   => $this->faker->numberBetween(1, 3),
            'visibility'    => $this->faker->numberBetween(1, 2),
            'version'       => $this->faker->word(),
            'status'        => $this->faker->numberBetween(1, 2),
        ]);

        $resource = Resource::create([
            'data_set_id'       => $dataSet->id,
            'uri'               => $this->faker->uuid(),
            'name'              => $this->faker->word(),
            'descript'          => $this->faker->text(),
            'version'           => $this->faker->word(),
            'schema_descript'   => $this->faker->word(),
            'file_format'       => $this->faker->numberBetween(1, 3),
            'post_data'         => $this->faker->text(),
            'schema_url'        => $this->faker->url(),
            'resource_type'     => $this->faker->numberBetween(1, 3),
            'resource_url'      => $this->faker->url(),
            'http_rq_type'      => $this->faker->numberBetween(1, 2),
            'authentication'    => $this->faker->word(),
            'http_headers'      => $this->faker->text(),
            'is_reported'       => false,
        ]);

        // test mising criteria
        $this->post(
            url('api/searchResourceData'),
            [
                'criteria' => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // test mising with criteria
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
