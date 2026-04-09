<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class TodayAttendanceChart extends ChartWidget
{
    protected static ?int $sort = 9;
    
    protected static ?string $heading = 'Kehadiran Pegawai Hari Ini';
    
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $today = Carbon::today();
        
        $totalEmployees = Employee::count();
        $present = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
            ->where('state', 'check_in')
            ->distinct('pin')
            ->count();
            
        $absent = max(0, $totalEmployees - $present);

        return [
            'datasets' => [
                [
                    'label' => 'Kehadiran',
                    'data' => [$present, $absent],
                    'backgroundColor' => [
                        '#4BC0C0', // Green for Present
                        '#FF6384', // Red for Absent
                    ],
                ],
            ],
            'labels' => ['Hadir', 'Tidak Hadir'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
