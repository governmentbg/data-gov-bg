<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Elasticsearch as ES;
use Symfony\Component\VarDumper\Cloner\Data;

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

    /**
     * Get Elasticsearch cluster parameters
     *
     * @param string $param can be "cluster_name",
     *                             "status",
     *                             "timed_out",
     *                             "number_of_nodes",
     *                             "number_of_data_nodes",
     *                             "active_primary_shards",
     *                             "active_shards",
     *                             "relocating_shards",
     *                             "initializing_shards",
     *                             "unassigned_shards",
     *                             "delayed_unassigned_shards",
     *                             "number_of_pending_tasks",
     *                             "number_of_in_flight_fetch",
     *                             "task_max_waiting_in_queue_millis",
     *                             "active_shards_percent_as_number",
     *                             "all"
     * @return mixed
     */
    public static function getElasticClusterParam(string $param) {

      if(ES::ping()) {
        $clusterHealth = ES::cluster()->health();

        return ($param == "all") ? $clusterHealth : $clusterHealth[$param];
      }

      return "No ping to ES";
    }

    /**\
     * Get Elasticsearch cluster stats
     *
     * @return string
     */
    public static function getElasticClusterStats() {

      if(ES::ping()) {
        return ES::cluster()->stats();
      }

      return "No ping to ES";
    }

    /**
     * Get Elasticsearch index info for docs
     * @param $index
     * @return mixed|string
     */
    public static function getElasticIndexInfo($index) {

      if(ES::ping()) {
        $indexStats = ES::indices()->stats(['index' => $index]);
        if(isset($indexStats['indices'][$index]['primaries'])) {
          return $indexStats['indices'][$index]['primaries'];
        }
      }

      return "No ping to ES";
    }

    /**
     * Get Elasticsearch index's docs count
     * @param $index
     * @return mixed|string
     */
    public static function getElasticIndexDocsCount($index) {

      if(ES::ping()) {
        $data = ES::count(['index' => $index]);

        if(isset($data['count'])) return $data['count'];
        else return 'N/A';
      }

      return "No ping to ES";
    }

    /**
     * Get Elasticsearch cluster nodes ips
     * @return mixed|array
     */
    public static function getElasticNodesIps() {

      if(ES::ping()) {
        $nodesIps = shell_exec('curl -X GET "'.env('ELASTICSEARCH_HOST').':'.env('ELASTICSEARCH_PORT').'/_cat/nodes?h=ip"');
        $nodesIpsArr = explode("\n", $nodesIps);

        if(empty($nodesIpsArr[count($nodesIpsArr)-1])) {
          array_pop($nodesIpsArr);
        }

        return $nodesIpsArr;
      }

      return "No ping to ES";
    }

    /**
     * Get Elasticsearch nodes tasks
     * @return mixed|array
     */
    public static function getElasticNodesTasks() {

      if(ES::ping()) {
        $tasksList = "";
        $esTasks = ES::tasks()->get()['nodes'];
        foreach ($esTasks as $node) {
          if(isset($node['tasks'])) {
            foreach ($node['tasks'] as $task) {
              $tasksList .= "{$node['host']} - {$task['action']} <br>\n";
            }
          }
        }

        return $tasksList;
      }

      return "No ping to ES";
    }

    public static function getElasticData($id, $version)
    {
        $elasticData = ElasticDataSet::where('resource_id', $id)
            ->where('version', $version)
            ->first();

        if (!empty($elasticData)) {
            $data = ES::get([
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

    /**
     * Get the Elasticsearch hosts from .env file
     * @return false|string[]
     */
    public static function getElasticHosts()
    {
        if (getenv('ELASTICSEARCH_HOSTS')) {
            return explode(',', env('ELASTICSEARCH_HOSTS'));
        }

        return null;
    }
}
