<?php

namespace App\Filament\Employee\Resources\MasterSubDepartmentResource\Pages;

use App\Filament\Employee\Resources\MasterSubDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterSubDepartments extends ListRecords
{
    protected static string $resource = MasterSubDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
