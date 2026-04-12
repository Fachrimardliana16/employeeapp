<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivityTrait;

class MasterEmployeeGrade extends Model
{
    use LogsActivityTrait;
    use HasUserTracking;
    protected $fillable = [
        'name',
        'desc',
        'is_active',
        'users_id',
    ];

    // Get the basic salary amount for this grade (assumes one active salary per grade)
    public function getBasicSalaryAttribute()
    {
        $basicSalary = $this->basicSalaries()->where('is_active', true)->first();
        return $basicSalary ? $basicSalary->amount : 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'basic_salary_id');
    }

    public function basicSalaries(): HasMany
    {
        return $this->hasMany(MasterEmployeeBasicSalary::class, 'employee_grade_id');
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(MasterEmployeeGradeBenefit::class, 'employee_grade_id');
    }

    public function salaryCuts(): HasMany
    {
        return $this->hasMany(MasterEmployeeGradeSalaryCut::class, 'employee_grade_id');
    }
}
