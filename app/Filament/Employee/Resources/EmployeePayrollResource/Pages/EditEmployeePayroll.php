<?php

namespace App\Filament\Employee\Resources\EmployeePayrollResource\Pages;

use App\Filament\Employee\Resources\EmployeePayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeePayroll extends EditRecord
{
    protected static string $resource = EmployeePayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
