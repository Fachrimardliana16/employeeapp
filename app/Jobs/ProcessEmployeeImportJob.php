<?php

namespace App\Jobs;

use App\Filament\Employee\Resources\EmployeeResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessEmployeeImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        public int $userId,
        public int $offset = 0,
        public int $chunkSize = 25
    ) {
        $this->userId = max(0, $this->userId);
        $this->offset = max(0, $this->offset);
        $this->chunkSize = max(1, min(100, $this->chunkSize));
    }

    public function handle(): void
    {
        $previewPayload = EmployeeResource::getEmployeeImportPreviewPayload($this->userId);
        $processedData = $previewPayload['processed_data'] ?? [];
        $warnings = $previewPayload['warnings'] ?? [];

        if (empty($processedData)) {
            $this->markFailedStatus('Data preview import tidak ditemukan atau sudah kedaluwarsa.');
            return;
        }

        $totalRows = count($processedData);
        $executionPayload = EmployeeResource::getEmployeeImportExecutionPayload($this->userId);

        if (($executionPayload['status'] ?? null) !== 'processing') {
            EmployeeResource::setEmployeeImportExecutionPayload([
                'status' => 'processing',
                'queued_at' => $executionPayload['queued_at'] ?? now()->toIso8601String(),
                'started_at' => now()->toIso8601String(),
                'total_rows' => $totalRows,
                'processed_rows' => (int) ($executionPayload['processed_rows'] ?? 0),
                'created' => (int) ($executionPayload['created'] ?? 0),
                'updated' => (int) ($executionPayload['updated'] ?? 0),
                'warning_count' => count($warnings),
            ], $this->userId);
        }

        $chunk = array_slice($processedData, $this->offset, $this->chunkSize);
        if (empty($chunk)) {
            EmployeeResource::setEmployeeImportExecutionPayload([
                'status' => 'completed',
                'queued_at' => $executionPayload['queued_at'] ?? null,
                'started_at' => $executionPayload['started_at'] ?? null,
                'finished_at' => now()->toIso8601String(),
                'total_rows' => $totalRows,
                'processed_rows' => $totalRows,
                'created' => (int) ($executionPayload['created'] ?? 0),
                'updated' => (int) ($executionPayload['updated'] ?? 0),
                'warning_count' => count($warnings),
            ], $this->userId);

            Cache::forget(EmployeeResource::getEmployeeImportPreviewCacheKey($this->userId));
            $this->notifyUser(
                'Import Pegawai Selesai',
                "Import selesai diproses. Total {$totalRows} baris telah diproses."
            );
            return;
        }

        $meta = [];
        $isSuccess = EmployeeResource::runEmployeeImportCommit(
            $chunk,
            [],
            [
                'suppress_notifications' => true,
                'user_id' => $this->userId,
            ],
            $meta
        );

        if (!$isSuccess) {
            $errorMessage = $meta['error'] ?? 'Terjadi kesalahan saat memproses import.';
            $this->markFailedStatus($errorMessage, [
                'failed_row' => $meta['row_index'] ?? null,
                'failed_name' => $meta['row_name'] ?? null,
            ]);
            return;
        }

        $processedRows = min($totalRows, $this->offset + count($chunk));
        $created = (int) ($executionPayload['created'] ?? 0) + (int) ($meta['stats']['created'] ?? 0);
        $updated = (int) ($executionPayload['updated'] ?? 0) + (int) ($meta['stats']['updated'] ?? 0);

        $statusPayload = [
            'status' => $processedRows >= $totalRows ? 'completed' : 'processing',
            'queued_at' => $executionPayload['queued_at'] ?? null,
            'started_at' => $executionPayload['started_at'] ?? now()->toIso8601String(),
            'finished_at' => $processedRows >= $totalRows ? now()->toIso8601String() : null,
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'created' => $created,
            'updated' => $updated,
            'warning_count' => count($warnings),
        ];

        EmployeeResource::setEmployeeImportExecutionPayload($statusPayload, $this->userId);

        if ($processedRows >= $totalRows) {
            Cache::forget(EmployeeResource::getEmployeeImportPreviewCacheKey($this->userId));
            $this->notifyUser(
                'Import Pegawai Selesai',
                "Import selesai diproses. {$created} pegawai baru, {$updated} pegawai diperbarui."
            );
            return;
        }

        static::dispatch($this->userId, $processedRows, $this->chunkSize);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Employee import queue failed', [
            'user_id' => $this->userId,
            'offset' => $this->offset,
            'message' => $exception->getMessage(),
        ]);

        $this->markFailedStatus('Proses import gagal dijalankan oleh worker queue.');
    }

    private function markFailedStatus(string $message, array $extra = []): void
    {
        $executionPayload = EmployeeResource::getEmployeeImportExecutionPayload($this->userId);

        EmployeeResource::setEmployeeImportExecutionPayload(array_merge([
            'status' => 'failed',
            'queued_at' => $executionPayload['queued_at'] ?? null,
            'started_at' => $executionPayload['started_at'] ?? null,
            'failed_at' => now()->toIso8601String(),
            'total_rows' => (int) ($executionPayload['total_rows'] ?? 0),
            'processed_rows' => (int) ($executionPayload['processed_rows'] ?? 0),
            'created' => (int) ($executionPayload['created'] ?? 0),
            'updated' => (int) ($executionPayload['updated'] ?? 0),
            'warning_count' => (int) ($executionPayload['warning_count'] ?? 0),
            'error_message' => $message,
        ], $extra), $this->userId);

        $this->notifyUser('Import Pegawai Gagal', $message, 'danger');
    }

    private function notifyUser(string $title, string $body, string $type = 'success'): void
    {
        if ($this->userId <= 0) {
            return;
        }

        $user = User::query()->find($this->userId);
        if (!$user) {
            return;
        }

        $notification = Notification::make()
            ->title($title)
            ->body($body);

        if ($type === 'danger') {
            $notification->danger();
        } elseif ($type === 'warning') {
            $notification->warning();
        } else {
            $notification->success();
        }

        $notification->sendToDatabase($user, isEventDispatched: true);
    }
}
