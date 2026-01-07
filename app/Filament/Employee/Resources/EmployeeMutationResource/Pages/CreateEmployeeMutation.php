<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Pages;

use App\Filament\Employee\Resources\EmployeeMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeMutation extends CreateRecord
{
    protected static string $resource = EmployeeMutationResource::class;
}
