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
            Actions\Action::make('report')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->modalHeading('Filter Laporan Kehadiran')
                ->modalSubmitActionLabel('Cetak')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from_date')
                        ->label('Dari Tanggal')
                        ->default(now()->startOfMonth())
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('to_date')
                        ->label('Sampai Tanggal')
                        ->default(now())
                        ->required(),
                    \Filament\Forms\Components\Select::make('employee_id')
                        ->label('Pegawai (Opsional)')
                        ->options(\App\Models\Employee::pluck('name', 'id'))
                        ->searchable(),
                    \Filament\Forms\Components\Select::make('office_location_id')
                        ->label('Lokasi (Opsional)')
                        ->options(\App\Models\MasterOfficeLocation::pluck('name', 'id'))
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $url = route('attendance.report', $data);
                    return redirect()->away($url);
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAttendanceRecordResource\Widgets\AttendanceStatsWidget::class,
            EmployeeAttendanceRecordResource\Widgets\ActivePermissionsWidget::class,
        ];
    }
}
