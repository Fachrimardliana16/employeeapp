<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeAttendanceRecord extends CreateRecord
{
    protected static string $resource = EmployeeAttendanceRecordResource::class;
}
