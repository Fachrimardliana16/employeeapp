<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePeriodicSalaryIncrease extends Model
{
    use HasUserTracking;

    protected $table = 'employee_periodic_salary_increase';

    protected $fillable = [
        'number_psi',
        'date_periodic_salary_increase',
        'employee_id',
        'old_basic_salary_id',
        'new_basic_salary_id',
        'total_basic_salary',
        'docs_letter',
        'docs_archive',
        'users_id',
        'is_applied',
        'proposal_docs',
        'new_employee_service_grade_id',
        'applied_at',
        'applied_by',
        'notes',
    ];

    protected $casts = [
        'total_basic_salary' => 'decimal:2',
        'date_periodic_salary_increase' => 'date',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime',
    ];

    /**
     * Get the employee that owns the salary increase.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the user who approved this increase.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Get the new service grade (MKG) after increase.
     */
    public function newServiceGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeServiceGrade::class, 'new_employee_service_grade_id');
    }

    public function oldSalaryGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'old_basic_salary_id');
    }

    public function newSalaryGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'new_basic_salary_id');
    }
}
