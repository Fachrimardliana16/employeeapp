<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Widgets;

use App\Models\JobApplication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class JobApplicationStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Lamaran', JobApplication::count())
                ->description('Total lamaran yang masuk')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Lamaran Baru', JobApplication::where('status', 'submitted')->count())
                ->description('Menunggu review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Interview Terjadwal', JobApplication::where('status', 'interview_scheduled')->count())
                ->description('Akan diinterview')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Diterima Bulan Ini', JobApplication::where('status', 'accepted')
                ->whereMonth('decision_at', now()->month)
                ->whereYear('decision_at', now()->year)
                ->count())
                ->description('Pelamar diterima')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
