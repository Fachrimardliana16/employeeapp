<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeeEducationChart extends ChartWidget
{
    protected static ?int $sort = 7;
    
    protected static ?string $heading = 'Pegawai menurut Tingkat Pendidikan';
    
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = Employee::query()
            ->join('master_employee_education', 'employees.employee_education_id', '=', 'master_employee_education.id')
            ->select('master_employee_education.name', DB::raw('count(*) as total'))
            ->groupBy('master_employee_education.name')
            ->pluck('total', 'name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pegawai',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40',
                    ],
                ],
            ],
            'labels' => array_keys($data),
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
