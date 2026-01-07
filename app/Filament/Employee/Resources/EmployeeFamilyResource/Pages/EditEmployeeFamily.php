<?php

namespace App\Filament\Employee\Resources\EmployeeFamilyResource\Pages;

use App\Filament\Employee\Resources\EmployeeFamilyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeFamily extends EditRecord
{
    protected static string $resource = EmployeeFamilyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
