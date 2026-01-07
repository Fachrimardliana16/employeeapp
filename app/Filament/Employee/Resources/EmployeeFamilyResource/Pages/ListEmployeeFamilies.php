<?php

namespace App\Filament\Employee\Resources\EmployeeFamilyResource\Pages;

use App\Filament\Employee\Resources\EmployeeFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeFamilies extends ListRecords
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
