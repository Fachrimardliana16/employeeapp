<?php

namespace App\Filament\Employee\Resources\AttendanceMachineLogResource\Pages;

use App\Filament\Employee\Resources\AttendanceMachineLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceMachineLogs extends ListRecords
{
    protected static string $resource = AttendanceMachineLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
