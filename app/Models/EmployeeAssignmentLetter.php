<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAssignmentLetter extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'registration_number',
        'assigning_employee_id',
        'additional_employee_ids',
        'additional_employees_detail',
        'employee_position_id',
        'task',
        'start_date',
        'end_date',
        'description',
        'signatory_employee_id',
        'signatory_name',
        'signatory_position',
        'pdf_file_path',
        'users_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'additional_employee_ids' => 'array',
        'additional_employees_detail' => 'array',
    ];

    /**
     * Get the assigning employee (main employee).
     */
    public function assigningEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigning_employee_id');
    }

    /**
     * Get the additional employees for this assignment.
     */
    public function additionalEmployees()
    {
        if (empty($this->additional_employee_ids)) {
            return collect();
        }
        return Employee::whereIn('id', $this->additional_employee_ids)->get();
    }

    /**
     * Get the employee position.
     */
    public function employeePosition(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MasterEmployeePosition::class, 'employee_position_id');
    }

    /**
     * Get the signatory employee.
     */
    public function signatoryEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'signatory_employee_id');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
