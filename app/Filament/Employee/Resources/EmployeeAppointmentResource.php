<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeAppointmentResource\Pages;
use App\Models\Employee;
use App\Models\EmployeeAppointment;
use App\Models\EmployeeAgreement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeAppointmentResource extends Resource
{
    protected static ?string $model = EmployeeAppointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Pengangkatan Pegawai';

    protected static ?int $navigationSort = 305;

    public static function getModelLabel(): string
    {
        return 'Pengangkatan Pegawai';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pengangkatan Pegawai';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Surat Keputusan (SK) Pengangkatan')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('decision_letter_number')
                            ->label('Nomor SK Pengangkatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('SK/HRD/001/2026')
                            ->helperText('Nomor Surat Keputusan Pengangkatan dari Manajemen'),

                        Forms\Components\DatePicker::make('appointment_date')
                            ->label('Tanggal Efektif Pengangkatan')
                            ->required()
                            ->default(now())
                            ->helperText('Tanggal berlakunya SK Pengangkatan'),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pegawai yang Diangkat')
                    ->icon('heroicon-m-user-circle')
                    ->description('Pilih pegawai yang akan diangkat / diubah status kepegawaiannya.')
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label('Pegawai')
                            ->options(function () {
                                return Employee::query()
                                    ->whereHas('employmentStatus', function ($q) {
                                        $q->whereIn('name', ['Kontrak', 'Magang', 'Tenaga Harian Lepas', 'Calon Pegawai']);
                                    })
                                    ->with('employmentStatus')
                                    ->get()
                                    ->mapWithKeys(fn ($emp) =>
                                        [$emp->id => $emp->name . ' (' . ($emp->nippam ?? 'No NIPPAM') . ') - ' . ($emp->employmentStatus?->name ?? 'Status tidak diketahui')]
                                    );
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $employee = Employee::with(['employmentStatus'])->find($state);
                                    if ($employee) {
                                        $set('old_employment_status_id', $employee->employment_status_id);
                                        // Reset status baru saat ganti pegawai agar tidak mismatch
                                        $set('new_employment_status_id', null);
                                        $set('employee_grade_id', null);
                                    }
                                }
                            })
                            ->helperText('Pilih pegawai (Kontrak/Magang/THL/CP) yang akan diangkat statusnya'),

                        Forms\Components\Placeholder::make('employee_info_placeholder')
                            ->label('Informasi Pegawai')
                            ->content(function (Forms\Get $get) {
                                $id = $get('employee_id');
                                if (!$id) return 'Pilih pegawai terlebih dahulu.';

                                $employee = Employee::with(['employmentStatus', 'department', 'position', 'grade'])->find($id);
                                if (!$employee) return 'Pegawai tidak ditemukan.';

                                $activeContract = EmployeeAgreement::where('employees_id', $id)
                                    ->where('is_active', true)->first();

                                return new \Illuminate\Support\HtmlString('
                                    <div class="space-y-1 text-sm">
                                        <div><strong>NIPPAM:</strong> ' . ($employee->nippam ?? '-') . '</div>
                                        <div><strong>Departemen:</strong> ' . ($employee->department?->name ?? '-') . '</div>
                                        <div><strong>Jabatan:</strong> ' . ($employee->position?->name ?? '-') . '</div>
                                        <div><strong>Golongan Saat Ini:</strong> ' . ($employee->grade?->name ?? '<span class="text-gray-400 italic">Tidak ada (Non-Permanent)</span>') . '</div>
                                        <div><strong>Status Saat Ini:</strong> <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-800 rounded-full">' . ($employee->employmentStatus?->name ?? 'Tidak diketahui') . '</span></div>
                                        <div><strong>Kontrak Aktif:</strong> ' . ($activeContract ? '<span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full">' . $activeContract->agreement_number . '</span> <span class="text-xs text-gray-500">(akan dinonaktifkan otomatis)</span>' : '<span class="text-xs text-gray-400">Tidak ada kontrak aktif</span>') . '</div>
                                    </div>
                                ');
                            })
                            ->visible(fn (Forms\Get $get) => $get('employee_id')),
                    ]),

                Forms\Components\Section::make('Perubahan Status Kepegawaian')
                    ->icon('heroicon-m-arrows-right-left')
                    ->schema([
                        Forms\Components\Select::make('old_employment_status_id')
                            ->label('Status Kepegawaian Lama')
                            ->relationship('oldEmploymentStatus', 'name')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Terisi otomatis dari data pegawai yang dipilih'),

                        Forms\Components\Select::make('new_employment_status_id')
                            ->label('Status Kepegawaian Baru')
                            ->options(function (Forms\Get $get) {
                                $employeeId = $get('employee_id');
                                if (!$employeeId) return [];

                                $employee = Employee::with('employmentStatus')->find($employeeId);
                                if (!$employee) return [];

                                $currentStatusName = $employee->employmentStatus?->name;

                                $targetStatuses = [];
                                if (in_array($currentStatusName, ['Magang', 'Tenaga Harian Lepas'])) {
                                    $targetStatuses = ['Kontrak'];
                                } elseif ($currentStatusName === 'Kontrak') {
                                    $targetStatuses = ['Calon Pegawai', 'Pegawai Tetap'];
                                } elseif ($currentStatusName === 'Calon Pegawai') {
                                    $targetStatuses = ['Pegawai Tetap'];
                                } else {
                                    return \App\Models\MasterEmployeeStatusEmployment::where('is_active', true)
                                        ->pluck('name', 'id');
                                }

                                return \App\Models\MasterEmployeeStatusEmployment::whereIn('name', $targetStatuses)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('Opsi status baru ditentukan berdasarkan status saat ini'),

                        Forms\Components\Select::make('employee_grade_id')
                            ->label('Golongan Baru')
                            ->relationship('grade', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(function (Forms\Get $get) {
                                $employeeId = $get('employee_id');
                                $newStatusId = $get('new_employment_status_id');

                                if (!$employeeId || !$newStatusId) return false;

                                $employee = Employee::with('employmentStatus')->find($employeeId);
                                $newStatus = \App\Models\MasterEmployeeStatusEmployment::find($newStatusId);

                                if (!$employee || !$newStatus) return false;

                                // Muncul hanya jika dari Kontrak ke Calon Pegawai
                                return $employee->employmentStatus?->name === 'Kontrak' &&
                                       $newStatus->name === 'Calon Pegawai';
                            })
                            ->helperText('Pilih golongan untuk pengangkatan menjadi Calon Pegawai'),
                    ])->columns(2),

                Forms\Components\Section::make('Dokumen & Keterangan')
                    ->icon('heroicon-m-paper-clip')
                    ->schema([
                        Forms\Components\FileUpload::make('docs')
                            ->label('Dokumen SK Pengangkatan (PDF)')
                            ->disk('public')
                            ->directory('employee-appointments')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->visibility('public')
                            ->helperText('Upload file PDF SK Pengangkatan, maksimal 10MB'),

                        Forms\Components\Textarea::make('desc')
                            ->label('Keterangan Tambahan')
                            ->rows(3)
                            ->placeholder('Mis: Diangkat berdasarkan hasil evaluasi kinerja tahunan...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('users_id')
                    ->default(fn () => auth()->id() ?? 0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('decision_letter_number')
                    ->label('No. SK Pengangkatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('employee.nippam')
                    ->label('NIPPAM')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Tanggal Efektif')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('oldEmploymentStatus.name')
                    ->label('Status Lama')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('newEmploymentStatus.name')
                    ->label('Status Baru')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('docs')
                    ->label('Dokumen SK')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->getStateUsing(fn ($record) => !empty($record->docs)),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('appointment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('employee_id')
                    ->label('Pegawai')
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('new_employment_status_id')
                    ->label('Status Baru')
                    ->relationship('newEmploymentStatus', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('appointment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('appointment_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('appointment_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Lihat'),
                    Tables\Actions\EditAction::make()->label('Edit'),
                    Tables\Actions\Action::make('download_doc')
                        ->label('Unduh SK')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->url(fn ($record) => $record->docs ? asset('storage/' . $record->docs) : null)
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => !empty($record->docs)),
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
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus yang Dipilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmployeeAppointments::route('/'),
            'create' => Pages\CreateEmployeeAppointment::route('/create'),
            'view'   => Pages\ViewEmployeeAppointment::route('/{record}'),
            'edit'   => Pages\EditEmployeeAppointment::route('/{record}/edit'),
        ];
    }
}
