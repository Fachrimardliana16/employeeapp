<?php

namespace App\Filament\Employee\Resources\EmployeeBusinessTravelLetterResource\Widgets;

use App\Models\EmployeeBusinessTravelLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class BusinessTravelLetterStats extends BaseWidget
{
    protected function getStats(): array
    {
        $year = Carbon::now()->year;

        $total = EmployeeBusinessTravelLetter::whereYear('start_date', $year)->count();
        $selesai = EmployeeBusinessTravelLetter::whereYear('start_date', $year)->where('status', 'selesai')->count();
        $onProgress = EmployeeBusinessTravelLetter::whereYear('start_date', $year)->where('status', 'on progress')->count();
        $totalCost = EmployeeBusinessTravelLetter::whereYear('start_date', $year)->sum('total_cost');

        return [
            Stat::make('Total SPPD ('.$year.')', $total)
                ->description('Seluruh perjalanan dinas')
                ->color('info'),
            Stat::make('SPPD Selesai', $selesai)
                ->description('Telah selesai penugasan')
                ->color('success'),
            Stat::make('SPPD On Progress', $onProgress)
                ->description('Sedang dalam proses')
                ->color('warning'),
            Stat::make('Total Biaya ('.$year.')', 'Rp ' . number_format($totalCost, 0, ',', '.'))
                ->description('Akumulasi biaya perjalanan')
                ->color('primary')
                ->chart([2, 4, 6, 8, 10, 12, 14, 16])
                ->extraAttributes(['class' => 'font-bold']),
        ];
    }
}
