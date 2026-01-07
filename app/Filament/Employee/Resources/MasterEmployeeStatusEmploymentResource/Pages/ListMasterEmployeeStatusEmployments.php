<?php

namespace App\Filament\Employee\Resources\MasterEmployeeStatusEmploymentResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeStatusEmploymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeStatusEmployments extends ListRecords
{
    protected static string $resource = MasterEmployeeStatusEmploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
