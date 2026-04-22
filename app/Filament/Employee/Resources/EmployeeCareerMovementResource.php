<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeCareerMovementResource\Pages;
use App\Models\EmployeeCareerMovement;
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

class EmployeeCareerMovementResource extends Resource
{
    protected static ?string $model = EmployeeCareerMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Promosi & Demosi';

    protected static ?string $modelLabel = 'Promosi & Demosi';

    protected static ?string $pluralModelLabel = 'Promosi & Demosi';

    protected static ?int $navigationSort = 306;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Keputusan')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('decision_letter_number')
                            ->label('Nomor SK')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SK/HRD/PRO/001/2026'),

                        Forms\Components\DatePicker::make('movement_date')
                            ->label('Tanggal Efektif')
                            ->required()
                            ->default(now()),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Pergerakan')
                            ->options([
                                'promotion' => 'Promosi',
                                'demotion' => 'Demosi',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_applied')
                            ->label('Terapkan langsung (Realisasi)')
                            ->default(true)
                            ->live()
                            ->helperText('Jika dicentang, data jabatan/bagian di profil pegawai akan langsung diperbarui saat disimpan. Jika tidak, data akan tersimpan sebagai usulan.'),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pegawai')
                    ->icon('heroicon-m-user')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship('employee', 'name', function ($query) {
                                return $query->whereHas('employmentStatus', function ($q) {
                                    $q->where('name', '!=', 'Pensiun');
                                });
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = Employee::find($state);
                                    if ($employee) {
                                        $set('old_department_id', $employee->departments_id);
                                        $set('old_sub_department_id', $employee->sub_department_id);
                                        $set('old_position_id', $employee->employee_position_id);
                                    }
                                }
                            })
                            ->helperText('Pilih pegawai. Data lamanya akan terisi otomatis.'),

                        Forms\Components\Placeholder::make('current_info')
                            ->label('Informasi Saat Ini')
                            ->content(function (Forms\Get $get) {
                                $id = $get('employee_id');
                                if (!$id) return 'Pilih pegawai terlebih dahulu.';
                                $employee = Employee::with(['department', 'subDepartment', 'position'])->find($id);
                                if (!$employee) return 'Data tidak ditemukan.';

                                return new \Illuminate\Support\HtmlString('
                                    <div class="text-sm space-y-1">
                                        <div><strong>Bagian:</strong> ' . ($employee->department?->name ?? '-') . '</div>
                                        <div><strong>Sub Bagian:</strong> ' . ($employee->subDepartment?->name ?? '-') . '</div>
                                        <div><strong>Jabatan:</strong> ' . ($employee->position?->name ?? '-') . '</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ])->columns(2),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Section::make('Posisi Lama (Sistem)')
                            ->description('Akan terisi otomatis berdasarkan data pegawai terpilih.')
                            ->schema([
                                Forms\Components\Select::make('old_department_id')
                                    ->label('Bagian Lama')
                                    ->relationship('oldDepartment', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('old_sub_department_id')
                                    ->label('Sub Bagian Lama')
                                    ->relationship('oldSubDepartment', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                                Forms\Components\Select::make('old_position_id')
                                    ->label('Jabatan Lama')
                                    ->relationship('oldPosition', 'name')
                                    ->disabled()
                                    ->dehydrated(),
                            ])->columnSpan(1),

                        Forms\Components\Section::make('Posisi Baru (Target)')
                            ->description('Tentukan jabatan dan bagian baru hasil promosi/demosi.')
                            ->schema([
                                Forms\Components\Select::make('new_department_id')
                                    ->label('Bagian Baru')
                                    ->relationship('newDepartment', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('new_sub_department_id', null)),
                                
                                Forms\Components\Select::make('new_sub_department_id')
                                    ->label('Sub Bagian Baru')
                                    ->relationship('newSubDepartment', 'name', fn (Builder $query, Forms\Get $get) => 
                                        $query->where('departments_id', $get('new_department_id'))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (Forms\Get $get) => !$get('new_department_id')),
                                
                                Forms\Components\Select::make('new_position_id')
                                    ->label('Jabatan Baru')
                                    ->options(function (Forms\Get $get) {
                                        $oldPositionId = $get('old_position_id');
                                        return MasterEmployeePosition::query()
                                            ->when($oldPositionId, fn ($query) => $query->where('id', '!=', $oldPositionId))
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                            ])->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Tambahan')
                    ->schema([
                        Forms\Components\FileUpload::make('proposal_docs')
                            ->label('Dokumen Usulan')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('career-movements/proposals')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('doc_path')
                            ->label('Dokumen SK Realisasi (PDF)')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('career-movements/realization')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->required(fn (Forms\Get $get) => $get('is_applied'))
                            ->visible(fn (Forms\Get $get) => $get('is_applied')),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('users_id')
                    ->default(fn () => auth()->id()),
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
                    ->label('Nama Pegawai / NIPPAM')
                    ->description(fn ($record) => $record->employee?->nippam ?? '-')
                    ->searchable(['name', 'nippam'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promotion' => 'success',
                        'demotion' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'promotion' => 'PROMOSI',
                        'demotion' => 'DEMOSI',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Usulan / Realisasi')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->applied_at ? 'Realisasi: ' . $record->applied_at->format('d/m/Y') : 'Realisasi: -')
                    ->sortable(),

                Tables\Columns\TextColumn::make('oldPosition.name')
                    ->label('Jabatan Lama')
                    ->description(fn ($record) => $record->oldDepartment?->name)
                    ->searchable(),

                Tables\Columns\TextColumn::make('newPosition.name')
                    ->label('Jabatan Baru')
                    ->description(fn ($record) => $record->newDepartment?->name)
                    ->searchable(),

                Tables\Columns\TextColumn::make('doc_path')
                    ->label('Berkas')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat PDF' : '-')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : null)
                    ->url(fn ($record) => $record->doc_path ? asset('storage/' . $record->doc_path) : null)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('is_applied')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Realisasi' : 'Usulan'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'promotion' => 'Promosi',
                        'demotion' => 'Demosi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('terapkan_pergerakan')
                        ->label('Terapkan Realisasi')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('decision_letter_number')
                                ->label('Nomor SK Realisasi')
                                ->required()
                                ->default(fn ($record) => $record->decision_letter_number),
                            Forms\Components\DatePicker::make('movement_date')
                                ->label('Tanggal Realisasi')
                                ->required()
                                ->default(now()),
                            Forms\Components\FileUpload::make('doc_path')
                                ->label('Dokumen SK Realisasi')
                                ->disk('public')
                                ->visibility('public')
                                ->directory('career-movements/realization')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            if ($record->employee) {
                                // Update record
                                $record->update([
                                    'decision_letter_number' => $data['decision_letter_number'],
                                    'movement_date' => $data['movement_date'],
                                    'doc_path' => $data['doc_path'],
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

                                \Filament\Notifications\Notification::make()
                                    ->title('Karir Berhasil Direalisasikan')
                                    ->body('Data Pegawai ' . $record->employee->name . ' telah diperbarui.')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => !$record->is_applied),

                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
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
                            'type' => 'career_movement',
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'employee_id' => $data['employee_id'],
                        ]);
                    }),
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
            'index' => Pages\ListEmployeeCareerMovements::route('/'),
            'create' => Pages\CreateEmployeeCareerMovement::route('/create'),
            'view' => Pages\ViewEmployeeCareerMovement::route('/{record}'),
            'edit' => Pages\EditEmployeeCareerMovement::route('/{record}/edit'),
        ];
    }
}
