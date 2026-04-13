<?php

namespace App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeNonPermanentSalary extends EditRecord
{
    protected static string $resource = MasterEmployeeNonPermanentSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
