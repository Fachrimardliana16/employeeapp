<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceSchedule extends Model
{
    protected $fillable = [
        'day',
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'late_threshold',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
