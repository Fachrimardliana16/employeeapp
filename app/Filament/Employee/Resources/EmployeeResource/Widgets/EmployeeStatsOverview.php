<?php

namespace App\Filament\Employee\Resources\EmployeeResource\Widgets;

use App\Models\Employee;
use App\Models\MasterDepartment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Get employee counts by different criteria
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::whereNotNull('entry_date')->count();
        $newEmployeesThisMonth = Employee::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Get employees by status
        $permanentEmployees = Employee::whereHas('employmentStatus', function ($query) {
            $query->where('name', 'like', '%tetap%');
        })->count();

        $contractEmployees = Employee::whereHas('employmentStatus', function ($query) {
            $query->where('name', 'like', '%kontrak%');
        })->count();

        // Get data completeness statistics
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

        // Get department with most employees
        $topDepartment = Employee::select('departments_id')
            ->with('department')
            ->whereNotNull('departments_id')
            ->groupBy('departments_id')
            ->selectRaw('count(*) as employee_count')
            ->orderBy('employee_count', 'desc')
            ->first();

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
