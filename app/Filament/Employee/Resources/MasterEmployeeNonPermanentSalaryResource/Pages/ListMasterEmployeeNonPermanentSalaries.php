<?php

namespace App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeNonPermanentSalaryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeNonPermanentSalaries extends ListRecords
{
    protected static string $resource = MasterEmployeeNonPermanentSalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Gaji Standar'),
        ];
    }
}
