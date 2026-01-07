<?php

namespace App\Filament\Employee\Resources\MasterEmployeeStatusEmploymentResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeStatusEmploymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeStatusEmployment extends EditRecord
{
    protected static string $resource = MasterEmployeeStatusEmploymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
