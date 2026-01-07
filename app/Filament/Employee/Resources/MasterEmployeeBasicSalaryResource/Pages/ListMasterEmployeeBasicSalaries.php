<?php

namespace App\Filament\Employee\Resources\MasterEmployeeBasicSalaryResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeBasicSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeBasicSalaries extends ListRecords
{
    protected static string $resource = MasterEmployeeBasicSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
