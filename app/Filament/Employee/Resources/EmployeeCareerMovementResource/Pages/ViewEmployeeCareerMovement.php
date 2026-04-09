<?php

namespace App\Filament\Employee\Resources\EmployeeCareerMovementResource\Pages;

use App\Filament\Employee\Resources\EmployeeCareerMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeCareerMovement extends ViewRecord
{
    protected static string $resource = EmployeeCareerMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
