<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Widgets;

use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AttendanceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        // Count unique employees who checked in today
        $presentCount = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
            ->whereIn('state', ['check_in', 'in'])
            ->distinct('pin')
            ->count();
            
        // Assuming total active employees for base
        $totalEmployees = Employee::count();
        $absentCount = max(0, $totalEmployees - $presentCount);
        
        // Count late arrivals
        $lateCount = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
            ->where('attendance_status', 'late')
            ->count();
            
        // Count overtime records
        $overtimeCount = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
            ->whereIn('state', ['ot_in', 'ot_out'])
            ->count();

        return [
            Stat::make('Pegawai Hadir', $presentCount)
                ->description('Total masuk hari ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->color('success'),
            Stat::make('Belum Absen', $absentCount)
                ->description('Estimasi tidak hadir')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),
            Stat::make('Terlambat', $lateCount)
                ->description('Melewati jam masuk')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Absensi Lembur', $overtimeCount)
                ->description('Tercatat sebagai OT')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('info'),
        ];
    }
}
