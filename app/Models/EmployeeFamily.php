<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeFamily extends Model
{
    use HasUserTracking, SoftDeletes, LogsActivityTrait;

    protected $fillable = [
        'employees_id',
        'master_employee_families_id',
        'family_name',
        'family_gender',
        'family_id_number',
        'family_place_birth',
        'family_date_birth',
        'family_address',
        'family_phone',
        'is_emergency_contact',
        'users_id',
    ];

    protected $casts = [
        'family_date_birth' => 'date',
        'is_emergency_contact' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    public function masterFamily(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeFamily::class, 'master_employee_families_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
