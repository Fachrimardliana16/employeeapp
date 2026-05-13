<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Widgets;

use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use App\Models\AttendanceSpecialSchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AttendanceStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $cacheKey = 'attendance_stats_widget_' . $today->format('Y-m-d');

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($today) {
            $presentCount = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
                ->whereIn('state', ['check_in', 'in'])
                ->distinct('pin')
                ->count();

            $totalEmployees = Employee::count();

            $leaveCount = \App\Models\EmployeePermission::where('approval_status', 'approved')
                ->whereDate('start_permission_date', '<=', $today)
                ->whereDate('end_permission_date', '>=', $today)
                ->distinct('employee_id')
                ->count();

            $holidayCount = AttendanceSpecialSchedule::whereDate('date', $today)
                ->where('is_working', false)
                ->whereIn('type', ['libur_nasional', 'cuti_bersama'])
                ->distinct('employee_id')
                ->count();

            $absentCount = max(0, $totalEmployees - $presentCount - $leaveCount - $holidayCount);

            $lateCount = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
                ->where('attendance_status', 'late')
                ->count();

            $overtimeCount = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
                ->whereIn('state', ['ot_in', 'ot_out'])
                ->count();

            return compact('presentCount', 'leaveCount', 'holidayCount', 'absentCount', 'lateCount');
        });

        extract($data);

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
