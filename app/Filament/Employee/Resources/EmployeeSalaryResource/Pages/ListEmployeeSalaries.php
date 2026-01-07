<?php

namespace App\Filament\Employee\Resources\EmployeeSalaryResource\Pages;

use App\Filament\Employee\Resources\EmployeeSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSalaries extends ListRecords
{
    protected static string $resource = EmployeeSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
