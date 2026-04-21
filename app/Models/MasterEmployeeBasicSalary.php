<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterEmployeeBasicSalary extends Model
{
    use HasUserTracking, LogsActivityTrait;
    protected $table = 'master_employee_basic_salary';

    protected $fillable = [
        'employee_service_grade_id',
        'employee_grade_id',
        'amount',
        'desc',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function serviceGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeServiceGrade::class, 'employee_service_grade_id');
    }

    public function employeeGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'employee_grade_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
