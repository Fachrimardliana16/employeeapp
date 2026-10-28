<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceMachine;
use App\Models\AttendanceMachineCommand;

class AdmsRebootAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adms:reboot-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a REBOOT command for all attendance machines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $machines = AttendanceMachine::all();
        
        $count = 0;
        foreach ($machines as $machine) {
            AttendanceMachineCommand::create([
                'attendance_machine_id' => $machine->id,
                'command' => 'REBOOT',
                'status' => 'pending',
            ]);
            $count++;
            $this->info("Queued REBOOT for machine ID: {$machine->id} (SN: {$machine->serial_number})");
        }
        
        $this->info("Successfully queued REBOOT command for {$count} machines.");
    }
}
