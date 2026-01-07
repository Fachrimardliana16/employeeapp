<?php

namespace App\Filament\Employee\Resources\MasterSubDepartmentResource\Pages;

use App\Filament\Employee\Resources\MasterSubDepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterSubDepartment extends EditRecord
{
    protected static string $resource = MasterSubDepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
