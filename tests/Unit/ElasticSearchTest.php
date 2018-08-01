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
        $this->insertTest();
        $this->searchTest();
        $this->deleteTest();
    }

    /**
     * A basic insert test
     *
     * @return void
     */
    public function insertTest()
    {
        $data = [
            'body'      => [
                'testField' => 'testing_string'
            ],
            'index'     => 'test_index',
            'type'      => 'test_type',
            'id'        => 'test_id',
        ];

        $insert = \Elasticsearch::index($data);

        $this->assertTrue(!empty($insert['result']) && $insert['result'] == 'created');
    }

    /**
     * A basic search and query language test
     *
     * @return void
     */
    public function searchTest()
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

        sleep(1); // It takes some time for the data to be indexed in the previos step

        $search = \Elasticsearch::search($data);

        $this->assertTrue(!empty($search['hits']['total']));
    }

    /**
     * A basic delete index test
     *
     * @return void
     */
    public function deleteTest()
    {
        $delete = \Elasticsearch::delete(['index' => 'test_index', 'type' => 'test_type', 'id' => 'test_id']);

        $this->assertTrue(!empty($delete['result']) && $delete['result'] == 'deleted');
    }
}
