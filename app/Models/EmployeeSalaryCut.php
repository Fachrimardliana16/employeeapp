<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryCut extends Model
{
    protected $fillable = [
        'employee_id',
        'cut_name',
        'cut_type',
        'calculation_type',
        'amount',
        'start_date',
        'end_date',
        'installment_months',
        'paid_months',
        'is_active',
        'description',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public static function getCutTypeOptions(): array
    {
        return [
            'permanent' => 'Tetap',
            'temporary' => 'Sementara',
        ];
    }

    public static function getCalculationTypeOptions(): array
    {
        return [
            'fixed' => 'Nominal Tetap',
            'percentage' => 'Persentase',
        ];
    }

    public function isCompleted(): bool
    {
        if ($this->cut_type === 'permanent') {
            return false;
        }
        return $this->paid_months >= $this->installment_months;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }
}
