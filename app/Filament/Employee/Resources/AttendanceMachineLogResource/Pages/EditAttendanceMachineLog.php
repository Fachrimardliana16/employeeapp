<?php

namespace App\Filament\Employee\Resources\AttendanceMachineLogResource\Pages;

use App\Filament\Employee\Resources\AttendanceMachineLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceMachineLog extends EditRecord
{
    protected static string $resource = AttendanceMachineLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
