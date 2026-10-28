<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\LogsActivityTrait;

class AttendanceMachineCommand extends Model
{
    use LogsActivityTrait;
    protected $fillable = [
        'attendance_machine_id',
        'command',
        'status',
        'sent_at',
        'completed_at',
        'response_payload',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(AttendanceMachine::class, 'attendance_machine_id');
    }
}
