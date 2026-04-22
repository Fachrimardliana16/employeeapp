<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use App\Models\EmployeeRetirement;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeGrowthChart extends ChartWidget
{
    protected static ?int $sort = 10;
    protected static bool $isLazy = true;
    
    protected static ?string $heading = 'Pertumbuhan Jumlah Pegawai';
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = [];
        $employeeCounts = [];

        $windowStart = Carbon::now()->subMonths(11)->startOfMonth();

        // Pre-fetch all monthly data in 2 aggregate queries instead of 24
        $hiredByMonth = Employee::selectRaw(
                config('database.default') === 'sqlite' 
                    ? "strftime('%Y-%m', entry_date) as month, COUNT(*) as total" 
                    : "DATE_FORMAT(entry_date, '%Y-%m') as month, COUNT(*) as total"
            )
            ->whereNotNull('entry_date')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $leftByMonth = EmployeeRetirement::selectRaw(
                config('database.default') === 'sqlite' 
                    ? "strftime('%Y-%m', retirement_date) as month, COUNT(*) as total" 
                    : "DATE_FORMAT(retirement_date, '%Y-%m') as month, COUNT(*) as total"
            )
            ->where('approval_status', 'approved')
            ->whereNotNull('retirement_date')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Calculate cumulative totals BEFORE our 12-month window (in PHP, no more queries)
        $cumulativeHired = 0;
        $cumulativeLeft = 0;

        foreach ($hiredByMonth as $m => $count) {
            if (Carbon::parse($m . '-01')->lt($windowStart)) {
                $cumulativeHired += $count;
            }
        }
        foreach ($leftByMonth as $m => $count) {
            if (Carbon::parse($m . '-01')->lt($windowStart)) {
                $cumulativeLeft += $count;
            }
        }

        // Build 12-month data using pre-fetched results
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $months[] = $date->format('M Y');

            $cumulativeHired += ($hiredByMonth[$key] ?? 0);
            $cumulativeLeft += ($leftByMonth[$key] ?? 0);
            $employeeCounts[] = max(0, $cumulativeHired - $cumulativeLeft);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pegawai',
                    'data' => $employeeCounts,
                    'fill' => 'start',
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
