<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class UserStatsWidget extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $data = Cache::remember('user_stats_widget', now()->addMinutes(10), function () {
            return [
                'total'      => User::count(),
                'verified'   => User::whereNotNull('email_verified_at')->count(),
                'unverified' => User::whereNull('email_verified_at')->count(),
            ];
        });

        return [
            Stat::make('Total Users', $data['total'])
                ->description('All users in the system')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Verified Users', $data['verified'])
                ->description('Users with verified email')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),
            Stat::make('Unverified Users', $data['unverified'])
                ->description('Users without verified email')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
