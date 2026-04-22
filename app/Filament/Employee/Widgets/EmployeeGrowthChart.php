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
        
        // Get data for the last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months[] = $monthName;
            
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Count total employees hired before or on this month
            $totalHired = Employee::where('entry_date', '<=', $endOfMonth)->count();
            
            // Count total employees retired/resigned before or on this month
            // Assuming approval_status is 'approved' for completed retirements
            $totalLeft = EmployeeRetirement::where('retirement_date', '<=', $endOfMonth)
                ->where('approval_status', 'approved')
                ->count();
                
            $currentCount = max(0, $totalHired - $totalLeft);
            $employeeCounts[] = $currentCount;
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
