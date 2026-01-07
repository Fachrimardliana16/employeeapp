<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Pages;

use App\Filament\Employee\Resources\EmployeeMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeMutation extends ViewRecord
{
    protected static string $resource = EmployeeMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit'),
        ];
    }
}
