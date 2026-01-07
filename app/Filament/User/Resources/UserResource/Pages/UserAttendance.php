<?php

namespace App\Filament\User\Resources\UserResource\Pages;

use App\Filament\User\Resources\UserResource;
use Filament\Resources\Pages\Page;

class UserAttendance extends Page
{
    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.user.resources.user-resource.pages.user-attendance';
}
