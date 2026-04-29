<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceMachineCommunication extends Model
{
    protected $fillable = [
        'attendance_machine_id',
        'serial_number',
        'endpoint',
        'method',
        'ip_address',
        'request_params',
        'request_body',
        'response_body',
        'response_code',
        'error_message',
    ];

    protected $casts = [
        'response_code' => 'integer',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(AttendanceMachine::class, 'attendance_machine_id');
    }
    
    /**
     * Check if this communication had an error
     */
    public function hasError(): bool
    {
        return !empty($this->error_message) || $this->response_code >= 400;
    }
    
    /**
     * Get formatted communication type
     */
    public function getTypeLabel(): string
    {
        return match($this->endpoint) {
            'cdata' => $this->method === 'POST' ? 'Upload Data' : 'Handshake',
            'getrequest' => 'Heartbeat/Command',
            'devicecmd' => 'Command Response',
            default => $this->endpoint,
        };
    }
}
