<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeAgreement;
use Carbon\Carbon;

class EmployeeAgreementObserver
{
    /**
     * Handle the EmployeeAgreement "creating" event.
     */
    public function creating(EmployeeAgreement $employeeAgreement): void
    {
        // Jika tanggal mulai ada tapi tanggal berakhir kosong, set otomatis 2 tahun
        if ($employeeAgreement->agreement_date_start && !$employeeAgreement->agreement_date_end) {
            $employeeAgreement->agreement_date_end = Carbon::parse($employeeAgreement->agreement_date_start)->addYears(2);
        }
    }

    /**
     * Handle the EmployeeAgreement "created" event.
     */
    public function created(EmployeeAgreement $employeeAgreement): void
    {
        $this->createOrUpdateEmployee($employeeAgreement);
    }

    /**
     * Handle the EmployeeAgreement "updating" event.
     */
    public function updating(EmployeeAgreement $employeeAgreement): void
    {
        // Jika tanggal mulai berubah, update tanggal berakhir otomatis
        if ($employeeAgreement->isDirty('agreement_date_start') && $employeeAgreement->agreement_date_start) {
            $employeeAgreement->agreement_date_end = Carbon::parse($employeeAgreement->agreement_date_start)->addYears(2);
        }
    }

    /**
     * Handle the EmployeeAgreement "updated" event.
     */
    public function updated(EmployeeAgreement $employeeAgreement): void
    {
        $this->createOrUpdateEmployee($employeeAgreement);
    }

    /**
     * Create new employee or update existing employee based on agreement data.
     */
    private function createOrUpdateEmployee(EmployeeAgreement $employeeAgreement): void
    {
        // Cari employee berdasarkan nama dari agreement
        $employee = Employee::where('name', $employeeAgreement->name)->first();

        // Data employee yang akan dibuat/diupdate
        $employeeData = [
            'name' => $employeeAgreement->name,
            'place_birth' => $employeeAgreement->place_birth,
            'date_birth' => $employeeAgreement->date_birth,
            'gender' => $employeeAgreement->gender,
            'marital_status' => $employeeAgreement->marital_status,
            'address' => $employeeAgreement->address,
            'phone_number' => $employeeAgreement->phone_number,
            'email' => $employeeAgreement->email,
            'employment_status_id' => $employeeAgreement->employment_status_id,
            'master_employee_agreement_id' => $employeeAgreement->agreement_id,
            'agreement_date_start' => $employeeAgreement->agreement_date_start,
            'agreement_date_end' => $employeeAgreement->agreement_date_end,
            'basic_salary_id' => $employeeAgreement->basic_salary_id,
            'employee_position_id' => $employeeAgreement->employee_position_id,
            'employee_education_id' => $employeeAgreement->employee_education_id,
            'departments_id' => $employeeAgreement->departments_id,
            'grade_date_start' => $employeeAgreement->agreement_date_start,
            'grade_date_end' => $employeeAgreement->agreement_date_end,
            'entry_date' => $employeeAgreement->agreement_date_start,
            'leave_balance' => 12, // Default 12 hari cuti per tahun
            'users_id' => $employeeAgreement->users_id,
        ];

        // Only add sub_department_id if it's not null
        if ($employeeAgreement->sub_department_id) {
            $employeeData['sub_department_id'] = $employeeAgreement->sub_department_id;
        }

        if ($employee) {
            // Update employee yang sudah ada
            $employee->update($employeeData);
        } else {
            // Buat employee baru
            $employeeData['nippam'] = $this->generateNippam();
            Employee::create($employeeData);
        }
    }

    /**
     * Generate NIPPAM for new employee.
     */
    private function generateNippam(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        $lastEmployee = Employee::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEmployee ? (intval(substr($lastEmployee->nippam, -3)) + 1) : 1;

        return $year . $month . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
