<?php

namespace App\Filament\Employee\Resources\EmployeePayrollResource\Pages;

use App\Filament\Employee\Resources\EmployeePayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeePayrolls extends ListRecords
{
    protected static string $resource = EmployeePayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generate_mass_payroll')
                ->label('Generasi Payroll Masal')
                ->icon('heroicon-o-cpu-chip')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('period')
                        ->label('Periode Payroll')
                        ->required()
                        ->displayFormat('F Y')
                        ->native(false)
                        ->default(now()->startOfMonth()),
                ])
                ->action(function (array $data) {
                    $period = \Carbon\Carbon::parse($data['period'])->startOfMonth();
                    $employees = \App\Models\Employee::where('employment_status_id', '!=', 6) // Non-retired
                        ->get();

                    $payrollService = new \App\Services\PayrollService();
                    $userId = auth()->id() ?? 1;
                    $successCount = 0;
                    $errorCount = 0;

                    foreach ($employees as $employee) {
                        try {
                            $payrollService->calculatePayroll($employee, $period, $userId);
                            $successCount++;
                        } catch (\Exception $e) {
                            $errorCount++;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Proses Payroll Selesai')
                        ->body("Berhasil: $successCount, Gagal: $errorCount")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalHeading('Generasi Payroll Masal')
                ->modalDescription('Apakah Anda yakin ingin melakukan proses payroll untuk semua pegawai aktif di periode ini?')
                ->modalSubmitActionLabel('Ya, Proses Sekarang'),
        ];
    }
}
