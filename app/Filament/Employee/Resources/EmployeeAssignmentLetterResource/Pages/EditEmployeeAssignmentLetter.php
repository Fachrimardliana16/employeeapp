<?php

namespace App\Filament\Employee\Resources\EmployeeAssignmentLetterResource\Pages;

use App\Filament\Employee\Resources\EmployeeAssignmentLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeAssignmentLetter extends EditRecord
{
    protected static string $resource = EmployeeAssignmentLetterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
