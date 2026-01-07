<?php

namespace App\Filament\Employee\Resources\MasterEmployeeBasicSalaryResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeBasicSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeBasicSalary extends EditRecord
{
    protected static string $resource = MasterEmployeeBasicSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
