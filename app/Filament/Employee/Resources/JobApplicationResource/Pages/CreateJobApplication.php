<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Pages;

use App\Filament\Employee\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateJobApplication extends CreateRecord
{
    protected static string $resource = JobApplicationResource::class;
}
