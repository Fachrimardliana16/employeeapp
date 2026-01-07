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
        'employees_id',
        'previous_basic_salary',
        'new_basic_salary',
        'increase_amount',
        'increase_percentage',
        'effective_date',
        'increase_reason',
        'approval_date',
        'approved_by',
        'notes',
        'users_id',
    ];

    protected $casts = [
        'previous_basic_salary' => 'decimal:2',
        'new_basic_salary' => 'decimal:2',
        'increase_amount' => 'decimal:2',
        'increase_percentage' => 'decimal:2',
        'effective_date' => 'date',
        'approval_date' => 'date',
    ];

    /**
     * Get the employee that owns the salary increase.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employees_id');
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
}
