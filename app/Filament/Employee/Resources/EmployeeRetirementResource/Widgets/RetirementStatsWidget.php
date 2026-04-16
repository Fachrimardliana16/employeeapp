<?php

namespace App\Filament\Employee\Resources\EmployeeRetirementResource\Widgets;

use App\Models\EmployeeRetirement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RetirementStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Note: Retirement typically doesn't use is_applied in current schema, 
        // but I'll add total and recent counts.
        return [
            Stat::make('Total Pensiun', EmployeeRetirement::count())
                ->description('Data historis pensiun')
                ->icon('heroicon-o-home'),
            Stat::make('Tahun Ini', EmployeeRetirement::whereYear('retirement_date', now()->year)->count())
                ->description('Pensiun tahun berjalan')
                ->icon('heroicon-o-calendar')
                ->color('info'),
            Stat::make('Bulan Ini', EmployeeRetirement::whereMonth('retirement_date', now()->month)->whereYear('retirement_date', now()->year)->count())
                ->description('Pensiun bulan berjalan')
                ->icon('heroicon-o-clock')
                ->color('success'),
        ];
    }
}
