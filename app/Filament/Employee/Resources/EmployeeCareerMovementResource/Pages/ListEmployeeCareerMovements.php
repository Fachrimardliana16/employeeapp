<?php

namespace App\Filament\Employee\Resources\EmployeeCareerMovementResource\Pages;

use App\Filament\Employee\Resources\EmployeeCareerMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeCareerMovements extends ListRecords
{
    protected static string $resource = EmployeeCareerMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
