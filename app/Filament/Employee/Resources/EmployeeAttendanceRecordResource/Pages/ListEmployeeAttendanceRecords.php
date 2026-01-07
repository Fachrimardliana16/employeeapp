<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAttendanceRecords extends ListRecords
{
    protected static string $resource = EmployeeAttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
