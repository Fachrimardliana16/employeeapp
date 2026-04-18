<?php

namespace App\Filament\Employee\Resources\AttendanceMachineResource\Pages;

use App\Filament\Employee\Resources\AttendanceMachineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceMachine extends EditRecord
{
    protected static string $resource = AttendanceMachineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
