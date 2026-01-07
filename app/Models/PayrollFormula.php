<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollFormula extends Model
{
    protected $fillable = [
        'formula_name',
        'formula_code',
        'applies_to',
        'applies_to_value',
        'formula_components',
        'calculation_rules',
        'percentage_multiplier',
        'is_active',
        'description',
        'users_id',
    ];

    protected $casts = [
        'formula_components' => 'array',
        'percentage_multiplier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public static function getAppliesToOptions(): array
    {
        return [
            'status' => 'Status Pegawai',
            'grade' => 'Golongan/Pangkat',
            'position' => 'Jabatan',
            'all' => 'Semua Pegawai',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEmployee($query, Employee $employee)
    {
        return $query->where(function ($q) use ($employee) {
            $q->where('applies_to', 'all')
                ->orWhere(function ($q2) use ($employee) {
                    $q2->where('applies_to', 'status')
                        ->where('applies_to_value', $employee->employment_status);
                })
                ->orWhere(function ($q3) use ($employee) {
                    $q3->where('applies_to', 'grade')
                        ->where('applies_to_value', $employee->master_employee_grade_id);
                })
                ->orWhere(function ($q4) use ($employee) {
                    $q4->where('applies_to', 'position')
                        ->where('applies_to_value', $employee->jabatan);
                });
        });
    }
}
