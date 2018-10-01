<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ElasticDataSet extends Model
{
    const ELASTIC_TYPE = 'default';

    protected $guarded = ['id'];
    protected $table = 'elastic_data_set';
    public $timestamps = false;

    public function resource()
    {
        $this->belongsTo('App\Resource');
    }

    public static function getElasticData($id, $version)
    {
        $elasticData = ElasticDataSet::where('resource_id', $id)
            ->where('version', $version)->first();

        if (!empty($elasticData)) {
            $data = \Elasticsearch::get([
                'index' => $elasticData->index,
                'type'  => $elasticData->index_type,
                'id'    => $elasticData->doc,
            ]);
        }

        return !empty($data['_source']['rows']) ? $data['_source']['rows'] : [];
    }
}
