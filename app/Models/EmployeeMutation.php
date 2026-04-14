<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\LogsActivityTrait;

class EmployeeMutation extends Model
{
    use LogsActivityTrait;
    use HasUserTracking, SoftDeletes;

    protected $fillable = [
        'decision_letter_number',
        'mutation_date',
        'employee_id',
        'old_department_id',
        'old_sub_department_id',
        'new_department_id',
        'new_sub_department_id',
        'old_position_id',
        'new_position_id',
        'is_applied',
        'docs',
        'proposal_docs',
        'applied_at',
        'applied_by',
        'users_id',
    ];

    protected $casts = [
        'mutation_date' => 'date',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function oldDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'old_department_id');
    }

    public function newDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'new_department_id');
    }

    public function oldSubDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterSubDepartment::class, 'old_sub_department_id');
    }

    public function newSubDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterSubDepartment::class, 'new_sub_department_id');
    }

    public function oldPosition(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'old_position_id');
    }

    public function newPosition(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeePosition::class, 'new_position_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
