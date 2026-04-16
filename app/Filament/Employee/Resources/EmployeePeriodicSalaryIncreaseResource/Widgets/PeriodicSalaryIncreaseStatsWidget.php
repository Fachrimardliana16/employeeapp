<?php

namespace App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Widgets;

use App\Models\EmployeePeriodicSalaryIncrease;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PeriodicSalaryIncreaseStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total KGB', EmployeePeriodicSalaryIncrease::count())
                ->description('Kenaikan Gaji Berkala')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('Pending (Usulan)', EmployeePeriodicSalaryIncrease::where('is_applied', false)->count())
                ->description('Menunggu realisasi')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('warning'),
            Stat::make('Selesai (Realisasi)', EmployeePeriodicSalaryIncrease::where('is_applied', true)->count())
                ->description('Sudah diperbarui ke profil')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
