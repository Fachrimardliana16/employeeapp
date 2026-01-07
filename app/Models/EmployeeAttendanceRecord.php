<?php

namespace App\Models;

use App\Traits\HasUserTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAttendanceRecord extends Model
{
    use HasUserTracking;

    protected $fillable = [
        'pin',
        'employee_name',
        'attendance_time',
        'state',
        'latitude',
        'longitude',
        'location_address',
        'distance_meters',
        'verification',
        'work_code',
        'reserved',
        'device',
        'picture',
        'users_id',
    ];

    protected $casts = [
        'attendance_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'distance_meters' => 'decimal:2',
    ];

    /**
     * Check if attendance is within allowed distance
     */
    public function isWithinAllowedDistance(float $maxDistanceMeters = 100): bool
    {
        if ($this->distance_meters === null) {
            return true; // No distance validation if not set
        }

        return $this->distance_meters <= $maxDistanceMeters;
    }

    /**
     * Calculate distance from office location
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos($latFrom) * cos($latTo) *
            sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get the employee that owns the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pin', 'pin');
    }

    /**
     * Get the user who created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
