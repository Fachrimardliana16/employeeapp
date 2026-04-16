<?php

namespace App\Filament\Employee\Resources\EmployeeFamilyResource\Pages;

use App\Filament\Employee\Resources\EmployeeFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeFamily extends CreateRecord
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
