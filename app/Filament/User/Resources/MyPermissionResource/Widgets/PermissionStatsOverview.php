<?php

namespace App\Filament\User\Resources\MyPermissionResource\Widgets;

use App\Models\EmployeePermission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PermissionStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return [];
        }

        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $monthlyCount = EmployeePermission::where('employee_id', $employee->id)
            ->whereMonth('start_permission_date', $thisMonth)
            ->whereYear('start_permission_date', $thisYear)
            ->count();

        $approvedCount = EmployeePermission::where('employee_id', $employee->id)
            ->where('approval_status', 'approved')
            ->whereYear('start_permission_date', $thisYear)
            ->count();

        $rejectedCount = EmployeePermission::where('employee_id', $employee->id)
            ->where('approval_status', 'rejected')
            ->whereYear('start_permission_date', $thisYear)
            ->count();

        return [
            Stat::make('Cuti Bulan Ini', $monthlyCount . ' Kali')
                ->description('Total pengajuan bulan ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Cuti Disetujui', $approvedCount . ' Kali')
                ->description('Total disetujui tahun ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Cuti Tidak Disetujui', $rejectedCount . ' Kali')
                ->description('Total ditolak tahun ini')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
