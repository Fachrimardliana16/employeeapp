<?php

namespace App\Filament\Employee\Resources\EmployeeAppointmentResource\Pages;

use App\Filament\Employee\Resources\EmployeeAppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAppointments extends ListRecords
{
    protected static string $resource = EmployeeAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Pengangkatan Baru')
                ->icon('heroicon-m-plus'),
        ];
    }
}
