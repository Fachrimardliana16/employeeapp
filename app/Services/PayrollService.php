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
        $payrollData = $this->getPayrollData($employee, $period);

        // Create or update payroll record
        return DB::transaction(function () use ($employee, $period, $payrollData, $userId) {
            $payroll = EmployeePayroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'payroll_period' => $period->format('Y-m-d'),
                ],
                [
                    'base_salary' => $payrollData['base_salary'],
                    'total_allowance' => $payrollData['total_allowance'],
                    'total_deduction' => $payrollData['total_deduction'],
                    'total_bonus' => $payrollData['total_bonus'],
                    'gross_salary' => $payrollData['gross_salary'],
                    'net_salary' => $payrollData['net_salary'],
                    'work_days' => $payrollData['attendance_data']['work_days'],
                    'present_days' => $payrollData['attendance_data']['present_days'],
                    'late_count' => $payrollData['attendance_data']['late_count'],
                    'absent_count' => $payrollData['attendance_data']['absent_count'],
                    'overtime_hours' => $payrollData['attendance_data']['overtime_hours'],
                    'payment_status' => 'calculated',
                    'users_id' => $userId,
                ]
            );

            // Delete existing details
            $payroll->details()->delete();

            // Create detail records
            foreach ($payrollData['allowances'] as $allowance) {
                EmployeePayrollDetail::create([
                    'employee_payroll_id' => $payroll->id,
                    'payroll_component_id' => $allowance['component_id'] ?? null,
                    'component_name' => $allowance['name'],
                    'component_type' => 'income',
                    'amount' => $allowance['amount'],
                    'calculation_note' => $allowance['note'] ?? null,
                ]);
            }

            foreach ($payrollData['bonuses'] as $bonus) {
                EmployeePayrollDetail::create([
                    'employee_payroll_id' => $payroll->id,
                    'payroll_component_id' => $bonus['component_id'] ?? null,
                    'component_name' => $bonus['name'],
                    'component_type' => 'bonus',
                    'amount' => $bonus['amount'],
                    'calculation_note' => $bonus['note'] ?? null,
                ]);
            }

            foreach ($payrollData['deductions'] as $deduction) {
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
     * Get payroll data without saving (for simulation/preview)
     */
    public function getPayrollData(Employee $employee, Carbon $period): array
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
        $allowances = $this->calculateAllowances($employee, $baseSalary, $formula);

        // Calculate bonuses
        $bonuses = $this->calculateBonuses($employee);

        // Calculate deductions (potongan)
        $deductions = $this->calculateDeductions($employee, $baseSalary, $attendanceData, $formula);

        // Calculate totals
        $totalAllowance = $allowances->sum('amount');
        $totalBonus = $bonuses->sum('amount');
        $totalDeduction = $deductions->sum('amount');
        
        // Initial gross and net
        $grossSalary = $baseSalary + $totalAllowance + $totalBonus;
        $netSalary = $grossSalary - $totalDeduction;

        // Apply Rounding if necessary
        $roundingAllowed = $formula ? in_array('ROUNDING', $formula->formula_components ?? []) : true;
        if ($roundingAllowed) {
            $roundingAmount = $this->calculateRounding($grossSalary, $netSalary);
            if ($roundingAmount != 0) {
                $allowances->push([
                    'component_id' => PayrollComponent::where('component_code', 'ROUNDING')->value('id'),
                    'name' => 'Pembulatan',
                    'amount' => $roundingAmount,
                    'note' => 'Pembulatan otomatis',
                ]);
                $totalAllowance += $roundingAmount;
                $grossSalary += $roundingAmount;
                $netSalary += $roundingAmount;
            }
        }

        return [
            'base_salary' => $baseSalary,
            'total_allowance' => $totalAllowance,
            'total_deduction' => $totalDeduction,
            'total_bonus' => $totalBonus,
            'gross_salary' => $grossSalary,
            'net_salary' => $netSalary,
            'allowances' => $allowances->toArray(),
            'deductions' => $deductions->toArray(),
            'bonuses' => $bonuses->toArray(),
            'attendance_data' => $attendanceData,
            'formula' => $formula,
        ];
    }

    /**
     * Calculate rounding amount to match standard PDAM slip logic
     */
    protected function calculateRounding(float $gross, float $net): float
    {
        // Many systems round to nearest 10 or 50. 
        // Based on the slip, a '50' is added to income.
        // If the net salary ends in a non-zero, let's add a rounding component.
        if ($net % 10 != 0) {
            return 50; 
        }
        return 0;
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
        $employmentStatus = $employee->employmentStatus?->name ?? 'Pegawai Tetap';
        $statusUpper = strtoupper($employmentStatus);

        // For THL and Intern, prioritize the non-permanent salary amount
        if (str_contains($statusUpper, 'HARIAN') || str_contains($statusUpper, 'THL') || str_contains($statusUpper, 'MAGANG')) {
            $baseAmount = $employee->nonPermanentSalary?->amount ?? 0;
            
            // THL is daily
            if (str_contains($statusUpper, 'HARIAN') || str_contains($statusUpper, 'THL')) {
                return $baseAmount * $attendanceData['present_days'];
            }
            
            // Intern is monthly
            return $baseAmount;
        }

        // For others, use the standard Grade/MKG lookup
        $standardSalary = $employee->basic_salary_amount ?? 0;

        // Use multiplier from formula if available
        $multiplier = $formula ? (float)$formula->percentage_multiplier : 1.0;

        // Special case for CPNS if multiplier not set in formula record manually
        if (!$formula && (str_contains($statusUpper, 'CALON') || str_contains($statusUpper, 'CPNS') || str_contains($statusUpper, 'PROBATION'))) {
            $multiplier = 0.80;
        }

        return $standardSalary * $multiplier;
    }

    /**
     * Calculate allowances
     */
    protected function calculateAllowances(Employee $employee, float $baseSalary, ?PayrollFormula $formula = null): \Illuminate\Support\Collection
    {
        $allowances = collect();

        // Get allowed components from formula
        $allowedCodes = $formula->formula_components ?? [];
        
        // Get all active income components
        $query = PayrollComponent::active()
            ->byType('income')
            ->where('component_code', '!=', 'ROUNDING');
            
        // If formula exists, only use listed components
        if ($formula) {
            $query->whereIn('component_code', $allowedCodes);
        }

        $components = $query->get();

        $employmentStatus = $employee->employmentStatus?->name ?? 'Pegawai Tetap';
        $statusUpper = strtoupper($employmentStatus);

        // Check for multiplier from formula
        $multiplier = $formula ? (float)$formula->percentage_multiplier : 1.0;

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

            // Apply multiplier (e.g. 80% for CPNS)
            $finalAmount = $amount * $multiplier;

            if ($finalAmount > 0) {
                $allowances->push([
                    'component_id' => $component->id,
                    'name' => $component->component_name,
                    'amount' => $finalAmount,
                    'note' => $multiplier < 1.0 ? "Metode: {$component->calculation_method} (Multiplier: {$multiplier})" : "Metode: {$component->calculation_method}",
                ]);
            }
        }

        // Add position-based allowance from MasterEmployeePositionBenefit
        if ($employee->position) {
            $positionBenefits = $employee->position->benefits()->where('is_active', true)->get();
            foreach ($positionBenefits as $posBenefit) {
                $allowances->push([
                    'component_id' => null,
                    'name' => $posBenefit->benefit?->name ?? 'Tunjangan Jabatan',
                    'amount' => $posBenefit->amount ?? 0,
                    'note' => 'Berdasarkan jabatan',
                ]);
            }
        }

        // Add grade-based allowance from MasterEmployeeGradeBenefit
        if ($employee->grade) {
            $gradeBenefits = $employee->grade->benefits()->where('is_active', true)->get();
            foreach ($gradeBenefits as $benefit) {
                $allowances->push([
                    'component_id' => null,
                    'name' => $benefit->benefit?->name ?? 'Tunjangan Pangkat',
                    'amount' => $benefit->amount ?? 0,
                    'note' => 'Berdasarkan golongan/pangkat',
                ]);
            }
        }

        // Add employee-specific benefits
        $individualBenefits = \App\Models\EmployeeBenefit::where('employee_id', $employee->id)
            ->where('is_active', true)
            ->get();

        foreach ($individualBenefits as $indBenefit) {
            $allowances->push([
                'component_id' => null,
                'name' => $indBenefit->benefit_name,
                'amount' => $indBenefit->amount,
                'note' => "Tunjangan khusus - {$indBenefit->description}",
            ]);
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
    protected function calculateDeductions(Employee $employee, float $baseSalary, array $attendanceData, ?PayrollFormula $formula = null): \Illuminate\Support\Collection
    {
        $deductions = collect();

        // Get allowed components from formula
        $allowedCodes = $formula->formula_components ?? [];

        // Get all active deduction components
        $query = PayrollComponent::active()->byType('deduction');
        
        // If formula exists, filter deductions too
        if ($formula) {
            $query->whereIn('component_code', $allowedCodes);
        }

        $components = $query->get();

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

        // Add position-based salary cuts
        if ($employee->position) {
            $positionCuts = $employee->position->salaryCuts()->where('is_active', true)->get();
            foreach ($positionCuts as $posCut) {
                $deductions->push([
                    'component_id' => null,
                    'name' => $posCut->salaryCut?->name ?? 'Potongan Jabatan',
                    'amount' => $posCut->amount ?? 0,
                    'note' => 'Berdasarkan jabatan',
                ]);
            }
        }

        // Add grade-based salary cuts
        if ($employee->grade) {
            $gradeCuts = $employee->grade->salaryCuts()->where('is_active', true)->get();
            foreach ($gradeCuts as $grCut) {
                $deductions->push([
                    'component_id' => null,
                    'name' => $grCut->salaryCut?->name ?? 'Potongan Golongan',
                    'amount' => $grCut->amount ?? 0,
                    'note' => 'Berdasarkan golongan/pangkat',
                ]);
            }
        }

        // Add employee-specific salary cuts (Loans, etc.)
        $salaryCuts = EmployeeSalaryCut::where('employee_id', $employee->id)
            ->active()
            ->get();

        foreach ($salaryCuts as $cut) {
            $amount = $cut->calculation_type === 'fixed' 
                ? $cut->amount 
                : $baseSalary * ($cut->amount / 100);

            if ($amount > 0) {
                $deductions->push([
                    'component_id' => null,
                    'name' => $cut->cut_name,
                    'amount' => $amount,
                    'note' => "Potongan khusus - {$cut->description}",
                ]);
            }
        }

        // Add absence-based deductions
        if ($attendanceData['absent_count'] > 0 || $attendanceData['late_count'] > 0) {
            $absentComponent = PayrollComponent::where('component_code', 'POT_ABSEN')->first();
            if ($absentComponent && $attendanceData['absent_count'] > 0) {
                $amount = $this->evaluateFormula($absentComponent->formula, $employee, $baseSalary, $attendanceData);
                $deductions->push([
                    'component_id' => $absentComponent->id,
                    'name' => $absentComponent->component_name,
                    'amount' => $amount,
                    'note' => "{$attendanceData['absent_count']} hari tidak hadir",
                ]);
            }

            $lateComponent = PayrollComponent::where('component_code', 'POT_LATE')->first();
            if ($lateComponent && $attendanceData['late_count'] > 0) {
                $amount = $lateComponent->default_amount * $attendanceData['late_count'];
                $deductions->push([
                    'component_id' => $lateComponent->id,
                    'name' => $lateComponent->component_name,
                    'amount' => $amount,
                    'note' => "{$attendanceData['late_count']} kali terlambat",
                ]);
            }
        }

        return $deductions;
    }

    /**
     * Calculate attendance data for the period
     */
    protected function calculateAttendanceData(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $workDays = $this->countWorkDays($startDate, $endDate);

        $attendances = EmployeeAttendanceRecord::where('pin', (string)$employee->pin)
            ->whereBetween('attendance_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        $presentDays = $attendances->whereIn('state', ['in', 'check_in'])->count();
        $lateCount = $attendances->where('attendance_status', 'late')->count();
        $absentCount = $workDays - $presentDays;
        $overtimeHours = 0; // Not available in basic schema, could be extended later

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
    protected function evaluateFormula(string $formula, Employee $employee, float $baseSalary, array $attendanceData = []): float
    {
        // Support variables: {base_salary}, {grade_salary}, {family_count}, {absent_count}, {late_count}
        $formula = str_replace('{base_salary}', (string)$baseSalary, $formula);
        $formula = str_replace('{grade_salary}', (string)($employee->grade?->base_salary ?? 0), $formula);
        $formula = str_replace('{family_count}', (string)$employee->families()->count(), $formula);
        $formula = str_replace('{absent_count}', (string)($attendanceData['absent_count'] ?? 0), $formula);
        $formula = str_replace('{late_count}', (string)($attendanceData['late_count'] ?? 0), $formula);

        try {
            // Simple numeric expression evaluation
            $formula = preg_replace('/[^0-9\+\-\*\/\.\(\)]/', '', $formula);
            if (empty($formula)) return 0;
            
            return (float)eval("return $formula;");
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
