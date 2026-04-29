<?php

namespace App\Console\Commands;

use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineCommand;
use Illuminate\Console\Command;

class AutoSyncAttendanceMachines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-sync {--all : Sync all machines regardless of online status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically sync attendance logs from all online machines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic attendance machine sync...');

        // Get machines based on option
        if ($this->option('all')) {
            $machines = AttendanceMachine::all();
            $this->info('Syncing ALL machines (online and offline)...');
        } else {
            // Only sync machines that are online (heard from in last 5 minutes)
            $machines = AttendanceMachine::where('last_heard_at', '>=', now()->subMinutes(5))->get();
            $this->info('Syncing only ONLINE machines...');
        }

        if ($machines->isEmpty()) {
            $this->warn('No machines found to sync.');
            return 0;
        }

        $count = 0;
        $bar = $this->output->createProgressBar($machines->count());
        $bar->start();

        foreach ($machines as $machine) {
            // Check if there's already a pending command
            $hasPending = AttendanceMachineCommand::where('attendance_machine_id', $machine->id)
                ->where('status', 'pending')
                ->where('command', 'DATA QUERY ATTLOG')
                ->exists();

            if (!$hasPending) {
                AttendanceMachineCommand::create([
                    'attendance_machine_id' => $machine->id,
                    'command' => 'DATA QUERY ATTLOG',
                    'status' => 'pending',
                ]);
                $count++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Sync command sent to {$count} machines.");
        $this->info("Total machines processed: {$machines->count()}");

        return 0;
    }
}
