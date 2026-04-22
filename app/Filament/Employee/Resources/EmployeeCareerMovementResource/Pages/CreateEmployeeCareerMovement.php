<?php

namespace App\Filament\Employee\Resources\EmployeeCareerMovementResource\Pages;

use App\Filament\Employee\Resources\EmployeeCareerMovementResource;
use App\Models\Employee;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateEmployeeCareerMovement extends CreateRecord
{
    protected static string $resource = EmployeeCareerMovementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        
        // Only update employee profile when is_applied is true (realisasi)
        if (!$record->is_applied) {
            Notification::make()
                ->info()
                ->title('Usulan Berhasil Disimpan')
                ->body("Data {$record->type} untuk {$record->employee->name} tersimpan sebagai usulan. Profil pegawai belum diperbarui.")
                ->send();
            return;
        }

        $employee = Employee::find($record->employee_id);
        
        if ($employee) {
            $record->update([
                'applied_at' => now(),
                'applied_by' => auth()->id(),
            ]);

            $employee->update([
                'departments_id' => $record->new_department_id,
                'sub_department_id' => $record->new_sub_department_id,
                'employee_position_id' => $record->new_position_id,
            ]);

            Log::info('EmployeeCareerMovement (Create): Data pegawai diperbarui.', [
                'employee_id' => $employee->id,
                'type' => $record->type,
                'new_position' => $record->new_position_id,
            ]);
            
            Notification::make()
                ->success()
                ->title('Struktur Pegawai Diperbarui')
                ->body("Jabatan {$employee->name} telah diperbarui berdasarkan data {$record->type}.")
                ->send();
        }
    }
}
