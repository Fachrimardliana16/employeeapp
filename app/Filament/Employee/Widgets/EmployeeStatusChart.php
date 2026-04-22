<?php

namespace App\Filament\Employee\Widgets;

use App\Models\Employee;
use App\Models\MasterEmployeeStatusEmployment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EmployeeStatusChart extends ChartWidget
{
    protected static ?int $sort = 6;
    protected static bool $isLazy = true;
    
    protected static ?string $heading = 'Pegawai menurut Status Kepegawaian';
    
    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $data = Employee::query()
            ->join('master_employee_status_employments', 'employees.employment_status_id', '=', 'master_employee_status_employments.id')
            ->select('master_employee_status_employments.name', DB::raw('count(*) as total'))
            ->groupBy('master_employee_status_employments.name')
            ->pluck('total', 'name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Pegawai',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#36A2EB',
                        '#FF6384',
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
