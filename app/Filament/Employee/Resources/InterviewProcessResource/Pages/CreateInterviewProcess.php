<?php

namespace App\Filament\Employee\Resources\InterviewProcessResource\Pages;

use App\Filament\Employee\Resources\InterviewProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateInterviewProcess extends CreateRecord
{
    protected static string $resource = InterviewProcessResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Interview berhasil dijadwalkan')
            ->body('Data interview telah disimpan.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('Interview validation failed', [
            'errors' => $exception->errors(),
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
