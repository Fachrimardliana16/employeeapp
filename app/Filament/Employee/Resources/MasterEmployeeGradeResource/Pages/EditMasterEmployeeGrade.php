<?php

namespace App\Filament\Employee\Resources\MasterEmployeeGradeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeGradeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeGrade extends EditRecord
{
    protected static string $resource = MasterEmployeeGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
