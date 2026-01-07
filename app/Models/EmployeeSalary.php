<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalary extends Model
{
    use HasUserTracking, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'benefits_1',
        'benefits_2',
        'benefits_3',
        'benefits_4',
        'benefits_5',
        'benefits_6',
        'benefits_7',
        'benefits_8',
        'amount',
        'users_id',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'benefits_1' => 'decimal:2',
        'benefits_2' => 'decimal:2',
        'benefits_3' => 'decimal:2',
        'benefits_4' => 'decimal:2',
        'benefits_5' => 'decimal:2',
        'benefits_6' => 'decimal:2',
        'benefits_7' => 'decimal:2',
        'benefits_8' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
