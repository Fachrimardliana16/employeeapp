<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineLog;
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
        
        if (!$sn) {
            return response("SN NOT FOUND", 400);
        }

        // Identify or register machine if it doesn't exist?
        // For security, maybe only allowed pre-registered SNs.
        // But for this initial phase, we'll auto-update last_heard_at.
        $machine = AttendanceMachine::where('serial_number', $sn)->first();
        
        if ($machine) {
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
                $this->parseAttendanceLogs($machine, $sn, $content);
            }

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
        
        if ($sn) {
            AttendanceMachine::where('serial_number', $sn)->update([
                'last_heard_at' => now(),
                'ip_address' => $request->ip(),
                'status' => 'online',
            ]);
        }

        // For now, no commands to send to the machine
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
                AttendanceMachineLog::create([
                    'attendance_machine_id' => $machine ? $machine->id : null,
                    'serial_number' => $sn,
                    'pin' => $data[0],
                    'timestamp' => $data[1],
                    'type' => $data[2] ?? '0',
                    'raw_payload' => $line,
                ]);
            }
        }
    }
}
