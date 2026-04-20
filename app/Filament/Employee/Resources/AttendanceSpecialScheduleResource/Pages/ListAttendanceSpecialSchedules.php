<?php

namespace App\Filament\Employee\Resources\AttendanceSpecialScheduleResource\Pages;

use App\Filament\Employee\Resources\AttendanceSpecialScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceSpecialSchedules extends ListRecords
{
    protected static string $resource = AttendanceSpecialScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
