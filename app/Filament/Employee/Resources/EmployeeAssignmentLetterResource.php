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
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'on progress')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'on progress')->count() > 0 ? 'warning' : 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama Surat Tugas')
                    ->schema([
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Nomor Surat Tugas')
                            ->default(fn () => \App\Services\LetterNumberService::generateAssignmentNumber())
                            ->disabled()
                            ->dehydrated()
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required(),

                        Forms\Components\Select::make('assigning_employee_id')
                            ->label('Pegawai Di Tugaskan')
                            ->relationship('assigningEmployee', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (blank($state)) {
                                    $set('employee_position_id', null);
                                    return;
                                }

                                $employee = \App\Models\Employee::find($state);
                                if ($employee) {
                                    $set('employee_position_id', $employee->employee_position_id);
                                }
                            })
                            ->helperText('Pegawai yang diberi tugas'),

                        Forms\Components\Select::make('employee_position_id')
                            ->label('Posisi/Jabatan')
                            ->relationship('employeePosition', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Otomatis terkunci sesuai jabatan pegawai'),
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

                Forms\Components\Section::make('Pegawai Tambahan')
                    ->description('Tambahkan pegawai lain yang ikut dalam penugasan jika ada.')
                    ->schema([
                        Forms\Components\Repeater::make('additional_employees_detail')
                            ->label('Daftar Pegawai Tambahan')
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
                            ->defaultItems(0),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('Penandatangan')
                    ->schema([
                        Forms\Components\Toggle::make('is_manual_signatory')
                            ->label('Input Manual Penandatangan')
                            ->helperText('Aktifkan jika ingin menginput nama penandatangan secara manual (bukan dari database)')
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Forms\Components\Toggle $component, $state, ?\App\Models\EmployeeAssignmentLetter $record) {
                                // Jika sedang edit dan ada data manual tapi tidak ada relasi pegawai, maka otomatis aktifkan toggle manual
                                if ($record && blank($record->signatory_employee_id) && !blank($record->signatory_name)) {
                                    $component->state(true);
                                }
                            })
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    // Pindah ke manual, hapus pilihan dari DB
                                    $set('signatory_employee_id', null);
                                } else {
                                    // Pindah ke database, hapus isian manual
                                    $set('signatory_name', null);
                                    $set('signatory_position', null);
                                }
                            }),

                        Forms\Components\Group::make([
                            Forms\Components\Select::make('signatory_employee_id')
                                ->label('Pilih Penandatangan')
                                ->options(function () {
                                    return \App\Models\Employee::getSignatoryEmployees();
                                })
                                ->searchable()
                                ->preload()
                                ->placeholder('Cari nama pegawai...')
                                ->live()
                                ->required(fn(Forms\Get $get) => !$get('is_manual_signatory'))
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    if ($state) {
                                        $employee = \App\Models\Employee::getSignatoryById($state);
                                        if ($employee) {
                                            $set('signatory_name', $employee->name);
                                            $set('signatory_position', $employee->position->name ?? '');
                                        }
                                    }
                                }),

                            Forms\Components\Placeholder::make('signatory_summary')
                                ->label('Detail Penandatangan')
                                ->content(function (Forms\Get $get) {
                                    $name = $get('signatory_name');
                                    $position = $get('signatory_position');
                                    if (blank($name)) return 'Belum ada pegawai dipilih.';
                                    return new \Illuminate\Support\HtmlString("
                                        <div class='flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700'>
                                            <div class='flex-1'>
                                                <div class='text-sm font-medium text-gray-900 dark:text-gray-100'>$name</div>
                                                <div class='text-xs text-gray-500 dark:text-gray-400'>$position</div>
                                            </div>
                                        </div>
                                    ");
                                })
                                ->visible(fn(Forms\Get $get) => !$get('is_manual_signatory') && !blank($get('signatory_employee_id'))),
                        ])
                        ->visible(fn(Forms\Get $get) => !$get('is_manual_signatory')),

                        Forms\Components\Group::make([
                            Forms\Components\TextInput::make('signatory_name')
                                ->label('Nama Penandatangan (Manual)')
                                ->placeholder('Contoh: Budi Santoso, S.T.')
                                ->maxLength(255)
                                ->required(fn(Forms\Get $get) => $get('is_manual_signatory')),

                            Forms\Components\TextInput::make('signatory_position')
                                ->label('Jabatan Penandatangan (Manual)')
                                ->placeholder('Contoh: Plh. Direktur Utama')
                                ->maxLength(255)
                                ->required(fn(Forms\Get $get) => $get('is_manual_signatory')),
                        ])
                        ->columns(2)
                        ->visible(fn(Forms\Get $get) => $get('is_manual_signatory')),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Forms\Components\Section::make('Arsip Digital')
                    ->description('Upload surat tugas yang sudah ditanda tangani dan stempel basah.')
                    ->schema([
                        Forms\Components\FileUpload::make('signed_file_path')
                            ->label('Upload Surat Tugas (TTD & Stempel)')
                            ->disk('public')
                            ->directory('assignment_letters_signed')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(2048)
                            ->downloadable()
                            ->openable()
                            ->helperText('Upload file PDF hasil scan surat tugas yang sudah ditelusuri.'),
                        Forms\Components\Select::make('status')
                            ->label('Status Surat')
                            ->options([
                                'on progress' => 'On Progress',
                                'selesai' => 'Selesai',
                            ])
                            ->default('on progress')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'selesai' => 'success',
                        'on progress' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->sortable(),

                Tables\Columns\IconColumn::make('archives')
                    ->label('Arsip (I/K)')
                    ->getStateUsing(fn($record) => (bool)$record->signed_file_path && (bool)$record->visit_file_path)
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-document-text')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn($record) => "Internal: " . ($record->signed_file_path ? '✅' : '❌') . " | Kunjungan: " . ($record->visit_file_path ? '✅' : '❌')),

                Tables\Columns\TextColumn::make('assigningEmployee.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->employeePosition?->name, position: 'below'),

                Tables\Columns\TextColumn::make('task')
                    ->label('Tugas')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->task),

                Tables\Columns\TextColumn::make('timespan')
                    ->label('Waktu')
                    ->getStateUsing(fn($record) => $record->start_date->format('d/m/y') . ' - ' . $record->end_date->format('d/m/y'))
                    ->description(fn($record) => $record->start_date->diffInDays($record->end_date) + 1 . ' hari'),


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
            ->headerActions([
                Tables\Actions\Action::make('cetak_report')
                    ->label('Cetak Report')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                        Forms\Components\Select::make('employee_id')
                            ->label('Filter Pegawai')
                            ->options(\App\Models\Employee::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $query = EmployeeAssignmentLetter::query();

                        if ($data['date_from']) {
                            $query->whereDate('start_date', '>=', $data['date_from']);
                        }
                        if ($data['date_until']) {
                            $query->whereDate('end_date', '<=', $data['date_until']);
                        }
                        if ($data['employee_id']) {
                            $query->where('assigning_employee_id', $data['employee_id']);
                        }

                        $records = $query->get();
                        $employeeName = $data['employee_id'] ? \App\Models\Employee::find($data['employee_id'])?->name : null;

                        $pdf = Pdf::loadView('pdf.report-summary', [
                            'title' => 'Surat Tugas',
                            'data' => $records,
                            'startDate' => $data['date_from'],
                            'endDate' => $data['date_until'],
                            'employeeName' => $employeeName,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'Report_Surat_Tugas_' . now()->format('YmdHis') . '.pdf');
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
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
                    Tables\Actions\Action::make('upload_signed')
                        ->label('Arsip TTD Internal')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('info')
                        ->form([
                            Forms\Components\FileUpload::make('signed_file_path')
                                ->label('File Scan TTD Internal (PDF)')
                                ->disk('public')
                                ->directory('assignment_letters_internal')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                        ])
                        ->action(function (EmployeeAssignmentLetter $record, array $data) {
                            $record->update([
                                'signed_file_path' => $data['signed_file_path'],
                            ]);
                        })
                        ->visible(fn(EmployeeAssignmentLetter $record) => empty($record->signed_file_path)),
                    Tables\Actions\Action::make('selesaikan')
                        ->label('Selesaikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\FileUpload::make('visit_file_path')
                                ->label('File Scan Cap Kunjungan (PDF)')
                                ->disk('public')
                                ->directory('assignment_letters_complete')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                        ])
                        ->action(function (EmployeeAssignmentLetter $record, array $data) {
                            $record->update([
                                'visit_file_path' => $data['visit_file_path'],
                                'status' => 'selesai',
                            ]);
                        })
                        ->visible(fn(EmployeeAssignmentLetter $record) => !empty($record->signed_file_path) && $record->status !== 'selesai'),
                    Tables\Actions\Action::make('view_signed')
                        ->label('Lihat Arsip')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->form([
                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('internal_file')
                                    ->label('Arsip TTD Internal')
                                    ->content(fn($record) => $record->signed_file_path ? new \Illuminate\Support\HtmlString("<a href='".asset('storage/'.$record->signed_file_path)."' target='_blank' class='text-primary-600 underline'>Buka File Internal</a>") : 'Belum diupload'),
                                Forms\Components\Placeholder::make('visit_file')
                                    ->label('Arsip Cap Kunjungan')
                                    ->content(fn($record) => $record->visit_file_path ? new \Illuminate\Support\HtmlString("<a href='".asset('storage/'.$record->visit_file_path)."' target='_blank' class='text-primary-600 underline'>Buka File Kunjungan</a>") : 'Belum diupload'),
                            ])->columns(2)
                        ])
                        ->visible(fn(EmployeeAssignmentLetter $record) => !empty($record->signed_file_path)),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                ->label('Aksi')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
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
