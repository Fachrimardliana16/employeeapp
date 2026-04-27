<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\LogsActivityTrait;

class AttendanceMachine extends Model
{
    use LogsActivityTrait;
    protected $fillable = [
        'serial_number',
        'name',
        'master_office_location_id',
        'last_heard_at',
        'ip_address',
        'status',
        'machine_datetime',
        'time_checked_at',
        'time_drift_seconds',
    ];

    protected $casts = [
        'last_heard_at' => 'datetime',
        'machine_datetime' => 'datetime',
        'time_checked_at' => 'datetime',
        'time_drift_seconds' => 'integer',
    ];

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_heard_at && $this->last_heard_at->diffInMinutes(now()) <= 5;
    }

    /**
     * Get human-readable time drift string.
     */
    public function getTimeDriftLabelAttribute(): string
    {
        if (is_null($this->time_drift_seconds)) {
            return 'Belum dicek';
        }

        $abs = abs($this->time_drift_seconds);

        if ($abs <= 5) {
            return '✅ Sinkron';
        }

        $direction = $this->time_drift_seconds > 0 ? 'lebih cepat' : 'lebih lambat';

        if ($abs < 60) {
            return "⚠️ {$abs} detik {$direction}";
        }

        $minutes = intdiv($abs, 60);
        $seconds = $abs % 60;

        if ($abs < 3600) {
            return "❌ {$minutes} menit {$seconds} detik {$direction}";
        }

        $hours = intdiv($abs, 3600);
        $remainMinutes = intdiv($abs % 3600, 60);
        return "❌ {$hours} jam {$remainMinutes} menit {$direction}";
    }

    /**
     * Get sync status color for badge.
     */
    public function getTimeSyncColorAttribute(): string
    {
        if (is_null($this->time_drift_seconds)) {
            return 'gray';
        }

        $abs = abs($this->time_drift_seconds);

        if ($abs <= 5) return 'success';    // ≤5 detik = sinkron
        if ($abs <= 60) return 'warning';   // ≤1 menit = warning
        return 'danger';                     // >1 menit = bahaya
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
