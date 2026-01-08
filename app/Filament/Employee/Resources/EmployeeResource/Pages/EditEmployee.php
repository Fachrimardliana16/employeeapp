<?php

namespace App\Filament\Employee\Resources\EmployeeResource\Pages;

use App\Filament\Employee\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (Actions\DeleteAction $action, $record) {
                    // Cek apakah ada kontrak aktif
                    $hasActiveContract = $record->employeeAgreements()
                        ->where('effective_date_start', '<=', Carbon::today())
                        ->where(function ($q) {
                            $q->whereNull('effective_date_end')
                                ->orWhere('effective_date_end', '>=', Carbon::today());
                        })
                        ->exists();

                    if ($hasActiveContract) {
                        Notification::make()
                            ->danger()
                            ->title('Tidak dapat menghapus')
                            ->body('Pegawai masih memiliki kontrak aktif.')
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Pegawai dihapus')
                        ->body('Data pegawai telah dihapus.')
                ),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Perubahan disimpan')
            ->body('Data pegawai telah diperbarui.');
    }

    protected function onValidationError(\Illuminate\Validation\ValidationException $exception): void
    {
        Notification::make()
            ->danger()
            ->title('Validasi gagal')
            ->body('Mohon periksa kembali data yang diinput.')
            ->send();

        Log::warning('Employee update validation failed', [
            'errors' => $exception->errors(),
            'record_id' => $this->record->id ?? null,
            'user' => auth()->id() ?? 0,
        ]);
    }
}
