<?php

namespace App\Filament\Employee\Resources\EmployeeAppointmentResource\Widgets;

use App\Models\EmployeeAppointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AppointmentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pengangkatan', EmployeeAppointment::count())
                ->description('Pengangkatan Pegawai Tetap/kontrak')
                ->icon('heroicon-o-identification'),
            Stat::make('Pending (Usulan)', EmployeeAppointment::where('is_applied', false)->count())
                ->description('Menunggu realisasi')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('warning'),
            Stat::make('Selesai (Realisasi)', EmployeeAppointment::where('is_applied', true)->count())
                ->description('Sudah diperbarui ke profil')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
