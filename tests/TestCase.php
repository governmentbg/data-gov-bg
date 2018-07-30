<?php

namespace Tests;

use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $apiKey;
    protected $userId;

    protected function log($message)
    {
        echo PHP_EOL . PHP_EOL . print_r($message, true) . PHP_EOL;
    }

    protected function getApiKey()
    {
        if (isset($this->apiKey)) {
            return $this->apiKey;
        }

        $this->apiKey = User::where('username', 'system')->first()->api_key;

        return $this->apiKey;
    }

    protected function getUserId()
    {
        if (isset($this->systemUser)) {
            return $this->systemUser->id;
        }

        if (isset($this->userId)) {
            return $this->userId;
        }

        $this->userId = User::where('username', 'system')->first()->id;

        return $this->userId;
    }

    protected function getSystemUser()
    {
        if (isset($this->systemUser)) {
            return $this->systemUser;
        }

        $this->systemUser = User::where('username', 'system')->first();

        return $this->systemUser;
    }
}
