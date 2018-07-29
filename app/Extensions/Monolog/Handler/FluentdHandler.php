<?php

namespace App\Extensions\Monolog\Handler;

use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class FluentdHandler extends AbstractProcessingHandler
{
    protected $config;

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->logger = new \Fluent\Logger\FluentLogger(config('logger.host'), config('logger.port'));
    }

    protected function getDefaultFormatter()
    {
        return new GelfMessageFormatter;
    }

    public function write(array $record)
    {
        $this->logger->post(config('logger.tag.'. config('app.env')), $record['formatted']->toArray());
    }
}
