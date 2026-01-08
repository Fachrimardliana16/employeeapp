<?php

namespace App\Filament\Employee\Resources\JobApplicationResource\Pages;

use App\Filament\Employee\Resources\JobApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateJobApplication extends CreateRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Lamaran berhasil dibuat')
            ->body('Data lamaran kerja telah disimpan.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('JobApplication validation failed', [
            'errors' => $exception->errors(),
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
