<?php

namespace App\Filament\Employee\Resources\EmployeeCareerMovementResource\Pages;

use App\Filament\Employee\Resources\EmployeeCareerMovementResource;
use App\Models\Employee;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditEmployeeCareerMovement extends EditRecord
{
    protected static string $resource = EmployeeCareerMovementResource::class;

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

    protected function afterSave(): void
    {
        $record = $this->record;
        
        $employee = Employee::find($record->employee_id);
        
        if ($employee) {
            $employee->update([
                'departments_id' => $record->new_department_id,
                'sub_department_id' => $record->new_sub_department_id,
                'employee_position_id' => $record->new_position_id,
            ]);

            Log::info('EmployeeCareerMovement (Edit): Data pegawai diperbarui.', [
                'employee_id' => $employee->id,
                'type' => $record->type,
                'new_position' => $record->new_position_id,
            ]);
            
            Notification::make()
                ->success()
                ->title('Struktur Pegawai Disinkronkan')
                ->body("Data jabatan {$employee->name} telah diperbarui berdasarkan perubahan data {$record->type}.")
                ->send();
        }
    }
}
