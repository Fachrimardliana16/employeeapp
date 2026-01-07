<?php

namespace App\Filament\Admin\Widgets;

use Spatie\Activitylog\Models\Activity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityLogStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Activities', Activity::count())
                ->description('All activity logs')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),
            Stat::make('Today Activities', Activity::whereDate('created_at', today())->count())
                ->description('Activities logged today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('This Week Activities', Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())
                ->description('Activities this week')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
