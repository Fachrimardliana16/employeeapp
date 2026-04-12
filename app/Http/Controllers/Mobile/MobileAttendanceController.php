<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use App\Models\MasterOfficeLocation;
use App\Models\AttendanceSchedule;
use App\Models\EmployeeDailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobileAttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $this->getEmployee($user);

        $history = $employee
            ? $employee->attendanceRecords()->orderBy('attendance_time', 'desc')->limit(30)->get()
            : collect();

        $todayRecords = $employee
            ? $employee->attendanceRecords()->whereDate('attendance_time', today())->orderBy('attendance_time')->get()
            : collect();

        $schedule = $this->getTodaySchedule();

        return view('mobile.attendance.index', compact('employee', 'history', 'todayRecords', 'schedule'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'state'     => 'required|in:in,out,ot_in,ot_out',
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'picture'   => 'required|string',
        ], [
            'state.required'     => 'Status kehadiran wajib dipilih.',
            'latitude.required'  => 'Lokasi GPS wajib diaktifkan.',
            'longitude.required' => 'Lokasi GPS wajib diaktifkan.',
            'picture.required'   => 'Foto selfie wajib diambil.',
        ]);

        $user = Auth::user();
        $employee = $this->getEmployee($user);

        if (!$employee) {
            return back()->with('error', 'Data pegawai tidak ditemukan. Hubungi Admin HRD.');
        }

        // Schedule validation
        $schedule = $this->getTodaySchedule();
        $attendanceStatus = 'on_time';
        $currentTime = now()->format('H:i:s');
        $state = $request->state;

        if ($schedule) {
            if ($state === 'in') {
                if ($currentTime < $schedule->check_in_start || $currentTime > $schedule->check_in_end) {
                    return back()->with('error', 'Di luar jam check-in (' . $schedule->check_in_start . ' - ' . $schedule->check_in_end . ').');
                }
                if ($currentTime > $schedule->late_threshold) {
                    $attendanceStatus = 'late';
                }
            }
            if ($state === 'out') {
                if ($currentTime > $schedule->check_out_end) {
                    return back()->with('error', 'Sudah melewati jam check-out.');
                }
                if ($currentTime < $schedule->check_out_start) {
                    $attendanceStatus = 'early';
                }
            }
        }

        // Location validation
        $officeLocations = MasterOfficeLocation::where(function ($q) use ($employee) {
            $q->where('departments_id', $employee->departments_id)->orWhereNull('departments_id');
        })->where('is_active', true)->get();

        if ($officeLocations->isEmpty()) {
            return back()->with('error', 'Tidak ada lokasi kantor yang dikonfigurasi.');
        }

        $isInRange = false;
        $minDistance = null;
        $closestLocation = null;

        foreach ($officeLocations as $location) {
            $distance = EmployeeAttendanceRecord::calculateDistance(
                $request->latitude, $request->longitude,
                $location->latitude, $location->longitude
            );
            if ($minDistance === null || $distance < $minDistance) {
                $minDistance = $distance;
                $closestLocation = $location;
            }
            if ($distance <= $location->radius) {
                $isInRange = true;
                break;
            }
        }

        if (!$isInRange) {
            return back()->with('error', sprintf(
                'Lokasi Anda terlalu jauh (%.0fm dari kantor, maks %dm).',
                $minDistance,
                $closestLocation->radius
            ));
        }

        // Process photo
        $picturePath = null;
        if (str_starts_with($request->picture, 'data:image')) {
            $picturePath = $this->processImage($request->picture);
        }

        // Save
        DB::transaction(function () use ($employee, $request, $state, $picturePath, $closestLocation, $isInRange, $minDistance, $user, $attendanceStatus) {
            EmployeeAttendanceRecord::create([
                'pin'                => $employee->pin ?? $employee->id,
                'employee_name'      => $employee->name,
                'attendance_time'    => now(),
                'state'              => $state,
                'latitude'           => $request->latitude,
                'longitude'          => $request->longitude,
                'distance_meters'    => $minDistance,
                'picture'            => $picturePath,
                'office_location_id' => $closestLocation?->id,
                'check_latitude'     => $request->latitude,
                'check_longitude'    => $request->longitude,
                'distance_from_office' => (int) round($minDistance),
                'is_within_radius'   => $isInRange,
                'photo_checkin'      => in_array($state, ['in', 'ot_in']) ? $picturePath : null,
                'photo_checkout'     => in_array($state, ['out', 'ot_out']) ? $picturePath : null,
                'attendance_status'  => $attendanceStatus,
                'users_id'           => $user->id,
                'device'             => 'mobile_pwa',
            ]);
        });

        $labels = ['late' => 'Terlambat', 'early' => 'Terlalu Cepat', 'on_time' => 'Tepat Waktu'];
        $stateLabels = ['in' => 'Check In', 'out' => 'Check Out', 'ot_in' => 'Overtime In', 'ot_out' => 'Overtime Out'];

        return redirect()->route('mobile.attendance')
            ->with('success', $stateLabels[$state] . ' berhasil dicatat! (' . $labels[$attendanceStatus] . ')');
    }

    private function getEmployee($user): ?Employee
    {
        return Employee::where('users_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('office_email', $user->email)
            ->first();
    }

    private function getTodaySchedule(): ?AttendanceSchedule
    {
        return AttendanceSchedule::where('day', now()->format('l'))->where('is_active', true)->first();
    }

    private function processImage(string $base64Data): string
    {
        $data = explode(',', $base64Data);
        $decoded = base64_decode($data[1]);
        $img = imagecreatefromstring($decoded);

        $w = imagesx($img);
        $h = imagesy($img);
        $maxDim = 1024;

        if ($w > $maxDim || $h > $maxDim) {
            $ratio = $w / $h;
            $img = imagescale($img, $ratio > 1 ? $maxDim : (int)($maxDim * $ratio), $ratio > 1 ? (int)($maxDim / $ratio) : $maxDim);
        }

        $filename = 'attendance_' . time() . '_' . uniqid() . '.jpg';
        $dir = storage_path('app/public/attendance-photos');
        if (!file_exists($dir)) mkdir($dir, 0755, true);

        imagejpeg($img, $dir . '/' . $filename, 70);
        return 'attendance-photos/' . $filename;
    }
}
