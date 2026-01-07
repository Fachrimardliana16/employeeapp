<?php

namespace App\Filament\User\Resources\EmployeeRetirementResource\Pages;

use App\Filament\User\Resources\EmployeeRetirementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeRetirements extends ListRecords
{
    protected static string $resource = EmployeeRetirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
