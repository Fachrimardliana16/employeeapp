<?php

namespace App\Filament\User\Resources\EmployeeFamilyResource\Pages;

use App\Filament\User\Resources\EmployeeFamilyResource;
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
