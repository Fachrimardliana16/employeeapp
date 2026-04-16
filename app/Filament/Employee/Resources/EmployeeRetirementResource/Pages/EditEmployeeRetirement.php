<?php

namespace App\Filament\Employee\Resources\EmployeeRetirementResource\Pages;

use App\Filament\Employee\Resources\EmployeeRetirementResource;
use App\Models\MasterEmployeeStatusEmployment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditEmployeeRetirement extends EditRecord
{
    protected static string $resource = EmployeeRetirementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, $record) {
                    // Peringatan jika sudah ada clearance yang diproses
                    if ($record->clearance_status && $record->clearance_status !== 'pending') {
                        Notification::make()
                            ->warning()
                            ->title('Peringatan')
                            ->body('Clearance sudah diproses. Pastikan membatalkan clearance terlebih dahulu.')
                            ->persistent()
                            ->send();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Pengunduran diri dihapus')
                        ->body('Data pengunduran diri telah dihapus.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Perubahan disimpan')
            ->body('Data pengunduran diri telah diperbarui.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeeRetirement update validation failed', [
            'errors' => $exception->errors(),
            'record_id' => $this->record->id ?? null,
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        // Hanya proses jika dicentang DAN belum pernah diterapkan sebelumnya
        if ($record->is_applied && !$record->applied_at) {
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
}
