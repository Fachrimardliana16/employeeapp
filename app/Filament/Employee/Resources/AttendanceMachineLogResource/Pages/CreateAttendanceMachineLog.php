<?php

namespace App\Filament\Employee\Resources\AttendanceMachineLogResource\Pages;

use App\Filament\Employee\Resources\AttendanceMachineLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceMachineLog extends CreateRecord
{
    protected static string $resource = AttendanceMachineLogResource::class;
}
