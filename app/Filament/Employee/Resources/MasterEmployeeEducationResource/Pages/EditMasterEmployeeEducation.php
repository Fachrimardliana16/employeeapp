<?php

namespace App\Filament\Employee\Resources\MasterEmployeeEducationResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeEducationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeEducation extends EditRecord
{
    protected static string $resource = MasterEmployeeEducationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
