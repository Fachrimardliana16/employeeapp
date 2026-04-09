<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityLogLineChartWidget extends ChartWidget
{
    protected static ?string $heading = 'User Activity Log';
    
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'day';

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $data = [];
        $labels = [];

        if ($activeFilter === 'minute') {
            $start = now()->subMinutes(59);
            $activities = Activity::select(DB::raw("strftime('%H:%M', created_at) as time_label"), DB::raw('count(*) as aggregate'))
                ->where('created_at', '>=', $start)
                ->groupBy('time_label')
                ->pluck('aggregate', 'time_label');

            for ($i = 59; $i >= 0; $i--) {
                $label = now()->subMinutes($i)->format('H:i');
                $labels[] = $label;
                $data[] = $activities[$label] ?? 0;
            }
        } elseif ($activeFilter === 'hour') {
            $start = now()->subHours(23);
            $activities = Activity::select(DB::raw("strftime('%H:00', created_at) as time_label"), DB::raw('count(*) as aggregate'))
                ->where('created_at', '>=', $start)
                ->groupBy('time_label')
                ->pluck('aggregate', 'time_label');

            for ($i = 23; $i >= 0; $i--) {
                $label = now()->subHours($i)->format('H:00');
                $labels[] = $label;
                $data[] = $activities[$label] ?? 0;
            }
        } else { // default: day
            $start = now()->subDays(29);
            $activities = Activity::select(DB::raw("date(created_at) as time_label"), DB::raw('count(*) as aggregate'))
                ->where('created_at', '>=', $start)
                ->groupBy('time_label')
                ->pluck('aggregate', 'time_label');

            for ($i = 29; $i >= 0; $i--) {
                $label = now()->subDays($i)->format('Y-m-d');
                $labels[] = Carbon::parse($label)->format('d M');
                $data[] = $activities[$label] ?? 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Activity Log Count',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'minute' => 'Last 60 Minutes',
            'hour' => 'Last 24 Hours',
            'day' => 'Last 30 Days',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
