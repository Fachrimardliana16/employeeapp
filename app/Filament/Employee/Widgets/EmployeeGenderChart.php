<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EmployeeGenderChart extends ChartWidget
{
    protected static ?int $sort = 8;
    protected static bool $isLazy = true;
    
    protected static ?string $heading = 'Pegawai menurut Jenis Kelamin';
    
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        return Cache::remember('employee_gender_chart', now()->addHour(), function () {
            $data = Employee::query()
                ->select('gender', DB::raw('count(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender')
                ->toArray();

            $formattedData = [];
            foreach ($data as $gender => $total) {
                $label = match (strtolower($gender)) {
                    'male', 'l' => 'Laki-laki',
                    'female', 'p' => 'Perempuan',
                    default => $gender,
                };
                $formattedData[$label] = $total;
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Total Pegawai',
                        'data' => array_values($formattedData),
                        'backgroundColor' => [
                            '#36A2EB',
                            '#FF6384',
                        ],
                    ],
                ],
                'labels' => array_keys($formattedData),
            ];
        });
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
