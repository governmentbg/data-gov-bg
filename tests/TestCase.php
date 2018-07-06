<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getApiKey()
    {
        return User::where('username', 'system')->first()->api_key;
    }
}
