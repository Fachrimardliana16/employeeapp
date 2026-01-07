<?php

namespace App\Filament\Employee\Resources\EmployeePermissionResource\Pages;

use App\Filament\Employee\Resources\EmployeePermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeePermissions extends ListRecords
{
    protected static string $resource = EmployeePermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
