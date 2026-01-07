<?php

namespace App\Filament\User\Resources\EmployeeDocumentResource\Pages;

use App\Filament\User\Resources\EmployeeDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeDocuments extends ListRecords
{
    protected static string $resource = EmployeeDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Upload Dokumen')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }
}
