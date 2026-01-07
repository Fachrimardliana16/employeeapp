<?php

namespace App\Filament\Employee\Resources\MasterDepartmentResource\Pages;

use App\Filament\Employee\Resources\MasterDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterDepartment extends EditRecord
{
    protected static string $resource = MasterDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
