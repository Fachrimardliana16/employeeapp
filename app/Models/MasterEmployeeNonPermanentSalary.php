<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterEmployeeNonPermanentSalary extends Model
{
    use HasUserTracking, SoftDeletes;

    protected $table = 'master_employee_non_permanent_salaries';

    protected $fillable = [
        'name',
        'employment_status_id',
        'amount',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeStatusEmployment::class, 'employment_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
