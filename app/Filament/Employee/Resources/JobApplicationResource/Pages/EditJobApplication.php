<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Pages;

use App\Filament\Employee\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditJobApplication extends EditRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, $record) {
                    // Cek apakah ada interview terkait
                    if ($record->interview()->exists()) {
                        Notification::make()
                            ->danger()
                            ->title('Tidak dapat menghapus')
                            ->body('Lamaran ini sudah memiliki data interview.')
                            ->send();

                        $action->cancel();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Lamaran dihapus')
                        ->body('Data lamaran kerja telah dihapus.')
                ),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Perubahan disimpan')
            ->body('Data lamaran kerja telah diperbarui.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('JobApplication update validation failed', [
            'errors' => $exception->errors(),
            'record_id' => $this->record->id ?? null,
            'user' => auth()->id() ?? 0,
        ]);
    }
}
