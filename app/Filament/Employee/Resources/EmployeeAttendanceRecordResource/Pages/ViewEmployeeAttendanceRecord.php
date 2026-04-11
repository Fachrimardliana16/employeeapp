<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeAttendanceRecord extends ViewRecord
{
    protected static string $resource = EmployeeAttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print')
                ->label('Cetak Bukti')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn ($record) => route('attendance.print', $record))
                ->openUrlInNewTab(),
        ];
    }
}
