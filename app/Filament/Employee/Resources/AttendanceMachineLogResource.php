<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\AttendanceMachineLogResource\Pages;
use App\Filament\Employee\Resources\AttendanceMachineLogResource\RelationManagers;
use App\Models\AttendanceMachineLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;

class AttendanceMachineLogResource extends Resource
{
    protected static ?string $model = AttendanceMachineLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $modelLabel = 'Log Mesin Presensi';

    protected static ?string $pluralModelLabel = 'Log Mesin Presensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('attendance_machine_id')
                    ->relationship('machine', 'name')
                    ->disabled(),
                Forms\Components\TextInput::make('serial_number')
                    ->disabled(),
                Forms\Components\TextInput::make('pin')
                    ->disabled(),
                Forms\Components\DateTimePicker::make('timestamp')
                    ->disabled(),
                Forms\Components\TextInput::make('type')
                    ->disabled(),
                Forms\Components\Textarea::make('raw_payload')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('timestamp')
                    ->dateTime()
                    ->sortable()
                    ->label('Waktu Absen'),
                Tables\Columns\TextColumn::make('machine.name')
                    ->searchable()
                    ->sortable()
                    ->label('Mesin'),
                Tables\Columns\TextColumn::make('machine.officeLocation.name')
                    ->label('Lokasi'),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Tidak Terdaftar'),
                Tables\Columns\TextColumn::make('pin')
                    ->searchable()
                    ->sortable()
                    ->label('PIN'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0' => 'success',
                        '1' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '0' => 'Masuk',
                        '1' => 'Keluar',
                        '2' => 'Break Out',
                        '3' => 'Break In',
                        '4' => 'Overtime In',
                        '5' => 'Overtime Out',
                        default => "Type $state",
                    })
                    ->label('Tipe'),
            ])
            ->defaultSort('timestamp', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('attendance_machine_id')
                    ->relationship('machine', 'name')
                    ->label('Mesin'),
                Tables\Filters\SelectFilter::make('employee')
                    ->relationship('employee', 'name')
                    ->label('Pegawai')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('timestamp')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('to')->label('Hingga Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('timestamp', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('timestamp', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['to'] ?? null) {
                            $indicators[] = 'Sampai ' . \Carbon\Carbon::parse($data['to'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('auto_process_today')
                    ->label('Auto-Proses Hari Ini')
                    ->icon('heroicon-o-bolt')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Otomatis Log Hari Ini')
                    ->modalDescription('Sistem akan otomatis memproses semua log hari ini yang belum masuk ke tabel kehadiran.')
                    ->action(function () {
                        $logs = AttendanceMachineLog::whereDate('timestamp', today())->get();
                        $count = 0;
                        
                        foreach ($logs as $record) {
                            $employee = \App\Models\Employee::where('pin', $record->pin)->first();
                            $state = match($record->type) {
                                '0' => 'check_in', '1' => 'check_out', '2' => 'break_out',
                                '3' => 'break_in', '4' => 'ot_in', '5' => 'ot_out', default => 'check_in'
                            };
                            
                            \App\Models\EmployeeAttendanceRecord::updateOrCreate(
                                ['pin' => $record->pin, 'attendance_time' => $record->timestamp->toDateTimeString(), 'state' => $state],
                                [
                                    'employee_name' => $employee ? $employee->name : "Unknown (PIN: {$record->pin})",
                                    'attendance_status' => 'on_time',
                                    'device' => $record->machine?->name,
                                    'office_location_id' => $record->machine?->master_office_location_id,
                                ]
                            );
                            $count++;
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Proses Otomatis Selesai')
                            ->body("{$count} log hari ini telah diproses ke tabel kehadiran.")
                            ->success()
                            ->duration(5000)
                            ->send();
                    }),
                
                Tables\Actions\Action::make('auto_cleanup_old')
                    ->label('Bersihkan Log Lama')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Log Lama (> 6 Bulan)')
                    ->modalDescription('PERINGATAN: Log yang sudah lebih dari 6 bulan akan dihapus permanen untuk menghemat storage.')
                    ->form([
                        Forms\Components\Select::make('months')
                            ->label('Hapus Log Lebih Dari')
                            ->options([
                                3 => '3 Bulan',
                                6 => '6 Bulan',
                                12 => '1 Tahun',
                                24 => '2 Tahun',
                            ])
                            ->default(6)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $months = $data['months'];
                        $cutoffDate = now()->subMonths($months);
                        
                        $count = AttendanceMachineLog::where('timestamp', '<', $cutoffDate)->count();
                        AttendanceMachineLog::where('timestamp', '<', $cutoffDate)->forceDelete();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Cleanup Selesai')
                            ->body("{$count} log lama telah dihapus dari database.")
                            ->warning()
                            ->duration(5000)
                            ->send();
                    }),
                
                Tables\Actions\Action::make('detect_duplicates')
                    ->label('Deteksi Duplikat Otomatis')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('warning')
                    ->action(function () {
                        $logs = AttendanceMachineLog::whereDate('timestamp', '>=', today()->subDays(7))->get();
                        $marked = \App\Services\AttendanceService::markDuplicates($logs);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Deteksi Selesai')
                            ->body("Log duplikat 7 hari terakhir telah dianalisis.")
                            ->info()
                            ->send();
                    }),
                
                Tables\Actions\Action::make('print_report')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->modalHeading('Filter & Cetak Laporan Absensi')
                    ->modalSubmitActionLabel('Proses Cetak/Export')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('from_date')
                                    ->label('Dari Tanggal')
                                    ->default(now()->startOfMonth())
                                    ->required(),
                                Forms\Components\DatePicker::make('to_date')
                                    ->label('Sampai Tanggal')
                                    ->default(now())
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Pegawai')
                                    ->options(\App\Models\Employee::pluck('name', 'id'))
                                    ->placeholder('Semua Pegawai')
                                    ->multiple()
                                    ->searchable(),
                                Forms\Components\Select::make('attendance_machine_id')
                                    ->label('Mesin Absensi')
                                    ->options(\App\Models\AttendanceMachine::pluck('name', 'id'))
                                    ->placeholder('Semua Mesin')
                                    ->searchable(),
                            ]),
                        Forms\Components\Select::make('report_type')
                            ->label('Jenis Laporan & Format')
                            ->options([
                                'summary_pdf' => '1. Analisa Kehadiran (PDF - Persentase)',
                                'log_pdf' => '2. Rekap Log Absensi (PDF - Dengan Kop)',
                                'log_excel' => '3. Rekap Log Absensi (Excel - Data Mentah)',
                            ])
                            ->default('summary_pdf')
                            ->required()
                            ->selectablePlaceholder(false),
                    ])
                    ->action(function (array $data, \Filament\Tables\Table $table) {
                        if ($data['report_type'] === 'summary_pdf') {
                            $url = route('attendance.summary.report', [
                                'from_date' => $data['from_date'],
                                'to_date' => $data['to_date'],
                                'employee_id' => $data['employee_id'],
                            ]);
                            $table->getLivewire()->js("window.open('{$url}', '_blank');");
                            return;
                        }

                        if ($data['report_type'] === 'log_pdf') {
                            $url = route('attendance.logs.report.pdf', $data);
                            $table->getLivewire()->js("window.open('{$url}', '_blank');");
                            return;
                        }

                        if ($data['report_type'] === 'log_excel') {
                            $query = AttendanceMachineLog::query()
                                ->with(['machine.officeLocation', 'employee'])
                                ->when($data['from_date'], fn($q, $date) => $q->whereDate('timestamp', '>=', $date))
                                ->when($data['to_date'], fn($q, $date) => $q->whereDate('timestamp', '<=', $date))
                                ->when($data['employee_id'], function($q, $ids) {
                                    $ids = is_array($ids) ? $ids : [$ids];
                                    $pins = \App\Models\Employee::whereIn('id', $ids)->pluck('pin')->filter()->toArray();
                                    if (!empty($pins)) $q->whereIn('pin', $pins);
                                })
                                ->when($data['attendance_machine_id'], fn($q, $id) => $q->where('attendance_machine_id', $id))
                                ->orderBy('timestamp', 'desc');

                            $records = $query->get();
                            
                            if ($records->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Data Kosong')
                                    ->body('Tidak ada data yang ditemukan untuk filter tersebut.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                            $sheet = $spreadsheet->getActiveSheet();
                            
                            // Header
                            $dayMap = [
                                'monday' => 'SENIN', 'tuesday' => 'SELASA', 'wednesday' => 'RABU',
                                'thursday' => 'KAMIS', 'friday' => 'JUMAT', 'saturday' => 'SABTU', 'sunday' => 'MINGGU',
                            ];

                            // 1. Process Records for Duplicates using Service
                            $records = \App\Services\AttendanceService::markDuplicates($records);

                            // Re-sort to DESC for export display
                            $sortedRecords = $records->sortByDesc('timestamp');

                            $headers = ['Hari', 'Waktu', 'Mesin', 'Lokasi', 'PIN', 'Nama Pegawai', 'Tipe', 'Keterangan'];
                            foreach ($headers as $key => $title) {
                                $col = chr(65 + $key);
                                $sheet->setCellValue($col . '1', $title);
                            }
                            
                            $sheet->getStyle('A1:H1')->getFont()->setBold(true);
                            
                            $row = 2;
                            foreach ($sortedRecords as $record) {
                                $dayEng = strtolower($record->timestamp->format('l'));
                                $dayInd = $dayMap[$dayEng] ?? $dayEng;

                                $typeLabel = match ($record->type) {
                                    '0' => 'MASUK', '1' => 'KELUAR',
                                    '2' => 'ISTIRAHAT KELUAR', '3' => 'ISTIRAHAT MASUK',
                                    '4' => 'LEMBUR MASUK', '5' => 'LEMBUR KELUAR',
                                    default => "TYPE " . $record->type,
                                };
                                
                                $sheet->setCellValue('A' . $row, $dayInd);
                                $sheet->setCellValue('B' . $row, $record->timestamp->format('d/m/Y H:i:s'));
                                $sheet->setCellValue('C' . $row, $record->machine?->name);
                                $sheet->setCellValue('D' . $row, $record->machine?->officeLocation?->name);
                                $sheet->setCellValue('E' . $row, $record->pin);
                                $sheet->setCellValue('F' . $row, $record->employee?->name ?? 'Tidak Terdaftar');
                                $sheet->setCellValue('G' . $row, $typeLabel);
                                $sheet->setCellValue('H' . $row, $record->is_record_duplicate ? 'DUPLIKAT' : '-');
                                $row++;
                            }
                            
                            foreach (range('A', 'H') as $col) {
                                $sheet->getColumnDimension($col)->setAutoSize(true);
                            }
                            
                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                            $filename = 'Log_Absensi_' . now()->format('Ymd_His') . '.xlsx';
                            $tempPath = tempnam(sys_get_temp_dir(), 'export_');
                            $writer->save($tempPath);
                            
                            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('process_to_attendance')
                    ->label('Proses ke Kehadiran')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (AttendanceMachineLog $record) {
                        $employee = \App\Models\Employee::where('pin', $record->pin)->first();
                        $state = match($record->type) {
                            '0' => 'check_in', '1' => 'check_out', '2' => 'break_out',
                            '3' => 'break_in', '4' => 'ot_in', '5' => 'ot_out', default => 'check_in'
                        };
                        
                        \App\Models\EmployeeAttendanceRecord::updateOrCreate(
                            ['pin' => $record->pin, 'attendance_time' => $record->timestamp->toDateTimeString(), 'state' => $state],
                            [
                                'employee_name' => $employee ? $employee->name : "Unknown (PIN: {$record->pin})",
                                'attendance_status' => 'on_time',
                                'device' => $record->machine?->name,
                                'office_location_id' => $record->machine?->master_office_location_id,
                            ]
                        );
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Berhasil Diproses')
                            ->body('Log telah diproses ke tabel kehadiran.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_process_to_attendance')
                        ->label('Proses ke Kehadiran (Massal)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Proses Log ke Tabel Kehadiran')
                        ->modalDescription('Semua log yang dipilih akan diproses ke tabel employee_attendance_records.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                $employee = \App\Models\Employee::where('pin', $record->pin)->first();
                                $state = match($record->type) {
                                    '0' => 'check_in', '1' => 'check_out', '2' => 'break_out',
                                    '3' => 'break_in', '4' => 'ot_in', '5' => 'ot_out', default => 'check_in'
                                };
                                
                                \App\Models\EmployeeAttendanceRecord::updateOrCreate(
                                    ['pin' => $record->pin, 'attendance_time' => $record->timestamp->toDateTimeString(), 'state' => $state],
                                    [
                                        'employee_name' => $employee ? $employee->name : "Unknown (PIN: {$record->pin})",
                                        'attendance_status' => 'on_time',
                                        'device' => $record->machine?->name,
                                        'office_location_id' => $record->machine?->master_office_location_id,
                                    ]
                                );
                                $count++;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Proses Selesai')
                                ->body("{$count} log berhasil diproses ke tabel kehadiran.")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('mark_as_duplicate')
                        ->label('Tandai Duplikat (Massal)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Support\Collection $records) {
                            // Use AttendanceService to mark duplicates
                            $marked = \App\Services\AttendanceService::markDuplicates($records);
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Duplikat Ditandai')
                                ->body("Log duplikat telah diidentifikasi.")
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export Terpilih ke Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function (\Illuminate\Support\Collection $records) {
                            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                            $sheet = $spreadsheet->getActiveSheet();
                            
                            $headers = ['Hari', 'Waktu', 'Mesin', 'Lokasi', 'PIN', 'Nama Pegawai', 'Tipe'];
                            foreach ($headers as $key => $title) {
                                $col = chr(65 + $key);
                                $sheet->setCellValue($col . '1', $title);
                            }
                            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
                            
                            $dayMap = [
                                'monday' => 'SENIN', 'tuesday' => 'SELASA', 'wednesday' => 'RABU',
                                'thursday' => 'KAMIS', 'friday' => 'JUMAT', 'saturday' => 'SABTU', 'sunday' => 'MINGGU',
                            ];
                            
                            $row = 2;
                            foreach ($records as $record) {
                                $dayEng = strtolower($record->timestamp->format('l'));
                                $dayInd = $dayMap[$dayEng] ?? $dayEng;
                                $typeLabel = match ($record->type) {
                                    '0' => 'MASUK', '1' => 'KELUAR',
                                    '2' => 'ISTIRAHAT KELUAR', '3' => 'ISTIRAHAT MASUK',
                                    '4' => 'LEMBUR MASUK', '5' => 'LEMBUR KELUAR',
                                    default => "TYPE " . $record->type,
                                };
                                
                                $sheet->setCellValue('A' . $row, $dayInd);
                                $sheet->setCellValue('B' . $row, $record->timestamp->format('d/m/Y H:i:s'));
                                $sheet->setCellValue('C' . $row, $record->machine?->name);
                                $sheet->setCellValue('D' . $row, $record->machine?->officeLocation?->name);
                                $sheet->setCellValue('E' . $row, $record->pin);
                                $sheet->setCellValue('F' . $row, $record->employee?->name ?? 'Tidak Terdaftar');
                                $sheet->setCellValue('G' . $row, $typeLabel);
                                $row++;
                            }
                            
                            foreach (range('A', 'G') as $col) {
                                $sheet->getColumnDimension($col)->setAutoSize(true);
                            }
                            
                            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                            $filename = 'Log_Selected_' . now()->format('Ymd_His') . '.xlsx';
                            $tempPath = tempnam(sys_get_temp_dir(), 'export_');
                            $writer->save($tempPath);
                            
                            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
                        }),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceMachineLogs::route('/'),
        ];
    }
}
