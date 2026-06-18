<?php

namespace App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource\Pages;

use App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeBusinessTravelLetter extends EditRecord
{
    protected static string $resource = EmployeeBusinessTravelLetterResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return EmployeeBusinessTravelLetterResource::recalculateFormData($data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
