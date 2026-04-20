<?php

namespace App\Filament\Employee\Resources\AttendanceSpecialScheduleResource\Pages;

use App\Filament\Employee\Resources\AttendanceSpecialScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceSpecialSchedule extends EditRecord
{
    protected static string $resource = AttendanceSpecialScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
