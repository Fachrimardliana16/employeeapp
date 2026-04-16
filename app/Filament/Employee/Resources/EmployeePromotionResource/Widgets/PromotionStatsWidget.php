<?php

namespace App\Filament\Employee\Resources\EmployeePromotionResource\Widgets;

use App\Models\EmployeePromotion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromotionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Kenaikan Golongan', EmployeePromotion::count())
                ->description('Semua data historis')
                ->icon('heroicon-o-arrow-trending-up'),
            Stat::make('Pending (Usulan)', EmployeePromotion::where('is_applied', false)->count())
                ->description('Menunggu realisasi')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('warning'),
            Stat::make('Selesai (Realisasi)', EmployeePromotion::where('is_applied', true)->count())
                ->description('Sudah diperbarui ke profil')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
