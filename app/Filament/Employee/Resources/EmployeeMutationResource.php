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

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Mutasi Pegawai';

    protected static ?string $modelLabel = 'Mutasi Pegawai';

    protected static ?string $pluralModelLabel = 'Mutasi Pegawai';

    protected static ?int $navigationSort = 302;

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
                Forms\Components\Tabs::make('mutation_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Mutasi')
                            ->icon('heroicon-m-document-text')
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
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Pegawai yang Dimutasi')
                                    ->description('Pilih Pegawai yang akan dimutasi')
                                    ->schema([
                                        Forms\Components\Select::make('employee_id')
                                            ->label('Nama Pegawai')
                                            ->relationship('employee', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                if ($state) {
                                                    $employee = Employee::with(['department', 'subDepartment', 'position'])->find($state);
                                                    if ($employee) {
                                                        // Auto-fill current data as "old" data
                                                        $set('old_department_id', $employee->departments_id);
                                                        $set('old_sub_department_id', $employee->sub_department_id);
                                                        $set('old_position_id', $employee->employee_position_id);
                                                    }
                                                }
                                            })
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' - ' . ($record->nippam ?? 'No NIPPAM'))
                                            ->helperText('Pilih Pegawai yang akan dimutasi'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Data Lama')
                            ->icon('heroicon-m-arrow-left-circle')
                            ->schema([
                                Forms\Components\Section::make('Posisi Sebelum Mutasi')
                                    ->description('Data jabatan dan departemen sebelum mutasi (akan otomatis terisi)')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('old_department_id')
                                                    ->label('Departemen Lama')
                                                    ->relationship('oldDepartment', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set) {
                                                        $set('old_sub_department_id', null);
                                                    })
                                                    ->helperText('Departemen sebelum mutasi'),
                                                Forms\Components\Select::make('old_sub_department_id')
                                                    ->label('Sub Departemen Lama')
                                                    ->relationship('oldSubDepartment', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->options(function (callable $get) {
                                                        $departmentId = $get('old_department_id');
                                                        if (!$departmentId) {
                                                            return [];
                                                        }
                                                        return MasterSubDepartment::where('departments_id', $departmentId)
                                                            ->pluck('name', 'id')
                                                            ->toArray();
                                                    })
                                                    ->disabled(fn(callable $get) => !$get('old_department_id'))
                                                    ->helperText('Sub departemen sebelum mutasi (opsional)'),
                                            ]),
                                        Forms\Components\Select::make('old_position_id')
                                            ->label('Jabatan Lama')
                                            ->relationship('oldPosition', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Jabatan sebelum mutasi'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Data Baru')
                            ->icon('heroicon-m-arrow-right-circle')
                            ->schema([
                                Forms\Components\Section::make('Posisi Setelah Mutasi')
                                    ->description('Data jabatan dan departemen baru setelah mutasi')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('new_department_id')
                                                    ->label('Departemen Baru')
                                                    ->relationship('newDepartment', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set) {
                                                        $set('new_sub_department_id', null);
                                                    })
                                                    ->helperText('Departemen tujuan mutasi'),
                                                Forms\Components\Select::make('new_sub_department_id')
                                                    ->label('Sub Departemen Baru')
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
                                                    ->helperText('Sub departemen tujuan mutasi (opsional)'),
                                            ]),
                                        Forms\Components\Select::make('new_position_id')
                                            ->label('Jabatan Baru')
                                            ->relationship('newPosition', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Jabatan baru setelah mutasi'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Dokumen')
                            ->icon('heroicon-m-document-arrow-up')
                            ->schema([
                                Forms\Components\Section::make('Dokumen Pendukung')
                                    ->description('Upload dokumen terkait mutasi')
                                    ->schema([
                                        Forms\Components\FileUpload::make('docs')
                                            ->label('Dokumen Mutasi')
                                            ->disk('public')
                                            ->directory('mutations')
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->maxSize(10240) // 10MB
                                            ->downloadable()
                                            ->openable()
                                            ->visibility('public')
                                            ->helperText('Upload dokumen pendukung mutasi (PDF, JPG, PNG) maksimal 10MB'),
                                        Forms\Components\Hidden::make('users_id')
                                            ->default(fn() => auth()->id() ?? 0),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('decision_letter_number')
                    ->label('Nomor SK')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Klik untuk copy'),

                Tables\Columns\TextColumn::make('mutation_date')
                    ->label('Tanggal Mutasi')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar-days'),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($record) => $record->employee?->name . ' (' . ($record->employee?->nippam ?? 'No NIPPAM') . ')')
                    ->html()
                    ->wrap(),

                Tables\Columns\TextColumn::make('mutation_from')
                    ->label('Dari')
                    ->getStateUsing(function ($record) {
                        $oldDept = $record->oldDepartment?->name ?? '-';
                        $oldSubDept = $record->oldSubDepartment?->name ?? '';
                        $oldPos = $record->oldPosition?->name ?? '-';

                        $from = $oldDept;
                        if ($oldSubDept) {
                            $from .= ' - ' . $oldSubDept;
                        }
                        $from .= "\n" . $oldPos;

                        return $from;
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
                        $newSubDept = $record->newSubDepartment?->name ?? '';
                        $newPos = $record->newPosition?->name ?? '-';

                        $to = $newDept;
                        if ($newSubDept) {
                            $to .= ' - ' . $newSubDept;
                        }
                        $to .= "\n" . $newPos;

                        return $to;
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
                    ->label('Dokumen')
                    ->formatStateUsing(fn($record) => $record->docs ? 'Ada Dokumen' : 'Tidak Ada')
                    ->badge()
                    ->color(fn($record) => $record->docs ? 'success' : 'gray')
                    ->icon(fn($record) => $record->docs ? 'heroicon-m-document-check' : 'heroicon-m-document-minus')
                    ->url(fn($record) => $record->docs ? asset('storage/' . $record->docs) : null)
                    ->openUrlInNewTab()
                    ->tooltip(fn($record) => $record->docs ? 'Klik untuk lihat dokumen' : 'Belum ada dokumen'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('System'),

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
            ->defaultSort('mutation_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->relationship('employee', 'name')
                    ->label('Pegawai')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('old_department_id')
                    ->relationship('oldDepartment', 'name')
                    ->label('Departemen Asal')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('new_department_id')
                    ->relationship('newDepartment', 'name')
                    ->label('Departemen Tujuan')
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
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),

                Tables\Actions\Action::make('apply_mutation')
                    ->label('Terapkan Mutasi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Terapkan Mutasi Pegawai')
                    ->modalSubheading('Apakah Anda yakin ingin menerapkan mutasi ini? Data Pegawai akan diperbarui sesuai dengan mutasi.')
                    ->modalIcon('heroicon-o-arrow-path')
                    ->action(function (EmployeeMutation $record) {
                        if ($record->employee) {
                            $record->employee->update([
                                'departments_id' => $record->new_department_id,
                                'sub_department_id' => $record->new_sub_department_id,
                                'employee_position_id' => $record->new_position_id,
                            ]);

                            Notification::make()
                                ->title('Mutasi Berhasil Diterapkan')
                                ->body('Data Pegawai ' . $record->employee->name . ' telah diperbarui sesuai mutasi.')
                                ->success()
                                ->send();
                        }
                    })
                    ->visible(fn(EmployeeMutation $record) => $record->employee !== null),
            ])
            ->headerActions([
                Tables\Actions\Action::make('generate_report')
                    ->label('Cetak Report PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function () {
                        return static::generatePDFReport();
                    })
                    ->tooltip('Generate laporan mutasi Pegawai dalam format PDF'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih'),
                ]),
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
