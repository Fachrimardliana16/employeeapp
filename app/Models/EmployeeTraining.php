<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use App\Traits\LogsActivityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTraining extends Model
{
    use HasUserTracking, SoftDeletes, LogsActivityTrait;

    protected $table = 'employee_training';

    protected $fillable = [
        'training_date',
        'employee_id',
        'training_title',
        'training_location',
        'organizer',
        'photo_training',
        'docs_training',
        'users_id',
    ];

    protected $casts = [
        'training_date' => 'date',
    ];

    /**
     * Get the employee that owns the training record.
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
