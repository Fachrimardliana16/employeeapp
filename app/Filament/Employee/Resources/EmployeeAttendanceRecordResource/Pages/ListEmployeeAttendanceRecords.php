<?php

namespace App\Filament\Employee\Resources\EmployeeAttendanceRecordResource\Pages;

use App\Filament\Employee\Resources\EmployeeAttendanceRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAttendanceRecords extends ListRecords
{
    protected static string $resource = EmployeeAttendanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('report')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->modalHeading('Filter Laporan Kehadiran')
                ->modalSubmitActionLabel('Cetak')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from_date')
                        ->label('Dari Tanggal')
                        ->default(now()->startOfMonth())
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('to_date')
                        ->label('Sampai Tanggal')
                        ->default(now())
                        ->required(),
                    \Filament\Forms\Components\Select::make('employee_id')
                        ->label('Pegawai (Opsional)')
                        ->options(\App\Models\Employee::pluck('name', 'id'))
                        ->searchable(),
                    \Filament\Forms\Components\Select::make('office_location_id')
                        ->label('Lokasi (Opsional)')
                        ->options(\App\Models\MasterOfficeLocation::pluck('name', 'id'))
                        ->searchable(),
                ])
                ->action(function (array $data, \Filament\Resources\Pages\ListRecords $livewire) {
                    $url = route('attendance.report', $data);
                    $livewire->js("window.open('{$url}', '_blank');");
                }),
            Actions\Action::make('summary_report')
                ->label('Analisa Kehadiran (PDF)')
                ->icon('heroicon-o-chart-pie')
                ->color('warning')
                ->modalHeading('Filter Analisa Kehadiran (Persentase)')
                ->modalSubmitActionLabel('Cetak Analisa')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from_date')
                        ->label('Dari Tanggal')
                        ->default(now()->startOfMonth())
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('to_date')
                        ->label('Sampai Tanggal')
                        ->default(now())
                        ->required(),
                    \Filament\Forms\Components\Select::make('employee_id')
                        ->label('Pegawai (Opsional - Kosongkan untuk Semua)')
                        ->options(\App\Models\Employee::pluck('name', 'id'))
                        ->searchable(),
                ])
                ->action(function (array $data, $livewire) {
                    $url = route('attendance.summary.report', $data);
                    $livewire->js("window.open('{$url}', '_blank');");
                }),
            Actions\Action::make('create_manual')
                ->label('Input Absensi Manual')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading('Input Kehadiran Manual')
                ->modalDescription('Fitur ini digunakan oleh Admin untuk memasukkan data kehadiran secara manual (Bypass GPS & Foto), misalnya untuk kasus Izin Datang Terlambat.')
                ->modalSubmitActionLabel('Simpan')
                ->form([
                    \Filament\Forms\Components\Select::make('employee_id')
                        ->label('Pegawai')
                        ->options(\App\Models\Employee::pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Grid::make(3)->schema([
                        \Filament\Forms\Components\Select::make('state')
                            ->label('Status Kehadiran')
                            ->options([
                                'in' => 'Masuk (Check In)',
                                'out' => 'Keluar (Check Out)',
                                'dl_in' => 'Dinas Luar (M)',
                                'dl_out' => 'Dinas Luar (P)',
                                'ot_in' => 'Lembur (M)',
                                'ot_out' => 'Lembur (P)',
                            ])
                            ->default('in')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        \Filament\Forms\Components\DateTimePicker::make('attendance_time')
                            ->label('Waktu Kehadiran')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                        \Filament\Forms\Components\Select::make('attendance_status')
                            ->label('Ketepatan Waktu')
                            ->options([
                                'on_time' => 'Tepat Waktu',
                                'late' => 'Terlambat',
                                'early' => 'Terlalu Cepat',
                            ])
                            ->default('late')
                            ->required()
                            ->native(false)
                            ->columnSpan(1),
                    ]),
                    \Filament\Forms\Components\Select::make('office_location_id')
                        ->label('Lokasi Kantor (Opsional)')
                        ->options(\App\Models\MasterOfficeLocation::pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Pilih lokasi jika relevan...')
                        ->columnSpanFull(),
                    \Filament\Forms\Components\Textarea::make('verification')
                        ->label('Catatan / Alasan Bypass')
                        ->placeholder('Contoh: Izin Datang Terlambat karena mengurus dokumen')
                        ->required()
                        ->rows(3)
                        ->columnSpanFull(),
                    \Filament\Forms\Components\FileUpload::make('foto_bukti')
                        ->label('Foto Bukti Kehadiran (Dengan Time Mark)')
                        ->directory('attendance/manual_bypass')
                        ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                        ->maxSize(5120)
                        ->required()
                        ->helperText('Unggah foto bukti kehadiran (berserta time mark) yang dikirimkan oleh pegawai.')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $employee = \App\Models\Employee::find($data['employee_id']);
                    
                    if (!$employee) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal')
                            ->body('Pegawai tidak ditemukan.')
                            ->danger()
                            ->send();
                        return;
                    }

                    \App\Models\EmployeeAttendanceRecord::create([
                        'pin' => $employee->pin ?? $employee->id,
                        'employee_name' => $employee->name,
                        'attendance_time' => $data['attendance_time'],
                        'state' => $data['state'],
                        'attendance_status' => $data['attendance_status'],
                        'verification' => $data['verification'],
                        'device' => 'Bypass Admin',
                        'office_location_id' => $data['office_location_id'] ?? null,
                        'is_within_radius' => true, // Anggap valid karena di-bypass
                        'picture' => $data['foto_bukti'] ?? null,
                        'photo_checkin' => in_array($data['state'], ['in', 'ot_in', 'dl_in']) ? ($data['foto_bukti'] ?? null) : null,
                        'photo_checkout' => in_array($data['state'], ['out', 'ot_out', 'dl_out']) ? ($data['foto_bukti'] ?? null) : null,
                        'users_id' => auth()->id(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Berhasil')
                        ->body('Kehadiran manual berhasil disimpan.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAttendanceRecordResource\Widgets\AttendanceStatsWidget::class,
            EmployeeAttendanceRecordResource\Widgets\ActivePermissionsWidget::class,
        ];
    }
}
