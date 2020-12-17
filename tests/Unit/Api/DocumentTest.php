<?php

namespace Tests\Unit\Api;

use App\Document;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Http\Controllers\ApiController;

class DocumentTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    /**
     * Test document creation
     *
     * @return void
     */
    public function testAddDocument()
    {
        // Test missing api key
        $this->post(url('api/addDocument'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/addDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Document::all()->count();

        $this->post(
            url('api/addDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'data'      => [
                    'locale'       => $this->locale,
                    'name'         => $this->faker->word(),
                    'description'  => $this->faker->word(),
                    'filename'     => $this->faker->word(),
                    'mimetype'     => $this->faker->word(),
                    'data'         => $this->faker->word()
                ]
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is made
        $this->assertTrue($count + 1 == Document::all()->count());
    }

    /**
     * Test document modification
     *
     * @return void
     */
    public function testEditDocument()
    {
        $document = $this->getNewDocument();

        // Test missing api key
        $this->post(url('api/addDocument'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing doc id
        $this->post(
            url('api/editDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => null,
                'data'      => [
                    'filename'     => $this->faker->word()
                ],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test missing data
        $this->post(
            url('api/editDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => $document->id,
                'data'      => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        // Test successful test Edit
        $this->post(
            url('api/editDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => $document->id,
                'data'      => [
                    'locale'        => $this->locale,
                    'name'          => $this->faker->word(),
                    'description'   => $this->faker->word(),
                    'filename'      => $this->faker->word(),
                    'mimetype'      => $this->faker->word(),
                    'data'          => $this->faker->word()
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        @unlink('storage/docs/'. $document->id);
    }

    /**
     * Test document deletion
     *
     * @return void
     */
    public function testDeleteDocument()
    {
        $document = $this->getNewDocument();

        // Test missing api key
        $this->post(url('api/deleteDocument'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // Test missing document id
        $this->post(
            url('api/deleteDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => null,
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        $count = Document::all()->count();

        // Test successful delete
        $this->post(
            url('api/deleteDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => $document->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check that a record is missing
        $this->assertTrue($count - 1 == Document::all()->count());

        @unlink('storage/docs/'. $document->id);
    }

    public function testList()
    {
        $response = $this->post(
            url('api/listDocuments'),
            ['api_key'  => $this->getApiKey()]
        );

        $response
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test document search
     *
     * @return void
     */
    public function testSearchDocument()
    {
        // Test successful search
        $this->post(
            url('api/listDocuments'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [
                    'keywords'    => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
