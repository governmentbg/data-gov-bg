<?php

return [
    /*
     * Graylog connection configuration.
     */
    'host' => env('GRAYLOG_HOST', '127.0.0.1'),
    'port' => env('GRAYLOG_PORT', '9000'),


    /*
     * Map enviroments to graylog tags. Each enviroment uses different graylog stream.
     */
    'tag' => [
        'local' => 'opendata.local',
        'demo' => 'opendata.demo',
        'production' => 'opendata.production'
    ]
];

