<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterEmployeeGradeBenefit extends Model
{
    use HasUserTracking, LogsActivityTrait;
    protected $table = 'master_employee_grade_benefit';

    protected $fillable = [
        'employee_grade_id',
        'benefit_id',
        'amount',
        'desc',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function employeeGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'employee_grade_id');
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeBenefit::class, 'benefit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
