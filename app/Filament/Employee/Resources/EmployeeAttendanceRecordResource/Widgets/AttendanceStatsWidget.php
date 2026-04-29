<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Widgets;

use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use App\Models\AttendanceSpecialSchedule;
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

        // Count approved permissions (leaves) for today
        $leaveCount = \App\Models\EmployeePermission::where('approval_status', 'approved')
            ->whereDate('start_permission_date', '<=', $today)
            ->whereDate('end_permission_date', '>=', $today)
            ->distinct('employee_id')
            ->count();

        // Count employees on national holiday or joint leave (not required to work)
        $holidayCount = AttendanceSpecialSchedule::whereDate('date', $today)
            ->where('is_working', false) // Tidak wajib masuk
            ->whereIn('type', ['libur_nasional', 'cuti_bersama'])
            ->distinct('employee_id')
            ->count();

        // Absent = Total - (Present + Leave + Holiday)
        $absentCount = max(0, $totalEmployees - $presentCount - $leaveCount - $holidayCount);

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
            Stat::make('Izin & Cuti', $leaveCount)
                ->description('Izin resmi hari ini')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
            Stat::make('Libur Nasional/Cuti Bersama', $holidayCount)
                ->description('Tidak wajib masuk')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),
            Stat::make('Belum Absen', $absentCount)
                ->description('Tanpa keterangan')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),
            Stat::make('Terlambat', $lateCount)
                ->description('Melewati jam masuk')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
