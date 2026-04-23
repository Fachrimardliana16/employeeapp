<?php

use App\Models\AttendanceMachineLog;
use App\Models\EmployeeAttendanceRecord;
use App\Models\Employee;
use App\Models\AttendanceSchedule;
use App\Models\AttendanceMachine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

ini_set('memory_limit', '512M');
if (class_exists('Laravel\Telescope\Telescope')) {
    \Laravel\Telescope\Telescope::stopRecording();
}

echo "Memulai proses sinkronisasi ulang data absensi (ULTRA OPTIMIZED)...\n";

// Cache schedules and employees for performance
$schedules = AttendanceSchedule::where('is_active', true)->get()->keyBy('day');
$employees = Employee::whereNotNull('pin')->get()->keyBy('pin');
$machines = AttendanceMachine::all()->keyBy('serial_number');

$totalProcessed = 0;

AttendanceMachineLog::where('timestamp', '>=', '2026-01-01 00:00:00')
    ->orderBy('timestamp', 'asc')
    ->chunk(2000, function ($logs) use ($schedules, $employees, $machines, &$totalProcessed) {
        $upsertData = [];
        
        foreach ($logs as $log) {
            $pin = $log->pin;
            $time = Carbon::parse($log->timestamp);
            $type = $log->type;
            $sn = $log->serial_number;
            $dayName = strtolower($time->format('l'));

            $state = match($type) {
                '0' => 'check_in',
                '1' => 'check_out',
                '2' => 'break_out',
                '3' => 'break_in',
                '4' => 'ot_in',
                '5' => 'ot_out',
                default => 'check_in'
            };

            $status = 'on_time';
            $schedule = $schedules->get($dayName);
            if ($schedule) {
                if ($state === 'check_in' && $schedule->check_in_end) {
                    $checkInTime = $time->format('H:i:s');
                    if ($checkInTime > $schedule->check_in_end) {
                        $status = 'late';
                    }
                } elseif ($state === 'check_out' && $schedule->check_out_start) {
                    $checkOutTime = $time->format('H:i:s');
                    if ($checkOutTime < $schedule->check_out_start) {
                        $status = 'early';
                    }
                }
            }

            $employee = $employees->get($pin);
            $machine = $machines->get($sn);

            $upsertData[] = [
                'pin' => $pin,
                'employee_name' => $employee ? $employee->name : "Unknown (PIN: {$pin})",
                'attendance_time' => $time->toDateTimeString(),
                'state' => $state,
                'attendance_status' => $status,
                'verification' => '1',
                'device' => $machine ? $machine->name : $sn,
                'office_location_id' => $machine ? $machine->master_office_location_id : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($upsertData)) {
            DB::table('employee_attendance_records')->upsert(
                $upsertData, 
                ['pin', 'attendance_time', 'state'], // Unique keys
                ['employee_name', 'attendance_status', 'device', 'office_location_id', 'updated_at'] // Update these
            );
        }

        $totalProcessed += count($upsertData);
        echo "Telah menyinkronkan {$totalProcessed} rekaman...\n";
    });

echo "\nSelesai! Total data yang disinkronkan: {$totalProcessed}\n";
