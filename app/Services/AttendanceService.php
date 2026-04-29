<?php

namespace App\Services;

use App\Models\AttendanceMachineLog;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceSpecialSchedule;
use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use App\Models\EmployeeBusinessTravelLetter;
use App\Models\MasterOfficeLocation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    // GPS anti-fake thresholds
    const MAX_GPS_ACCURACY = 150.0; // meter - tolak jika sinyal terlalu lemah
    const MIN_GPS_JITTER   = 0.3;   // meter - terlalu statis = mock GPS
    const MAX_SPEED_KMH    = 500.0; // km/h  - teleport → tolak
    const SUSPICIOUS_SPEED = 120.0; // km/h  - mencurigakan → flag

    // Indonesian day name mapping (matches AttendanceSchedule.day seeder)
    private const DAY_MAP = [
        'monday'    => 'Senin',
        'tuesday'   => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday'  => 'Kamis',
        'friday'    => 'Jumat',
        'saturday'  => 'Sabtu',
        'sunday'    => 'Minggu',
    ];

    /**
     * Get allowed attendance states for an employee at a given time (smart dropdown).
     *
     * Rules:
     *  - Dalam window masuk  → check_in
     *  - Dalam window pulang → check_out
     *  - Setelah jam pulang  → ot_in / ot_out (lembur)
     *  - Di luar semua window / tidak ada jadwal → tampilkan check_in + check_out (fallback)
     *  - Ada surat dinas aktif hari ini → +dl_in / dl_out
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getAllowedStates(Employee $employee, Carbon $now): array
    {
        $dayEng = strtolower($now->format('l'));
        $dayInd = self::DAY_MAP[$dayEng] ?? ucfirst($dayEng);

        // Cek jadwal khusus per-pegawai hari ini (override)
        $special = AttendanceSpecialSchedule::where('employee_id', $employee->id)
            ->whereDate('date', $now->toDateString())
            ->first();

        $schedule = null;
        if (!$special || $special->is_working) {
            $schedule = AttendanceSchedule::where('is_active', true)
                ->where(function ($q) use ($dayInd, $dayEng) {
                    $q->whereRaw('LOWER(day) = ?', [strtolower($dayInd)])
                        ->orWhereRaw('LOWER(day) = ?', [strtolower($dayEng)]);
                })
                ->first();
        }

        $states  = [];
        $timeStr = $now->format('H:i:s');

        if ($schedule) {
            $ciStart = $schedule->check_in_start;
            $ciEnd   = $schedule->check_in_end;
            $coStart = $schedule->check_out_start;
            $coEnd   = $schedule->check_out_end;

            if ($ciStart && $ciEnd && $timeStr >= $ciStart && $timeStr <= $ciEnd) {
                $states[] = ['value' => 'check_in', 'label' => '🟢 Masuk Kerja (Check In)  [' . substr($ciStart, 0, 5) . '–' . substr($ciEnd, 0, 5) . ']'];
            }

            if ($coStart && $coEnd && $timeStr >= $coStart && $timeStr <= $coEnd) {
                $states[] = ['value' => 'check_out', 'label' => '🔴 Pulang Kerja (Check Out)  [' . substr($coStart, 0, 5) . '–' . substr($coEnd, 0, 5) . ']'];
            }

            if ($coEnd && $timeStr > $coEnd) {
                $states[] = ['value' => 'ot_in',  'label' => '🟣 Lembur (Masuk)'];
                $states[] = ['value' => 'ot_out', 'label' => '🟣 Lembur (Pulang)'];
            }
        }

        // Fallback jika di luar semua window atau tidak ada jadwal
        if (empty($states)) {
            $states = [
                ['value' => 'check_in',  'label' => 'Masuk Kerja (Check In)'],
                ['value' => 'check_out', 'label' => 'Pulang Kerja (Check Out)'],
            ];
        }

        // Surat dinas aktif → tampilkan opsi Dinas Luar
        if ($this->hasActiveTravelLetter($employee, $now)) {
            $states[] = ['value' => 'dl_in',  'label' => '🔵 Dinas Luar (Berangkat)'];
            $states[] = ['value' => 'dl_out', 'label' => '🔵 Dinas Luar (Kembali)'];
        }

        return $states;
    }

    /**
     * Check if employee has an active business travel letter today.
     */
    public function hasActiveTravelLetter(Employee $employee, Carbon $date): bool
    {
        $dateStr = $date->toDateString();
        $excluded = ['rejected', 'ditolak', 'cancelled', 'dibatalkan'];

        if (EmployeeBusinessTravelLetter::where('employee_id', $employee->id)
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->whereNotIn('status', $excluded)
            ->exists()
        ) {
            return true;
        }

        // Cek sebagai pegawai tambahan
        return EmployeeBusinessTravelLetter::whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->whereNotIn('status', $excluded)
            ->where(function ($q) use ($employee) {
                $q->whereJsonContains('additional_employee_ids', (string) $employee->id)
                    ->orWhereJsonContains('additional_employee_ids', $employee->id);
            })
            ->exists();
    }

    /**
     * Validate GPS for online attendance — anti-fake berlapis.
     *
     * Layer 1: Akurasi GPS (sinyal lemah → tolak)
     * Layer 2: Jitter check (terlalu statis → suspected mock)
     * Layer 3: Radius kantor (di luar kantor → tolak)
     *
     * @return array{valid: bool, reason: string|null, distance: int|null, suspected_fake: bool, location: MasterOfficeLocation|null}
     */
    public function validateGps(
        Employee $employee,
        float $lat,
        float $lng,
        float $accuracy,
        float $jitter,
        ?int $officeLocationId = null
    ): array {
        $suspectedFake = false;
        $flagReasons   = [];

        // Layer 1: Akurasi terlalu buruk
        if ($accuracy > self::MAX_GPS_ACCURACY) {
            return [
                'valid'          => false,
                'reason'         => "Sinyal GPS terlalu lemah (akurasi: {$accuracy}m). Pindah ke area terbuka dan coba lagi.",
                'distance'       => null,
                'suspected_fake' => false,
                'location'       => null,
            ];
        }

        // Layer 2: Jitter terlalu kecil + akurasi terlalu bagus = mock GPS
        // GPS asli selalu bergerak ±2-20m. Jitter < 0.3m + akurasi < 10m = mencurigakan.
        if ($jitter < self::MIN_GPS_JITTER && $accuracy < 10.0) {
            $suspectedFake = true;
            $flagReasons[] = "Statis (jitter: {$jitter}m) — terindikasi GPS palsu";
        }

        // Layer 3: Radius kantor
        $distance = null;
        $location = null;

        if ($officeLocationId) {
            $location = MasterOfficeLocation::find($officeLocationId);
        }
        if (!$location) {
            $result   = MasterOfficeLocation::getClosestLocation($lat, $lng, $employee->departments_id);
            $location = $result ? $result['location'] : null;
        }

        if ($location && $location->latitude && $location->longitude) {
            $distance  = (int) round(MasterOfficeLocation::calculateDistance(
                (float) $location->latitude,
                (float) $location->longitude,
                $lat,
                $lng
            ));
            $maxRadius = $location->radius ?: 100;

            if ($distance > $maxRadius) {
                return [
                    'valid'          => false,
                    'reason'         => "Anda berada {$distance}m dari {$location->name} (batas: {$maxRadius}m).",
                    'distance'       => $distance,
                    'suspected_fake' => $suspectedFake,
                    'location'       => $location,
                ];
            }
        }

        return [
            'valid'          => true,
            'reason'         => !empty($flagReasons) ? implode('; ', $flagReasons) : null,
            'distance'       => $distance,
            'suspected_fake' => $suspectedFake,
            'location'       => $location,
        ];
    }

    /**
     * Layer 4: Speed/teleport anomaly check.
     * Bandingkan posisi saat ini vs absensi online terakhir 24 jam.
     *
     * @return array{ok: bool, reject: bool, message: string|null}
     */
    public function checkSpeedAnomaly(Employee $employee, float $lat, float $lng): array
    {
        if (!$employee->pin) return ['ok' => true, 'reject' => false, 'message' => null];

        $last = EmployeeAttendanceRecord::where('pin', $employee->pin)
            ->whereNotNull('check_latitude')
            ->whereNotNull('check_longitude')
            ->where('source', 'online')
            ->where('attendance_time', '>=', now()->subHours(24))
            ->latest('attendance_time')
            ->first();

        if (!$last) return ['ok' => true, 'reject' => false, 'message' => null];

        $distanceM       = MasterOfficeLocation::calculateDistance(
            (float) $last->check_latitude,
            (float) $last->check_longitude,
            $lat,
            $lng
        );
        $timeDiffSeconds = max(1, now()->diffInSeconds($last->attendance_time));
        $speedKmh        = ($distanceM / 1000) / ($timeDiffSeconds / 3600);

        if ($speedKmh > self::MAX_SPEED_KMH) {
            return [
                'ok'      => false,
                'reject'  => true,
                'message' => sprintf(
                    'Perpindahan tidak mungkin: %.0fkm dalam %s menit (%.0f km/jam).',
                    $distanceM / 1000,
                    round($timeDiffSeconds / 60),
                    $speedKmh
                ),
            ];
        }

        if ($speedKmh > self::SUSPICIOUS_SPEED) {
            return [
                'ok'      => true,
                'reject'  => false,
                'message' => sprintf(
                    'Lokasi mencurigakan: perpindahan %.0fkm dari absensi sebelumnya.',
                    $distanceM / 1000
                ),
            ];
        }

        return ['ok' => true, 'reject' => false, 'message' => null];
    }

    /**
     * Hitung attendance_status berdasarkan state + jadwal.
     */
    public function calculateAttendanceStatus(string $state, Carbon $attendanceTime): string
    {
        if (!in_array($state, ['check_in', 'check_out'])) return 'on_time';

        $dayEng = strtolower($attendanceTime->format('l'));
        $dayInd = self::DAY_MAP[$dayEng] ?? ucfirst($dayEng);

        $schedule = AttendanceSchedule::where('is_active', true)
            ->where(function ($q) use ($dayInd, $dayEng) {
                $q->whereRaw('LOWER(day) = ?', [strtolower($dayInd)])
                    ->orWhereRaw('LOWER(day) = ?', [strtolower($dayEng)]);
            })
            ->first();

        if (!$schedule) return 'on_time';

        $timeStr = $attendanceTime->format('H:i:s');

        if ($state === 'check_in') {
            $limit = $schedule->late_threshold ?: $schedule->check_in_end;
            return ($limit && $timeStr > $limit) ? 'late' : 'on_time';
        }

        return ($schedule->check_out_start && $timeStr < $schedule->check_out_start)
            ? 'early_out'
            : 'on_time';
    }


    public static function markDuplicates(Collection $records): Collection
    {
        // Process Records for Duplicates (ASC order for logical processing)
        $records = $records->sortBy('timestamp');
        $grouped = $records->groupBy(function ($item) {
            return $item->timestamp->toDateString() . '_' . $item->pin . '_' . $item->type;
        });

        foreach ($grouped as $group) {
            if ($group->count() > 1) {
                $type = (string)$group->first()->type;
                // Logic:
                // For IN (0, 3, 4), take the EARLIEST (min timestamp)
                // For OUT (1, 5), take the LATEST (max timestamp)
                if (in_array($type, ['0', '3', '4'])) {
                    $primaryId = $group->sortBy('timestamp')->first()->id;
                } elseif (in_array($type, ['1', '5'])) {
                    $primaryId = $group->sortByDesc('timestamp')->first()->id;
                } else {
                    $primaryId = $group->sortBy('timestamp')->first()->id;
                }

                foreach ($group as $log) {
                    $log->is_record_duplicate = ($log->id !== $primaryId);
                }
            } else if ($group->count() === 1) {
                $group->first()->is_record_duplicate = false;
            }
        }

        return $records;
    }

    /**
     * Get monthly attendance statistics for an employee from raw logs.
     */
    public static function getMonthlyStatsForEmployee(\App\Models\Employee $employee, int $month, int $year): array
    {
        $startOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $today = now()->isSameMonth($startOfMonth) ? now() : $endOfMonth;

        // 1. Fetch raw logs
        $logs = \App\Models\AttendanceMachineLog::where('pin', $employee->pin)
            ->whereMonth('timestamp', $month)
            ->whereYear('timestamp', $year)
            ->orderBy('timestamp', 'asc')
            ->get();

        // Mark duplicates to identifies "valid" entries
        $processedLogs = self::markDuplicates($logs);

        // 2. Presence & Late
        $validCheckIns = $processedLogs->where('is_record_duplicate', false)->where('type', '0');
        $presenceDays = $validCheckIns->pluck('timestamp')->map(fn($t) => $t->format('Y-m-d'))->unique();

        $schedules = \App\Models\AttendanceSchedule::where('is_active', true)->get();
        $lateCount = 0;
        foreach ($validCheckIns as $log) {
            $dayName = $log->timestamp->format('l');
            $sched = $schedules->where('day', $dayName)->first();
            $threshold = $sched ? $sched->late_threshold : '07:30:59';
            if ($log->timestamp->format('H:i:s') > $threshold) {
                $lateCount++;
            }
        }

        // 3. Permits/Leave
        $permits = $employee->employeePermissions()
            ->where('approval_status', 'approved')
            ->where(function ($q) use ($startOfMonth, $today) {
                $q->whereBetween('start_permission_date', [$startOfMonth, $today])
                    ->orWhereBetween('end_permission_date', [$startOfMonth, $today]);
            })
            ->get();

        $permitDays = $permits->sum(fn($p) => max(1, $p->start_permission_date->diffInDays($p->end_permission_date) + 1));

        // 4. Overtime
        $otIn = $processedLogs->where('is_record_duplicate', false)->where('type', '4');
        $otOut = $processedLogs->where('is_record_duplicate', false)->where('type', '5');

        $totalMinutes = 0;
        foreach ($otIn as $in) {
            $out = $otOut->where('timestamp', '>', $in->timestamp)
                ->where('timestamp', '<', $in->timestamp->copy()->endOfDay())
                ->first();
            if ($out) {
                $totalMinutes += $in->timestamp->diffInMinutes($out->timestamp);
            }
        }

        $hours = floor($totalMinutes / 60);
        $mins = $totalMinutes % 60;

        // 5. Absence
        // Count holidays and joint leave days for this employee
        $specialScheduleDays = \App\Models\AttendanceSpecialSchedule::where('employee_id', $employee->id)
            ->whereBetween('date', [$startOfMonth, $today])
            ->where('is_working', false) // Not required to work
            ->whereIn('type', ['libur_nasional', 'cuti_bersama'])
            ->count();

        $workDays = 0;
        $current = $startOfMonth->copy();
        while ($current <= $today) {
            if (!$current->isSunday()) $workDays++;
            $current->addDay();
        }

        // Subtract holidays from work days
        $effectiveWorkDays = max(0, $workDays - $specialScheduleDays);

        $absenceDays = max(0, $effectiveWorkDays - $presenceDays->count() - $permitDays);

        return [
            'presence' => $presenceDays->count(),
            'late' => $lateCount,
            'permit' => $permitDays,
            'holiday' => $specialScheduleDays,
            'absence' => $absenceDays,
            'overtime_count' => $otIn->count(),
            'overtime_hours' => "{$hours}j {$mins}m",
            'work_days' => $effectiveWorkDays,
        ];
    }
}
