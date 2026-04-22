<?php

namespace App\Filament\Employee\Widgets;

use App\Models\EmployeeAttendanceRecord;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyAttendanceTrendChart extends ChartWidget
{
    protected static ?int $sort = 11;
    protected static bool $isLazy = true;
    
    protected static ?string $heading = 'Tren Kehadiran Harian';
    
    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'this_week' => 'Minggu Ini',
            'this_month' => 'Bulan Ini',
            'last_month' => 'Bulan Lalu',
            'this_year' => 'Tahun Ini',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        
        if ($activeFilter === 'this_week') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
        } elseif ($activeFilter === 'last_month') {
            $start = now()->subMonth()->startOfMonth();
            $end = $start->copy()->endOfMonth();
        } elseif ($activeFilter === 'this_year') {
            $start = now()->startOfYear();
            $end = now()->endOfYear();
        }

        $query = EmployeeAttendanceRecord::query()
            ->whereBetween('attendance_time', [$start, $end])
            ->where('state', 'check_in')
            ->select(
                DB::raw(config('database.default') === 'sqlite' 
                    ? 'strftime("%Y-%m-%d", attendance_time) as date' 
                    : 'DATE(attendance_time) as date'), 
                DB::raw('count(distinct pin) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Fill in missing dates with 0
        $labels = [];
        $data = [];
        $current = $start->copy();
        
        while ($current <= $end && $current <= now()) {
            $dateString = $current->toDateString();
            $labels[] = $current->format('d M');
            $data[] = $query[$dateString] ?? 0;
            $current->addDay();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pegawai Hadir',
                    'data' => $data,
                    'borderColor' => '#4BC0C0',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'fill' => true,
                    'tension' => 0.1,
                ],
            ],
            'labels' => $labels,
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
