<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// SCHEDULED TASKS - Attendance Automation
// ==========================================

// Auto-sync all online machines every 30 minutes during work hours (07:00-17:00)
Schedule::command('attendance:auto-sync')
    ->everyThirtyMinutes()
    ->between('07:00', '17:00')
    ->weekdays()
    ->timezone('Asia/Jakarta')
    ->runInBackground()
    ->description('Auto-sync attendance logs from all online machines');

// Auto-process today's logs every hour during work hours
Schedule::command('attendance:auto-process')
    ->hourly()
    ->between('08:00', '18:00')
    ->weekdays()
    ->timezone('Asia/Jakarta')
    ->runInBackground()
    ->description('Auto-process today\'s attendance logs to records');

// Auto-fix machine time drift every day at 00:30 (after midnight)
Schedule::command('attendance:auto-fix-time --threshold=60')
    ->dailyAt('00:30')
    ->timezone('Asia/Jakarta')
    ->runInBackground()
    ->description('Auto-fix machines with time drift > 60 seconds');

// Check machine time sync status every 2 hours during work hours
Schedule::call(function () {
    $machines = \App\Models\AttendanceMachine::where('last_heard_at', '>=', now()->subMinutes(5))->get();

    foreach ($machines as $machine) {
        $hasPending = \App\Models\AttendanceMachineCommand::where('attendance_machine_id', $machine->id)
            ->where('status', 'pending')
            ->where('command', 'DATA QUERY INFO')
            ->exists();

        if (!$hasPending) {
            \App\Models\AttendanceMachineCommand::create([
                'attendance_machine_id' => $machine->id,
                'command' => 'DATA QUERY INFO',
                'status' => 'pending',
            ]);
        }
    }
})->everyTwoHours()
    ->between('07:00', '17:00')
    ->weekdays()
    ->timezone('Asia/Jakarta')
    ->name('check-machine-time-sync')
    ->description('Check time sync status on all online machines');

// Cleanup old attendance logs (> 1 year) every month on the 1st at 02:00
Schedule::call(function () {
    $cutoffDate = now()->subYear();
    $count = \App\Models\AttendanceMachineLog::where('timestamp', '<', $cutoffDate)->count();
    \App\Models\AttendanceMachineLog::where('timestamp', '<', $cutoffDate)->forceDelete();

    \Illuminate\Support\Facades\Log::info("Scheduled cleanup: Deleted {$count} old attendance logs.");
})->monthlyOn(1, '02:00')
    ->timezone('Asia/Jakarta')
    ->name('cleanup-old-logs')
    ->description('Cleanup attendance logs older than 1 year');

// Mark offline machines - Update status of machines not heard from in 5 minutes
Schedule::call(function () {
    \App\Models\AttendanceMachine::where('last_heard_at', '<', now()->subMinutes(5))
        ->where('status', 'online')
        ->update(['status' => 'offline']);
})->everyFiveMinutes()
    ->name('mark-offline-machines')
    ->description('Mark machines as offline if not heard from in 5 minutes');
