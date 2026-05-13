<?php

namespace App\Filament\Employee\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\JobApplication;
use App\Models\InterviewProcess;
use App\Models\Employee;
use App\Models\EmployeeAgreement;
use App\Models\EmployeeAttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EmployeeStats extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $cacheKey = 'employee_dashboard_stats_' . $today->format('Y-m-d');

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($today) {
            $thisMonth = Carbon::now()->startOfMonth();

            $newApplications = JobApplication::where('created_at', '>=', $thisMonth)->count();

            $scheduledInterviews = InterviewProcess::whereMonth('interview_date', Carbon::now()->month)
                ->whereYear('interview_date', Carbon::now()->year)
                ->count();

            $activeEmployees = Employee::whereNotNull('master_employee_agreement_id')
                ->where('agreement_date_start', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('agreement_date_end')
                        ->orWhere('agreement_date_end', '>=', $today);
                })
                ->count();

            $expiringContracts = EmployeeAgreement::whereBetween('agreement_date_end', [
                $today,
                $today->copy()->addDays(30)
            ])->count();

            $todayAttendance = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
                ->where('state', 'check_in')
                ->count();

            return compact('newApplications', 'scheduledInterviews', 'activeEmployees', 'expiringContracts', 'todayAttendance');
        });

        extract($data);

        return [
            Stat::make('Lamaran Bulan Ini', $newApplications)
                ->description('Total lamaran yang masuk')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->chart([7, 12, 8, 15, $newApplications]),

            Stat::make('Interview Terjadwal', $scheduledInterviews)
                ->description('Bulan ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),

            Stat::make('Pegawai Aktif', $activeEmployees)
                ->description('Total pegawai dengan kontrak aktif')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Kontrak Akan Habis', $expiringContracts)
                ->description('Dalam 30 hari ke depan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($expiringContracts > 0 ? 'danger' : 'success'),

            Stat::make('Kehadiran Hari Ini', $todayAttendance)
                ->description('Pegawai yang sudah check-in')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
