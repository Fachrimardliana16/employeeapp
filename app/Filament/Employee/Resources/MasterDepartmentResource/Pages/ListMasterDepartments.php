<?php

namespace App\Filament\Employee\Resources\MasterDepartmentResource\Pages;

use App\Filament\Employee\Resources\MasterDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterDepartments extends ListRecords
{
    protected static string $resource = MasterDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
