<?php

namespace App\Filament\Employee\Resources\EmployeeAgreementResource\Pages;

use App\Filament\Employee\Resources\EmployeeAgreementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateEmployeeAgreement extends CreateRecord
{
    protected static string $resource = EmployeeAgreementResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Kontrak berhasil dibuat')
            ->body('Data kontrak kerja telah disimpan.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeeAgreement validation failed', [
            'errors' => $exception->errors(),
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
