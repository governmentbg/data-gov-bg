<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function log($message)
    {
        echo PHP_EOL . PHP_EOL . print_r($message, true) . PHP_EOL;
    }

    protected function getApiKey()
    {
        return User::where('username', 'system')->first()->api_key;
    }
}
