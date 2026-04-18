<?php

namespace App\Filament\Employee\Resources\AttendanceMachineResource\Pages;

use App\Filament\Employee\Resources\AttendanceMachineResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceMachine extends CreateRecord
{
    protected static string $resource = AttendanceMachineResource::class;
}
