<?php

namespace App\Filament\Employee\Resources\EmployeeAssignmentLetterResource\Widgets;

use App\Models\EmployeeAssignmentLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AssignmentLetterStats extends BaseWidget
{
    protected function getStats(): array
    {
        $year = Carbon::now()->year;

        $total = EmployeeAssignmentLetter::whereYear('start_date', $year)->count();
        $selesai = EmployeeAssignmentLetter::whereYear('start_date', $year)->where('status', 'selesai')->count();
        $onProgress = EmployeeAssignmentLetter::whereYear('start_date', $year)->where('status', 'on progress')->count();

        return [
            Stat::make('Total Surat Tugas ('.$year.')', $total)
                ->description('Seluruh tugas tahun ini')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
            Stat::make('Surat Tugas Selesai', $selesai)
                ->description('Telah diupload cap kunjungan')
                ->chart([3, 5, 2, 8, 4, 10, 12])
                ->color('success'),
            Stat::make('Surat Tugas On Progress', $onProgress)
                ->description('Belum diselesaikan')
                ->chart([15, 4, 10, 2, 12, 4, 5])
                ->color('warning'),
        ];
    }
}
