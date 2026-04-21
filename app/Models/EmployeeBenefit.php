<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\LogsActivityTrait;

class EmployeeBenefit extends Model
{
    use HasUserTracking, SoftDeletes, LogsActivityTrait;

    protected $fillable = [
        'employee_id',
        'benefit_name',
        'amount',
        'is_active',
        'description',
        'users_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
