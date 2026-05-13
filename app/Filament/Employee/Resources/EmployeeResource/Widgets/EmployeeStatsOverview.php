<?php

namespace App\Filament\Employee\Resources\EmployeeResource\Widgets;

use App\Models\Employee;
use App\Models\MasterDepartment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class EmployeeStatsOverview extends BaseWidget
{
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $stats = Cache::remember('employee_stats_overview', now()->addMinutes(30), function () {
            $totalEmployees = Employee::count();
            $newEmployeesThisMonth = Employee::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            // Join daripada whereHas agar tidak ada correlated subquery
            $permanentEmployees = Employee::join('master_employee_status_employments as es', 'employees.employment_status_id', '=', 'es.id')
                ->where('es.name', 'like', '%tetap%')
                ->count();

            $contractEmployees = Employee::join('master_employee_status_employments as es', 'employees.employment_status_id', '=', 'es.id')
                ->where('es.name', 'like', '%kontrak%')
                ->count();

            $incompleteDataCount = Employee::where(function ($q) {
                $q->whereNull('id_number')
                  ->orWhereNull('familycard_number')
                  ->orWhereNull('bank_account_number')
                  ->orWhereNull('bpjs_kes_number')
                  ->orWhereNull('bpjs_tk_number')
                  ->orWhereNull('employee_education_id')
                  ->orWhereNull('probation_appointment_date');
            })->count();

            $completeDataPercentage = $totalEmployees > 0
                ? round((($totalEmployees - $incompleteDataCount) / $totalEmployees) * 100, 1)
                : 0;

            $topDepartment = Employee::select('departments_id')
                ->with('department')
                ->whereNotNull('departments_id')
                ->groupBy('departments_id')
                ->selectRaw('count(*) as employee_count')
                ->orderBy('employee_count', 'desc')
                ->first();

            return compact(
                'totalEmployees', 'newEmployeesThisMonth',
                'permanentEmployees', 'contractEmployees',
                'incompleteDataCount', 'completeDataPercentage',
                'topDepartment'
            );
        });

        extract($stats);

        return [
            Stat::make('Total Pegawai', $totalEmployees)
                ->description($newEmployeesThisMonth . ' Pegawai baru bulan ini')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Pegawai Tetap', $permanentEmployees)
                ->description($contractEmployees . ' Pegawai kontrak')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success')
                ->chart([3, 4, 3, 5, 6, 7, 8]),

            Stat::make('Kelengkapan Data', $completeDataPercentage . '%')
                ->description($incompleteDataCount . ' data tidak lengkap')
                ->descriptionIcon($completeDataPercentage >= 80 ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($completeDataPercentage >= 80 ? 'success' : ($completeDataPercentage >= 60 ? 'warning' : 'danger'))
                ->chart([60, 65, 70, 75, 80, 75, $completeDataPercentage]),

            Stat::make('Departemen Terbesar', $topDepartment?->department?->name ?? 'Belum ada data')
                ->description(($topDepartment?->employee_count ?? 0) . ' Pegawai')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info')
                ->chart([1, 3, 2, 4, 3, 5, 4]),
        ];
    }
}
