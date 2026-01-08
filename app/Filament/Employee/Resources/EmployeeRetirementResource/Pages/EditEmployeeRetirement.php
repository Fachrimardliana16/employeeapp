<?php

namespace App\Filament\Employee\Resources\EmployeeRetirementResource\Pages;

use App\Filament\Employee\Resources\EmployeeRetirementResource;
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
}
