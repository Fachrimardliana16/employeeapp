<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUserTracking;

class MasterOfficeLocation extends Model
{
    use SoftDeletes, HasUserTracking, \Spatie\Activitylog\Traits\LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'address',
        'latitude',
        'longitude',
        'radius',
        'departments_id',
        'description',
        'is_active',
        'users_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDepartment::class, 'departments_id');
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in meters
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Check if given coordinates are within this location's radius
     */
    public function isWithinRadius($latitude, $longitude): bool
    {
        $distance = self::calculateDistance(
            $this->latitude,
            $this->longitude,
            $latitude,
            $longitude
        );

        return $distance <= $this->radius;
    }

    /**
     * Get the closest office location to given coordinates
     * Optionally filter by department
     */
    public static function getClosestLocation($latitude, $longitude, $departmentId = null)
    {
        $query = self::where('is_active', true);

        // Filter by department if provided
        if ($departmentId) {
            $query->where(function ($q) use ($departmentId) {
                $q->where('departments_id', $departmentId)
                    ->orWhereNull('departments_id'); // Allow locations without department restriction
            });
        }

        $locations = $query->get();

        if ($locations->isEmpty()) {
            return null;
        }

        $closestLocation = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($locations as $location) {
            $distance = self::calculateDistance(
                $location->latitude,
                $location->longitude,
                $latitude,
                $longitude
            );

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestLocation = $location;
            }
        }

        return [
            'location' => $closestLocation,
            'distance' => $minDistance,
        ];
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
