<?php

namespace App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateEmployeePeriodicSalaryIncrease extends CreateRecord
{
    protected static string $resource = EmployeePeriodicSalaryIncreaseResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Kenaikan gaji berkala berhasil dibuat')
            ->body('Data kenaikan gaji berkala telah disimpan.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput. Pastikan pegawai memenuhi syarat kenaikan berkala (2 tahun masa kerja).')
            ->send();

        Log::warning('EmployeePeriodicSalaryIncrease validation failed', [
            'errors' => $exception->errors(),
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
