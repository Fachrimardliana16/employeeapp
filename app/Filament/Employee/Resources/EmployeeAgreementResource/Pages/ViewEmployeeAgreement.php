<?php

namespace App\Filament\Employee\Resources\EmployeeAgreementResource\Pages;

use App\Filament\Employee\Resources\EmployeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeAgreement extends ViewRecord
{
    protected static string $resource = EmployeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
