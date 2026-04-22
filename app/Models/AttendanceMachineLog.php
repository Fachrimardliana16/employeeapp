<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceMachineLog extends Model
{
    use SoftDeletes;
    // LogsActivityTrait dihapus: machine logs sudah merupakan log dari mesin absensi
    // volume sangat tinggi (ratusan per sync), tidak perlu di-log ulang
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
