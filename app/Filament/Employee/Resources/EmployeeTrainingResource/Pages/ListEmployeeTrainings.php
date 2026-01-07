<?php

namespace App\Filament\Employee\Resources\EmployeeTrainingResource\Pages;

use App\Filament\Employee\Resources\EmployeeTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeTrainings extends ListRecords
{
    protected static string $resource = EmployeeTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
