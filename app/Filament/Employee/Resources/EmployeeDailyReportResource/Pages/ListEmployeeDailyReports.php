<?php

namespace App\Filament\Employee\Resources\EmployeeDailyReportResource\Pages;

use App\Filament\Employee\Resources\EmployeeDailyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeDailyReports extends ListRecords
{
    protected static string $resource = EmployeeDailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('report')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->modalHeading('Filter Laporan Harian')
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
                ])
                ->action(function (array $data, \Filament\Resources\Pages\ListRecords $livewire) {
                    $url = route('daily-reports.report', $data);
                    $livewire->js("window.open('{$url}', '_blank');");
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeDailyReportResource\Widgets\DailyReportStatsWidget::class,
        ];
    }
}
