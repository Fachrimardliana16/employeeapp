<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeeGenderChart extends ChartWidget
{
    protected static ?int $sort = 8;
    protected static bool $isLazy = true;
    
    protected static ?string $heading = 'Pegawai menurut Jenis Kelamin';
    
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = Employee::query()
            ->select('gender', DB::raw('count(*) as total'))
            ->groupBy('gender')
            ->pluck('total', 'gender')
            ->toArray();

        // Map gender codes to labels if needed
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
                        '#36A2EB', // Blue for Male
                        '#FF6384', // Pink for Female
                    ],
                ],
            ],
            'labels' => array_keys($formattedData),
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
