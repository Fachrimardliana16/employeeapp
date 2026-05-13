<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TodayAttendanceChart extends ChartWidget
{
    protected static ?int $sort = 9;
    protected static bool $isLazy = true;
    
    protected static ?string $heading = 'Kehadiran Pegawai Hari Ini';
    
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $today = Carbon::today();
        $data = Cache::remember('today_attendance_chart_' . $today->format('Y-m-d'), now()->addMinutes(5), function () use ($today) {
            $totalEmployees = Employee::count();
            $present = EmployeeAttendanceRecord::whereDate('attendance_time', $today)
                ->where('state', 'check_in')
                ->distinct('pin')
                ->count();
            $absent = max(0, $totalEmployees - $present);
            return compact('present', 'absent');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Kehadiran',
                    'data' => [$data['present'], $data['absent']],
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
