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

        $machine = AttendanceMachine::where('serial_number', $sn)->first();
        
        if (!$machine) {
            // Find a valid office location ID dynamically
            $locationId = \App\Models\MasterOfficeLocation::first()?->id;
            
            if (!$locationId) {
                Log::error("ADMS Error: No office locations found in database. Cannot auto-register machine.");
                return response("LOCATION ERROR", 500);
            }

            // Auto-register unknown machine
            $machine = AttendanceMachine::create([
                'serial_number' => $sn,
                'name' => 'Auto Registered: ' . $sn,
                'master_office_location_id' => $locationId,
                'status' => 'online',
                'ip_address' => $request->ip(),
                'last_heard_at' => now(),
            ]);
            Log::info("Auto-registered new attendance machine: " . $sn);
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
                    'content_preview' => substr($content, 0, 200),
                ]);
                $this->parseAttendanceLogs($machine, $sn, $content);
            }
            // OPERLOG = operation logs (menu access, settings changes) - ignore silently
            // Other tables (FIRST, etc.) - ignore silently

            return response("OK");
        }

        // Handshake response
        return response("OK");
    }

    /**
     * Handle the heartbeat/request from machines.
     * URL: /iclock/getrequest
     */
    public function getrequest(Request $request)
    {
        $sn = $request->query('SN');
        Log::debug("ADMS getrequest Heartbeat", ['SN' => $sn, 'IP' => $request->ip()]);
        
        if ($sn) {
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

            // Check for pending commands
            if ($machine) {
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
            }
        }

        return response("OK");
    }

    /**
     * Handle the response/feedback of a command from the machine.
     * URL: /iclock/devicecmd
     */
    public function devicecmd(Request $request)
    {
        $sn = $request->query('SN');
        $id = $request->query('ID');
        
        Log::debug("ADMS devicecmd Feedback", ['SN' => $sn, 'ID' => $id, 'Return' => $request->getContent()]);

        if ($id) {
            $command = AttendanceMachineCommand::find($id);
            if ($command) {
                $command->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'response_payload' => $request->getContent()
                ]);
            }
        }

        return response("OK");
    }

    /**
     * Parse the raw attendance logs from the machine.
     */
    private function parseAttendanceLogs($machine, $sn, $content)
    {
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // ADMS format is tab separated: PIN	TIME	STATUS	VERIFY	SOURCE	RESERVED
            $data = explode("\t", $line);
            
            if (count($data) >= 2) {
                $pin = $data[0];
                $time = $data[1];
                $type = $data[2] ?? '0'; // 0=In, 1=Out, etc.
                $verify = $data[3] ?? '0';

                // 1. Save to Raw Machine Logs
                AttendanceMachineLog::create([
                    'attendance_machine_id' => $machine ? $machine->id : null,
                    'serial_number' => $sn,
                    'pin' => $pin,
                    'timestamp' => $time,
                    'type' => $type,
                    'raw_payload' => $line,
                ]);

                // 2. Synchronize to Employee Attendance Records
                $employee = \App\Models\Employee::where('pin', $pin)->first();
                if ($employee) {
                    $attendanceTime = \Carbon\Carbon::parse($time);
                    $dayName = strtolower($attendanceTime->format('l'));
                    
                    // Map machine type to system state
                    $state = match($type) {
                        '0' => 'check_in',
                        '1' => 'check_out',
                        '2' => 'break_out',
                        '3' => 'break_in',
                        '4' => 'ot_in',
                        '5' => 'ot_out',
                        default => 'check_in'
                    };

                    // Fetch Schedule for status calculation
                    $schedule = \App\Models\AttendanceSchedule::where('is_active', true)
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
                            'employee_name' => $employee->name,
                            'attendance_status' => $status,
                            'verification' => $verify,
                            'device' => $machine ? $machine->name : $sn,
                            'office_location_id' => $machine ? $machine->master_office_location_id : null,
                        ]
                    );
                }
            }
        }
    }
}
