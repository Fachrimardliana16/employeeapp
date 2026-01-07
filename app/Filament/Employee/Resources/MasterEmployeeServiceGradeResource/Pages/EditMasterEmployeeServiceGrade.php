<?php

namespace App\Filament\Employee\Resources\MasterEmployeeServiceGradeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeServiceGradeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterEmployeeServiceGrade extends EditRecord
{
    protected static string $resource = MasterEmployeeServiceGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
