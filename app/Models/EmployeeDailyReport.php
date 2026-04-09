<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDailyReport extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'employee_id',
        'daily_report_date',
        'work_description',
        'work_status',
        'desc',
        'image',
        'users_id',
    ];

    protected $casts = [
        'daily_report_date' => 'date',
    ];

    /**
     * Get the employee that owns the daily report.
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
