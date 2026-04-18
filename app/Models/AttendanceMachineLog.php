<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceMachineLog extends Model
{
    protected $fillable = [
        'attendance_machine_id',
        'serial_number',
        'pin',
        'timestamp',
        'type',
        'raw_payload',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(AttendanceMachine::class, 'attendance_machine_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pin', 'pin');
    }
}
