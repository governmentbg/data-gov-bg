<?php

namespace App\Http\Controllers\Traits;

use App\User;
use Illuminate\Support\Facades\Auth;

trait RecordSignature
{
    protected static function bootRecordSignature()
    {
        $userId = null;

        if (Auth::check()) {
            $userId = Auth::user()->id;
        } else if (!empty($system = User::select('id')->where('username', 'system')->first())) {
            $userId = $system->id;
        }

        static::updating(function ($model) use ($userId) {
            if (empty($model->updated_by)) {
                $model->updated_by = $userId;
            }
        });

        static::creating(function ($model) use ($userId) {
            if (empty($model->created_by)) {
                $model->created_by = $userId;
            }
        });
    }
}
