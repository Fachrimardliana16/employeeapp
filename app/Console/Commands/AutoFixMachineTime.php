<?php

namespace App\Console\Commands;

use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineCommand;
use Illuminate\Console\Command;

class AutoFixMachineTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-fix-time {--threshold=20 : Time drift threshold in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fix time drift on attendance machines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $this->info("Checking machines with time drift > {$threshold} seconds...");

        $machines = AttendanceMachine::whereNotNull('time_drift_seconds')
            ->whereRaw('ABS(time_drift_seconds) > ?', [$threshold])
            ->get();

        if ($machines->isEmpty()) {
            $this->info('✅ All machines are synchronized. No action needed.');
            return 0;
        }

        $this->warn("Found {$machines->count()} machines with time drift:");

        $headers = ['Mesin', 'SN', 'Lokasi', 'Selisih', 'Aksi'];
        $rows = [];

        foreach ($machines as $machine) {
            $rows[] = [
                $machine->name,
                $machine->serial_number,
                $machine->officeLocation?->name ?? '-',
                $machine->time_drift_label,
                '⏳ Scheduling restart...'
            ];
        }

        $this->table($headers, $rows);

        if (!$this->confirm('Send REBOOT command to all these machines?', true)) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $count = 0;
        foreach ($machines as $machine) {
            // Check if there's already a pending REBOOT command
            $hasPending = AttendanceMachineCommand::where('attendance_machine_id', $machine->id)
                ->where('status', 'pending')
                ->where('command', 'REBOOT')
                ->exists();

            if (!$hasPending) {
                AttendanceMachineCommand::create([
                    'attendance_machine_id' => $machine->id,
                    'command' => 'REBOOT',
                    'status' => 'pending',
                ]);
                $count++;
            }
        }

        $this->info("✅ REBOOT command sent to {$count} machines.");
        $this->info("Machines will restart and auto-sync time from server (TimeZone=7).");

        return 0;
    }
}
