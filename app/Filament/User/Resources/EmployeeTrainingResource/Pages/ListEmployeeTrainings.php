<?php

namespace App\Filament\User\Resources\EmployeeTrainingResource\Pages;

use App\Filament\User\Resources\EmployeeTrainingResource;
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
