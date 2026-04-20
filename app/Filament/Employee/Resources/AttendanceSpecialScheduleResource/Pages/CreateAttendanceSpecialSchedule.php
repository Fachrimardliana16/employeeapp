<?php

namespace App\Filament\Employee\Resources\AttendanceSpecialScheduleResource\Pages;

use App\Filament\Employee\Resources\AttendanceSpecialScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceSpecialSchedule extends CreateRecord
{
    protected static string $resource = AttendanceSpecialScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['users_id'] = auth()->id();
        return $data;
    }
}
