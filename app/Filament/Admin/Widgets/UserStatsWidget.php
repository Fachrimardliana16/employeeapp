<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All users in the system')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Verified Users', User::whereNotNull('email_verified_at')->count())
                ->description('Users with verified email')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info'),
            Stat::make('Unverified Users', User::whereNull('email_verified_at')->count())
                ->description('Users without verified email')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),
        ];
    }
}
