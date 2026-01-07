<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRetirement extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'employee_id',
        'retirement_date',
        'reason',
        'docs',
        'users_id',
    ];

    protected $casts = [
        'retirement_date' => 'date',
    ];

    /**
     * Get the employee that owns the retirement record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
