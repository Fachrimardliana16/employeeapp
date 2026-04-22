<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\EmployeeAgreementResource\Pages;
use App\Filament\Employee\Resources\EmployeeAgreementResource\RelationManagers;
use App\Models\EmployeeAgreement;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class EmployeeAgreementResource extends Resource
{
    protected static ?string $model = EmployeeAgreement::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Operasional Pegawai';

    protected static ?string $navigationLabel = 'Kontrak Kerja';

    protected static ?int $navigationSort = 301;

    public static function getModelLabel(): string
    {
        return 'Kontrak Kerja';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kontrak Kerja';
    }

    public static function generatePDFReport()
    {
        // Generate PDF menggunakan DomPDF dengan orientasi landscape
        $pdf = Pdf::loadView('reports.employee-agreement-report', [
            'data' => static::getReportData(),
            'generated_at' => now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s')
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Report_Kontrak_Pegawai_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    protected static function getReportData()
    {
        $agreements = EmployeeAgreement::with([
            'masterAgreement',
            'employeePosition',
            'employmentStatus',
            'education',
            'basicSalaryGrade',
            'department',
            'subDepartment'
        ])->get();

        // Hitung jumlah berdasarkan jenis kontrak (PKWT dan PKWTT)
        $contractStats = [
            'PKWT' => $agreements->filter(function ($agreement) {
                return $agreement->masterAgreement &&
                    (stripos($agreement->masterAgreement->name, 'PKWT') !== false ||
                        stripos($agreement->masterAgreement->name, 'Kontrak') !== false ||
                        stripos($agreement->masterAgreement->name, 'Waktu Tertentu') !== false);
            })->count(),
            'PKWTT' => $agreements->filter(function ($agreement) {
                return $agreement->masterAgreement &&
                    (stripos($agreement->masterAgreement->name, 'PKWTT') !== false ||
                        stripos($agreement->masterAgreement->name, 'Tetap') !== false ||
                        stripos($agreement->masterAgreement->name, 'Waktu Tidak Tertentu') !== false);
            })->count(),
        ];

        // Hitung jumlah berdasarkan posisi
        $positionStats = [
            'staff' => $agreements->whereIn('employeePosition.name', ['Staff', 'Staf'])->count(),
            'kepala_sub_bagian' => $agreements->where('employeePosition.name', 'like', '%Kepala Sub%')->count(),
            'kepala_bagian' => $agreements->where('employeePosition.name', 'like', '%Kepala Bagian%')->count(),
            'direksi' => $agreements->where('employeePosition.name', 'like', '%Direktur%')->count(),
        ];

        return [
            'agreements' => $agreements,
            'contract_stats' => $contractStats,
            'position_stats' => $positionStats,
            'total_agreements' => $agreements->count(),
        ];
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('employee_agreement_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Kontrak')
                            ->icon('heroicon-m-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('agreement_number')
                                    ->label('Nomor Kontrak')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('agreement_id')
                                    ->relationship('masterAgreement', 'name')
                                    ->label('Jenis Kontrak')
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
                            ]),

                        Forms\Components\Tabs\Tab::make('Data Personal')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('place_birth')
                                            ->label('Tempat Lahir')
                                            ->maxLength(255),
                                        Forms\Components\DatePicker::make('date_birth')
                                            ->label('Tanggal Lahir'),
                                    ]),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('marital_status')
                                            ->label('Status Perkawinan')
                                            ->options([
                                                'single' => 'Belum Menikah',
                                                'married' => 'Menikah',
                                                'divorced' => 'Cerai',
                                                'widowed' => 'Janda/Duda',
                                            ]),
                                        Forms\Components\Select::make('gender')
                                            ->label('Jenis Kelamin')
                                            ->options([
                                                'male' => 'Laki-laki',
                                                'female' => 'Perempuan',
                                            ]),
                                    ]),
                                Forms\Components\Textarea::make('address')
                                    ->label('Alamat')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('phone_number')
                                            ->label('Nomor Telepon')
                                            ->tel()
                                            ->numeric()
                                            ->rules(['regex:/^[0-9+\-\s()]+$/'])
                                            ->placeholder('Contoh: 081234567890')
                                            ->helperText('Hanya boleh angka, +, -, spasi, dan kurung')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->rules(['email:rfc,dns'])
                                            ->placeholder('contoh@email.com')
                                            ->helperText('Format email yang valid')
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Jabatan & Pendidikan')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Forms\Components\Select::make('employee_position_id')
                                    ->label('Posisi/Jabatan')
                                    ->relationship('employeePosition', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                Forms\Components\Select::make('employment_status_id')
                                    ->label('Status Kepegawaian')
                                    ->relationship('employmentStatus', 'name')
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->preload(),
                                Forms\Components\Select::make('basic_salary_id')
                                    ->label('Golongan')
                                    ->relationship('basicSalaryGrade', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name . ' - Rp ' . number_format($record->basic_salary, 0, ',', '.'))
                                    ->helperText('Gaji pokok akan otomatis sesuai dengan golongan yang dipilih'),
                                Forms\Components\Select::make('employee_education_id')
                                    ->label('Tingkat Pendidikan')
                                    ->relationship('education', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih tingkat pendidikan')
                                    ->helperText('Tingkat pendidikan terakhir yang dimiliki'),
                            ]),

                        Forms\Components\Tabs\Tab::make('Tanggal & Departemen')
                            ->icon('heroicon-m-calendar-days')
                            ->schema([
                                Forms\Components\DatePicker::make('agreement_date_start')
                                    ->label('Tanggal Mulai Kontrak')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            // Otomatis set tanggal berakhir 2 tahun dari tanggal mulai
                                            $endDate = \Carbon\Carbon::parse($state)->addYears(2);
                                            $set('agreement_date_end', $endDate->format('Y-m-d'));
                                        }
                                    })
                                    ->helperText('Tanggal berakhir akan otomatis diset 2 tahun dari tanggal mulai'),
                                Forms\Components\DatePicker::make('agreement_date_end')
                                    ->label('Tanggal Berakhir Kontrak')
                                    ->helperText('Otomatis diisi berdasarkan tanggal mulai + 2 tahun')
                                    ->hidden(function (callable $get) {
                                        $agreementId = $get('agreement_id');
                                        $statusId = $get('employment_status_id');
                                        
                                        $isPkwtt = false;
                                        if ($agreementId) {
                                            $agreement = \App\Models\MasterEmployeeAgreement::find($agreementId);
                                            if ($agreement && (stripos($agreement->name, 'PKWTT') !== false || stripos($agreement->name, 'Tetap') !== false)) {
                                                $isPkwtt = true;
                                            }
                                        }
                                        
                                        if ($statusId) {
                                            $status = \App\Models\MasterEmployeeStatusEmployment::find($statusId);
                                            // Tetap sembunyikan jika Calon Pegawai atau Pegawai Tetap
                                            if ($status && (stripos($status->name, 'Calon Pegawai') !== false || stripos($status->name, 'Pegawai') !== false || stripos($status->name, 'Tetap') !== false)) {
                                                $isPkwtt = true;
                                            }
                                        }
                                        
                                        return $isPkwtt;
                                    }),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('departments_id')
                                            ->label('Bagian')
                                            ->relationship('department', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set) {
                                                // Reset sub department ketika department berubah
                                                $set('sub_department_id', null);
                                            }),
                                        Forms\Components\Select::make('sub_department_id')
                                            ->label('Sub Bagian')
                                            ->relationship('subDepartment', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->options(function (callable $get) {
                                                $departmentId = $get('departments_id');
                                                if (!$departmentId) {
                                                    return [];
                                                }

                                                return \App\Models\MasterSubDepartment::where('departments_id', $departmentId)
                                                    ->pluck('name', 'id')
                                                    ->toArray();
                                            })
                                            ->disabled(fn(callable $get) => !$get('departments_id'))
                                            ->helperText('Pilih departemen terlebih dahulu'),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Dokumen')
                            ->icon('heroicon-m-document-arrow-up')
                            ->schema([
                                Forms\Components\FileUpload::make('docs')
                                    ->label('Dokumen Kontrak')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('agreements')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240) // 10MB
                                    ->downloadable()
                                    ->openable()
                                    ->helperText('Upload file PDF maksimal 10MB'),
                                    
                                Forms\Components\Hidden::make('users_id')
                                    ->default(fn() => auth()->id() ?? 0),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Pegawai')
                    ->description(fn (EmployeeAgreement $record): string => "No: {$record->agreement_number}")
                    ->searchable(['name', 'agreement_number'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact')
                    ->label('Kontak')
                    ->getStateUsing(fn ($record) => $record->email . "\n" . $record->phone_number)
                    ->description(fn ($record) => $record->phone_number)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('masterAgreement.name')
                    ->label('Jenis & Status')
                    ->description(fn (EmployeeAgreement $record): string => $record->employmentStatus->name ?? '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('employeePosition.name')
                    ->label('Jabatan & Unit')
                    ->description(function (EmployeeAgreement $record) {
                        $unit = $record->department->name ?? '-';
                        $subUnit = $record->subDepartment->name ?? '';
                        return $unit . ($subUnit ? " / " . $subUnit : "");
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('education_grade')
                    ->label('Pendidikan & Gol')
                    ->getStateUsing(fn($record) => ($record->education->name ?? '-') . " / " . ($record->basicSalaryGrade->name ?? '-'))
                    ->description(fn($record) => $record->basic_salary ? 'Rp ' . number_format($record->basic_salary, 0, ',', '.') : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('agreement_period')
                    ->label('Periode Kontrak')
                    ->getStateUsing(function (EmployeeAgreement $record) {
                        $start = Carbon::parse($record->agreement_date_start)->format('d/m/Y');
                        $end = $record->agreement_date_end ? Carbon::parse($record->agreement_date_end)->format('d/m/Y') : 'Tetap';
                        return "{$start} - {$end}";
                    })
                    ->description(function (EmployeeAgreement $record) {
                        if (!$record->agreement_date_end) return 'Kontrak Tidak Terbatas';
                        $days = $record->days_remaining;
                        return $days > 0 ? "Sisa {$days} hari ({$record->contract_duration} thn)" : "Kontrak Berakhir";
                    })
                    ->color(fn($record) => !$record->agreement_date_end || $record->days_remaining > 30 ? 'success' : ($record->days_remaining > 0 ? 'warning' : 'danger'))
                    ->sortable(['agreement_date_end']),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('docs')
                    ->label('Dokumen')
                    ->formatStateUsing(fn($record) => $record->has_document ? 'Ada Dokumen' : 'Tidak Ada')
                    ->badge()
                    ->color(fn($record) => $record->has_document ? 'success' : 'gray')
                    ->url(fn($record) => $record->docs_url)
                    ->openUrlInNewTab(),
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
            ->defaultSort('agreement_date_end', 'asc')
            ->headerActions([
                Tables\Actions\Action::make('generate_report')
                    ->label('Cetak Report PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function () {
                        return static::generatePDFReport();
                    })
                    ->tooltip('Generate laporan kontrak pegawai dalam format PDF'),

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
                        $url = route('report.contract-schedule', ['year' => $data['year']]);
                        $livewire->js("window.open('{$url}', '_blank')");
                    })
                    ->tooltip('Cetak jadwal kontrak yang berakhir pada tahun terpilih'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('agreement_id')
                    ->relationship('masterAgreement', 'name')
                    ->label('Jenis Kontrak'),
                Tables\Filters\SelectFilter::make('departments_id')
                    ->relationship('department', 'name')
                    ->label('Bagian'),
                Tables\Filters\SelectFilter::make('employment_status_id')
                    ->relationship('employmentStatus', 'name')
                    ->label('Status Kepegawaian'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Lihat'),
                    Tables\Actions\EditAction::make()
                        ->label('Edit'),
                    Tables\Actions\Action::make('renew_contract')
                        ->label('Perpanjang Kontrak')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(
                            fn(EmployeeAgreement $record): bool =>
                                $record->is_active &&
                                $record->masterAgreement &&
                                stripos($record->masterAgreement->name, 'PKWT') !== false &&
                                stripos($record->masterAgreement->name, 'PKWTT') === false
                        )
                    ->form([
                        Forms\Components\Placeholder::make('current_contract_info')
                            ->label('Kontrak Saat Ini')
                            ->content(
                                fn(EmployeeAgreement $record): string =>
                                "Kontrak: {$record->agreement_number}\n" .
                                    "Periode: " . Carbon::parse($record->agreement_date_start)->format('d/m/Y') .
                                    " - " . Carbon::parse($record->agreement_date_end)->format('d/m/Y') . "\n" .
                                    "Durasi: {$record->contract_duration} tahun"
                            ),

                        Forms\Components\DatePicker::make('new_start_date')
                            ->label('Tanggal Mulai Kontrak Baru')
                            ->required()
                            ->default(
                                fn(EmployeeAgreement $record) =>
                                Carbon::parse($record->agreement_date_end)->addDay()
                            )
                            ->minDate(
                                fn(EmployeeAgreement $record) =>
                                Carbon::parse($record->agreement_date_end)->addDay()
                            )
                            ->helperText('Biasanya dimulai sehari setelah kontrak lama berakhir'),

                        Forms\Components\TextInput::make('new_duration')
                            ->label('Durasi Kontrak Baru (tahun)')
                            ->numeric()
                            ->required()
                            ->default(2)
                            ->minValue(1)
                            ->maxValue(2)
                            ->helperText('Maksimal 2 tahun sesuai peraturan PKWT'),

                        Forms\Components\Textarea::make('renewal_notes')
                            ->label('Catatan Perpanjangan')
                            ->rows(3),

                        Forms\Components\FileUpload::make('docs')
                            ->label('Dokumen Kontrak Baru (PDF)')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('agreements')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->required()
                            ->helperText('Upload file PDF SK Perpanjangan Kontrak, maksimal 10MB'),
                    ])
                    ->action(function (EmployeeAgreement $record, array $data): void {
                        // Validasi PKWT maksimal 2 tahun
                        if ($data['new_duration'] > 2) {
                            Notification::make()
                                ->danger()
                                ->title('Validasi Gagal')
                                ->body('Durasi kontrak PKWT maksimal 2 tahun sesuai peraturan ketenagakerjaan.')
                                ->persistent()
                                ->send();
                            return;
                        }

                        $startDate = Carbon::parse($data['new_start_date']);
                        $endDate = $startDate->copy()->addYears($data['new_duration'])->subDay();

                        // Buat kontrak baru
                        $newContract = EmployeeAgreement::create([
                            'employees_id' => $record->employees_id,
                            'agreement_number' => 'KTR-' . strtoupper(uniqid()),
                            'name' => $record->name,
                            'agreement_id' => $record->agreement_id,
                            'place_birth' => $record->place_birth,
                            'date_birth' => $record->date_birth,
                            'marital_status' => $record->marital_status,
                            'gender' => $record->gender,
                            'address' => $record->address,
                            'phone_number' => $record->phone_number,
                            'email' => $record->email,
                            'employee_position_id' => $record->employee_position_id,
                            'employment_status_id' => $record->employment_status_id,
                            'basic_salary_id' => $record->basic_salary_id,
                            'employee_education_id' => $record->employee_education_id,
                            'departments_id' => $record->departments_id,
                            'sub_department_id' => $record->sub_department_id,
                            'agreement_date_start' => $startDate,
                            'agreement_date_end' => $endDate,
                            'contract_duration' => $data['new_duration'],
                            'docs' => $data['docs'],
                            'is_active' => true,
                            'users_id' => auth()->id() ?? 0,
                        ]);

                        // Update kontrak lama menjadi tidak aktif
                        $record->update(['is_active' => false]);

                        Notification::make()
                            ->success()
                            ->title('Kontrak berhasil diperpanjang')
                            ->body("Kontrak baru: {$newContract->agreement_number}")
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->label('Lihat Kontrak Baru')
                                    ->url(route('filament.employee.resources.employee-agreements.view', ['record' => $newContract->id]))
                                    ->button(),
                            ])
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Perpanjang Kontrak Kerja')
                    ->modalDescription('Kontrak baru akan dibuat dan kontrak lama akan dinonaktifkan.')
                    ->modalSubmitActionLabel('Perpanjang Kontrak'),
                
                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            EmployeeAgreementResource\Widgets\EmployeeAgreementStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeAgreements::route('/'),
            'create' => Pages\CreateEmployeeAgreement::route('/create'),
            'view' => Pages\ViewEmployeeAgreement::route('/{record}'),
            'edit' => Pages\EditEmployeeAgreement::route('/{record}/edit'),
        ];
    }
}
