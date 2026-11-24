<?php

namespace App\Jobs;

use App\Filament\Employee\Resources\EmployeeResource;
use App\Models\Employee;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessEmployeeAccountProvisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    /**
     * @param array<int> $employeeIds
     */
    public function __construct(
        public int $userId,
        public array $employeeIds = [],
        public int $offset = 0,
        public int $chunkSize = 25
    ) {
        $this->userId = max(0, $this->userId);
        $this->employeeIds = array_values(array_unique(array_filter(
            array_map(static fn($value) => (int) $value, $this->employeeIds),
            static fn(int $value) => $value > 0
        )));
        $this->offset = max(0, $this->offset);
        $this->chunkSize = max(1, min(100, $this->chunkSize));
    }

    public function handle(): void
    {
        if (empty($this->employeeIds)) {
            $this->markFailedStatus('Daftar pegawai untuk pembuatan akun tidak ditemukan.');
            return;
        }

        $totalRows = count($this->employeeIds);
        $executionPayload = EmployeeResource::getEmployeeImportExecutionPayload($this->userId);
        $currentStatus = $executionPayload['account_provision_status'] ?? null;

        if (!in_array($currentStatus, ['queued', 'processing'], true)) {
            EmployeeResource::setEmployeeImportExecutionPayload(array_merge($executionPayload, [
                'account_provision_status' => 'processing',
                'account_provision_queued_at' => $executionPayload['account_provision_queued_at'] ?? now()->toIso8601String(),
                'account_provision_started_at' => now()->toIso8601String(),
                'account_provision_total' => $totalRows,
                'account_provision_processed' => (int) ($executionPayload['account_provision_processed'] ?? 0),
                'account_provision_created' => (int) ($executionPayload['account_provision_created'] ?? 0),
                'account_provision_skipped' => (int) ($executionPayload['account_provision_skipped'] ?? 0),
                'account_provision_failed' => (int) ($executionPayload['account_provision_failed'] ?? 0),
                'account_provision_error_message' => null,
            ]), $this->userId);
        }

        $chunkIds = array_slice($this->employeeIds, $this->offset, $this->chunkSize);
        if (empty($chunkIds)) {
            $this->markCompletedStatus($totalRows);
            return;
        }

        $employees = Employee::query()
            ->whereIn('id', $chunkIds)
            ->orderBy('id')
            ->get();

        $chunkCreated = 0;
        $chunkSkipped = 0;
        $chunkFailed = 0;

        foreach ($employees as $employee) {
            try {
                if (!empty($employee->account_user_id)) {
                    $chunkSkipped++;
                    continue;
                }

                [$email, $username] = $this->buildUniqueCredentials($employee);

                $user = User::query()->create([
                    'name' => (string) $employee->name,
                    'email' => $email,
                    'username' => $username,
                    'password' => Hash::make(Str::password(12)),
                    'is_verified' => true,
                    'is_active' => true,
                ]);

                $employee->account_user_id = $user->id;
                $employee->save();

                $chunkCreated++;
            } catch (Throwable $exception) {
                $chunkFailed++;

                Log::error('Employee account provision row failed', [
                    'employee_id' => $employee->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $executionPayload = EmployeeResource::getEmployeeImportExecutionPayload($this->userId);
        $processedRows = min($totalRows, $this->offset + count($chunkIds));
        $created = (int) ($executionPayload['account_provision_created'] ?? 0) + $chunkCreated;
        $skipped = (int) ($executionPayload['account_provision_skipped'] ?? 0) + $chunkSkipped;
        $failed = (int) ($executionPayload['account_provision_failed'] ?? 0) + $chunkFailed;

        EmployeeResource::setEmployeeImportExecutionPayload(array_merge($executionPayload, [
            'account_provision_status' => $processedRows >= $totalRows ? 'completed' : 'processing',
            'account_provision_finished_at' => $processedRows >= $totalRows ? now()->toIso8601String() : null,
            'account_provision_total' => $totalRows,
            'account_provision_processed' => $processedRows,
            'account_provision_created' => $created,
            'account_provision_skipped' => $skipped,
            'account_provision_failed' => $failed,
            'pending_account_provision_count' => max(0, $totalRows - ($created + $skipped + $failed)),
        ]), $this->userId);

        if ($processedRows >= $totalRows) {
            $this->notifyUser(
                'Pembuatan Akun Pegawai Selesai',
                "Pembuatan akun selesai: {$created} berhasil, {$skipped} dilewati, {$failed} gagal."
            );

            return;
        }

        static::dispatch($this->userId, $this->employeeIds, $processedRows, $this->chunkSize);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Employee account provision queue failed', [
            'user_id' => $this->userId,
            'offset' => $this->offset,
            'message' => $exception->getMessage(),
        ]);

        $this->markFailedStatus('Proses pembuatan akun gagal dijalankan oleh worker queue.');
    }

    private function markCompletedStatus(int $totalRows): void
    {
        $executionPayload = EmployeeResource::getEmployeeImportExecutionPayload($this->userId);

        EmployeeResource::setEmployeeImportExecutionPayload(array_merge($executionPayload, [
            'account_provision_status' => 'completed',
            'account_provision_finished_at' => now()->toIso8601String(),
            'account_provision_total' => $totalRows,
            'account_provision_processed' => $totalRows,
            'pending_account_provision_count' => 0,
        ]), $this->userId);
    }

    private function markFailedStatus(string $message): void
    {
        $executionPayload = EmployeeResource::getEmployeeImportExecutionPayload($this->userId);

        EmployeeResource::setEmployeeImportExecutionPayload(array_merge($executionPayload, [
            'account_provision_status' => 'failed',
            'account_provision_failed_at' => now()->toIso8601String(),
            'account_provision_error_message' => $message,
        ]), $this->userId);

        $this->notifyUser('Pembuatan Akun Pegawai Gagal', $message, 'danger');
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function buildUniqueCredentials(Employee $employee): array
    {
        $baseEmail = trim((string) ($employee->email ?: $employee->office_email ?: "pegawai_{$employee->id}@pdam.com"));
        if ($baseEmail === '') {
            $baseEmail = "pegawai_{$employee->id}@pdam.com";
        }

        $email = $baseEmail;
        $counter = 1;
        while (User::query()->where('email', $email)->exists()) {
            $parts = explode('@', $baseEmail);
            $local = $parts[0] ?? "pegawai_{$employee->id}";
            $domain = $parts[1] ?? 'pdam.com';
            $email = "{$local}+{$counter}@{$domain}";
            $counter++;
        }

        $baseUsername = trim((string) ($employee->username ?: ''));
        if ($baseUsername === '') {
            return [$email, null];
        }

        $username = $baseUsername;
        $usernameCounter = 1;
        while (User::query()->where('username', $username)->exists()) {
            $username = "{$baseUsername}{$usernameCounter}";
            $usernameCounter++;
        }

        return [$email, $username];
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
