<?php

namespace App\Filament\Employee\Resources\EmployeeRetirementResource\Pages;

use App\Filament\Employee\Resources\EmployeeRetirementResource;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateEmployeeRetirement extends CreateRecord
{
    protected static string $resource = EmployeeRetirementResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengunduran diri berhasil dibuat')
            ->body('Data pengunduran diri telah disimpan.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeeRetirement validation failed', [
            'errors' => $exception->errors(),
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if ($record->is_applied) {
            $employee = $record->employee;
            if ($employee) {
                // 1. Dapatkan ID status Pensiun
                $pensiunStatus = MasterEmployeeStatusEmployment::where('name', 'Pensiun')->first();
                
                // 2. Update Profil Pegawai
                $employee->update([
                    'employment_status_id' => $pensiunStatus?->id ?? $employee->employment_status_id,
                    'bpjs_kes_status' => 'Non-Aktif',
                    'bpjs_tk_status' => 'Non-Aktif',
                    'dapenma_status' => 'Non-Aktif',
                    'agreement_date_end' => $record->retirement_date,
                ]);

                // 3. Deaktivasi User Account
                if ($employee->users_id) {
                    User::where('id', $employee->users_id)->update(['is_active' => false]);
                }

                // 4. Update data Pensiun
                $record->update([
                    'applied_at' => now(),
                    'applied_by' => auth()->id(),
                    'approval_status' => 'approved',
                ]);

                Notification::make()
                    ->title('Realisasi Berhasil')
                    ->body('Status pegawai telah diperbarui secara otomatis.')
                    ->success()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
