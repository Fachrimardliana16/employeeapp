<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineLog;
use App\Models\AttendanceMachineCommand;
use App\Models\AttendanceMachineCommunication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    /**
     * Log communication for troubleshooting and monitoring.
     * CRITICAL: This helps track communication issues and machine behavior.
     */
    private function logCommunication(
        string $sn,
        string $endpoint,
        Request $request,
        string $response,
        int $responseCode = 200,
        ?string $error = null,
        ?AttendanceMachine $machine = null
    ): void {
        try {
            AttendanceMachineCommunication::create([
                'attendance_machine_id' => $machine?->id,
                'serial_number' => $sn,
                'endpoint' => $endpoint,
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'request_params' => json_encode($request->query()),
                'request_body' => $request->method() === 'POST' ? substr($request->getContent(), 0, 10000) : null, // Limit to 10KB
                'response_body' => substr($response, 0, 5000), // Limit to 5KB
                'response_code' => $responseCode,
                'error_message' => $error,
            ]);

            // Update machine communication stats
            if ($machine) {
                if ($error) {
                    $machine->increment('communication_error_count');
                    $machine->update([
                        'last_error_at' => now(),
                        'last_error_message' => substr($error, 0, 500),
                    ]);
                } else {
                    $machine->increment('communication_success_count');
                }
            }
        } catch (\Exception $e) {
            // Don't fail the request if logging fails
            Log::error("Failed to log communication", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get the standard handshake options to send to machines.
     *
     * IMPORTANT NOTES:
     * 1. NEVER send commands that delete data (CLEAR DATA, CLEAR ATTLOG, etc.)
     * 2. TimeZone setting is OPTIONAL and controlled per-machine via auto_sync_time flag
     * 3. Different machine types handle TimeZone differently - some auto-adjust, causing +1 hour drift
     */
    private function getHandshakeOptions(string $sn, ?AttendanceMachine $machine = null): string
    {
        $options = [
            "GET OPTION FROM: {$sn}",
            "Stamp=9999",
            "OpStamp=9999",
            "ErrorDelay=60",
            "Delay=1",
            "TransTimes=00:00;14:05",
            "TransInterval=1",
            "TransFlag=TransData AttLog OpLog AttPhoto EnrollUser ChgUser EnrollFP ChgFP FACE UserPic",
        ];

        // CRITICAL: Only send timezone if machine has auto_sync_time enabled
        // Some machines (like newer models) auto-adjust timezone causing +1 hour drift
        // Default behavior: DON'T send timezone, let machine use its own clock
        if ($machine && $machine->auto_sync_time) {
            $timezone = $machine->timezone_offset ?? '7'; // Default WIB = +7
            $options[] = "TimeZone={$timezone}";
            $options[] = "GMTPlus={$timezone}";

            Log::info("ADMS TimeZone SENT (auto_sync enabled)", [
                'SN' => $sn,
                'timezone' => $timezone,
            ]);
        } else {
            Log::info("ADMS TimeZone NOT SENT (machine uses own clock)", [
                'SN' => $sn,
                'reason' => $machine ? 'auto_sync_time disabled' : 'machine not in DB',
            ]);
        }

        $options[] = "Realtime=1";
        $options[] = "PushVersion=3.0";
        $options[] = "Encrypt=0";

        // SAFETY: Never include CLEAR commands
        // $options[] = "CLEAR DATA";  // NEVER!
        // $options[] = "CLEAR ATTLOG"; // NEVER!

        return implode("\n", $options);
    }

    /**
     * Handle the handshake and data upload from machines.
     * URL: /iclock/cdata
     */
    public function cdata(Request $request)
    {
        $sn = $request->query('SN');
        $response = '';
        $error = null;

        Log::debug("ADMS cdata Request", [
            'SN' => $sn,
            'IP' => $request->ip(),
            'Method' => $request->method(),
            'Query' => $request->query(),
        ]);

        if (!$sn) {
            $error = "SN NOT FOUND in request";
            $this->logCommunication($sn ?? 'UNKNOWN', 'cdata', $request, $error, 400, $error);
            return response($error, 400);
        }

        // DB operations wrapped in try-catch — if DB is temporarily unreachable,
        // we still send the handshake so machine stays connected
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
                        'auto_sync_time' => false, // Default: DON'T sync time automatically
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

            // If it's a POST, it's data upload
            if ($request->isMethod('post')) {
                $table = $request->query('table');
                $content = $request->getContent();

                if ($table === 'ATTLOG') {
                    Log::info("ADMS ATTLOG Data Received", [
                        'SN' => $sn,
                        'content_length' => strlen($content),
                        'lines' => substr_count($content, "\n"),
                    ]);
                    $this->parseAttendanceLogs($machine, $sn, $content);
                }

                if ($table === 'USER') {
                    Log::info("ADMS USER Data Received", [
                        'SN' => $sn,
                        'content_length' => strlen($content),
                        'sample' => substr($content, 0, 200),
                    ]);
                    $this->parseUserLogs($machine, $sn, $content);
                }

                $response = "OK";
                $this->logCommunication($sn, 'cdata', $request, $response, 200, null, $machine);
                return response($response);
            }
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
            Log::error("ADMS cdata DB Error (handshake still sent)", [
                'SN' => $sn,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Fall through to return handshake — Connection is more important than DB
        }

        // Return handshake options
        $response = $this->getHandshakeOptions($sn, $machine ?? null);
        $this->logCommunication($sn, 'cdata', $request, $response, 200, $error, $machine ?? null);
        return response($response);
    }

    /**
     * Handle the heartbeat/request from machines.
     * URL: /iclock/getrequest
     */
    public function getrequest(Request $request)
    {
        $sn = $request->query('SN');
        $response = '';
        $error = null;

        Log::debug("ADMS getrequest Heartbeat", [
            'SN' => $sn,
            'IP' => $request->ip(),
            'Query' => $request->query(),
        ]);

        if (!$sn) {
            $this->logCommunication($sn ?? 'UNKNOWN', 'getrequest', $request, "OK", 200, "No SN provided");
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
                        'auto_sync_time' => false,
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
                    $response = "C:{$pendingCommand->id}:{$pendingCommand->command}";

                    Log::info("ADMS Command Sent", [
                        'SN' => $sn,
                        'command_id' => $pendingCommand->id,
                        'command' => $pendingCommand->command,
                    ]);

                    $this->logCommunication($sn, 'getrequest', $request, $response, 200, null, $machine);
                    return response($response);
                }

                // --- Realtime Time Sync Check (Auto-polling every 1 minute) ---
                $shouldAskTime = !$machine->time_checked_at ||
                    $machine->time_checked_at->diffInMinutes(now()) >= 1;

                if ($shouldAskTime) {
                    // 1. Actively ask the machine for its current time
                    $alreadyAsked = AttendanceMachineCommand::where('attendance_machine_id', $machine->id)
                        ->where('command', 'DATA QUERY INFO')
                        ->where('status', 'pending')
                        ->exists();

                    if (!$alreadyAsked) {
                        AttendanceMachineCommand::create([
                            'attendance_machine_id' => $machine->id,
                            'command' => "DATA QUERY INFO",
                            'status' => 'pending',
                        ]);
                    }

                    // 2. Passive Fallback: Check recent logs in case machine doesn't reply to INFO
                    $latestLog = AttendanceMachineLog::where('attendance_machine_id', $machine->id)
                        ->whereNotNull('timestamp')
                        ->where('created_at', '>=', now()->subDay())
                        ->orderByDesc('created_at')
                        ->first();

                    if ($latestLog && $latestLog->timestamp && $latestLog->created_at) {
                        $machineTime = \Carbon\Carbon::parse($latestLog->timestamp);
                        $serverTime = $latestLog->created_at;

                        // Calculate drift: machine - server
                        $driftSeconds = $serverTime->diffInSeconds($machineTime, false);

                        $machine->update([
                            'machine_datetime' => $machineTime,
                            'time_checked_at' => now(), // Mark as checked
                            'time_drift_seconds' => $driftSeconds,
                        ]);
                    } else {
                        // Mark as checked to prevent loop
                        $machine->update(['time_checked_at' => now()]);
                    }
                }
            }

            $response = "OK";
            $this->logCommunication($sn, 'getrequest', $request, $response, 200, null, $machine);
            return response($response);
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
            Log::error("ADMS getrequest DB Error (handshake fallback sent)", [
                'SN' => $sn,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // If DB is down, fallback to handshake to ensure connection maintained
            $response = $this->getHandshakeOptions($sn, $machine ?? null);
            $this->logCommunication($sn, 'getrequest', $request, $response, 200, $error, $machine ?? null);
            return response($response);
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
        $response = "OK";
        $error = null;

        Log::debug("ADMS devicecmd Feedback", [
            'SN' => $sn,
            'ID' => $id,
            'Return' => substr($content, 0, 500), // Log first 500 chars
            'IP' => $request->ip(),
        ]);

        try {
            $machine = AttendanceMachine::where('serial_number', $sn)->first();

            if ($id) {
                $command = AttendanceMachineCommand::find($id);
                if ($command) {
                    $command->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'response_payload' => $content,
                    ]);

                    Log::info("ADMS Command Completed", [
                        'SN' => $sn,
                        'command_id' => $id,
                        'command' => $command->command,
                        'response_length' => strlen($content),
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

            $this->logCommunication($sn, 'devicecmd', $request, $response, 200, null, $machine);
        } catch (\Exception $e) {
            $error = "DB Error: " . $e->getMessage();
            Log::error("ADMS devicecmd DB Error", [
                'SN' => $sn,
                'ID' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->logCommunication($sn, 'devicecmd', $request, $response, 200, $error, $machine ?? null);
        }

        return response($response);
    }

    /**
     * Parse INFO response from machine.
     * Extracts: DateTime, DeviceName/Model, FirmwareVersion, SerialNumber, UserCount, etc.
     *
     * Firmware variations (different brands respond differently):
     *   ZKTeco  : "~ServerVer=2.4.1\nDeviceName=BIO800\nDateTime=2026-04-28 10:00:00"
     *   Virdi   : "Return=0\nDateTime=2026-04-28 10:00:00\nDeviceName=AC2100"
     *   Solution: "SN=ABC123\nDateTime=2026-04-28 10:00:00\n"
     */
    private function parseInfoResponse(string $sn, string $content): void
    {
        $machine = AttendanceMachine::where('serial_number', $sn)->first();
        if (!$machine) return;

        $updateData = [];

        Log::debug("ADMS parseInfoResponse raw content", [
            'SN' => $sn,
            'content' => substr($content, 0, 2000),
        ]);

        // --- Extract Device Info fields ---
        $infoFields = [
            // DeviceName / Model
            'DeviceName'   => '/DeviceName[=:]\s*([^\r\n,]+)/i',
            'FWVersion'    => '/FWVersion[=:]\s*([^\r\n,]+)/i',
            'Platform'     => '/Platform[=:]\s*([^\r\n,]+)/i',
            'OEMVendor'    => '/OEMVendor[=:]\s*([^\r\n,]+)/i',
            'MAC'          => '/MAC[=:]\s*([0-9a-fA-F:]+)/i',
            'UserCount'    => '/UserCount[=:]\s*(\d+)/i',
            'AttLogCount'  => '/AttLogCount[=:]\s*(\d+)/i',
            'FPCount'      => '/FPCount[=:]\s*(\d+)/i',
        ];

        $parsedInfo = [];
        foreach ($infoFields as $field => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $parsedInfo[$field] = trim($matches[1]);
            }
        }

        // Build device_model from available fields
        if (!empty($parsedInfo['DeviceName'])) {
            $modelParts = [$parsedInfo['DeviceName']];
            if (!empty($parsedInfo['FWVersion'])) $modelParts[] = 'FW:' . $parsedInfo['FWVersion'];
            $deviceModel = implode(' | ', $modelParts);
            if ($machine->device_model !== $deviceModel) {
                $updateData['device_model'] = $deviceModel;
            }
        } elseif (!empty($parsedInfo['Platform'])) {
            $updateData['device_model'] = $parsedInfo['Platform'];
        }

        Log::info("ADMS Info Parsed", array_merge(['SN' => $sn], $parsedInfo));

        // --- Extract DateTime (multiple patterns for different firmware) ---
        $machineDateTime = null;

        // Pattern 1: DateTime=YYYY-MM-DD HH:MM:SS
        if (preg_match('/DateTime[=:]\s*(\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}:\d{2})/i', $content, $m)) {
            $machineDateTime = $m[1];
        }
        // Pattern 2: Date=YYYY-MM-DD and Time=HH:MM:SS separately
        elseif (
            preg_match('/\bDate[=:]\s*(\d{4}-\d{2}-\d{2})/i', $content, $dm) &&
            preg_match('/\bTime[=:]\s*(\d{2}:\d{2}:\d{2})/i', $content, $tm)
        ) {
            $machineDateTime = $dm[1] . ' ' . $tm[1];
        }
        // Pattern 3: Any ISO datetime in content (fallback)
        elseif (preg_match('/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})/', $content, $m)) {
            $machineDateTime = $m[1];
        }

        if ($machineDateTime) {
            try {
                $serverNow = now()->timezone('Asia/Jakarta');
                $machineTime = \Carbon\Carbon::parse($machineDateTime)->timezone('Asia/Jakarta');

                // positive = machine ahead of server, negative = machine behind server
                $driftSeconds = $machineTime->diffInSeconds($serverNow, false) * -1;

                $updateData['machine_datetime']    = $machineTime;
                $updateData['time_checked_at']     = $serverNow;
                $updateData['time_drift_seconds']  = $driftSeconds;

                Log::info("ADMS Time Sync Check", [
                    'SN'            => $sn,
                    'machine_time'  => $machineTime->toDateTimeString(),
                    'server_time'   => $serverNow->toDateTimeString(),
                    'drift_seconds' => $driftSeconds,
                    'drift_label'   => $machine->fresh()->time_drift_label ?? '',
                ]);
            } catch (\Exception $e) {
                Log::warning("ADMS Failed to parse machine datetime", [
                    'SN'           => $sn,
                    'raw_datetime' => $machineDateTime,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        if (!empty($updateData)) {
            $machine->update($updateData);
        }
    }

    /**
     * Parse the raw attendance logs from the machine.
     *
     * IMPORTANT RULES:
     * 1. NEVER send CLEAR/DELETE commands to machine — data stays on machine
     * 2. Use updateOrCreate to avoid duplicates but preserve existing data
     * 3. Type field: 0=Check In, 1=Check Out, 2=Break Out, 3=Break In, 4=OT In, 5=OT Out
     */
    private function parseAttendanceLogs($machine, $sn, $content)
    {
        $lines = explode("\n", $content);
        $processedCount = 0;
        $errorCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            try {
                // Format: PIN\tTimestamp\tType\tVerifyMethod\tWorkCode\tReserved
                $data = explode("\t", $line);
                if (count($data) < 2) continue;

                $pin    = trim($data[0]);
                $time   = trim($data[1]);
                $type   = trim($data[2] ?? '0');
                $verify = trim($data[3] ?? '0');

                if (empty($pin) || empty($time)) continue;

                // Store raw log — updateOrCreate prevents duplicates
                // NEVER sends CLEAR command back to machine
                AttendanceMachineLog::updateOrCreate(
                    [
                        'serial_number' => $sn,
                        'pin'           => $pin,
                        'timestamp'     => $time,
                    ],
                    [
                        'attendance_machine_id' => $machine?->id,
                        'type'                  => $type,
                        'raw_payload'           => $line,
                    ]
                );

                // Sync to main attendance table
                $attendanceTime = \Carbon\Carbon::parse($time);
                $state = match ($type) {
                    '0' => 'check_in',
                    '1' => 'check_out',
                    '2' => 'break_out',
                    '3' => 'break_in',
                    '4' => 'ot_in',
                    '5' => 'ot_out',
                    default => 'check_in'
                };

                $employee = \App\Models\Employee::where('pin', $pin)->first();
                \App\Models\EmployeeAttendanceRecord::updateOrCreate(
                    [
                        'pin'             => $pin,
                        'attendance_time' => $attendanceTime->toDateTimeString(),
                        'state'           => $state,
                    ],
                    [
                        'employee_name'     => $employee ? $employee->name : "Unknown (PIN: {$pin})",
                        'attendance_status' => 'on_time',
                        'verification'      => $verify,
                        'device'            => $machine ? $machine->name : $sn,
                        'office_location_id' => $machine?->master_office_location_id,
                    ]
                );

                $processedCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("ADMS Line Parse Error", [
                    'SN'    => $sn,
                    'line'  => $line,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("ADMS ATTLOG processed", [
            'SN'         => $sn,
            'total'      => count($lines),
            'processed'  => $processedCount,
            'errors'     => $errorCount,
        ]);

        if ($machine) {
            $this->detectTimeDriftFromLogs($machine, $content);
        }
    }

    /**
     * Parse user info from machine.
     * Format: PIN\tName\tPassword\tGroup\tPrivilege\tCardNo
     *
     * SAFETY: Only updates employee PIN if they don't have one yet.
     * Never deletes or overwrites existing employee data.
     */
    private function parseUserLogs($machine, $sn, $content)
    {
        $lines = explode("\n", $content);
        $syncedCount = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $data = explode("\t", $line);
            if (count($data) < 2) continue;

            $pin  = trim($data[0]);
            $name = trim($data[1]);

            if (empty($pin) || empty($name)) continue;

            // Only update employee PIN if they don't have one yet (safe operation)
            $employee = \App\Models\Employee::where('name', 'LIKE', $name)->first();
            if ($employee && empty($employee->pin)) {
                $employee->update(['pin' => $pin]);
                $syncedCount++;
                Log::info("ADMS Synced PIN from machine", [
                    'SN'   => $sn,
                    'pin'  => $pin,
                    'name' => $name,
                ]);
            }
        }

        Log::info("ADMS USER sync complete", [
            'SN'     => $sn,
            'synced' => $syncedCount,
            'total'  => count($lines),
        ]);
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
