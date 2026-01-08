<?php

namespace App\Filament\Employee\Resources\MasterEmployeeRetirementTypeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeRetirementTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeRetirementTypes extends ListRecords
{
    protected static string $resource = MasterEmployeeRetirementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
