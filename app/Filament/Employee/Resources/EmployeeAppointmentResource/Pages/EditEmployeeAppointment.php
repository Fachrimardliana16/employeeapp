<?php

namespace App\Filament\Employee\Resources\EmployeeAppointmentResource\Pages;

use App\Filament\Employee\Resources\EmployeeAppointmentResource;
use App\Models\Employee;
use App\Models\EmployeeAgreement;
use App\Models\JobApplicationArchive;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class EditEmployeeAppointment extends EditRecord
{
    protected static string $resource = EmployeeAppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengangkatan berhasil diperbarui')
            ->body('Perubahan data pengangkatan telah disimpan.');
    }

    /**
     * Setelah update: sync kembali status pegawai berdasarkan new_employment_status_id terbaru.
     * Juga nonaktifkan kontrak aktif jika ada.
     */
    protected function afterSave(): void
    {
        $appointment = $this->record;
        $employeeId  = $appointment->employee_id;
        $newStatusId = $appointment->new_employment_status_id;

        // Sync status kepegawaian ke data pegawai
        $employee = Employee::find($employeeId);
        if ($employee) {
            $employee->update(['employment_status_id' => $newStatusId]);
            Log::info('EmployeeAppointment (Edit): Status pegawai diperbarui.', [
                'employee_id'    => $employeeId,
                'new_status_id'  => $newStatusId,
                'appointment_id' => $appointment->id,
            ]);
        }

        // Nonaktifkan kontrak aktif via employees_id langsung
        EmployeeAgreement::where('employees_id', $employeeId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Nonaktifkan kontrak via archive (jalur rekrutmen) menggunakan NIK
        if ($employee && $employee->id_number) {
            $archiveIds = JobApplicationArchive::whereHas('jobApplication', function ($q) use ($employee) {
                    $q->where('id_number', $employee->id_number);
                })
                ->pluck('id');

            if ($archiveIds->isNotEmpty()) {
                EmployeeAgreement::whereIn('job_application_archives_id', $archiveIds)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        }
    }
}
