<?php

namespace App\Filament\User\Resources\EmployeeTrainingResource\Pages;

use App\Filament\User\Resources\EmployeeTrainingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeTraining extends EditRecord
{
    protected static string $resource = EmployeeTrainingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
