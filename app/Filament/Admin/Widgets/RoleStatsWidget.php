<?php

namespace App\Filament\Admin\Widgets;

use Spatie\Permission\Models\Role;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoleStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Roles', Role::count())
                ->description('All roles in the system')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),
            Stat::make('Total Permissions', \Spatie\Permission\Models\Permission::count())
                ->description('All permissions available')
                ->descriptionIcon('heroicon-m-key')
                ->color('info'),
            Stat::make('Roles with Users', Role::has('users')->count())
                ->description('Roles assigned to users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
