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

class AttendanceMachineLogResource extends Resource
{
    protected static ?string $model = AttendanceMachineLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Absensi & Kehadiran';

    protected static ?string $modelLabel = 'Log Mesin Absensi';

    protected static ?string $pluralModelLabel = 'Log Mesin Absensi';

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
            ])
            ->headerActions([
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
                                ->when($data['employee_id'], function($q, $id) {
                                    $employee = \App\Models\Employee::find($id);
                                    if ($employee && $employee->pin) {
                                        $q->where('pin', $employee->pin);
                                    }
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
                            $headers = ['Waktu', 'Mesin', 'Lokasi', 'PIN', 'Nama Pegawai', 'Tipe'];
                            foreach ($headers as $key => $title) {
                                $col = chr(65 + $key);
                                $sheet->setCellValue($col . '1', $title);
                            }
                            
                            $sheet->getStyle('A1:F1')->getFont()->setBold(true);
                            
                            $row = 2;
                            foreach ($records as $record) {
                                $typeLabel = match ($record->type) {
                                    '0' => 'Masuk', '1' => 'Keluar',
                                    '2' => 'Break Out', '3' => 'Break In',
                                    '4' => 'Overtime In', '5' => 'Overtime Out',
                                    default => "Type " . $record->type,
                                };
                                
                                $sheet->setCellValue('A' . $row, $record->timestamp->format('d/m/Y H:i:s'));
                                $sheet->setCellValue('B' . $row, $record->machine?->name);
                                $sheet->setCellValue('C' . $row, $record->machine?->officeLocation?->name);
                                $sheet->setCellValue('D' . $row, $record->pin);
                                $sheet->setCellValue('E' . $row, $record->employee?->name ?? 'Tidak Terdaftar');
                                $sheet->setCellValue('F' . $row, $typeLabel);
                                $row++;
                            }
                            
                            foreach (range('A', 'F') as $col) {
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendanceMachineLogs::route('/'),
            'create' => Pages\CreateAttendanceMachineLog::route('/create'),
            'edit' => Pages\EditAttendanceMachineLog::route('/{record}/edit'),
        ];
    }
}
