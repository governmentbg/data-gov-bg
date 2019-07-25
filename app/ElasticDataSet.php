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
            ->where('version', $version)
            ->first();

        if (!empty($elasticData)) {
            $data = \Elasticsearch::get([
                'index' => $elasticData->index,
                'type'  => $elasticData->index_type,
                'id'    => $elasticData->doc,
            ]);
        }

        $result = [];

        if (!empty($data['_source'][$id .'_'. $version])) {
            $result = $data['_source'][$id .'_'. $version];
        } elseif (!empty($data['_source']['rows'])) {
            $result = $data['_source']['rows'];
        } elseif (!empty($data['_source'])) {
            $result = $data['_source'];
        }

        return $result;
    }

    public static function setElasticHosts()
    {
        if (getenv('ELASTICSEARCH_HOSTS')) {
            $hostsList = explode(',', env('ELASTICSEARCH_HOSTS'));
            $port = env('ELASTICSEARCH_PORT');
            $scheme = env('ELASTICSEARCH_SCHEME');
            $user = env('ELASTICSEARCH_USER');
            $pass = env('ELASTICSEARCH_PASS');

            foreach ($hostsList as $host) {
                $hosts[] = [
                    'host'   => trim($host),
                    'port'   => $port,
                    'scheme' => $scheme,
                    'user'   => $user,
                    'pass'   => $pass
                ];
            }

            return $hosts;
        }
    }
}
