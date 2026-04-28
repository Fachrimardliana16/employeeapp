<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineLog;
use App\Models\AttendanceMachineCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    /**
     * Get the standard handshake options to send to machines.
     * Always includes TimeZone=7 (WIB).
     */
    private function getHandshakeOptions(string $sn): string
    {
        return implode("\n", [
            "GET OPTION FROM: {$sn}",
            "Stamp=9999",
            "OpStamp=9999",
            "ErrorDelay=60",
            "Delay=1",
            "TransTimes=00:00;14:05",
            "TransInterval=1",
            "TransFlag=TransData AttLog OpLog AttPhoto EnrollUser ChgUser EnrollFP ChgFP FACE UserPic",
            "TimeZone=7",
            "GMTPlus=7",
            "Realtime=1",
            "PushVersion=3.0",
            "Encrypt=0",
        ]);
    }

    /**
     * Handle the handshake and data upload from machines.
     * URL: /iclock/cdata
     */
    public function cdata(Request $request)
    {
        $sn = $request->query('SN');
        Log::debug("ADMS cdata Request", ['SN' => $sn, 'IP' => $request->ip(), 'Method' => $request->method()]);
        
        if (!$sn) {
            return response("SN NOT FOUND", 400);
        }

        // DB operations wrapped in try-catch — if DB is temporarily unreachable,
        // we still send the handshake with TimeZone=7 so the machine clock stays correct.
        try {
            $machine = AttendanceMachine::where('serial_number', $sn)->first();
            
            if (!$machine) {
                $locationId = \App\Models\MasterOfficeLocation::first()?->id;
                
                if ($locationId) {
                    $machine = AttendanceMachine::create([
                        'serial_number' => $sn,
                        'name' => 'Auto Registered: ' . $sn,
                        'master_office_location_id' => $locationId,
                        'status' => 'online',
                        'ip_address' => $request->ip(),
                        'last_heard_at' => now(),
                    ]);
                    Log::info("Auto-registered new attendance machine: " . $sn);
                }
            } else {
                $machine->update([
                    'last_heard_at' => now(),
                    'ip_address' => $request->ip(),
                    'status' => 'online',
                ]);
            }

            // If it's a POST, it's data
            if ($request->isMethod('post')) {
                $table = $request->query('table');
                $content = $request->getContent();

                if ($table === 'ATTLOG') {
                    Log::info("ADMS ATTLOG Data Received", [
                        'SN' => $sn,
                        'content_length' => strlen($content),
                    ]);
                    $this->parseAttendanceLogs($machine, $sn, $content);
                }

                if ($table === 'USER') {
                    Log::info("ADMS USER Data Received", [
                        'SN' => $sn,
                        'content_length' => strlen($content),
                        'content_payload' => $content,
                    ]);
                    $this->parseUserLogs($machine, $sn, $content);
                }

                return response("OK");
            }
        } catch (\Exception $e) {
            Log::error("ADMS cdata DB Error (handshake still sent)", [
                'SN' => $sn,
                'error' => $e->getMessage(),
            ]);
            // Fall through to return handshake — TimeZone=7 is more important than DB
        }

        return response($this->getHandshakeOptions($sn));
    }

    /**
     * Handle the heartbeat/request from machines.
     * URL: /iclock/getrequest
     */
    public function getrequest(Request $request)
    {
        $sn = $request->query('SN');
        Log::debug("ADMS getrequest Heartbeat", ['SN' => $sn, 'IP' => $request->ip()]);
        
        if (!$sn) {
            return response("OK");
        }

        try {
            $machine = AttendanceMachine::where('serial_number', $sn)->first();
            if (!$machine) {
                $locationId = \App\Models\MasterOfficeLocation::first()?->id;
                if ($locationId) {
                    $machine = AttendanceMachine::create([
                        'serial_number' => $sn,
                        'name' => 'Auto Registered: ' . $sn,
                        'master_office_location_id' => $locationId,
                        'status' => 'online',
                        'ip_address' => $request->ip(),
                        'last_heard_at' => now(),
                    ]);
                }
            } else {
                $machine->update([
                    'last_heard_at' => now(),
                    'ip_address' => $request->ip(),
                    'status' => 'online',
                ]);
            }

            // Check for pending commands and timeout stale ones
            if ($machine) {
                // --- Timeout Detection: mark 'sent' commands older than 2 min as 'failed' ---
                AttendanceMachineCommand::where('attendance_machine_id', $machine->id)
                    ->where('status', 'sent')
                    ->where('sent_at', '<', now()->subMinutes(2))
                    ->update([
                        'status' => 'failed',
                        'completed_at' => now(),
                        'response_payload' => 'TIMEOUT: Mesin tidak merespons dalam 2 menit. Kemungkinan mesin tidak mendukung perintah ini.',
                    ]);

                $pendingCommand = AttendanceMachineCommand::where('attendance_machine_id', $machine->id)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($pendingCommand) {
                    $pendingCommand->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);

                    // Return command in ZKTeco format: C:ID:COMMAND
                    return response("C:{$pendingCommand->id}:{$pendingCommand->command}");
                }

                // --- Drift Detection from DB (runs max once per 5 minutes) ---
                // Compare latest ATTLOG timestamp (machine clock) vs created_at (server clock)
                $shouldCheck = !$machine->time_checked_at || 
                               $machine->time_checked_at->diffInMinutes(now()) >= 5;

                if ($shouldCheck) {
                    $latestLog = AttendanceMachineLog::where('attendance_machine_id', $machine->id)
                        ->whereNotNull('timestamp')
                        ->where('created_at', '>=', now()->subDay()) // Only recent logs
                        ->orderByDesc('created_at')
                        ->first();

                    if ($latestLog && $latestLog->timestamp && $latestLog->created_at) {
                        // timestamp = machine's clock when employee scanned
                        // created_at = server's clock when data was received
                        $machineTime = \Carbon\Carbon::parse($latestLog->timestamp);
                        $serverTime = $latestLog->created_at;

                        $driftSeconds = $machineTime->diffInSeconds($serverTime, false);
                        $driftSeconds = -$driftSeconds; // positive = machine ahead

                        $machine->update([
                            'machine_datetime' => $machineTime,
                            'time_checked_at' => now(),
                            'time_drift_seconds' => $driftSeconds,
                        ]);
                    }
                }
            }

            return response("OK");
        } catch (\Exception $e) {
            Log::error("ADMS getrequest DB Error (handshake fallback sent)", [
                'SN' => $sn,
                'error' => $e->getMessage(),
            ]);
            // If DB is down, fallback to handshake to ensure time sync is maintained
            return response($this->getHandshakeOptions($sn));
        }
    }

    /**
     * Handle the response/feedback of a command from the machine.
     * URL: /iclock/devicecmd
     */
    public function devicecmd(Request $request)
    {
        $sn = $request->query('SN');
        $id = $request->query('ID');
        $content = $request->getContent();
        
        Log::debug("ADMS devicecmd Feedback", ['SN' => $sn, 'ID' => $id, 'Return' => $content]);

        try {
            if ($id) {
                $command = AttendanceMachineCommand::find($id);
                if ($command) {
                    $command->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'response_payload' => $content,
                    ]);

                    // If this was an INFO command, parse the machine's datetime for sync verification
                    if (str_contains($command->command, 'INFO')) {
                        $this->parseInfoResponse($sn, $content);
                    }
                }
            }

            // Also handle unsolicited INFO responses (machine may send without command ID)
            if ($sn && str_contains($content, 'DateTime')) {
                $this->parseInfoResponse($sn, $content);
            }
        } catch (\Exception $e) {
            Log::error("ADMS devicecmd DB Error", [
                'SN' => $sn,
                'ID' => $id,
                'error' => $e->getMessage(),
            ]);
        }

        return response("OK");
    }

    /**
     * Parse INFO response from machine to extract DateTime and calculate drift.
     * Typical INFO response format varies by firmware:
     *   - "~ServerVer=...,DateTime=2026-04-27 19:51:09,..."
     *   - Key=Value pairs separated by comma or newline
     *   - "Return=0\nDateTime=2026-04-27 19:51:09"
     */
    private function parseInfoResponse(string $sn, string $content): void
    {
        $machine = AttendanceMachine::where('serial_number', $sn)->first();
        if (!$machine) return;

        $machineDateTime = null;

        // Try multiple patterns to extract DateTime from INFO response
        // Pattern 1: DateTime=YYYY-MM-DD HH:MM:SS (standard)
        if (preg_match('/DateTime[=:]\s*(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $content, $matches)) {
            $machineDateTime = $matches[1];
        }
        // Pattern 2: Date=YYYY-MM-DD and Time=HH:MM:SS (split)
        elseif (preg_match('/Date[=:]\s*(\d{4}-\d{2}-\d{2})/', $content, $dateMatch) &&
                preg_match('/Time[=:]\s*(\d{2}:\d{2}:\d{2})/', $content, $timeMatch)) {
            $machineDateTime = $dateMatch[1] . ' ' . $timeMatch[1];
        }
        // Pattern 3: Loose datetime anywhere in content
        elseif (preg_match('/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $content, $matches)) {
            $machineDateTime = $matches[1];
        }

        if ($machineDateTime) {
            try {
                $serverNow = now()->timezone('Asia/Jakarta');
                $machineTime = \Carbon\Carbon::parse($machineDateTime);
                
                // Calculate drift: positive = machine ahead, negative = machine behind
                $driftSeconds = $machineTime->diffInSeconds($serverNow, false);
                // diffInSeconds with absolute=false: server - machine
                // We want machine - server, so negate:
                $driftSeconds = -$driftSeconds;

                $machine->update([
                    'machine_datetime' => $machineTime,
                    'time_checked_at' => $serverNow,
                    'time_drift_seconds' => $driftSeconds,
                ]);

                Log::info("ADMS Time Sync Check", [
                    'SN' => $sn,
                    'machine_time' => $machineDateTime,
                    'server_time' => $serverNow->toDateTimeString(),
                    'drift_seconds' => $driftSeconds,
                ]);
            } catch (\Exception $e) {
                Log::warning("ADMS Failed to parse machine datetime", [
                    'SN' => $sn,
                    'raw_datetime' => $machineDateTime,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Parse the raw attendance logs from the machine.
     */
    private function parseAttendanceLogs($machine, $sn, $content)
    {
        $lines = explode("\n", $content);
        $processedCount = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            try {
                // ADMS format is tab separated: PIN	TIME	STATUS	VERIFY	SOURCE	RESERVED
                $data = explode("\t", $line);
                
                if (count($data) >= 2) {
                    $pin = $data[0];
                    $time = $data[1];
                    $type = $data[2] ?? '0'; 
                    $verify = $data[3] ?? '0';

                    // 1. Save to Raw Machine Logs
                    AttendanceMachineLog::updateOrCreate(
                        [
                            'serial_number' => $sn,
                            'pin' => $pin,
                            'timestamp' => $time,
                        ],
                        [
                            'attendance_machine_id' => $machine ? $machine->id : null,
                            'type' => $type,
                            'raw_payload' => $line,
                        ]
                    );

                    // 2. Synchronize to Employee Attendance Records
                    $employee = \App\Models\Employee::where('pin', $pin)->first();
                    if ($employee) {
                        $attendanceTime = \Carbon\Carbon::parse($time);
                        
                        $state = match($type) {
                            '0' => 'check_in',
                            '1' => 'check_out',
                            '2' => 'break_out',
                            '3' => 'break_in',
                            '4' => 'ot_in',
                            '5' => 'ot_out',
                            default => 'check_in'
                        };

                        // Fast update/create for attendance
                        \App\Models\Attendance::updateOrCreate(
                            [
                                'employee_id' => $employee->id,
                                'date' => $attendanceTime->toDateString(),
                                'state' => $state,
                            ],
                            [
                                'attendance_machine_id' => $machine ? $machine->id : null,
                                'time' => $attendanceTime->toTimeString(),
                                'raw_data' => $line,
                            ]
                        );
                    }
                    $processedCount++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to process individual ADMS log line", [
                    'SN' => $sn,
                    'line' => $line,
                    'error' => $e->getMessage()
                ]);
                // Continue to next line even if this one failed due to DB timeout
            }
        }

        Log::info("ADMS logs processed for {$sn}: {$processedCount} lines.");
    }
                    ->where('day', $dayName)
                    ->first();

                $status = 'on_time';
                if ($schedule) {
                    if ($state === 'check_in' && $schedule->check_in_end) {
                        $checkInTime = $attendanceTime->format('H:i:s');
                        if ($checkInTime > $schedule->check_in_end) {
                            $status = 'late';
                        }
                    } elseif ($state === 'check_out' && $schedule->check_out_start) {
                        $checkOutTime = $attendanceTime->format('H:i:s');
                        if ($checkOutTime < $schedule->check_out_start) {
                            $status = 'early';
                        }
                    }
                }

                // Create or Update Main Attendance Record
                // We use updateOrCreate to prevent exact duplicate logs in the main table if machine re-sends
                \App\Models\EmployeeAttendanceRecord::updateOrCreate(
                    [
                        'pin' => $pin,
                        'attendance_time' => $attendanceTime->toDateTimeString(),
                        'state' => $state,
                    ],
                    [
                        'employee_name' => $employee ? $employee->name : "Unknown (PIN: {$pin})",
                        'attendance_status' => $status,
                        'verification' => $verify,
                        'device' => $machine ? $machine->name : $sn,
                        'office_location_id' => $machine ? $machine->master_office_location_id : null,
                    ]
                );
            }
        }

        // --- Time Drift Detection from ATTLOG ---
        // Since Solution X105-ID doesn't support devicecmd feedback (INFO command),
        // we detect drift by comparing the latest ATTLOG timestamp with server time.
        // If a scan happened within the last 5 minutes, it's "live" and reflects the machine's clock.
        if ($machine) {
            $this->detectTimeDriftFromLogs($machine, $content);
        }
    }

    /**
     * Parse the raw user info from the machine.
     * Format: PIN\tName\tPassword\tGroup\tPrivilege\tCardNo
     */
    private function parseUserLogs($machine, $sn, $content)
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = explode("\t", $line);
            
            if (count($data) >= 2) {
                $pin = $data[0];
                $name = $data[1];

                // Logic: If we find an employee with the SAME NAME but WITHOUT PIN, 
                // or with a different PIN, we might want to update it.
                // For safety, let's only update if the employee has NO PIN yet.
                $employee = \App\Models\Employee::where('name', 'LIKE', $name)->first();
                
                if ($employee && empty($employee->pin)) {
                    $employee->update(['pin' => $pin]);
                    Log::info("Synced PIN {$pin} from machine to employee: {$name}");
                }
            }
        }
    }

    /**
     * Detect time drift by analyzing the most recent ATTLOG timestamp.
     * 
     * Strategy: If the latest log timestamp is very close to "now" (within ~2 hours
     * to account for possible drift), it's a live scan and we can measure drift.
     * The machine sends ATTLOG in real-time when Realtime=1, so the latest entry's
     * timestamp reflects the machine's current clock.
     */
    private function detectTimeDriftFromLogs(AttendanceMachine $machine, string $content): void
    {
        try {
            $lines = explode("\n", $content);
            $latestTimestamp = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $data = explode("\t", $line);
                if (count($data) >= 2) {
                    $time = \Carbon\Carbon::parse($data[1]);
                    if (!$latestTimestamp || $time->gt($latestTimestamp)) {
                        $latestTimestamp = $time;
                    }
                }
            }

            if (!$latestTimestamp) return;

            $serverNow = now()->timezone('Asia/Jakarta');
            
            // Only consider as "live" if the log is within 2 hours of server time
            // (to account for drift up to ~2 hours but reject old historical data)
            $absDiffMinutes = abs($serverNow->diffInMinutes($latestTimestamp));
            
            if ($absDiffMinutes <= 120) {
                // This is a live scan — calculate drift
                $driftSeconds = $latestTimestamp->diffInSeconds($serverNow, false);
                $driftSeconds = -$driftSeconds; // positive = machine ahead

                $machine->update([
                    'machine_datetime' => $latestTimestamp,
                    'time_checked_at' => $serverNow,
                    'time_drift_seconds' => $driftSeconds,
                ]);

                Log::info("ADMS Time Drift (from ATTLOG)", [
                    'SN' => $machine->serial_number,
                    'machine_time' => $latestTimestamp->toDateTimeString(),
                    'server_time' => $serverNow->toDateTimeString(),
                    'drift_seconds' => $driftSeconds,
                    'drift_label' => $machine->fresh()->time_drift_label,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("ADMS Drift detection failed", [
                'SN' => $machine->serial_number,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
