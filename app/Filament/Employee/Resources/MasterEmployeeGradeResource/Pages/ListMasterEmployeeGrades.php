<?php

namespace App\Filament\Employee\Resources\MasterEmployeeGradeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeGradeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeGrades extends ListRecords
{
    protected static string $resource = MasterEmployeeGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
