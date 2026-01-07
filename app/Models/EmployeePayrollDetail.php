<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayrollDetail extends Model
{
    protected $fillable = [
        'employee_payroll_id',
        'payroll_component_id',
        'component_name',
        'component_type',
        'amount',
        'calculation_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(EmployeePayroll::class, 'employee_payroll_id');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class, 'payroll_component_id');
    }
}
