<?php

namespace App\Http\Controllers\Traits;

use App\User;
use Illuminate\Support\Facades\Auth;

trait RecordSignature
{
    protected static function bootRecordSignature()
    {
        static::updating(function ($model) {
            $userId = self::getUserId();

            if (array_key_exists('updated_by', $model->attributes)) {
                $model->updated_by = $userId;
            }
        });

        static::creating(function ($model) {
            $userId = self::getUserId();

            if (empty($model->created_by)) {
                $model->created_by = $userId;
            }
        });

    }

    private static function getUserId()
    {
        $userId = null;

        if (Auth::check()) {
            $userId = Auth::user()->id;
        } else if (!empty($system = User::select('id')->where('username', 'system')->first())) {
            $userId = $system->id;
        }

        return $userId;

    }
}
