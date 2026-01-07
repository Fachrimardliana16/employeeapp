<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterEmployeeServiceGrade extends Model
{
    use HasUserTracking;
    protected $table = 'master_employee_service_grade';

    protected $fillable = [
        'employee_grade_id',
        'service_grade',
        'desc',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function employeeGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'employee_grade_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function basicSalaries(): HasMany
    {
        return $this->hasMany(MasterEmployeeBasicSalary::class, 'employee_service_grade_id');
    }
}
