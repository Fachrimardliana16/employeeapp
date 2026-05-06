<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\LogsActivityTrait;

class EmployeePromotion extends Model
{
    use LogsActivityTrait;
    use HasUserTracking, SoftDeletes;

    protected $fillable = [
        'decision_letter_number',
        'promotion_date',
        'next_promotion_date',
        'employee_id',
        'promotion_type_id',
        'old_basic_salary_id',
        'new_basic_salary_id',
        'doc_promotion',
        'dpk_file',
        'work_report_file',
        'attendance_proof',
        'previous_sk_file',
        'diploma_file',
        'proposal_docs',
        'is_applied',
        'applied_at',
        'applied_by',
        'status',
        'approved_by',
        'approved_at',
        'rejection_note',
        'desc',
        'users_id',
    ];

    protected $casts = [
        'promotion_date' => 'date',
        'next_promotion_date' => 'date',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
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
     * Get the promotion type.
     */
    public function promotionType(): BelongsTo
    {
        return $this->belongsTo(MasterPromotionType::class, 'promotion_type_id');
    }

    /**
     * Get the user who approved this promotion.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForYear($query, $year)
    {
        return $query->whereYear('promotion_date', $year);
    }

    // Accessors
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Diajukan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function getFormattedPromotionDateAttribute(): string
    {
        return $this->promotion_date ? $this->promotion_date->format('d F Y') : '-';
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
