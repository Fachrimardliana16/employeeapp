<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePromotionResource\Pages;
use App\Filament\Employee\Resources\EmployeePromotionResource\RelationManagers;
use App\Models\EmployeePromotion;
use App\Models\MasterSubDepartment;
use App\Models\MasterEmployeePosition;
use App\Models\MasterEmployeeServiceGrade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeePromotionResource extends Resource
{
    protected static ?string $model = EmployeePromotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

   protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Kenaikan Golongan';

    protected static ?string $modelLabel = 'Kenaikan Golongan';

    protected static ?string $pluralModelLabel = 'Kenaikan Golongan';

    protected static ?int $navigationSort = 305;
    public static function getModelLabel(): string
    {
        return 'Kenaikan Golongan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kenaikan Golongan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kenaikan Golongan')
                    ->schema([
                        Forms\Components\TextInput::make('decision_letter_number')
                            ->label('Nomor Surat Keputusan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SK/HRD/001/2025'),

                        Forms\Components\DatePicker::make('promotion_date')
                            ->label('Tanggal Berlaku')
                            ->required()
                            ->default(now()),

                        Forms\Components\Toggle::make('is_applied')
                            ->label('Terapkan langsung (Realisasi)')
                            ->default(true)
                            ->helperText('Jika dicentang, golongan gaji di profil pegawai akan langsung diperbarui saat disimpan. Jika tidak, data akan tersimpan sebagai usulan.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pegawai')
                    ->description('Pilih Pegawai yang akan mendapatkan kenaikan golongan (khusus Pegawai Tetap)')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->relationship(
                                'employee',
                                'name',
                                fn (Builder $query) => $query->whereHas('employmentStatus', function (Builder $query) {
                                    $query->where('name', 'like', '%tetap%')
                                          ->orWhere('name', 'like', '%permanen%')
                                          ->orWhere('name', 'like', '%PKWTT%');
                                })
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = \App\Models\Employee::find($state);
                                    if ($employee) {
                                        $set('old_basic_salary_id', $employee->basic_salary_id);
                                    }
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' - ' . ($record->nippam ?? 'No NIPPAM') . ' (' . ($record->employmentStatus?->name ?? 'Status tidak diketahui') . ')')
                            ->helperText('Hanya pegawai tetap yang dapat diproses kenaikan golongan')
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    if ($value) {
                                        $employee = \App\Models\Employee::with('employmentStatus')->find($value);
                                        if (!$employee || !$employee->employmentStatus) {
                                            $fail('Pegawai tidak memiliki status employment yang valid.');
                                            return;
                                        }

                                        $statusName = strtolower($employee->employmentStatus->name);
                                        $isPermanent = str_contains($statusName, 'tetap') ||
                                                      str_contains($statusName, 'permanen') ||
                                                      str_contains($statusName, 'pkwtt');

                                        if (!$isPermanent) {
                                            $fail('Hanya Pegawai dengan status tetap/permanen yang dapat diajukan kenaikan golongan. Status saat ini: ' . $employee->employmentStatus->name);
                                        }
                                    }
                                },
                            ]),

                        Forms\Components\Placeholder::make('employee_info')
                            ->label('Info Pegawai')
                            ->content(function (Forms\Get $get) {
                                $employeeId = $get('employee_id');
                                if (!$employeeId) {
                                    return 'Pilih Pegawai terlebih dahulu';
                                }

                                $employee = \App\Models\Employee::with(['employmentStatus', 'department', 'position'])->find($employeeId);
                                if (!$employee) {
                                    return 'Pegawai tidak ditemukan';
                                }

                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-2">
                                        <div><strong>NIPPAM:</strong> ' . ($employee->nippam ?? 'Belum ada') . '</div>
                                        <div><strong>Status Employment:</strong> <span class="px-2 py-1 text-sm bg-green-100 text-green-800 rounded">' . ($employee->employmentStatus?->name ?? 'Tidak diketahui') . '</span></div>
                                        <div><strong>Departemen:</strong> ' . ($employee->department?->name ?? 'Tidak diketahui') . '</div>
                                        <div><strong>Posisi:</strong> ' . ($employee->position?->name ?? 'Tidak diketahui') . '</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ]),

                Forms\Components\Section::make('Perubahan Gaji/Golongan')
                    ->schema([
                        Forms\Components\Select::make('old_basic_salary_id')
                            ->label('Golongan Lama')
                            ->relationship('oldSalaryGrade', 'name')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('new_basic_salary_id')
                            ->label('Golongan Baru')
                            ->relationship('newSalaryGrade', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen & Keterangan')
                    ->schema([
                        Forms\Components\FileUpload::make('proposal_docs')
                            ->label('Dokumen Usulan')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('employee-promotions/proposals')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->helperText('Dokumen usulan promosi (PDF)'),
                        
                        Forms\Components\FileUpload::make('doc_promotion')
                            ->label('Dokumen SK Realisasi')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('employee-promotions/realization')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->required(fn (Forms\Get $get) => $get('is_applied'))
                            ->helperText('Dokumen SK realisasi (PDF)'),

                        Forms\Components\Textarea::make('desc')
                            ->label('Deskripsi/Keterangan')
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
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('employee.employmentStatus', function (Builder $query) {
                $query->where('name', 'like', '%tetap%')
                      ->orWhere('name', 'like', '%permanen%')
                      ->orWhere('name', 'like', '%PKWTT%');
            }))
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Usulan / Realisasi')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->applied_at ? 'Realisasi: ' . $record->applied_at->format('d/m/Y') : 'Realisasi: -')
                    ->sortable(),

                Tables\Columns\TextColumn::make('newSalaryGrade.name')
                    ->label('Golongan Lama / Baru')
                    ->description(fn ($record) => 'Lama: ' . ($record->oldSalaryGrade?->name ?? '-'))
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('salary_increase')
                    ->label('Kenaikan Gaji')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_promotion_date')
                    ->label('Kenaikan Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('warning')
                    ->icon('heroicon-m-calendar')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('doc_promotion')
                    ->label('Berkas')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn ($record) => !empty($record->doc_promotion)),

                Tables\Columns\TextColumn::make('is_applied')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Realisasi' : 'Usulan'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('promotion_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('promotion_date', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('promotion_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\Action::make('terapkan_kenaikan')
                        ->label('Terapkan Kenaikan')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('decision_letter_number')
                                ->label('Nomor SK Realisasi')
                                ->required()
                                ->default(fn ($record) => $record->decision_letter_number),
                            Forms\Components\DatePicker::make('promotion_date')
                                ->label('Tanggal Realisasi')
                                ->required()
                                ->default(now()),
                            Forms\Components\FileUpload::make('doc_promotion')
                                ->label('Dokumen SK Realisasi')
                                ->disk('public')->visibility('public')
                                ->directory('employee-promotions/realization')
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            if ($record->employee) {
                                // Find Service Grade ID for 0 years
                                $mkg0 = MasterEmployeeServiceGrade::where('service_grade', 0)->first();

                                // Update record with realization data
                                $record->update([
                                    'decision_letter_number' => $data['decision_letter_number'],
                                    'promotion_date' => $data['promotion_date'],
                                    'doc_promotion' => $data['doc_promotion'],
                                    'is_applied' => true,
                                    'applied_at' => now(),
                                    'applied_by' => auth()->id(),
                                ]);

                                // Update Employee Profile
                                $record->employee->update([
                                    'basic_salary_id' => $record->new_basic_salary_id,
                                    'employee_service_grade_id' => $mkg0?->id ?? $record->employee->employee_service_grade_id,
                                    'grade_date_start' => $data['promotion_date'],
                                ]);

                                \Filament\Notifications\Notification::make()
                                    ->title('Kenaikan Golongan Berhasil Direalisasikan')
                                    ->body('Data Pegawai ' . $record->employee->name . ' telah diperbarui dan MKG direset ke 0.')
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
                            'type' => 'promotion',
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'employee_id' => $data['employee_id'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ])
            ->defaultSort('promotion_date', 'desc');
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
            'index' => Pages\ListEmployeePromotions::route('/'),
            'create' => Pages\CreateEmployeePromotion::route('/create'),
            'view' => Pages\ViewEmployeePromotion::route('/{record}'),
            'edit' => Pages\EditEmployeePromotion::route('/{record}/edit'),
        ];
    }
}
