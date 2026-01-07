<?php

namespace App\Filament\Employee\Resources\EmployeeAgreementResource\Pages;

use App\Filament\Employee\Resources\EmployeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeAgreement extends EditRecord
{
    protected static string $resource = EmployeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
