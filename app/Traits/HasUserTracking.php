<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasUserTracking
{
    protected static function bootHasUserTracking()
    {
        static::creating(function ($model) {
            if (empty($model->users_id) && Auth::check()) {
                $model->users_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (empty($model->users_id) && Auth::check()) {
                $model->users_id = Auth::id();
            }
        });
    }
}
