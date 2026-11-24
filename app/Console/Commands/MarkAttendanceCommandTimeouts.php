<?php

namespace App\Console\Commands;

use App\Models\AttendanceMachineCommand;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class MarkAttendanceCommandTimeouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-command-timeouts
                            {--minutes=2 : Timeout threshold in minutes}
                            {--chunk=200 : Number of commands to process per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark stale sent attendance machine commands as failed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $minutes = max(1, (int) $this->option('minutes'));
        $chunkSize = max(25, (int) $this->option('chunk'));
        $cutoff = now()->subMinutes($minutes);
        $totalUpdated = 0;

        while (true) {
            $ids = AttendanceMachineCommand::query()
                ->where('status', 'sent')
                ->whereNotNull('sent_at')
                ->where('sent_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($chunkSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $completedAt = now();
            $updated = $this->withDeadlockRetry(function () use ($ids, $completedAt) {
                return AttendanceMachineCommand::query()
                    ->whereIn('id', $ids)
                    ->where('status', 'sent')
                    ->update([
                        'status' => 'failed',
                        'completed_at' => $completedAt,
                        'response_payload' => 'TIMEOUT: Mesin tidak merespons dalam 2 menit. Kemungkinan mesin tidak mendukung perintah ini.',
                    ]);
            });

            $totalUpdated += $updated;

            if ($ids->count() < $chunkSize) {
                break;
            }
        }

        if ($totalUpdated > 0) {
            Log::info('ADMS timeout commands marked as failed by scheduler', [
                'updated' => $totalUpdated,
                'cutoff' => $cutoff->toDateTimeString(),
            ]);
        }

        $this->line("Updated timeout commands: {$totalUpdated}");

        return self::SUCCESS;
    }

    /**
     * Retry DB operation on deadlock/serialization failure with small backoff.
     */
    private function withDeadlockRetry(callable $callback, int $maxAttempts = 3)
    {
        $attempt = 1;

        while (true) {
            try {
                return $callback();
            } catch (QueryException $e) {
                $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
                $driverErrorCode = (int) ($e->errorInfo[1] ?? 0);
                $message = strtolower($e->getMessage());

                $isDeadlock = $sqlState === '40001'
                    || $driverErrorCode === 1213
                    || str_contains($message, 'deadlock found')
                    || str_contains($message, 'serialization failure');

                if (!$isDeadlock || $attempt >= $maxAttempts) {
                    throw $e;
                }

                usleep(50000 * $attempt); // 50ms, 100ms, ...
                $attempt++;
            }
        }
    }
}
