<?php

namespace App\Filament\Employee\Resources\AttendanceMachineResource\Pages;

use App\Filament\Employee\Resources\AttendanceMachineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceMachines extends ListRecords
{
    protected static string $resource = AttendanceMachineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
