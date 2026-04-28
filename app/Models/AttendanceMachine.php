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

        if ($abs === 0) {
            return '✅ Sinkron';
        }

        $direction = $this->time_drift_seconds > 0 ? 'lebih cepat' : 'lebih lambat';

        $minutes = intdiv($abs, 60);
        $seconds = $abs % 60;

        if ($abs < 60) {
            return "⚠️ {$abs} detik {$direction}";
        }

        if ($abs < 3600) {
            return "⚠️ {$minutes} menit {$seconds} detik {$direction}";
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

        if ($abs === 0) return 'success';    // Hanya 0 detik yang hijau
        if ($abs < 3600) return 'warning';   // Selisih berapapun di bawah 1 jam = warning (kuning)
        return 'danger';                     // >1 jam = bahaya (merah)
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(MasterOfficeLocation::class, 'master_office_location_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceMachineLog::class, 'attendance_machine_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(AttendanceMachineCommand::class, 'attendance_machine_id');
    }
}
