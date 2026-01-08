<?php

namespace App\Filament\Employee\Resources\EmployeeAgreementResource\Pages;

use App\Filament\Employee\Resources\EmployeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EditEmployeeAgreement extends EditRecord
{
    protected static string $resource = EmployeeAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, $record) {
                    // Cek apakah kontrak masih aktif
                    if ($record->effective_date_end && Carbon::parse($record->effective_date_end)->isFuture()) {
                        Notification::make()
                            ->warning()
                            ->title('Peringatan')
                            ->body('Kontrak ini masih aktif. Pastikan sudah membuat kontrak baru sebelum menghapus.')
                            ->persistent()
                            ->send();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Kontrak dihapus')
                        ->body('Data kontrak kerja telah dihapus.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Perubahan disimpan')
            ->body('Data kontrak kerja telah diperbarui.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeeAgreement update validation failed', [
            'errors' => $exception->errors(),
            'record_id' => $this->record->id ?? null,
            'user' => auth()->id() ?? 0,
        ]);
    }
}
