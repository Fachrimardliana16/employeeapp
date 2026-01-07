<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeAttendanceRecord extends EditRecord
{
    protected static string $resource = EmployeeAttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
