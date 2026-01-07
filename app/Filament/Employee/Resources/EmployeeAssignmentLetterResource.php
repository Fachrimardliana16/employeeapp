<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeAssignmentLetterResource\Pages;
use App\Filament\Employee\Resources\EmployeeAssignmentLetterResource\RelationManagers;
use App\Models\EmployeeAssignmentLetter;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeAssignmentLetterResource extends Resource
{
    protected static ?string $model = EmployeeAssignmentLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Surat & Tugas Dinas';

    protected static ?string $navigationLabel = 'Surat Tugas';

    protected static ?string $modelLabel = 'Surat Tugas';

    protected static ?string $pluralModelLabel = 'Surat Tugas';

    protected static ?int $navigationSort = 701;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar Surat Tugas')
                    ->schema([
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Nomor Surat Tugas')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: ST/001/2024'),

                        Forms\Components\Select::make('assigning_employee_id')
                            ->label('Pegawai Utama')
                            ->relationship('assigningEmployee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Pegawai utama yang diberi tugas'),

                        Forms\Components\Select::make('employee_position_id')
                            ->label('Posisi/Jabatan Pegawai Utama')
                            ->relationship('employeePosition', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Repeater::make('additional_employees_detail')
                            ->label('Pegawai Tambahan')
                            ->schema([
                                Forms\Components\Select::make('employee_id')
                                    ->label('Nama Pegawai')
                                    ->options(function () {
                                        return \App\Models\Employee::pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $employee = \App\Models\Employee::with('position')->find($state);
                                            if ($employee && $employee->position) {
                                                $set('position', $employee->position->name);
                                            }
                                        }
                                    }),
                                Forms\Components\TextInput::make('position')
                                    ->label('Jabatan')
                                    ->required()
                                    ->placeholder('Jabatan akan terisi otomatis'),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Pegawai Tambahan')
                            ->defaultItems(0)
                            ->helperText('Tambahkan pegawai lain yang ikut dalam penugasan'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Penugasan')
                    ->schema([
                        Forms\Components\Textarea::make('task')
                            ->label('Deskripsi Tugas')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Periode Penugasan')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Penandatangan')
                    ->schema([
                        Forms\Components\Select::make('signatory_employee_id')
                            ->label('Pilih Penandatangan')
                            ->options(function () {
                                return \App\Models\Employee::getSignatoryEmployees();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih dari pegawai yang berjabatan Direktur/Kepala Bagian')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $employee = \App\Models\Employee::getSignatoryById($state);
                                    if ($employee) {
                                        $set('signatory_name', $employee->name);
                                        $set('signatory_position', $employee->position->name ?? '');
                                    }
                                } else {
                                    // Jika dropdown dikosongkan, bersihkan field
                                    $set('signatory_name', '');
                                    $set('signatory_position', '');
                                }
                            }),

                        Forms\Components\TextInput::make('signatory_name')
                            ->label('Nama Penandatangan')
                            ->placeholder('Akan terisi otomatis setelah memilih pegawai, atau isi manual jika tidak memilih dari dropdown')
                            ->maxLength(255)
                            ->required()
                            ->disabled(fn(callable $get) => !empty($get('signatory_employee_id')))
                            ->dehydrated(true)
                            ->helperText(fn(callable $get) => !empty($get('signatory_employee_id'))
                                ? 'Field ini terkunci karena Anda sudah memilih penandatangan dari dropdown. Kosongkan dropdown untuk mengedit manual.'
                                : 'Isi manual jika tidak memilih dari dropdown di atas.'),

                        Forms\Components\TextInput::make('signatory_position')
                            ->label('Jabatan Penandatangan')
                            ->placeholder('Akan terisi otomatis setelah memilih pegawai, atau isi manual jika tidak memilih dari dropdown')
                            ->maxLength(255)
                            ->required()
                            ->disabled(fn(callable $get) => !empty($get('signatory_employee_id')))
                            ->dehydrated(true)
                            ->helperText(fn(callable $get) => !empty($get('signatory_employee_id'))
                                ? 'Field ini terkunci karena Anda sudah memilih penandatangan dari dropdown. Kosongkan dropdown untuk mengedit manual.'
                                : 'Isi manual jika tidak memilih dari dropdown di atas.'),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('assigningEmployee.name')
                    ->label('Pegawai Utama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('additional_employees_names')
                    ->label('Pegawai Tambahan')
                    ->limit(40)
                    ->placeholder('—')
                    ->getStateUsing(function ($record) {
                        if (empty($record->additional_employee_ids)) {
                            return '—';
                        }
                        $employees = $record->additionalEmployees();
                        $count = $employees->count();
                        $names = $employees->pluck('name')->join(', ');

                        if ($count > 0) {
                            return "({$count}) {$names}";
                        }
                        return '—';
                    })
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (empty($state) || $state === '—' || strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_employees')
                    ->label('Total Pegawai')
                    ->getStateUsing(function ($record) {
                        $total = 1; // Pegawai utama (required)
                        if (!empty($record->additional_employee_ids)) {
                            $total += count($record->additional_employee_ids); // Pegawai tambahan
                        }
                        return $total . ' orang';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        str_contains($state, '1 orang') => 'warning',
                        default => 'success',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('employeePosition.name')
                    ->label('Posisi/Jabatan')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('task')
                    ->label('Tugas')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pdf_status')
                    ->label('Status PDF')
                    ->getStateUsing(function ($record) {
                        if (empty($record->pdf_file_path)) {
                            return 'Belum dibuat';
                        }

                        $fullPath = storage_path('app/public/' . $record->pdf_file_path);
                        if (file_exists($fullPath)) {
                            return 'Tersedia';
                        }

                        return 'File hilang';
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Tersedia' => 'success',
                        'Belum dibuat' => 'warning',
                        'File hilang' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('assignment_date')
                    ->label('Periode Penugasan')
                    ->form([
                        Forms\Components\DatePicker::make('assignment_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('assignment_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['assignment_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['assignment_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('end_date', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('employee_position_id')
                    ->label('Posisi/Jabatan')
                    ->relationship('employeePosition', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\Action::make('view_pdf')
                    ->label('Lihat PDF')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn(EmployeeAssignmentLetter $record): bool => !empty($record->pdf_file_path) && file_exists(storage_path('app/public/' . $record->pdf_file_path)))
                    ->url(fn(EmployeeAssignmentLetter $record): string => asset('storage/' . $record->pdf_file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('generate_pdf')
                    ->label('Cetak PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (EmployeeAssignmentLetter $record) {
                        // Prioritas: data dari relasi pegawai > data manual > default
                        $signatoryName = $record->signatory_name;
                        $signatoryPosition = $record->signatory_position;

                        if ($record->signatoryEmployee) {
                            $signatoryName = $record->signatoryEmployee->name;
                            $signatoryPosition = $record->signatoryEmployee->position->name ?? $signatoryPosition;
                        }

                        // Fallback ke default jika kosong
                        $signatoryName = $signatoryName ?: 'Direktur PERUMDA';
                        $signatoryPosition = $signatoryPosition ?: 'Direktur';

                        // Generate PDF dengan data dinamis
                        $pdf = Pdf::loadView('pdf.assignment-letter', [
                            'assignment' => $record,
                            'signatory_name' => $signatoryName,
                            'signatory_position' => $signatoryPosition
                        ]);

                        $filename = 'Surat_Tugas_' . $record->registration_number . '_' . date('Y-m-d') . '.pdf';
                        $filename = str_replace(['/', '\\', ' '], '_', $filename);

                        // Simpan PDF ke storage
                        $pdfPath = 'assignment_letters/' . $filename;
                        $fullPath = storage_path('app/public/' . $pdfPath);

                        // Buat direktori jika belum ada
                        if (!file_exists(dirname($fullPath))) {
                            mkdir(dirname($fullPath), 0755, true);
                        }

                        // Simpan file PDF
                        $pdf->save($fullPath);

                        // Update record dengan path PDF
                        $record->update(['pdf_file_path' => $pdfPath]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, $filename);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus'),
                ]),
            ])
            ->emptyStateHeading('Belum ada surat tugas')
            ->emptyStateDescription('Mulai dengan membuat surat tugas pertama Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
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
            'index' => Pages\ListEmployeeAssignmentLetters::route('/'),
            'create' => Pages\CreateEmployeeAssignmentLetter::route('/create'),
            'edit' => Pages\EditEmployeeAssignmentLetter::route('/{record}/edit'),
        ];
    }
}
