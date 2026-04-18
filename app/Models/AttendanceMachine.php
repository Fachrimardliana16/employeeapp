<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceMachine extends Model
{
    protected $fillable = [
        'serial_number',
        'name',
        'master_office_location_id',
        'last_heard_at',
        'ip_address',
        'status',
    ];

    protected $casts = [
        'last_heard_at' => 'datetime',
    ];

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_heard_at && $this->last_heard_at->diffInMinutes(now()) <= 5;
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(MasterOfficeLocation::class, 'master_office_location_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceMachineLog::class, 'attendance_machine_id');
    }
}
