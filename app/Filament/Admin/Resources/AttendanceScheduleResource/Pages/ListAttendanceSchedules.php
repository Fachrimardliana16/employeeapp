<?php

namespace App\Filament\Admin\Resources\AttendanceScheduleResource\Pages;

use App\Filament\Admin\Resources\AttendanceScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceSchedules extends ListRecords
{
    protected static string $resource = AttendanceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
