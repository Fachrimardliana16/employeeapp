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

    protected function afterCreate(): void
    {
        $record = $this->record;

        if ($record->is_applied && $record->employee) {
            $record->employee->update([
                'employee_service_grade_id' => $record->new_employee_service_grade_id ?? $record->employee->employee_service_grade_id,
                'periodic_salary_date_start' => $record->date_periodic_salary_increase,
            ]);

            Notification::make()
                ->title('Profil Pegawai Diperbarui')
                ->body('MKG dan Tanggal KGB telah diperbarui otomatis.')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
