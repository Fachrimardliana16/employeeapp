<?php

namespace App\Filament\Employee\Resources\EmployeeCareerMovementResource\Widgets;

use App\Models\EmployeeCareerMovement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CareerMovementStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Promosi & Demosi', EmployeeCareerMovement::count())
                ->description('Perubahan karir pegawai')
                ->icon('heroicon-o-arrows-up-down'),
            Stat::make('Pending (Usulan)', EmployeeCareerMovement::where('is_applied', false)->count())
                ->description('Menunggu realisasi')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('warning'),
            Stat::make('Selesai (Realisasi)', EmployeeCareerMovement::where('is_applied', true)->count())
                ->description('Sudah diperbarui ke profil')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
