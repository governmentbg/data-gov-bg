<?php

use App\DataSet;
use App\Resource;
use App\ElasticDataSet;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ElasticDataSetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->faker = Faker::create();

        $resources = Resource::orderBy('created_at', 'desc')->limit(ResourceSeeder::RESOURCE_RECORDS)->get();

        foreach ($resources as $resource) {
            $index = $resource->data_set_id;
            $id = $resource->id;

            $elasticDataSet = ElasticDataSet::create([
                'index'         => $index,
                'index_type'    => ElasticDataSet::ELASTIC_TYPE,
                'doc'           => $id
            ]);

            $resource->es_id = $elasticDataSet->id;
            $resource->save();

            $data = [
                'username'  => $this->faker->name,
                'password'  => bcrypt($elasticDataSet->id),
                'email'     => $this->faker->email,
                'firstname' => $this->faker->firstName(),
                'lastname'  => $this->faker->lastName(),
                'text'      => $this->faker->text(),
                'active'    => $this->faker->boolean(),
                'hash_id'   => $this->faker->md5(),
            ];

           \Elasticsearch::index([
               'body'  => $data,
               'index' => $index,
               'type'  => ElasticDataSet::ELASTIC_TYPE,
               'id'    => $id,
           ]);
        }
    }
}
