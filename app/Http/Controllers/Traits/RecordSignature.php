<?php

namespace App\Http\Controllers\Traits;

use App\User;

trait RecordSignature
{
    protected static function bootRecordSignature()
    {
        $userId = \Auth::check() ?
            \Auth::user()->id :
            User::select('id')->where('username', 'system')->first()->id;

        static::updating(function ($model) use ($userId) {
            $model->updated_by = $userId;
        });

        static::creating(function ($model) use ($userId) {
            $model->created_by = $userId;
        });
    }
}
