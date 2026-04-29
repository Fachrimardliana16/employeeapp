<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivityTrait;

class AttendanceSpecialSchedule extends Model
{
    use LogsActivityTrait;
    protected $fillable = [
        'employee_id',
        'date',
        'is_working',
        'type',
        'description',
        'users_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_working' => 'boolean',
    ];

    /**
     * Get the employee that owns the special schedule.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created/updated the special schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
