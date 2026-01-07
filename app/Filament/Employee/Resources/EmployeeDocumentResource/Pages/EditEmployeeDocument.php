<?php

namespace App\Filament\Employee\Resources\EmployeeDocumentResource\Pages;

use App\Filament\Employee\Resources\EmployeeDocumentResource;
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
