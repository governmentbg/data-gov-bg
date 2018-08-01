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

    public static function getElasticData($id)
    {
        $elasticData = ElasticDataSet::find($id);

        if (!empty($elasticData)) {
            $data = \Elasticsearch::get([
                'index' => $elasticData->index,
                'type'  => $elasticData->index_type,
                'id'    => $elasticData->doc,
            ]);
        }

        return !empty($data['_source']) ? $data['_source'] : null;
    }
}
