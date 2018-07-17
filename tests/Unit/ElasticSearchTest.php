<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ElasticSearchTest extends TestCase
{
    /**
     * Basic tests
     *
     * @return void
     */
    public function test()
    {
        $this->insert();
        $this->search();
        $this->delete();
    }

    /**
     * A basic insert test
     *
     * @return void
     */
    public function insert()
    {
        $data = [
            'body' => [
                'testField' => 'testing_string'
            ],
            'index'     => 'test_index',
            'type'      => 'test_type',
            'id'        => 'test_id',
        ];

        $insert = \Elasticsearch::index($data);
        $insert = \Elasticsearch::index($data);

        $this->assertTrue(is_array($insert));
    }

    /**
     * A basic search and query language test
     *
     * @return void
     */
    public function search()
    {
        $data = [
            'index' => 'test_index',
            'type'  => 'test_type',
            'body'  => '{
                "query" : {
                    "match" : {
                        "testField" : "testing_string"
                    }
                }
            }',
        ];

        $search = \Elasticsearch::search($data);

        $this->assertTrue(!empty($search['hits']['total']));
    }

    /**
     * A basic delete index test
     *
     * @return void
     */
    public function delete()
    {
        $delete = \Elasticsearch::indices()->delete(['index' => 'test_index']);

        $this->assertTrue(!empty($delete['acknowledged']));
    }
}
