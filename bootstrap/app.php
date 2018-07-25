<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->configureMonologUsing(function ($monolog) use($app){
    $logLevel = \Monolog\Logger::toMonologLevel(config('app.log_level'));

    if (App::environment(['devel', 'test'])){
//    if (App::environment(['local', 'demo'])){

        //graylog handler
        $fluentd = new \App\Extensions\Monolog\Handler\FluentdHandler($logLevel);
        $fluentd->pushProcessor(function ($record) {
                $record['extra']['dbname'] = env('DB_DATABASE');
                $record['extra']['dbhost'] = env('DB_HOST');
                return $record;
            });
        $monolog->pushHandler($fluentd);

        //slack webhook
        //$monolog->pushHandler($slack = new \Monolog\Handler\SlackWebhookHandler('https://hooks.slack.com/services/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'));
    }

    //handler for default laravel log file "laravel.log"
    $monolog->pushHandler($fileHandler = new \Monolog\Handler\StreamHandler($app->storagePath() . '/logs/laravel.log'), $logLevel, true);
    $fileHandler->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, false, true));

    // fix cases when file and line are undefined because function is called inside callable
    $monolog->pushProcessor(function ($record) {
            foreach (['file', 'line'] as $field) {
                if (empty($record['extra'][$field]) && !empty($record['context'][$field])) {
                    $record['extra'][$field] = $record['context'][$field];
                }
            }

            if (!empty($record['context']['exception'])) { //get file and line from exception
                $record['extra']['file'] = $record['context']['exception']->getFile();
                $record['extra']['line'] = $record['context']['exception']->getLine();
            }

            return $record;
        });

    //additional message processors
    $monolog->pushProcessor(new \Monolog\Processor\GitProcessor());
    $monolog->pushProcessor(new \Monolog\Processor\WebProcessor());
    $monolog->pushProcessor(new \Monolog\Processor\IntrospectionProcessor($logLevel, ['Illuminate\\Log', 'Exceptions\\Handler', 'Illuminate\\Routing\\Pipeline']));
    $monolog->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
});

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
