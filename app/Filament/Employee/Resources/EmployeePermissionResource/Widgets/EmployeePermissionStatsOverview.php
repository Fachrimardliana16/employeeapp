<?php

namespace App\Filament\Employee\Resources\EmployeePermissionResource\Widgets;

use App\Models\EmployeePermission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class EmployeePermissionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $monthlyCount = EmployeePermission::whereMonth('start_permission_date', $thisMonth)
            ->whereYear('start_permission_date', $thisYear)
            ->count();

        $approvedCount = EmployeePermission::where('approval_status', 'approved')
            ->whereYear('start_permission_date', $thisYear)
            ->count();

        $rejectedCount = EmployeePermission::where('approval_status', 'rejected')
            ->whereYear('start_permission_date', $thisYear)
            ->count();

        return [
            Stat::make('Pengajuan Bulan Ini', $monthlyCount . ' Data')
                ->description('Total semua pengajuan bulan ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Total Disetujui', $approvedCount . ' Data')
                ->description('Total disetujui tahun ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Ditolak', $rejectedCount . ' Data')
                ->description('Total ditolak tahun ini')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
