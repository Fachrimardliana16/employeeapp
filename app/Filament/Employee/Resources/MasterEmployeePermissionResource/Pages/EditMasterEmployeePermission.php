<?php

namespace App\Filament\Employee\Resources\MasterEmployeePermissionResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeePermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeePermission extends EditRecord
{
    protected static string $resource = MasterEmployeePermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
