<?php

namespace App\Filament\Employee\Resources\EmployeeAppointmentResource\Pages;

use App\Filament\Employee\Resources\EmployeeAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeAppointment extends ViewRecord
{
    protected static string $resource = EmployeeAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Edit'),
        ];
    }
}
