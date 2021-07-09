<?php

namespace App\Console\Commands;

use App\ElasticDataSet;
use Elasticsearch as ES;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckElasticNodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:nodes-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if all Elasticsearch nodes are running';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!ES::ping()) {
          $this->error('Elasticsearch is not running');
          die();
        }

        $nodes = ElasticDataSet::getElasticClusterParam('number_of_nodes');

        if($nodes < 6) {

          $this->info('Starting a check of Elasticsearch down nodes..');

          $nodesIpsEnv = ElasticDataSet::getElasticHosts();
          $runningNodesIps = ElasticDataSet::getElasticNodesIps();
          $nodesDownIps = [];

          foreach ($nodesIpsEnv as $ip) {
            if(!in_array($ip, $runningNodesIps)) {

              $this->line("");
              $this->info("Node with ip $ip is down");

              $nodesDownIps[] = $ip;
            }
          }

          if(!empty($nodesDownIps)) {
            $mailData = ['nodesDownIps' => $nodesDownIps];

            $this->line("");
            $this->info("Sending alert email");

            Mail::send('mail/nodesDownAlert', $mailData, function ($m) {
              $m->from(config('app.MAIL_FROM'), config('app.APP_NAME'));
              $m->to('iliyan.dimitrov@asap.bg');
              //$m->to('dev@asap.bg;skirov@e-gov.bg;bikozhuharov@e-gov.bg');
              $m->subject(__('custom.cluster_nodes_down_alert'));
            });
          }
        }

        $this->line("");
        $this->info("Check has finished");
    }
}
