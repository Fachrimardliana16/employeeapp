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
                ->description('Total roles defined')
                ->color('success'),
            Stat::make('Admin Roles', Role::where('name', 'like', '%admin%')->count())
                ->description('Roles with admin access')
                ->color('warning'),
        ];
    }
}
