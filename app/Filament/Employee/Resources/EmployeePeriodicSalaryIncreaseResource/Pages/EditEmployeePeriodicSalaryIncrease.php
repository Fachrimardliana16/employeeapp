<?php

namespace App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeePeriodicSalaryIncrease extends EditRecord
{
    protected static string $resource = EmployeePeriodicSalaryIncreaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
