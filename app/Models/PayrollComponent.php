<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivityTrait;

class PayrollComponent extends Model
{
    use SoftDeletes, LogsActivityTrait;

    protected $fillable = [
        'component_name',
        'component_code',
        'component_type',
        'calculation_method',
        'default_amount',
        'formula',
        'is_taxable',
        'is_active',
        'description',
        'users_id',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function payrollDetails(): HasMany
    {
        return $this->hasMany(EmployeePayrollDetail::class);
    }

    public static function getTypeOptions(): array
    {
        return [
            'income' => 'Pendapatan',
            'deduction' => 'Potongan',
            'bonus' => 'Bonus',
        ];
    }

    public static function getCalculationMethodOptions(): array
    {
        return [
            'fixed' => 'Nilai Tetap',
            'percentage' => 'Persentase',
            'formula' => 'Formula Dinamis',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('component_type', $type);
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
