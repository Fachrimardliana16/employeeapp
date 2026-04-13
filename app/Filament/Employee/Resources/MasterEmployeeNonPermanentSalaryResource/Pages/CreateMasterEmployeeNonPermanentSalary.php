<?php

namespace App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterEmployeeNonPermanentSalary extends CreateRecord
{
    protected static string $resource = MasterEmployeeNonPermanentSalaryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
