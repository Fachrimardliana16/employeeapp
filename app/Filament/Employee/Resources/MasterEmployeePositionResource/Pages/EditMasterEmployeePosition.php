<?php

namespace App\Filament\Employee\Resources\MasterEmployeePositionResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeePositionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeePosition extends EditRecord
{
    protected static string $resource = MasterEmployeePositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
