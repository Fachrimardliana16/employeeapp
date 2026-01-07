<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasUserCreator
{
    protected static function bootHasUserCreator()
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->users_id)) {
                $model->users_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && empty($model->users_id)) {
                $model->users_id = Auth::id();
            }
        });
    }
}
