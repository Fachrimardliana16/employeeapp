<?php

namespace App\Filament\User\Resources\EmployeeDocumentResource\Pages;

use App\Filament\User\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeDocument extends EditRecord
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
