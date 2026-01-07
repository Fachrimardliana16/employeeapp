<?php

namespace App\Filament\Employee\Resources\EmployeeRetirementResource\Pages;

use App\Filament\Employee\Resources\EmployeeRetirementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeRetirement extends EditRecord
{
    protected static string $resource = EmployeeRetirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
