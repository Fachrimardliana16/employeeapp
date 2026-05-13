<?php

namespace App\Filament\Admin\Widgets;

use Spatie\Activitylog\Models\Activity;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ActivityLogStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $stats = Cache::remember('activity_log_stats_widget', now()->addMinutes(5), function () {
            return [
                'total' => Activity::count(),
                'today' => Activity::whereDate('created_at', today())->count(),
                'week' => Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ];
        });

        return [
            Stat::make('Total Activities', $stats['total'])
                ->description('All activity logs')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),
            Stat::make('Today Activities', $stats['today'])
                ->description('Activities logged today')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            Stat::make('This Week Activities', $stats['week'])
                ->description('Activities this week')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
