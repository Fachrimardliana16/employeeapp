<?php

namespace App\Filament\Employee\Resources\EmployeeMutationResource\Pages;

use App\Filament\Employee\Resources\EmployeeMutationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class CreateEmployeeMutation extends CreateRecord
{
    protected static string $resource = EmployeeMutationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Mutasi berhasil dibuat')
            ->body('Data mutasi pegawai telah disimpan.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeeMutation validation failed', [
            'errors' => $exception->errors(),
            'user' => auth()->id() ?? 0,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $record = $this->getRecord();
        
        if ($record->is_applied && $record->employee) {
            $record->employee->update([
                'departments_id' => $record->new_department_id,
                'sub_department_id' => $record->new_sub_department_id,
                'employee_position_id' => $record->new_position_id,
            ]);

            Notification::make()
                ->title('Mutasi Langsung Diterapkan')
                ->body('Data Profil Pegawai ' . $record->employee->name . ' telah diperbarui.')
                ->success()
                ->send();
        }
    }
}
