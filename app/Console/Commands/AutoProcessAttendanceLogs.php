<?php

namespace App\Console\Commands;

use App\Models\AttendanceMachineLog;
use App\Models\Employee;
use App\Models\EmployeeAttendanceRecord;
use Illuminate\Console\Command;

class AutoProcessAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-process {--date= : Process logs for specific date (Y-m-d format)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically process attendance machine logs to attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? \Carbon\Carbon::parse($this->option('date')) : today();
        
        $this->info("Processing attendance logs for: {$date->format('Y-m-d')}");

        $logs = AttendanceMachineLog::whereDate('timestamp', $date)->get();

        if ($logs->isEmpty()) {
            $this->warn('No logs found for this date.');
            return 0;
        }

        $count = 0;
        $bar = $this->output->createProgressBar($logs->count());
        $bar->start();

        foreach ($logs as $record) {
            $employee = Employee::where('pin', $record->pin)->first();
            $state = match($record->type) {
                '0' => 'check_in', '1' => 'check_out', '2' => 'break_out',
                '3' => 'break_in', '4' => 'ot_in', '5' => 'ot_out', default => 'check_in'
            };
            
            EmployeeAttendanceRecord::updateOrCreate(
                ['pin' => $record->pin, 'attendance_time' => $record->timestamp->toDateTimeString(), 'state' => $state],
                [
                    'employee_name' => $employee ? $employee->name : "Unknown (PIN: {$record->pin})",
                    'attendance_status' => 'on_time',
                    'device' => $record->machine?->name,
                    'office_location_id' => $record->machine?->master_office_location_id,
                ]
            );
            $count++;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Processed {$count} attendance logs.");

        return 0;
    }
}
