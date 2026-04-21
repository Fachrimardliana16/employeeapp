<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivityTrait;

class EmployeePayroll extends Model
{
    use LogsActivityTrait;
    protected $fillable = [
        'employee_id',
        'payroll_period',
        'base_salary',
        'total_allowance',
        'total_deduction',
        'total_bonus',
        'gross_salary',
        'net_salary',
        'work_days',
        'present_days',
        'late_count',
        'absent_count',
        'overtime_hours',
        'payment_status',
        'payment_date',
        'notes',
        'approved_by',
        'approved_at',
        'users_id',
    ];

    protected $casts = [
        'payroll_period' => 'date',
        'base_salary' => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'total_bonus' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(EmployeePayrollDetail::class);
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'calculated' => 'Terhitung',
            'approved' => 'Disetujui',
            'paid' => 'Dibayar',
        ];
    }

    public function calculateGrossSalary(): void
    {
        $this->gross_salary = $this->base_salary + $this->total_allowance + $this->total_bonus;
    }

    public function calculateNetSalary(): void
    {
        $this->net_salary = $this->gross_salary - $this->total_deduction;
    }

    public function scopeByPeriod($query, $year, $month)
    {
        return $query->whereYear('payroll_period', $year)
            ->whereMonth('payroll_period', $month);
    }
}
