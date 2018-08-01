<?php

namespace Tests\Unit\Api;

use App\Document;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocumentTest extends TestCase
{
    use WithFaker;
    use DatabaseTransactions;

    public function testAddDocument()
    {
        //test missing api key
        $this->post(url('api/addDocument'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing data
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
                    'locale'       => 'en',
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

        // check that a record is made
        $this->assertTrue($count + 1 == Document::all()->count());
    }

    public function testEditDocument()
    {
        $document = Document::create([
            'name'         => $this->faker->word(),
            'descript'     => $this->faker->word(),
            'file_name'    => $this->faker->word(),
            'mime_type'    => $this->faker->word(),
            'data'         => $this->faker->word()
        ]);

        //test missing api key
        $this->post(url('api/addDocument'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing doc id
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

        //test missing data
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

        // test successful test Edit
        $this->post(
            url('api/editDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => $document->id,
                'data'      => [
                    'locale'        => 'en',
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
    }

    public function testDeleteDocument()
    {
        $document = Document::create([
            'name'         => $this->faker->word(),
            'descript'     => $this->faker->word(),
            'file_name'    => $this->faker->word(),
            'mime_type'    => $this->faker->word(),
            'data'         => $this->faker->word()
        ]);

        //test missing api key
        $this->post(url('api/deleteDocument'), ['api_key' => null])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        //test missing document id
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

        //test successful delete
        $this->post(
            url('api/deleteDocument'),
            [
                'api_key'   => $this->getApiKey(),
                'doc_id'    => $document->id,
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // check that a record is missing
        $this->assertTrue($count - 1 == Document::all()->count());
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

    public function testSearchDocument()
    {
        // test missing criteria
        $this->post(
            url('api/searchDocuments'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [],
            ]
        )
            ->assertStatus(500)
            ->assertJson(['success' => false]);

        //test successful search
        $this->post(
            url('api/searchDocuments'),
            [
                'api_key'   => $this->getApiKey(),
                'criteria'  => [
                    'search'    => $this->faker->word(),
                ],
            ]
        )
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
