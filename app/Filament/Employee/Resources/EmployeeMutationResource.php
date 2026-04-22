<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeMutationResource\Pages;
use App\Filament\Employee\Resources\EmployeeMutationResource\RelationManagers;
use App\Models\EmployeeMutation;
use App\Models\Employee;
use App\Models\MasterDepartment;
use App\Models\MasterSubDepartment;
use App\Models\MasterEmployeePosition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeMutationResource extends Resource
{
    protected static ?string $model = EmployeeMutation::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Mutasi Pegawai';

    protected static ?string $modelLabel = 'Mutasi Pegawai';

    protected static ?string $pluralModelLabel = 'Mutasi Pegawai';

    protected static ?int $navigationSort = 303;

    public static function getModelLabel(): string
    {
        return 'Mutasi Pegawai';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Mutasi Pegawai';
    }

    public static function generatePDFReport()
    {
        // Generate PDF menggunakan DomPDF dengan orientasi landscape
        $pdf = Pdf::loadView('reports.employee-mutation-report', [
            'data' => static::getReportData(),
            'generated_at' => now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s')
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Report_Mutasi_Pegawai_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    protected static function getReportData()
    {
        $mutations = EmployeeMutation::with([
            'employee',
            'oldDepartment',
            'newDepartment',
            'oldSubDepartment',
            'newSubDepartment',
            'oldPosition',
            'newPosition',
            'user'
        ])->orderBy('mutation_date', 'desc')->get();

        // Hitung statistik berdasarkan jenis mutasi
        $mutationStats = [
            'department_change' => $mutations->filter(function ($mutation) {
                return $mutation->old_department_id !== $mutation->new_department_id;
            })->count(),
            'position_change' => $mutations->filter(function ($mutation) {
                return $mutation->old_position_id !== $mutation->new_position_id;
            })->count(),
            'both_change' => $mutations->filter(function ($mutation) {
                return $mutation->old_department_id !== $mutation->new_department_id &&
                    $mutation->old_position_id !== $mutation->new_position_id;
            })->count(),
        ];

        // Hitung statistik berdasarkan departemen tujuan
        $departmentStats = $mutations->groupBy('newDepartment.name')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5);

        // Hitung statistik berdasarkan bulan
        $monthlyStats = $mutations->groupBy(function ($mutation) {
            return $mutation->mutation_date->format('Y-m');
        })->map(function ($group) {
            return $group->count();
        })->sortKeysDesc()->take(6);

        return [
            'mutations' => $mutations,
            'mutation_stats' => $mutationStats,
            'department_stats' => $departmentStats,
            'monthly_stats' => $monthlyStats,
            'total_mutations' => $mutations->count(),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                                Forms\Components\Section::make('Detail Surat Keputusan')
                                    ->description('Informasi dasar tentang keputusan mutasi')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('decision_letter_number')
                                                    ->label('Nomor Surat Keputusan')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: SK/001/HRD/2025')
                                                    ->helperText('Nomor resmi surat keputusan mutasi'),
                                                Forms\Components\DatePicker::make('mutation_date')
                                                    ->label('Tanggal Mutasi')
                                                    ->required()
                                                    ->default(now())
                                                    ->helperText('Tanggal efektif berlakunya mutasi'),
                                                Forms\Components\Toggle::make('is_applied')
                                                    ->label('Terapkan langsung (Realisasi)')
                                                    ->default(true)
                                                    ->live()
                                                    ->helperText('Jika dicentang, data jabatan/bagian di profil pegawai akan langsung diperbarui saat disimpan. Jika tidak, data akan tersimpan sebagai usulan.')
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Pegawai yang Dimutasi')
                                    ->description('Pilih Pegawai yang akan dimutasi')
                                    ->schema([
                                        Forms\Components\Select::make('employee_id')
                                            ->label('Nama Pegawai')
                                            ->relationship('employee', 'name', function ($query) {
                                                return $query->whereHas('employmentStatus', function ($q) {
                                                    $q->where('name', '!=', 'Pensiun');
                                                });
                                            })
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                if ($state) {
                                                    $employee = \App\Models\Employee::with(['department', 'subDepartment', 'position'])->find($state);
                                                    if ($employee) {
                                                        // Auto-fill current data as "old" and "new" data (since position stays same in mutation)
                                                        $set('old_department_id', $employee->departments_id);
                                                        $set('old_sub_department_id', $employee->sub_department_id);
                                                        $set('old_position_id', $employee->employee_position_id);
                                                        $set('new_position_id', $employee->employee_position_id);
                                                    }
                                                }
                                            })
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' - ' . ($record->nippam ?? 'No NIPPAM'))
                                            ->helperText('Pilih Pegawai yang akan dimutasi'),
                                    ]),
                                    
                                Forms\Components\Section::make('Posisi Sebelum Mutasi')
                                    ->description('Data jabatan dan bagian sebelum mutasi (akan otomatis terisi)')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('old_department_id')
                                                    ->label('Bagian Lama')
                                                    ->relationship('oldDepartment', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set) {
                                                        $set('old_sub_department_id', null);
                                                    })
                                                    ->helperText('Bagian sebelum mutasi'),
                                                Forms\Components\Select::make('old_sub_department_id')
                                                    ->label('Sub Bagian Lama')
                                                    ->relationship('oldSubDepartment', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->options(function (callable $get) {
                                                        $departmentId = $get('old_department_id');
                                                        if (!$departmentId) {
                                                            return [];
                                                        }
                                                        return MasterSubDepartment::where('departments_id', $departmentId)
                                                            ->pluck('name', 'id')
                                                            ->toArray();
                                                    })
                                                    ->helperText('Sub bagian sebelum mutasi (opsional)'),
                                            ]),
                                        Forms\Components\Select::make('old_position_id')
                                            ->label('Jabatan Lama')
                                            ->relationship('oldPosition', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Jabatan sebelum mutasi'),
                                    ]),
                                    
                                Forms\Components\Section::make('Posisi Setelah Mutasi')
                                    ->description('Data jabatan dan bagian baru setelah mutasi')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('new_department_id')
                                                    ->label('Bagian Baru')
                                                    ->relationship('newDepartment', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set) {
                                                        $set('new_sub_department_id', null);
                                                    })
                                                    ->helperText('Bagian tujuan mutasi'),
                                                Forms\Components\Select::make('new_sub_department_id')
                                                    ->label('Sub Bagian Baru')
                                                    ->relationship('newSubDepartment', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->options(function (callable $get) {
                                                        $departmentId = $get('new_department_id');
                                                        if (!$departmentId) {
                                                            return [];
                                                        }
                                                        return MasterSubDepartment::where('departments_id', $departmentId)
                                                            ->pluck('name', 'id')
                                                            ->toArray();
                                                    })
                                                    ->disabled(fn(callable $get) => !$get('new_department_id'))
                                                    ->helperText('Sub bagian tujuan mutasi (opsional)'),
                                            ]),
                                        Forms\Components\Select::make('new_position_id')
                                            ->label('Jabatan Baru')
                                            ->relationship('newPosition', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->disabled()
                                            ->dehydrated()
                                            ->helperText('Jabatan tetap sama untuk Mutasi. Gunakan menu Promosi/Demosi untuk mengubah jabatan.'),
                                    ]),
                                    
                                Forms\Components\Section::make('Dokumen Pendukung')
                                    ->description('Upload dokumen terkait mutasi')
                                    ->schema([
                                        Forms\Components\FileUpload::make('proposal_docs')
                                            ->label('Dokumen Usulan')
                                        ->disk('public')
                                        ->visibility('public')
                                        ->directory('mutations/proposals')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->maxSize(10240)
                                        ->downloadable()
                                        ->openable()
                                        ->helperText('Upload dokumen usulan mutasi (PDF) maksimal 10MB'),
                                    Forms\Components\FileUpload::make('docs')
                                        ->label('Dokumen SK Realisasi')
                                        ->disk('public')
                                        ->directory('mutations/realization')
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->maxSize(10240) // 10MB
                                        ->downloadable()
                                        ->openable()
                                        ->visibility('public')
                                        ->required(fn (Forms\Get $get) => $get('is_applied'))
                                        ->visible(fn (Forms\Get $get) => $get('is_applied'))
                                        ->helperText('Upload dokumen SK realisasi mutasi (PDF) maksimal 10MB'),
                                        Forms\Components\Hidden::make('users_id')
                                            ->default(fn() => auth()->id() ?? 0),
                                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('decision_letter_number')
                    ->label('No. SK')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->description(fn ($record) => $record->employee?->nippam ?? '-')
                    ->searchable(['name', 'nippam'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Usulan / Realisasi')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->applied_at ? 'Realisasi: ' . $record->applied_at->format('d/m/Y') : 'Realisasi: -')
                    ->sortable(),

                Tables\Columns\TextColumn::make('mutation_from')
                    ->label('Dari')
                    ->getStateUsing(function ($record) {
                        $oldDept = $record->oldDepartment?->name ?? '-';
                        $oldPos = $record->oldPosition?->name ?? '-';
                        return $oldDept . "\n" . $oldPos;
                    })
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $lines = explode("\n", $state);
                        return '<div class="leading-tight">' .
                            '<div class="font-medium text-gray-900 dark:text-gray-100 text-sm">' . $lines[0] . '</div>' .
                            '<div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' . ($lines[1] ?? '') . '</div>' .
                            '</div>';
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('mutation_to')
                    ->label('Ke')
                    ->getStateUsing(function ($record) {
                        $newDept = $record->newDepartment?->name ?? '-';
                        $newPos = $record->newPosition?->name ?? '-';
                        return $newDept . "\n" . $newPos;
                    })
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $lines = explode("\n", $state);
                        return '<div class="leading-tight">' .
                            '<div class="font-medium text-gray-900 dark:text-gray-100 text-sm">' . $lines[0] . '</div>' .
                            '<div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">' . ($lines[1] ?? '') . '</div>' .
                            '</div>';
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('docs')
                    ->label('Berkas')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat PDF' : '-')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : null)
                    ->url(fn ($record) => $record->docs ? url('image-view/' . $record->docs) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('is_applied')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Realisasi' : 'Usulan'),
            ])
            ->defaultSort('mutation_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Pegawai')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('old_department_id')
                    ->relationship('oldDepartment', 'name')
                    ->label('Bagian Asal')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('new_department_id')
                    ->relationship('newDepartment', 'name')
                    ->label('Bagian Tujuan')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('mutation_date')
                    ->label('Periode Mutasi')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('mutation_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('mutation_date', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('has_documents')
                    ->label('Memiliki Dokumen')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('docs')),

                Tables\Filters\Filter::make('no_documents')
                    ->label('Tanpa Dokumen')
                    ->query(fn(Builder $query): Builder => $query->whereNull('docs')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\Action::make('apply_mutation')
                        ->label('Terapkan Mutasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('decision_letter_number')
                                ->label('Nomor SK Realisasi')
                                ->required()
                                ->default(fn (EmployeeMutation $record) => $record->decision_letter_number),
                            Forms\Components\DatePicker::make('mutation_date')
                                ->label('Tanggal Realisasi')
                                ->required()
                                ->default(now()),
                            Forms\Components\FileUpload::make('docs')
                                ->label('Dokumen SK Realisasi')
                                ->disk('public')->visibility('public')
                                ->directory('mutations/realization')
                                ->required(),
                        ])
                        ->action(function (EmployeeMutation $record, array $data) {
                            if ($record->employee) {
                                // Update record with realization data
                                $record->update([
                                    'decision_letter_number' => $data['decision_letter_number'],
                                    'mutation_date' => $data['mutation_date'],
                                    'docs' => $data['docs'],
                                    'is_applied' => true,
                                    'applied_at' => now(),
                                    'applied_by' => auth()->id(),
                                ]);

                                // Update Employee Profile
                                $record->employee->update([
                                    'departments_id' => $record->new_department_id,
                                    'sub_department_id' => $record->new_sub_department_id,
                                    'employee_position_id' => $record->new_position_id,
                                ]);

                                Notification::make()
                                    ->title('Mutasi Berhasil Direalisasikan')
                                    ->body('Data Pegawai ' . $record->employee->name . ' telah diperbarui.')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn(EmployeeMutation $record) => !$record->is_applied),
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('cetak_laporan')
                    ->label('Cetak Laporan')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->default(now()),
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai (Opsional)')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('report.career-movement', [
                            'type' => 'mutation',
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'employee_id' => $data['employee_id'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
            ])
            ->emptyStateHeading('Belum Ada Data Mutasi')
            ->emptyStateDescription('Klik tombol "Buat Baru" untuk menambahkan data mutasi Pegawai.')
            ->emptyStateIcon('heroicon-o-arrow-path');
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
            'index' => Pages\ListEmployeeMutations::route('/'),
            'create' => Pages\CreateEmployeeMutation::route('/create'),
            'view' => Pages\ViewEmployeeMutation::route('/{record}'),
            'edit' => Pages\EditEmployeeMutation::route('/{record}/edit'),
        ];
    }
}
