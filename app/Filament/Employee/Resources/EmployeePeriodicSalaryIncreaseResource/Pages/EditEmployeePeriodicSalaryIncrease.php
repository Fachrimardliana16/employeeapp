<?php

namespace App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditEmployeePeriodicSalaryIncrease extends EditRecord
{
    protected static string $resource = EmployeePeriodicSalaryIncreaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Kenaikan gaji dihapus')
                        ->body('Data kenaikan gaji berkala telah dihapus.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Perubahan disimpan')
            ->body('Data kenaikan gaji berkala telah diperbarui.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeePeriodicSalaryIncrease update validation failed', [
            'errors' => $exception->errors(),
            'record_id' => $this->record->id ?? null,
            'user' => auth()->id() ?? 0,
        ]);
    }
}
