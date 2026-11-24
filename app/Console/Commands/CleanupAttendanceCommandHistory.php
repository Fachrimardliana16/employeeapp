<?php

namespace App\Console\Commands;

use App\Models\AttendanceMachineCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupAttendanceCommandHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:cleanup-command-history
                            {--days=60 : Delete completed/failed command history older than N days}
                            {--chunk=500 : Number of rows to delete per batch}
                            {--max-batches=20 : Safety limit for deletion loops per run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old attendance machine command history to keep table size manageable';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $chunk = max(100, (int) $this->option('chunk'));
        $maxBatches = max(1, (int) $this->option('max-batches'));
        $cutoff = now()->subDays($days);
        $deletedTotal = 0;

        for ($batch = 1; $batch <= $maxBatches; $batch++) {
            $ids = AttendanceMachineCommand::query()
                ->whereIn('status', ['completed', 'failed'])
                ->where(function ($query) use ($cutoff) {
                    $query->where('completed_at', '<', $cutoff)
                        ->orWhere(function ($nested) use ($cutoff) {
                            $nested->whereNull('completed_at')
                                ->where('updated_at', '<', $cutoff);
                        });
                })
                ->orderBy('id')
                ->limit($chunk)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted = AttendanceMachineCommand::query()
                ->whereIn('id', $ids)
                ->delete();

            $deletedTotal += $deleted;

            if ($ids->count() < $chunk) {
                break;
            }
        }

        if ($deletedTotal > 0) {
            Log::info('ADMS old command history cleaned', [
                'deleted' => $deletedTotal,
                'older_than_days' => $days,
                'cutoff' => $cutoff->toDateTimeString(),
            ]);
        }

        $this->line("Deleted command rows: {$deletedTotal}");

        return self::SUCCESS;
    }
}
