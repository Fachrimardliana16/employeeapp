<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeePayroll;
use App\Models\EmployeePayrollDetail;
use App\Models\EmployeeSalaryCut;
use App\Models\PayrollComponent;
use App\Models\PayrollFormula;
use App\Models\EmployeeAttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Calculate payroll for an employee for a given period
     */
    public function calculatePayroll(Employee $employee, Carbon $period, int $userId): EmployeePayroll
    {
        $startOfMonth = $period->copy()->startOfMonth();
        $endOfMonth = $period->copy()->endOfMonth();

        // Get applicable formula for this employee
        $formula = $this->getApplicableFormula($employee);

        // Calculate attendance data
        $attendanceData = $this->calculateAttendanceData($employee, $startOfMonth, $endOfMonth);

        // Calculate base salary based on employment status
        $baseSalary = $this->calculateBaseSalary($employee, $formula, $attendanceData);

        // Calculate allowances (tunjangan)
        $allowances = $this->calculateAllowances($employee, $baseSalary);

        // Calculate bonuses
        $bonuses = $this->calculateBonuses($employee);

        // Calculate deductions (potongan)
        $deductions = $this->calculateDeductions($employee, $baseSalary, $attendanceData);

        // Calculate totals
        $totalAllowance = $allowances->sum('amount');
        $totalBonus = $bonuses->sum('amount');
        $totalDeduction = $deductions->sum('amount');
        $grossSalary = $baseSalary + $totalAllowance + $totalBonus;
        $netSalary = $grossSalary - $totalDeduction;

        // Create or update payroll record
        return DB::transaction(function () use (
            $employee,
            $period,
            $baseSalary,
            $totalAllowance,
            $totalDeduction,
            $totalBonus,
            $grossSalary,
            $netSalary,
            $attendanceData,
            $allowances,
            $bonuses,
            $deductions,
            $userId
        ) {
            $payroll = EmployeePayroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'payroll_period' => $period->format('Y-m-d'),
                ],
                [
                    'base_salary' => $baseSalary,
                    'total_allowance' => $totalAllowance,
                    'total_deduction' => $totalDeduction,
                    'total_bonus' => $totalBonus,
                    'gross_salary' => $grossSalary,
                    'net_salary' => $netSalary,
                    'work_days' => $attendanceData['work_days'],
                    'present_days' => $attendanceData['present_days'],
                    'late_count' => $attendanceData['late_count'],
                    'absent_count' => $attendanceData['absent_count'],
                    'overtime_hours' => $attendanceData['overtime_hours'],
                    'payment_status' => 'calculated',
                    'users_id' => $userId,
                ]
            );

            // Delete existing details
            $payroll->details()->delete();

            // Create detail records
            foreach ($allowances as $allowance) {
                EmployeePayrollDetail::create([
                    'employee_payroll_id' => $payroll->id,
                    'payroll_component_id' => $allowance['component_id'],
                    'component_name' => $allowance['name'],
                    'component_type' => 'income',
                    'amount' => $allowance['amount'],
                    'calculation_note' => $allowance['note'] ?? null,
                ]);
            }

            foreach ($bonuses as $bonus) {
                EmployeePayrollDetail::create([
                    'employee_payroll_id' => $payroll->id,
                    'payroll_component_id' => $bonus['component_id'],
                    'component_name' => $bonus['name'],
                    'component_type' => 'bonus',
                    'amount' => $bonus['amount'],
                    'calculation_note' => $bonus['note'] ?? null,
                ]);
            }

            foreach ($deductions as $deduction) {
                EmployeePayrollDetail::create([
                    'employee_payroll_id' => $payroll->id,
                    'payroll_component_id' => $deduction['component_id'] ?? null,
                    'component_name' => $deduction['name'],
                    'component_type' => 'deduction',
                    'amount' => $deduction['amount'],
                    'calculation_note' => $deduction['note'] ?? null,
                ]);
            }

            return $payroll->fresh(['details']);
        });
    }

    /**
     * Get applicable formula for employee
     */
    protected function getApplicableFormula(Employee $employee): ?PayrollFormula
    {
        return PayrollFormula::active()
            ->forEmployee($employee)
            ->orderBy('applies_to', 'asc') // specific formula first
            ->first();
    }

    /**
     * Calculate base salary based on employment status
     */
    protected function calculateBaseSalary(Employee $employee, ?PayrollFormula $formula, array $attendanceData): float
    {
        $employmentStatus = $employee->employee_status; // THL, Kontrak, PNS, dll

        // Get base salary from grade
        $gradeSalary = $employee->grade?->base_salary ?? 0;

        // For THL (daily workers), calculate based on working days
        if (strtoupper($employmentStatus) === 'THL') {
            $dailyRate = $gradeSalary / 30; // assuming 30 days per month
            return $dailyRate * $attendanceData['present_days'];
        }

        // For Probation/Calon Pegawai (CAPEG), apply 80% multiplier
        if (in_array(strtoupper($employmentStatus), ['CAPEG', 'PROBATION', 'CALON PEGAWAI'])) {
            $multiplier = $formula?->percentage_multiplier ?? 0.80;
            return $gradeSalary * $multiplier;
        }

        // For Contract and Permanent (based on UMR or grade salary)
        return $gradeSalary;
    }

    /**
     * Calculate allowances
     */
    protected function calculateAllowances(Employee $employee, float $baseSalary): \Illuminate\Support\Collection
    {
        $allowances = collect();

        // Get all active income components
        $components = PayrollComponent::active()->byType('income')->get();

        foreach ($components as $component) {
            $amount = 0;

            switch ($component->calculation_method) {
                case 'fixed':
                    $amount = $component->default_amount;
                    break;

                case 'percentage':
                    $amount = $baseSalary * ($component->default_amount / 100);
                    break;

                case 'formula':
                    // Evaluate formula dynamically
                    $amount = $this->evaluateFormula($component->formula, $employee, $baseSalary);
                    break;
            }

            if ($amount > 0) {
                $allowances->push([
                    'component_id' => $component->id,
                    'name' => $component->component_name,
                    'amount' => $amount,
                    'note' => "Metode: {$component->calculation_method}",
                ]);
            }
        }

        // Add position-based allowance from grade benefits
        if ($employee->grade) {
            $gradeBenefits = $employee->grade->benefits ?? collect();
            foreach ($gradeBenefits as $benefit) {
                $allowances->push([
                    'component_id' => null,
                    'name' => $benefit->benefit_name ?? 'Tunjangan Pangkat',
                    'amount' => $benefit->benefit_amount ?? 0,
                    'note' => 'Berdasarkan golongan/pangkat',
                ]);
            }
        }

        return $allowances;
    }

    /**
     * Calculate bonuses
     */
    protected function calculateBonuses(Employee $employee): \Illuminate\Support\Collection
    {
        $bonuses = collect();

        // Get all active bonus components
        $components = PayrollComponent::active()->byType('bonus')->get();

        foreach ($components as $component) {
            $amount = $component->default_amount;

            if ($amount > 0) {
                $bonuses->push([
                    'component_id' => $component->id,
                    'name' => $component->component_name,
                    'amount' => $amount,
                    'note' => 'Bonus',
                ]);
            }
        }

        return $bonuses;
    }

    /**
     * Calculate deductions
     */
    protected function calculateDeductions(Employee $employee, float $baseSalary, array $attendanceData): \Illuminate\Support\Collection
    {
        $deductions = collect();

        // Get all active deduction components
        $components = PayrollComponent::active()->byType('deduction')->get();

        foreach ($components as $component) {
            $amount = 0;

            switch ($component->calculation_method) {
                case 'fixed':
                    $amount = $component->default_amount;
                    break;

                case 'percentage':
                    $amount = $baseSalary * ($component->default_amount / 100);
                    break;

                case 'formula':
                    $amount = $this->evaluateFormula($component->formula, $employee, $baseSalary);
                    break;
            }

            if ($amount > 0) {
                $deductions->push([
                    'component_id' => $component->id,
                    'name' => $component->component_name,
                    'amount' => $amount,
                    'note' => "Metode: {$component->calculation_method}",
                ]);
            }
        }

        // Add employee-specific salary cuts
        $salaryCuts = EmployeeSalaryCut::where('employee_id', $employee->id)
            ->active()
            ->get();

        foreach ($salaryCuts as $cut) {
            $amount = 0;

            if ($cut->calculation_type === 'fixed') {
                $amount = $cut->amount;
            } else { // percentage
                $amount = $baseSalary * ($cut->amount / 100);
            }

            if ($amount > 0) {
                $deductions->push([
                    'component_id' => null,
                    'name' => $cut->cut_name,
                    'amount' => $amount,
                    'note' => "Potongan khusus - {$cut->description}",
                ]);

                // Update paid months for temporary cuts
                if ($cut->cut_type === 'temporary') {
                    $cut->increment('paid_months');
                    if ($cut->isCompleted()) {
                        $cut->update(['is_active' => false]);
                    }
                }
            }
        }

        // Add absence-based deductions
        if ($attendanceData['absent_count'] > 0) {
            $dailyRate = $baseSalary / $attendanceData['work_days'];
            $absentDeduction = $dailyRate * $attendanceData['absent_count'];

            $deductions->push([
                'component_id' => null,
                'name' => 'Potongan Absen',
                'amount' => $absentDeduction,
                'note' => "{$attendanceData['absent_count']} hari tidak hadir",
            ]);
        }

        return $deductions;
    }

    /**
     * Calculate attendance data for the period
     */
    protected function calculateAttendanceData(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $workDays = $this->countWorkDays($startDate, $endDate);

        $attendances = EmployeeAttendanceRecord::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $presentDays = $attendances->where('attendance_type', 'check_in')->count();
        $lateCount = $attendances->where('is_late', true)->count();
        $absentCount = $workDays - $presentDays;
        $overtimeHours = $attendances->sum('overtime_hours') ?? 0;

        return [
            'work_days' => $workDays,
            'present_days' => $presentDays,
            'late_count' => $lateCount,
            'absent_count' => max(0, $absentCount),
            'overtime_hours' => $overtimeHours,
        ];
    }

    /**
     * Count work days (excluding weekends)
     */
    protected function countWorkDays(Carbon $startDate, Carbon $endDate): int
    {
        $workDays = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            if (!$current->isWeekend()) {
                $workDays++;
            }
            $current->addDay();
        }

        return $workDays;
    }

    /**
     * Evaluate formula dynamically
     */
    protected function evaluateFormula(string $formula, Employee $employee, float $baseSalary): float
    {
        // Simple formula evaluation
        // Support variables: {base_salary}, {grade_salary}, {position_allowance}
        $formula = str_replace('{base_salary}', $baseSalary, $formula);
        $formula = str_replace('{grade_salary}', $employee->grade?->base_salary ?? 0, $formula);

        try {
            // Evaluate the formula safely (you might want to use a proper expression evaluator)
            return eval("return $formula;");
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
