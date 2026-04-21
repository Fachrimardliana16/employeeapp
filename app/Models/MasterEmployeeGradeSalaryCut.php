<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterEmployeeGradeSalaryCut extends Model
{
    use HasUserTracking, LogsActivityTrait;
    protected $table = 'master_employee_grade_salary_cuts';

    protected $fillable = [
        'employee_grade_id',
        'salary_cuts_id',
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

    public function salaryCut(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeSalaryCut::class, 'salary_cuts_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
