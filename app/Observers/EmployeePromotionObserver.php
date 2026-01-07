<?php

namespace App\Observers;

use App\Models\EmployeePromotion;
use App\Models\Employee;

class EmployeePromotionObserver
{
    /**
     * Handle the EmployeePromotion "created" event.
     */
    public function created(EmployeePromotion $employeePromotion): void
    {
        $this->updateEmployeeSalaryGrade($employeePromotion);
    }

    /**
     * Handle the EmployeePromotion "updated" event.
     */
    public function updated(EmployeePromotion $employeePromotion): void
    {
        if ($employeePromotion->wasChanged(['new_basic_salary_id', 'employee_id'])) {
            $this->updateEmployeeSalaryGrade($employeePromotion);
        }
    }

    /**
     * Update employee's basic salary grade when promotion is created/updated.
     */
    private function updateEmployeeSalaryGrade(EmployeePromotion $employeePromotion): void
    {
        $employee = Employee::find($employeePromotion->employee_id);

        if ($employee) {
            $employee->update([
                'basic_salary_id' => $employeePromotion->new_basic_salary_id,
                'grade_date_start' => $employeePromotion->promotion_date,
                // Set grade_date_end to null for active promotion
                'grade_date_end' => null,
            ]);

            // Update previous promotion's end date if exists
            $previousPromotion = EmployeePromotion::where('employee_id', $employeePromotion->employee_id)
                ->where('id', '!=', $employeePromotion->id)
                ->where('promotion_date', '<', $employeePromotion->promotion_date)
                ->latest('promotion_date')
                ->first();

            if ($previousPromotion) {
                // Update employee's grade_date_end for the previous promotion period
                Employee::where('id', $employeePromotion->employee_id)
                    ->update(['grade_date_end' => $employeePromotion->promotion_date]);
            }
        }
    }
}
