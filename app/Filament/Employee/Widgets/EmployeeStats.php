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

class EmployeeStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // Total lamaran bulan ini
        $newApplications = JobApplication::where('created_at', '>=', $thisMonth)->count();

        // Interview scheduled bulan ini
        $scheduledInterviews = InterviewProcess::whereMonth('interview_date', Carbon::now()->month)
            ->whereYear('interview_date', Carbon::now()->year)
            ->count();

        // Total pegawai aktif (yang memiliki agreement aktif)
        $activeEmployees = Employee::whereNotNull('master_employee_agreement_id')
            ->where('agreement_date_start', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('agreement_date_end')
                    ->orWhere('agreement_date_end', '>=', $today);
            })
            ->count();

        // Kontrak yang akan habis dalam 30 hari
        $expiringContracts = EmployeeAgreement::whereBetween('agreement_date_end', [
            $today,
            $today->copy()->addDays(30)
        ])->count();

        // Kehadiran hari ini (check-in)
        $todayAttendance = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
            ->where('state', 'check_in')
            ->count();

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
