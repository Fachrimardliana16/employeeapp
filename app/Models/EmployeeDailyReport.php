<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDailyReport extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'employees_id',
        'report_date',
        'work_start_time',
        'work_end_time',
        'break_start_time',
        'break_end_time',
        'activities_description',
        'achievement_description',
        'problem_description',
        'solution_description',
        'tomorrow_plan',
        'overtime_duration_minutes',
        'work_location',
        'attachment_files',
        'users_id',
    ];

    protected $casts = [
        'report_date' => 'date',
        'work_start_time' => 'datetime',
        'work_end_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'attachment_files' => 'array',
    ];

    /**
     * Get the employee that owns the daily report.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employees_id');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
