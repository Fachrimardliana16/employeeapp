<?php

namespace App\Filament\Employee\Resources\EmployeeDailyReportResource\Widgets;

use App\Models\EmployeeDailyReport;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class DailyReportStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $totalToday = EmployeeDailyReport::whereDate('daily_report_date', $today)->count();
        $completedCount = EmployeeDailyReport::whereDate('daily_report_date', $today)->where('work_status', 'Selesai')->count();
        $progressCount = EmployeeDailyReport::whereDate('daily_report_date', $today)->where('work_status', 'Proses')->count();
        $pendingCount = EmployeeDailyReport::whereDate('daily_report_date', $today)->where('work_status', 'Tertunda')->count();

        return [
            Stat::make('Total Laporan', $totalToday)
                ->description('Total hari ini')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Selesai', $completedCount)
                ->description('Pekerjaan tuntas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Proses', $progressCount)
                ->description('Sedang dikerjakan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Tertunda', $pendingCount)
                ->description('Belum dikerjakan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
