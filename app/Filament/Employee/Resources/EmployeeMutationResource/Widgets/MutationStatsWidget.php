<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Widgets;

use App\Models\EmployeeMutation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MutationStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Mutasi', EmployeeMutation::count())
                ->description('Perpindahan bagian/jabatan')
                ->icon('heroicon-o-arrows-right-left'),
            Stat::make('Pending (Usulan)', EmployeeMutation::where('is_applied', false)->count())
                ->description('Menunggu realisasi')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('warning'),
            Stat::make('Selesai (Realisasi)', EmployeeMutation::where('is_applied', true)->count())
                ->description('Sudah diperbarui ke profil')
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
    }
}
