<?php

namespace App\Filament\Employee\Resources\MasterEmployeeServiceGradeResource\Pages;

use App\Filament\Employee\Resources\MasterEmployeeServiceGradeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterEmployeeServiceGrades extends ListRecords
{
    protected static string $resource = MasterEmployeeServiceGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
