<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Pages;

use App\Filament\Employee\Resources\EmployeeMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeMutation extends EditRecord
{
    protected static string $resource = EmployeeMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
