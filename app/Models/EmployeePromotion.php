<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePromotion extends Model
{
    use HasUserTracking, SoftDeletes;

    protected $fillable = [
        'decision_letter_number',
        'promotion_date',
        'next_promotion_date',
        'employee_id',
        'old_basic_salary_id',
        'new_basic_salary_id',
        'doc_promotion',
        'desc',
        'users_id',
    ];

    protected $casts = [
        'promotion_date' => 'date',
        'next_promotion_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->promotion_date) {
                $model->next_promotion_date = $model->promotion_date->copy()->addYears(4);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('promotion_date') && $model->promotion_date) {
                $model->next_promotion_date = $model->promotion_date->copy()->addYears(4);
            }
        });
    }

    /**
     * Get the employee that owns the promotion.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the old salary grade before promotion.
     */
    public function oldSalaryGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'old_basic_salary_id');
    }

    /**
     * Get the new salary grade after promotion.
     */
    public function newSalaryGrade(): BelongsTo
    {
        return $this->belongsTo(MasterEmployeeGrade::class, 'new_basic_salary_id');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    /**
     * Get the salary increase amount from this promotion.
     */
    public function getSalaryIncreaseAttribute(): float
    {
        if ($this->newSalaryGrade && $this->oldSalaryGrade) {
            return $this->newSalaryGrade->basic_salary - $this->oldSalaryGrade->basic_salary;
        }
        return 0;
    }

    /**
     * Get the salary increase percentage from this promotion.
     */
    public function getSalaryIncreasePercentageAttribute(): float
    {
        if ($this->oldSalaryGrade && $this->oldSalaryGrade->basic_salary > 0) {
            return ($this->salary_increase / $this->oldSalaryGrade->basic_salary) * 100;
        }
        return 0;
    }
}
