<?php

namespace App\Filament\User\Resources\EmployeeBusinessTravelLetterResource\Widgets;

use App\Models\EmployeeBusinessTravelLetter;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserBusinessTravelLetterStats extends BaseWidget
{
    protected function getStats(): array
    {
        $year = Carbon::now()->year;
        $employeeId = Auth::user()->employee?->id;

        if (!$employeeId) {
            return [];
        }

        $query = EmployeeBusinessTravelLetter::whereYear('start_date', $year)
            ->where(function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                  ->orWhereJsonContains('additional_employee_ids', (string)$employeeId)
                  ->orWhereJsonContains('additional_employee_ids', (int)$employeeId);
            })
            ->whereNotNull('signed_file_path');

        $total = (clone $query)->count();
        $selesai = (clone $query)->where('status', 'selesai')->count();
        $onProgress = (clone $query)->where('status', 'on progress')->count();
        $totalCost = (clone $query)->sum('total_cost');

        return [
            Stat::make('Perjalanan Saya ('.$year.')', $total)
                ->description('Total dinas tahun ini')
                ->color('info'),
            Stat::make('Perjalanan Selesai', $selesai)
                ->color('success'),
            Stat::make('Perjalanan On Progress', $onProgress)
                ->color('warning'),
            Stat::make('Total Biaya ('.$year.')', 'Rp ' . number_format($totalCost, 0, ',', '.'))
                ->description('Biaya dinas pribadi')
                ->color('primary'),
        ];
    }
}
