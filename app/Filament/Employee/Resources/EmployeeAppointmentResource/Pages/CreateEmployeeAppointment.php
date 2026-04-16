<?php

namespace App\Filament\Employee\Resources\EmployeeAppointmentResource\Pages;

use App\Filament\Employee\Resources\EmployeeAppointmentResource;
use App\Models\Employee;
use App\Models\EmployeeAgreement;
use App\Models\JobApplicationArchive;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateEmployeeAppointment extends CreateRecord
{
    protected static string $resource = EmployeeAppointmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pengangkatan berhasil disimpan')
            ->body('Status kepegawaian pegawai telah diperbarui dan kontrak aktif dinonaktifkan.');
    }

    /**
     * Setelah record Pengangkatan disimpan:
     * 1. Update employment_status_id di profil pegawai.
     * 2. Set is_active = false pada semua kontrak aktif pegawai tersebut
     *    (baik yang terhubung via employees_id langsung maupun via job_application_archives).
     */
    protected function afterCreate(): void
    {
        $appointment = $this->record;
        $employeeId  = $appointment->employee_id;
        $newStatusId = $appointment->new_employment_status_id;

        // 1. Update status kepegawaian & golongan di data pegawai
        $employee = Employee::find($employeeId);
        if ($employee) {
            $updateData = ['employment_status_id' => $newStatusId];
            
            // Update Golongan & MKG jika diinputkan
            if (!empty($appointment->employee_grade_id)) {
                $updateData['basic_salary_id'] = $appointment->employee_grade_id;
            }

            if (!empty($appointment->employee_service_grade_id)) {
                $updateData['employee_service_grade_id'] = $appointment->employee_service_grade_id;
            }

            // Jika diangkat menjadi Pegawai Tetap, update permanent_appointment_date
            $targetStatus = \App\Models\MasterEmployeeStatusEmployment::find($newStatusId);
            if ($targetStatus && $targetStatus->name === 'Pegawai Tetap') {
                $updateData['permanent_appointment_date'] = $appointment->appointment_date;
            }

            $employee->update($updateData);
            
            Log::info('EmployeeAppointment: Status/Golongan/MKG pegawai diperbarui.', [
                'employee_id'    => $employeeId,
                'new_status_id'  => $newStatusId,
                'new_grade_id'   => $appointment->employee_grade_id,
                'new_mkg_id'     => $appointment->employee_service_grade_id,
                'appointment_id' => $appointment->id,
            ]);
        }

        // 2a. Nonaktifkan kontrak yang terhubung langsung via employees_id
        $deactivatedDirect = EmployeeAgreement::where('employees_id', $employeeId)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // 2b. Nonaktifkan kontrak yang terhubung via job_application_archives
        //     (jalur rekrutmen: employee_agreement -> job_application_archives -> job_applications)
        //     Kita cari berdasarkan kesamaan nama + phone_number atau id_number jika ada,
        //     atau lebih aman menggunakan id_number pegawai via id_number di job_applications.
        $deactivatedViaArchive = 0;
        if ($employee) {
            // Cari archives yang job_application-nya punya NIK yang sama dengan pegawai
            $archiveIds = JobApplicationArchive::whereHas('jobApplication', function ($q) use ($employee) {
                    $q->where('id_number', $employee->id_number);
                })
                ->pluck('id');

            if ($archiveIds->isNotEmpty()) {
                $deactivatedViaArchive = EmployeeAgreement::whereIn('job_application_archives_id', $archiveIds)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        }

        $totalDeactivated = $deactivatedDirect + $deactivatedViaArchive;

        if ($totalDeactivated > 0) {
            Log::info('EmployeeAppointment: Kontrak aktif pegawai dinonaktifkan.', [
                'employee_id'             => $employeeId,
                'contracts_closed_direct' => $deactivatedDirect,
                'contracts_closed_archive'=> $deactivatedViaArchive,
                'appointment_id'          => $appointment->id,
            ]);
        }
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('EmployeeAppointment validation failed', [
            'errors' => $exception->errors(),
            'user'   => auth()->id() ?? 0,
        ]);
    }
}
