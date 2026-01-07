<?php

namespace App\Filament\Employee\Resources\MasterEmployeeArchiveTypeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeArchiveTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeArchiveType extends EditRecord
{
    protected static string $resource = MasterEmployeeArchiveTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
