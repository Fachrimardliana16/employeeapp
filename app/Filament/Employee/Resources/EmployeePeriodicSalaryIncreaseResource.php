<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\Pages;
use App\Filament\Employee\Resources\EmployeePeriodicSalaryIncreaseResource\RelationManagers;
use App\Models\EmployeePeriodicSalaryIncrease;
use App\Models\Employee;
use App\Models\EmployeeAgreement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EmployeePeriodicSalaryIncreaseResource extends Resource
{
    protected static ?string $model = EmployeePeriodicSalaryIncrease::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationGroup = 'Operasional Pegawai';
    protected static ?string $navigationLabel = 'Kenaikan Gaji Berkala';
    protected static ?string $modelLabel = 'Kenaikan Gaji Berkala';
    protected static ?string $pluralModelLabel = 'Kenaikan Gaji Berkala';

    protected static ?int $navigationSort = 304;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kenaikan Gaji Berkala')
                    ->schema([
                        Forms\Components\TextInput::make('number_psi')
                            ->label('Nomor SK KGB')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('KGB/HRD/001/2025'),

                        Forms\Components\DatePicker::make('date_periodic_salary_increase')
                            ->label('Tanggal Berlaku')
                            ->required()
                            ->default(now()),

                        Forms\Components\Toggle::make('is_applied')
                            ->label('Terapkan langsung (Realisasi)')
                            ->default(true)
                            ->live()
                            ->helperText('Jika dicentang, gaji berkala di profil pegawai akan langsung diperbarui saat disimpan. Jika tidak, data akan tersimpan sebagai usulan.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pegawai')
                    ->description('Pilih Pegawai yang akan diajukan kenaikan berkala')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->relationship('employee', 'name', function (Builder $query) {
                                return $query->whereHas('employmentStatus', function (Builder $q) {
                                    $q->where('name', '!=', 'Pensiun')
                                      ->where(function ($inner) {
                                          $inner->where('name', 'like', '%tetap%')
                                                ->orWhere('name', 'like', '%permanen%')
                                                ->orWhere('name', 'like', '%PKWTT%');
                                      });
                                });
                            })
                            ->label('Pegawai')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = Employee::with(['serviceGrade'])->find($state);
                                    if ($employee) {
                                        $set('previous_basic_salary', $employee->basic_salary_amount);
                                        $set('old_mkg_label', ($employee->serviceGrade->service_grade ?? '0') . ' Tahun');
                                        
                                        // Set required Grade fields (they stay the same for KGB)
                                        $set('old_basic_salary_id', $employee->basic_salary_id);
                                        $set('new_basic_salary_id', $employee->basic_salary_id);

                                        $currentMkgValue = intval($employee->serviceGrade->service_grade ?? 0);
                                        $nextMkgValue = $currentMkgValue + 2;
                                        $nextMkg = \App\Models\MasterEmployeeServiceGrade::where('service_grade', (string)$nextMkgValue)->where('is_active', true)->first();
                                            
                                        if ($nextMkg) {
                                            $set('new_employee_service_grade_id', $nextMkg->id);
                                            $salaryRecord = \App\Models\MasterEmployeeBasicSalary::where('employee_grade_id', $employee->basic_salary_id)
                                                ->where('employee_service_grade_id', $nextMkg->id)
                                                ->first();
                                            if ($salaryRecord) {
                                                $set('total_basic_salary', (string)$salaryRecord->amount);
                                            }
                                        }
                                    }
                                }
                            })
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' - ' . ($record->nippam ?? 'No NIPPAM')),

                        Forms\Components\Placeholder::make('employee_info')
                            ->label('Info Pegawai')
                            ->content(function (Forms\Get $get) {
                                $employeeId = $get('employee_id');
                                if (!$employeeId) return 'Pilih Pegawai terlebih dahulu';

                                $employee = Employee::with(['serviceGrade', 'grade'])->find($employeeId);
                                if (!$employee) return 'Pegawai tidak ditemukan';

                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-1">
                                        <div><strong>NIPPAM:</strong> ' . ($employee->nippam ?? '-') . '</div>
                                        <div><strong>Golongan Saat Ini:</strong> ' . ($employee->grade->name ?? '-') . '</div>
                                        <div><strong>MKG Saat Ini:</strong> ' . ($employee->serviceGrade->service_grade ?? '0') . ' Tahun</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ]),

                Forms\Components\Section::make('Perubahan MKG & Gaji')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Baris 1: Data Lama
                                Forms\Components\TextInput::make('old_mkg_label')
                                    ->label('MKG Lama')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->extraInputAttributes(['class' => 'bg-gray-100']),
                                
                                Forms\Components\TextInput::make('previous_basic_salary')
                                    ->label('Gaji Pokok Lama')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->extraInputAttributes(['class' => 'bg-gray-100']),

                                // Baris 2: Usulan Baru
                                Forms\Components\Select::make('new_employee_service_grade_id')
                                    ->label('MKG Baru (Usulan)')
                                    ->options(\App\Models\MasterEmployeeServiceGrade::where('is_active', true)->orderByRaw('CAST(service_grade AS UNSIGNED) ASC')->pluck('service_grade', 'id')->map(fn($v) => $v . ' Tahun'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $empId = $get('employee_id');
                                        if ($state && $empId) {
                                            $emp = Employee::find($empId);
                                            if ($emp && $emp->basic_salary_id) {
                                                $sal = \App\Models\MasterEmployeeBasicSalary::where('employee_grade_id', $emp->basic_salary_id)->where('employee_service_grade_id', $state)->first();
                                                if ($sal) $set('total_basic_salary', (string)$sal->amount);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('total_basic_salary')
                                    ->label('Gaji Pokok Baru')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'bg-gray-100'])
                                    ->helperText('Otomatis terisi berdasarkan MKG'),
                            ]),
                    ]),

                Forms\Components\Section::make('Dokumen & Keterangan')
                    ->schema([
                        Forms\Components\FileUpload::make('proposal_docs')
                            ->label('Dokumen Usulan')
                            ->directory('employee-kgb/proposals')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120),

                        Forms\Components\FileUpload::make('docs_letter')
                            ->label('Dokumen SK Realisasi')
                            ->directory('employee-kgb/realization')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->required(fn (Forms\Get $get) => $get('is_applied'))
                            ->visible(fn (Forms\Get $get) => $get('is_applied')),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan/Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('users_id')
                            ->default(fn () => auth()->id() ?? 0),
                        
                        Forms\Components\Hidden::make('old_basic_salary_id'),
                        Forms\Components\Hidden::make('new_basic_salary_id'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number_psi')
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

                Tables\Columns\TextColumn::make('newSalaryGrade.name')
                    ->label('Golongan')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('old_mkg_label')
                    ->label('MKG / Gaji Pokok Lama')
                    ->getStateUsing(function ($record) {
                        $mkg = ($record->employee->serviceGrade->service_grade ?? '0') . ' Thn';
                        $salary = $record->oldSalaryGrade?->basic_salary ? 'Rp ' . number_format($record->oldSalaryGrade->basic_salary, 0, ',', '.') : '-';
                        return $mkg . "\n" . $salary;
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

                Tables\Columns\TextColumn::make('new_mkg_label')
                    ->label('MKG / Gaji Pokok Baru')
                    ->getStateUsing(function ($record) {
                        $mkg = ($record->newServiceGrade?->service_grade ?? '0') . ' Thn';
                        $salary = $record->total_basic_salary ? 'Rp ' . number_format($record->total_basic_salary, 0, ',', '.') : '-';
                        return $mkg . "\n" . $salary;
                    })
                    ->html()
                    ->formatStateUsing(function ($state) {
                        $lines = explode("\n", $state);
                        return '<div class="leading-tight">' .
                            '<div class="font-medium text-gray-900 dark:text-gray-100 text-sm">' . $lines[0] . '</div>' .
                            '<div class="text-xs text-success-600 dark:text-success-400 mt-0.5 font-semibold">' . ($lines[1] ?? '') . '</div>' .
                            '</div>';
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl Usulan / Realisasi')
                    ->date('d/m/Y')
                    ->description(fn ($record) => $record->applied_at ? 'Realisasi: ' . $record->applied_at->format('d/m/Y') : 'Realisasi: -')
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.next_kgb_date')
                    ->label('KGB Berikutnya')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('warning')
                    ->description(function ($record) {
                        if (!$record->employee?->next_kgb_date) return null;
                        
                        $now = now();
                        $target = $record->employee->next_kgb_date;
                        
                        if ($now->gt($target)) return 'Melewati jadwal';
                        if ($now->isSameDay($target)) return 'Hari ini';
                        
                        $diff = $now->diff($target);
                        $months = $diff->m + ($diff->y * 12);
                        $days = $diff->d;
                        
                        $parts = [];
                        if ($months > 0) $parts[] = "{$months} bulan";
                        if ($days > 0) $parts[] = "{$days} hari";
                        
                        return 'Sisa ' . (implode(' ', $parts) ?: '0 hari');
                    }),

                Tables\Columns\TextColumn::make('docs_letter')
                    ->label('Berkas')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat PDF' : '-')
                    ->color(fn ($state) => $state ? 'primary' : 'gray')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-text' : null)
                    ->url(fn ($record) => $record->docs_letter ? url('image-view/' . $record->docs_letter) : null)
                    ->openUrlInNewTab(),

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
                Tables\Filters\Filter::make('date_periodic_salary_increase')
                    ->label('Tanggal Berlaku')
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
                                fn(Builder $query, $date): Builder => $query->whereDate('date_periodic_salary_increase', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('date_periodic_salary_increase', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\Action::make('terapkan_kgb')
                        ->label('Terapkan KGB')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Forms\Components\TextInput::make('number_psi')
                                ->label('Nomor SK Realisasi')
                                ->required()
                                ->default(fn ($record) => $record->number_psi),
                            Forms\Components\DatePicker::make('applied_date')
                                ->label('Tanggal Realisasi')
                                ->required()
                                ->default(now()),
                            Forms\Components\FileUpload::make('docs_letter')
                                ->label('Dokumen SK Realisasi')
                                ->directory('employee-kgb/realization')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),
                            Forms\Components\TextInput::make('notes')
                                ->label('Keterangan Tambahan'),
                        ])
                        ->action(function ($record, array $data) {
                            if ($record->employee) {
                                // Update record
                                $record->update([
                                    'number_psi' => $data['number_psi'],
                                    'is_applied' => true,
                                    'applied_at' => now(),
                                    'applied_by' => auth()->id(),
                                    'docs_letter' => $data['docs_letter'],
                                    'notes' => ($record->notes ? $record->notes . "\n" : "") . "Direalisasikan pada " . $data['applied_date'],
                                ]);

                                // Update Employee Profile
                                $record->employee->update([
                                    'periodic_salary_date_start' => $data['applied_date'],
                                    'employee_service_grade_id' => $record->new_employee_service_grade_id ?? $record->employee->employee_service_grade_id,
                                ]);

                                Notification::make()
                                    ->title('KGB Berhasil Direalisasikan')
                                    ->body('Data Pegawai ' . $record->employee->name . ' telah diperbarui.')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn ($record) => !$record->is_applied),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])->label('Aksi')->button()->color('primary'),
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
                        Forms\Components\Select::make('is_applied')
                            ->label('Status')
                            ->options([
                                1 => 'Realisasi',
                                0 => 'Usulan',
                            ])
                            ->placeholder('Semua Status'),
                    ])
                    ->action(function (array $data) {
                        return redirect()->route('report.career-movement', [
                            'type' => 'psi',
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                            'employee_id' => $data['employee_id'],
                            'is_applied' => $data['is_applied'],
                        ]);
                    }),

                Tables\Actions\Action::make('cetak_jadwal')
                    ->label('Cetak Jadwal')
                    ->icon('heroicon-o-calendar-days')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->label('Pilih Tahun')
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                for ($i = $currentYear; $i <= $currentYear + 2; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data, $livewire) {
                        $url = route('report.kgb-schedule', ['year' => $data['year']]);
                        $livewire->js("window.open('{$url}', '_blank')");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])->label('Hapus yang Dipilih'),
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
            'index' => Pages\ListEmployeePeriodicSalaryIncreases::route('/'),
            'create' => Pages\CreateEmployeePeriodicSalaryIncrease::route('/create'),
            'edit' => Pages\EditEmployeePeriodicSalaryIncrease::route('/{record}/edit'),
        ];
    }
}
