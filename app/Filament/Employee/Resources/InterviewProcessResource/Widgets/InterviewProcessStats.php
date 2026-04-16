<?php

namespace App\Filament\Employee\Resources\InterviewProcessResource\Widgets;

use App\Models\InterviewProcess;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InterviewProcessStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Terjadwal', InterviewProcess::where('status', 'scheduled')->count())
                ->description('Interview yang akan datang')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Total Selesai', InterviewProcess::where('status', 'completed')->count())
                ->description('Interview yang sudah dilaksanakan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Lulus', InterviewProcess::where('result', 'passed')->count())
                ->description('Kandidat yang lolos tahap ini')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            Stat::make('Tidak Lulus', InterviewProcess::where('result', 'failed')->count())
                ->description('Kandidat yang tidak lolos')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),
        ];
    }
}
