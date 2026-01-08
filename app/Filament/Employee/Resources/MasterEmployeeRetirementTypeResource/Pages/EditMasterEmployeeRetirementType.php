<?php

namespace App\Filament\Employee\Resources\MasterEmployeeRetirementTypeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeRetirementTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeRetirementType extends EditRecord
{
    protected static string $resource = MasterEmployeeRetirementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
