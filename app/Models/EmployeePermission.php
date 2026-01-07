<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePermission extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'employees_id',
        'master_employee_permissions_id',
        'permission_start_date',
        'permission_end_date',
        'permission_duration_days',
        'permission_reason',
        'permission_description',
        'permission_file',
        'permission_status',
        'approval_date',
        'approved_by',
        'users_id',
    ];

    protected $casts = [
        'permission_start_date' => 'date',
        'permission_end_date' => 'date',
        'approval_date' => 'date',
    ];

    /**
     * Get the employee that owns the permission.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    /**
     * Get the master permission type.
     */
    public function masterPermission(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePermission::class, 'master_employee_permissions_id');
    }

    /**
     * Get the user who approved this permission.
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
