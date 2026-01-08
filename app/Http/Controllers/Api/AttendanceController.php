<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterOfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * Upload attendance photo from camera
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:5120', // Max 5MB
            'field' => 'required|in:photo_checkin,photo_checkout',
        ]);

        try {
            $file = $request->file('photo');
            $field = $request->input('field');

            // Generate unique filename
            $filename = Str::uuid() . '.jpg';
            $path = "attendance/{$field}/" . date('Y/m/d');

            // Store the file
            $storedPath = $file->storeAs($path, $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => $storedPath,
                'url' => Storage::url($storedPath),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah foto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate user location against office locations
     */
    public function validateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Get employee's department
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $employee = $user?->employee ?? null;
        $departmentId = $employee?->departments_id;

        // Find closest office location (filtered by department)
        $result = MasterOfficeLocation::getClosestLocation($latitude, $longitude, $departmentId);

        if (!$result) {
            return response()->json([
                'valid' => false,
                'message' => $departmentId
                    ? 'Tidak ada lokasi kantor aktif untuk departemen Anda.'
                    : 'Tidak ada lokasi kantor aktif yang ditemukan.',
            ]);
        }

        $location = $result['location'];
        $distance = $result['distance'];
        $withinRadius = $location->isWithinRadius($latitude, $longitude);

        // Check department restriction
        if ($location->departments_id && $departmentId && $location->departments_id != $departmentId) {
            return response()->json([
                'valid' => false,
                'message' => "Lokasi {$location->name} khusus untuk departemen {$location->department->name}.",
            ]);
        }

        return response()->json([
            'valid' => true,
            'office_id' => $location->id,
            'office_name' => $location->name,
            'department_name' => $location->department?->name ?? 'Semua Departemen',
            'distance' => (int) round($distance),
            'allowed_radius' => $location->radius,
            'within_radius' => $withinRadius,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}
